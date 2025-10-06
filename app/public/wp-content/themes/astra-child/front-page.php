<?php
/**
 * Front page template
 */
get_header();
?>

<main id="main" class="site-main">
  <?php get_template_part('template-parts/homepage', 'hero'); ?>
  <?php //get_template_part('template-parts/homepage', 'categories'); ?>
  <?php //get_template_part('template-parts/homepage', 'featured'); ?>
  <?php //get_template_part('template-parts/homepage', 'products'); ?>
  <?php
  get_template_part(
    'template-parts/homepage-products-section',
    null,
    [
      'title' => 'Sản phẩm nổi bật',
      'subtitle' => 'Được nhiều khách hàng yêu thích',
      'type' => 'featured'
    ]
  );

  get_template_part(
    'template-parts/homepage-products-section',
    null,
    [
      'title' => 'Sản phẩm mới',
      'subtitle' => 'Vừa cập nhật gần đây',
      'type' => 'new'
    ]
  );

  get_template_part(
    'template-parts/homepage-products-section',
    null,
    [
      'title' => 'Sản phẩm bán chạy',
      'subtitle' => 'Bán nhiều nhất tuần này',
      'type' => 'best_selling'
    ]
  );
  ?>
  <?php get_template_part('template-parts/homepage', 'services'); ?>
  <?php get_template_part('template-parts/homepage', 'blog'); ?>
</main>

<?php
get_footer();
