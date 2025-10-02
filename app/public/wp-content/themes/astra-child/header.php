<?php
/**
 * Custom Header for Astra Child
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php wp_body_open(); ?>
    <!-- ✅ TOP BAR -->
    <?php astra_child_topbar(); ?>

    <header id="site-header" class="site-header">

        <div class="site-mainheader">
            <div class="mainheader-inner">

                <!-- Logo -->
                <div class="header-logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <?php
                        if (has_custom_logo()) {
                            the_custom_logo();
                        } else {
                            bloginfo('name');
                        }
                        ?>
                    </a>
                </div>

                <!-- Search -->
                <div class="header-search">
                    <?php get_search_form(); ?>
                </div>

                <!-- Header Buttons -->
                <div class="header-buttons">
                    <a href="/khuyen-mai" class="header-btn">
                        <span class="icon">🎁</span>
                        <span class="text">Khuyến mãi</span>
                    </a>
                    <a href="/showroom" class="header-btn">
                        <span class="icon">🏬</span>
                        <span class="text">Showroom</span>
                    </a>
                    <a href="/kiem-tra-don-hang" class="header-btn">
                        <span class="icon">📦</span>
                        <span class="text">Kiểm tra đơn hàng</span>
                    </a>
                </div>

            </div>
        </div>


        <!-- ✅ MENU -->
        <!-- Main Navigation -->
        <nav class="site-main-navigation">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'main-menu',
            ));
            ?>
        </nav>
    </header>