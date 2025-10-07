<?php
/**
 * Hero Banner Section
 */
?>

<section class="hero-banner">
  <div class="hero-inner">
    <?php if (get_theme_mod('home_hero_image')): ?>
      <img src="<?php echo esc_url(get_theme_mod('home_hero_image')); ?>" alt="Hero Banner">
    <?php else: ?>
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/hero.png" alt="Hero Banner">
    <?php endif; ?>
  </div>
  <div class="bannerTop-item__insert">
    <div class="container">
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/pianonamdinh_bannertop.jpg"
        alt="Khuyến mãi đặc biệt" class="banner-simple">
    </div>
  </div>

  <div class="promo-sub-banner">
    <div class="container text-center">
      <h2>Mua Nhạc Cụ Chính Hãng Với Mức Giá Cực Shock 🔥</h2>
    </div>
  </div>

</section>