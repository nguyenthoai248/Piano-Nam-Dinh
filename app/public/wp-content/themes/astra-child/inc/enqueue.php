<?php
function astra_child_enqueue_assets() {
    // CSS
    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/assets/css/custom.css', array(), '1.0');
    wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), null);

    // JS
    wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array(), null, true);
    wp_enqueue_script('astra-child-main', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('swiper-js','jquery'), '1.0', true);

    // Optional: localize ajax url
    wp_localize_script('astra-child-main', 'astraChild', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_assets');
