<?php
$cats = get_terms(array(
  'taxonomy' => 'product_cat',
  'hide_empty' => true,
  'parent' => 0,
  'number' => 6
));
if ( $cats && ! is_wp_error( $cats ) ) : ?>
<section class="hp-categories">
  <div class="container">
    <div class="hp-cat-grid">
      <?php foreach ( $cats as $cat ) :
        $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
        $img = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : get_stylesheet_directory_uri() . '/assets/img/cat-placeholder.png';
        $link = get_term_link($cat);
      ?>
        <a class="hp-cat-card" href="<?php echo esc_url($link); ?>">
          <div class="hp-cat-thumb"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($cat->name); ?>"></div>
          <div class="hp-cat-name"><?php echo esc_html($cat->name); ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
