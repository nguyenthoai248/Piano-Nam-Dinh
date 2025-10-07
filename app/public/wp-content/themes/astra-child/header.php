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
                        <span class="icon">
                            <!-- 🎁 Gift (Khuyến mãi) -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-icon">
                                <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                <rect x="2" y="7" width="20" height="5"></rect>
                                <line x1="12" y1="22" x2="12" y2="7"></line>
                                <path d="M12 7a3 3 0 1 1 3-3c0 3-3 3-3 3z"></path>
                                <path d="M12 7a3 3 0 1 0-3-3c0 3 3 3 3 3z"></path>
                            </svg>
                        </span>
                        <span class="text">Khuyến mãi</span>
                    </a>

                    <a href="/showroom" class="header-btn">
                        <span class="icon">
                            <!-- 🏬 Building (Showroom) -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-icon">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <path d="M9 22V12h6v10"></path>
                                <path d="M9 8h.01M15 8h.01"></path>
                            </svg>
                        </span>
                        <span class="text">Showroom</span>
                    </a>

                    <a href="/kiem-tra-don-hang" class="header-btn">
                        <span class="icon">
                            <!-- 📦 Package (Kiểm tra đơn hàng) -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="header-icon">
                                <path
                                    d="M21 16V8a2 2 0 0 0-1-1.73L12 2 4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73L12 22l8-4.27a2 2 0 0 0 1-1.73z">
                                </path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </span>
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