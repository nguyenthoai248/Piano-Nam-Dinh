<?php
/**
 * Hero Banner Section
 */
?>

<section class="hero-banner">
  <div class="hero-inner">
    <?php if ( get_theme_mod('home_hero_image') ) : ?>
      <img src="<?php echo esc_url( get_theme_mod('home_hero_image') ); ?>" alt="Hero Banner">
    <?php else : ?>
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/hero.jpg" alt="Hero Banner">
    <?php endif; ?>
  </div>
</section>
