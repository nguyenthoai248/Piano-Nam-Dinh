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

function pianonamdinh_enqueue_assets()
{
    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');

    // Font Awesome (để social icons)
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css');

    // Bootstrap JS (nếu muốn dropdown, collapse…)
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'pianonamdinh_enqueue_assets');

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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="topbar-icon">
                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2"></rect>
                    <path d="M16 8h4l3 3v5a2 2 0 0 1-2 2h-1"></path>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>

                <span class="topbar-text">Giao hàng toàn quốc</span>
            </div>

            <div class="topbar-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#111"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="topbar-icon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>

                <span class="topbar-text">08:00 - 21:00 hàng ngày</span>
            </div>

            <div class="topbar-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#111"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="topbar-icon">
                    <path
                        d="M22 16.92V21a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 3.07 12.8 19.79 19.79 0 0 1 0 4.18 2 2 0 0 1 2 2h4.09a2 2 0 0 1 2 1.72c.13.96.38 1.9.73 2.79a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 7 7l1.3-1.3a2 2 0 0 1 2.11-.45c.89.35 1.83.6 2.79.73a2 2 0 0 1 1.72 2.03z">
                    </path>
                </svg>

                <span class="topbar-text">
                    Hotline: <a href="tel:0703553999">070 3553 999</a>
                </span>
            </div>

            <div class="topbar-item login">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#111"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="topbar-icon">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span class="topbar-text"><a href="/my-account">Đăng nhập</a></span>
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

// Thay đổi nút thêm vào giỏ khi hết hàng
add_filter('woocommerce_loop_add_to_cart_link', function ($button, $product) {
    if (!$product->is_in_stock()) {
        $button = '<button class="btn btn-secondary out-of-stock-btn" disabled>Hết hàng</button>';
    }
    return $button;
}, 10, 2);
// Thêm label "Hết hàng" trên sản phẩm
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if (!$product->is_in_stock()) {
        echo '<span class="out-of-stock-badge">Hết hàng</span>';
    }
}, 5);


