<?php
/**
 * Functions for Astra Child Theme
 */

// Enqueue parent + child styles
function astra_child_enqueue_scripts()
{
    // Load parent theme CSS
    wp_enqueue_style(
        'astra-parent-style',
        get_template_directory_uri() . '/style.css'
    );

    // Load child theme CSS
    wp_enqueue_style(
        'astra-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-parent-style'),
        wp_get_theme()->get('Version')
    );

    // Load Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

    wp_enqueue_script(
        'homepage-ajax',
        get_stylesheet_directory_uri() . '/assets/js/custom.js',
        ['jquery'], // cần có jQuery làm dependency
        null,
        true // load ở footer
    );

    wp_localize_script('homepage-ajax', 'wpData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
    ]);

}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_scripts');


// Customizer Settings cho Hero Image
function astra_child_customize_register($wp_customize)
{
    $wp_customize->add_section('home_section', array(
        'title' => __('Home Settings', 'astra-child'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('home_hero_image');

    $wp_customize->add_control(new WP_Customize_Image_Control(
        $wp_customize,
        'home_hero_image_control',
        array(
            'label' => __('Hero image', 'astra-child'),
            'section' => 'home_section',
            'settings' => 'home_hero_image',
        )
    ));
}
add_action('customize_register', 'astra_child_customize_register');

// Topbar custom
function astra_child_topbar()
{
    ?>
    <div class="site-topbar">
        <div class="topbar-inner">
            <div class="topbar-item">
                🚚 Giao hàng toàn quốc
            </div>
            <div class="topbar-item">
                🕒 08:00 - 21:00 hàng ngày
            </div>
            <div class="topbar-item">
                📞 Hotline: <a href="tel:0123456789">070 3553 999</a>
            </div>
            <div class="topbar-item login">
                👤 <a href="/my-account">Đăng nhập</a>
            </div>
        </div>
    </div>
    <?php
}
add_action('astra_masthead_top', 'astra_child_topbar');

// Register primary menu
function astra_child_register_menus()
{
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'astra-child'),
    ));
}
add_action('after_setup_theme', 'astra_child_register_menus');

add_action('wp_ajax_load_homepage_products', 'ajax_load_homepage_products');
add_action('wp_ajax_nopriv_load_homepage_products', 'ajax_load_homepage_products');

function ajax_load_homepage_products()
{
    error_log('🧩 AJAX type = ' . ($_POST['type'] ?? 'null'));
    $type = sanitize_text_field($_POST['type'] ?? 'featured');
    $cat = sanitize_text_field($_POST['category'] ?? 'all');
    $paged = intval($_POST['paged'] ?? 1);

    $args = [
        'post_type' => 'product',
        'posts_per_page' => 8,
        'paged' => $paged
    ];

    switch ($type) {
        case 'featured':
            $args['tax_query'][] = [
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => 'featured',
                'operator' => 'IN',
            ];
            break;
        case 'new':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'best_selling':
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
    }

    if ($cat !== 'all') {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $cat,
        ];
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()):
        while ($query->have_posts()):
            $query->the_post();
            global $product;
            $price = $product->get_regular_price();
            $sale = $product->get_sale_price();
            $discount = ($price && $sale) ? round((($price - $sale) / $price) * 100) : 0;
            ?>
            <div class="product-card">
                <div class="product-image">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) {
                            echo get_the_post_thumbnail(get_the_ID(), 'medium', ['loading' => 'lazy']);
                        } else {
                            echo '<img src="https://via.placeholder.com/300x300?text=No+Image" loading="lazy">';
                        } ?>
                    </a>
                    <?php if ($discount > 0): ?>
                        <span class="sale-badge">-<?= esc_html($discount) ?>%</span>
                    <?php endif; ?>
                </div>
                <h3 class="product-title"><?php the_title(); ?></h3>
                <div class="product-prices">
                    <?php if ($sale): ?>
                        <span class="price-sale"><?= wc_price($sale) ?></span>
                        <span class="price-regular"><?= wc_price($price) ?></span>
                    <?php else: ?>
                        <span class="price-normal"><?= wc_price($price) ?></span>
                    <?php endif; ?>
                </div>
                <a href="?add-to-cart=<?= esc_attr($product->get_id()); ?>" class="add-to-cart">Thêm vào giỏ</a>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    else:
        echo '<p>Không có sản phẩm.</p>';
    endif;

    $html = ob_get_clean();

    $pagination = paginate_links([
        'total' => $query->max_num_pages,
        'current' => $paged,
        'type' => 'array',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ]);

    $pag_html = '';
    if ($pagination) {
        foreach ($pagination as $p) {
            // Kiểm tra nếu có pattern /page/{n}
            if (preg_match('/page\/(\d+)/', $p, $m)) {
                $page_num = intval($m[1]);
            } else {
                // Nếu không có, mặc định là page 1 (thường là prev hoặc first page)
                $page_num = 1;
            }

            // Thay href thành data-page
            $p = preg_replace('/href="[^"]+"/', 'href="#" data-page="' . $page_num . '"', $p);
            $pag_html .= $p;
        }
    }

    echo json_encode(['html' => $html, 'pagination' => $pag_html]);
    wp_die();
}

