<?php
/**
 * Front page template
 */
get_header();
?>

<main id="main" class="site-main">
  <?php get_template_part('template-parts/homepage', 'hero'); ?>
  <?php get_template_part('template-parts/homepage', 'categories'); ?>
  <?php get_template_part('template-parts/homepage', 'featured'); ?>
  <?php get_template_part('template-parts/homepage', 'products'); ?>
  <?php get_template_part('template-parts/homepage', 'services'); ?>
  <?php get_template_part('template-parts/homepage', 'blog'); ?>
</main>

<?php
get_footer();