function ajax_load_homepage_products()
{
    // error_log('🧩 AJAX type = ' . ($_POST['type'] ?? 'null'));
    $type = sanitize_text_field($_POST['type'] ?? 'featured');
    $cat = sanitize_text_field($_POST['category'] ?? 'all');
    $paged = intval($_POST['paged'] ?? 1);

    $args = [
        'post_type' => 'product',
        'posts_per_page' => 8,
        'paged' => $paged,
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
                        <div class="sale-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 46" fill="#fcb900">
                                <g>
                                    <path
                                        d="M34.0100234,1 C16.6242434,2.98840527 7.21988603,4.01646794 5.79695148,4.08418802 C3.75126304,4.28472303 1.00586332,6.25951457 1.00586332,10.3051192 C1.00586332,13.002189 1.00586332,22.0757609 1.00586332,37.5258348 C0.949118982,38.4163211 1.30424713,39.162947 2.07124777,39.7657128 C2.88071146,40.4580699 8.00169748,42.7876344 14.1646466,45.2211667 C21.0663671,43.7580483 32.5338169,39.7657128 32.5338169,39.7657128 C32.5338169,39.7657128 33.9877953,39.5099349 34.0099313,37.5258348 C34.0099313,34.3470094 34.009962,22.1717312 34.0100234,1 Z"
                                        opacity=".33"></path>
                                    <path
                                        d="M33.0100234,0 C15.6242434,1.98840527 6.21988603,3.01646794 4.79695148,3.08418802 C2.75126304,3.28472303 0.00586331918,5.25951457 0.00586331918,9.30511923 C0.00586331918,12.002189 0.00586331918,21.0757609 0.00586331918,36.5258348 C-0.0508810181,37.4163211 0.304247133,38.162947 1.07124777,38.7657128 C1.88071146,39.4580699 7.00169748,41.7876344 13.1646466,44.2211667 C20.0663671,42.7580483 31.5338169,38.7657128 31.5338169,38.7657128 C31.5338169,38.7657128 32.9877953,38.5099349 33.0099313,36.5258348 C33.0099313,33.3470094 33.009962,21.1717312 33.0100234,0 Z">
                                    </path>
                                </g>
                            </svg>
                            <span class="sale-badge-text">Giảm <?= esc_html($discount) ?>%</span>
                        </div>
                    <?php endif; ?>
                    <?php
                    // Badge hết hàng
                    if (!$product->is_in_stock()): ?>
                        <span class="out-of-stock-badge">Hết hàng</span>
                    <?php endif; ?>
                </div>
                <div class="product-title text-uppercase"><?php the_title(); ?></div>
                <div class="product-prices">
                    <?php if ($sale): ?>
                        <span class="price-sale"><?= wc_price($sale) ?></span>
                        <span class="price-regular"><?= wc_price($price) ?></span>
                    <?php else: ?>
                        <span class="price-normal"><?= wc_price($price) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Rating -->
                <div class="product-rating d-flex justify-content-center mb-2">
                    <div class="d-inline-flex align-items-center">
                        <?php
                        $rating = 0; // giá trị trung bình rating
                        $max_stars = 5;
                        for ($i = 1; $i <= $max_stars; $i++):
                            if ($i <= $rating): ?>
                                <i class="fa-solid fa-star text-warning me-1"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star text-warning me-1"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="ms-2 text-muted small">0 đánh giá</span>
                    </div>
                </div>

                <?php if ($product->is_in_stock()): ?>
                    <!-- <a href="?add-to-cart=<?= esc_attr($product->get_id()); ?>" class="btn-add-to-cart mt-2 mb-4">
                        <svg class="cart-icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 321.2 321.2" width="16" height="16">
                            <g>
                                <g>
                                    <path fill="currentColor"
                                        d="M306.4,313.2l-24-223.6c-0.4-3.6-3.6-6.4-7.2-6.4h-44.4V69.6c0-38.4-31.2-69.6-69.6-69.6c-38.4,0-69.6,31.2-69.6,69.6 v13.6H46c-3.6,0-6.8,2.8-7.2,6.4l-24,223.6c-0.4,2,0.4,4,1.6,5.6c1.2,1.6,3.2,2.4,5.2,2.4h278c2,0,4-0.8,5.2-2.4 C306,317.2,306.8,315.2,306.4,313.2z M223.6,123.6c3.6,0,6.4,2.8,6.4,6.4c0,3.6-2.8,6.4-6.4,6.4c-3.6,0-6.4-2.8-6.4-6.4 C217.2,126.4,220,123.6,223.6,123.6z M106,69.6c0-30.4,24.8-55.2,55.2-55.2c30.4,0,55.2,24.8,55.2,55.2v13.6H106V69.6z M98.8,123.6c3.6,0,6.4,2.8,6.4,6.4c0,3.6-2.8,6.4-6.4,6.4c-3.6,0-6.4-2.8-6.4-6.4C92.4,126.4,95.2,123.6,98.8,123.6z M30,306.4 L52.4,97.2h39.2v13.2c-8,2.8-13.6,10.4-13.6,19.2c0,11.2,9.2,20.4,20.4,20.4c11.2,0,20.4-9.2,20.4-20.4c0-8.8-5.6-16.4-13.6-19.2 V97.2h110.4v13.2c-8,2.8-13.6,10.4-13.6,19.2c0,11.2,9.2,20.4,20.4,20.4c11.2,0,20.4-9.2,20.4-20.4c0-8.8-5.6-16.4-13.6-19.2V97.2 H270l22.4,209.2H30z" />
                                </g>
                            </g>
                        </svg>
                        <span class="cart-text">Thêm vào giỏ</span>
                    </a> -->

                    <a href="#" class="btn-add-to-cart mt-2 mb-4">
                        <svg class="cart-icon me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 321.2 321.2" width="16" height="16">
                            <g>
                                <g>
                                    <path fill="currentColor"
                                        d="M306.4,313.2l-24-223.6c-0.4-3.6-3.6-6.4-7.2-6.4h-44.4V69.6c0-38.4-31.2-69.6-69.6-69.6c-38.4,0-69.6,31.2-69.6,69.6 v13.6H46c-3.6,0-6.8,2.8-7.2,6.4l-24,223.6c-0.4,2,0.4,4,1.6,5.6c1.2,1.6,3.2,2.4,5.2,2.4h278c2,0,4-0.8,5.2-2.4 C306,317.2,306.8,315.2,306.4,313.2z M223.6,123.6c3.6,0,6.4,2.8,6.4,6.4c0,3.6-2.8,6.4-6.4,6.4c-3.6,0-6.4-2.8-6.4-6.4 C217.2,126.4,220,123.6,223.6,123.6z M106,69.6c0-30.4,24.8-55.2,55.2-55.2c30.4,0,55.2,24.8,55.2,55.2v13.6H106V69.6z M98.8,123.6c3.6,0,6.4,2.8,6.4,6.4c0,3.6-2.8,6.4-6.4,6.4c-3.6,0-6.4-2.8-6.4-6.4C92.4,126.4,95.2,123.6,98.8,123.6z M30,306.4 L52.4,97.2h39.2v13.2c-8,2.8-13.6,10.4-13.6,19.2c0,11.2,9.2,20.4,20.4,20.4c11.2,0,20.4-9.2,20.4-20.4c0-8.8-5.6-16.4-13.6-19.2 V97.2h110.4v13.2c-8,2.8-13.6,10.4-13.6,19.2c0,11.2,9.2,20.4,20.4,20.4c11.2,0,20.4-9.2,20.4-20.4c0-8.8-5.6-16.4-13.6-19.2V97.2 H270l22.4,209.2H30z" />
                                </g>
                            </g>
                        </svg>
                        <span class="cart-text">Liên hệ</span>
                    </a>
                <?php else: ?>
                    <a class="btn-out-of-stock  mt-2 mb-4 disabled">
                        <span class="cart-text">Hết hàng</span>
                    </a>
                <?php endif; ?>

            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    else:
        if ($paged === 1) {
            echo '<div class="no-products text-center py-5 w-100">
                <i class="fa-solid fa-box-open fa-2x text-muted mb-3"></i>
                <p class="text-muted mb-0">Không có sản phẩm nào trong danh mục này.</p>
              </div>';
        }
    endif;

    $html = ob_get_clean();

    // 🔥 Tính xem còn trang tiếp theo không
    $has_more = $query->max_num_pages > $paged;

    // ✅ Trả kết quả JSON gọn gàng
    wp_send_json([
        'html' => $html,
        'has_more' => $has_more
    ]);
    wp_die();

}

