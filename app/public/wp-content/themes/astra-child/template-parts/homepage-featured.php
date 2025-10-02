<?php
// lấy sản phẩm featured (WooCommerce)
$featured = wc_get_products(array(
  'limit' => 8,
  'status' => 'publish',
  'featured' => true
));

if ( ! empty($featured) ) : ?>
<section class="hp-featured">
  <div class="container">
    <h2>Sản phẩm Nổi bật</h2>

    <div class="hp-product-grid">
      <?php foreach ( $featured as $p ) : 
        $prod_id = $p->get_id();
        $img = $p->get_image('medium'); // returns <img> tag
      ?>
      <div class="product-card">
        <a class="thumb" href="<?php echo esc_url($p->get_permalink()); ?>">
          <?php echo $img; ?>
        </a>
        <h3 class="title"><a href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo esc_html($p->get_name()); ?></a></h3>
        <div class="price"><?php echo wp_kses_post($p->get_price_html()); ?></div>
        <a class="btn btn-add" href="<?php echo esc_url( $p->get_permalink() . '?add-to-cart=' . $prod_id ); ?>">Thêm vào giỏ</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
