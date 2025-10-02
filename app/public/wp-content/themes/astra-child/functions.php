<?php
/**
 * Functions for Astra Child Theme
 */

// Enqueue parent + child styles
function astra_child_enqueue_scripts() {
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
        'astra-child-custom-js',
        get_stylesheet_directory_uri() . '/assets/js/custom.js',
        array(),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_scripts');


// Customizer Settings cho Hero Image
function astra_child_customize_register($wp_customize) {
    $wp_customize->add_section('home_section', array(
        'title'    => __('Home Settings', 'astra-child'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('home_hero_image');

    $wp_customize->add_control(new WP_Customize_Image_Control(
        $wp_customize,
        'home_hero_image_control',
        array(
            'label'    => __('Hero image', 'astra-child'),
            'section'  => 'home_section',
            'settings' => 'home_hero_image',
        )
    ));
}
add_action('customize_register','astra_child_customize_register');

// Topbar custom
function astra_child_topbar() {
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
function astra_child_register_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'astra-child'),
    ));
}
add_action('after_setup_theme', 'astra_child_register_menus');

