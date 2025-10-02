<?php
// New arrivals
$new = wc_get_products(array('limit'=>8,'orderby'=>'date','order'=>'DESC'));

// On sale
$sale_ids = wc_get_product_ids_on_sale();
$on_sale = !empty($sale_ids) ? wc_get_products(array('include'=>$sale_ids,'limit'=>8)) : array();
?>
<section class="hp-products">
  <div class="container">
    <?php if($new): ?>
    <h2>Sản phẩm mới</h2>
    <div class="hp-product-grid">
      <?php foreach($new as $p): ?>
        <div class="product-card">
          <a class="thumb" href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo $p->get_image(); ?></a>
          <h3><a href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo esc_html($p->get_name()); ?></a></h3>
          <div class="price"><?php echo $p->get_price_html(); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($on_sale): ?>
    <h2>Đang giảm giá</h2>
    <div class="hp-product-grid">
      <?php foreach($on_sale as $p): ?>
        <div class="product-card">
          <a class="thumb" href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo $p->get_image(); ?></a>
          <h3><a href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo esc_html($p->get_name()); ?></a></h3>
          <div class="price"><?php echo $p->get_price_html(); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>
