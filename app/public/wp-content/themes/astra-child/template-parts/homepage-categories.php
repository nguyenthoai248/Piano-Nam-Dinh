<?php
/**
 * Template Part: Homepage Categories Highlight (Version 4)
 * Features: Tabs filter + Lazy load + jQuery fade + "Load More" pagination for All tab
 */
if (!defined('ABSPATH')) exit;
?>

<section class="categories-highlight">
  <div class="container">
    <h2 class="section-title">Danh mục nổi bật</h2>
    <p class="section-subtitle">Khám phá những dòng sản phẩm được yêu thích nhất</p>

    <?php
    $categories = get_terms(array(
      'taxonomy' => 'product_cat',
      'hide_empty' => true,
    ));
    ?>

    <div class="category-tabs">
      <ul class="tabs">
        <li class="tab active" data-cat="all">Tất cả</li>
        <?php foreach ($categories as $cat) : ?>
          <li class="tab" data-cat="<?php echo esc_attr($cat->slug); ?>">
            <?php echo esc_html($cat->name); ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="product-grid">
      <?php
      $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 50, // load sẵn 50 sp để có paging client-side
      );
      $products = new WP_Query($args);

      if ($products->have_posts()) :
        while ($products->have_posts()) : $products->the_post();
          global $product;
          $price     = $product->get_regular_price();
          $sale      = $product->get_sale_price();
          $discount  = $price && $sale ? round((($price - $sale) / $price) * 100) : 0;
          $cat_slugs = join(' ', wp_get_post_terms(get_the_ID(), 'product_cat', array('fields' => 'slugs')));
      ?>
          <div class="product-card" data-cat="<?php echo esc_attr($cat_slugs); ?>">
            <div class="product-image">
              <a href="<?php the_permalink(); ?>">
                <?php
                if (has_post_thumbnail()) {
                  echo get_the_post_thumbnail(get_the_ID(), 'medium', array('loading' => 'lazy'));
                } else {
                  echo '<img src="https://via.placeholder.com/600x600?text=No+Image" alt="No image" loading="lazy">';
                }
                ?>
              </a>
              <?php if ($discount > 0) : ?>
                <span class="sale-badge">-<?php echo esc_html($discount); ?>%</span>
              <?php endif; ?>
            </div>

            <h3 class="product-title"><?php the_title(); ?></h3>

            <div class="product-prices">
              <?php if ($sale) : ?>
                <span class="price-sale"><?php echo wc_price($sale); ?></span>
                <span class="price-regular"><?php echo wc_price($price); ?></span>
              <?php else : ?>
                <span class="price-normal"><?php echo wc_price($price); ?></span>
              <?php endif; ?>
            </div>

            <a href="<?php echo esc_url('?add-to-cart=' . $product->get_id()); ?>" class="add-to-cart">Thêm vào giỏ</a>
          </div>
      <?php
        endwhile;
        wp_reset_postdata();
      else :
        echo '<p>Chưa có sản phẩm nào.</p>';
      endif;
      ?>
      <p class="no-products" style="display:none;">Không có sản phẩm trong danh mục này.</p>
    </div>

    <div class="load-more-wrap" style="margin-top:30px;">
      <button class="load-more-btn">Xem thêm</button>
    </div>
  </div>
</section>

<script>
jQuery(function($){
  const $tabs = $('.category-tabs .tab');
  const $products = $('.product-card');
  const $noProducts = $('.no-products');
  const $loadMore = $('.load-more-wrap');
  const $btn = $('.load-more-btn');
  const perPage = 8; // số sản phẩm mỗi lần hiển thị
  let currentCat = 'all';
  let visibleCount = perPage;

  function matchesCat($el, cat) {
    if(cat === 'all') return true;
    const cats = ($el.data('cat') || '').toString().trim();
    return (' ' + cats + ' ').indexOf(' ' + cat + ' ') !== -1;
  }

  function showProducts(cat, resetPage = true) {
    const $matched = $products.filter(function(){ return matchesCat($(this), cat); });
    $products.hide();
    $noProducts.hide();

    if ($matched.length === 0) {
      $noProducts.fadeIn(200);
      $loadMore.hide();
      return;
    }

    if (resetPage) visibleCount = perPage;

    $matched.slice(0, visibleCount).fadeIn(250);
    if (visibleCount < $matched.length) $loadMore.show();
    else $loadMore.hide();
  }

  // initial
  showProducts('all');

  // tab click
  $tabs.on('click', function(){
    const cat = $(this).data('cat');
    currentCat = cat;
    $tabs.removeClass('active');
    $(this).addClass('active');
    showProducts(cat);
  });

  // load more
  $btn.on('click', function(){
    visibleCount += perPage;
    showProducts(currentCat, false);
  });
});
</script>

<style>
.categories-highlight {
  padding: 60px 0;
  text-align: center;
  background: #fff;
}
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}
.section-title {
  font-size: 1.9rem;
  font-weight: 700;
  color: #111;
  margin-bottom: 6px;
}
.section-subtitle {
  color: #555;
  font-size: 1rem;
  margin-bottom: 28px;
}

/* TABS */
.category-tabs {
  margin-bottom: 28px;
}
.category-tabs .tabs {
  display: flex;
  justify-content: center;
  gap: 20px;
  list-style: none;
  padding: 0;
  border-bottom: 2px solid #eee;
  flex-wrap: wrap;
}
.category-tabs .tab {
  cursor: pointer;
  padding: 10px 6px;
  font-weight: 600;
  color: #333;
  position: relative;
  transition: color 0.2s;
}
.category-tabs .tab.active { color: #000; }
.category-tabs .tab.active::after {
  content: "";
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 4px;
  background: #ffdd00;
  border-radius: 3px;
}

/* PRODUCT GRID */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
}
.product-card {
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  text-align: left;
  padding-bottom: 12px;
  display: none;
}
.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.09);
}

/* IMAGE */
.product-image { position: relative; }
.product-image img {
  width: 100%;
  display: block;
  height: 220px;
  object-fit: cover;
  border-bottom: 4px solid #ffdd00;
}
.sale-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: #ffdd00;
  color: #000;
  padding: 6px 10px;
  font-weight: 700;
  border-radius: 6px;
  font-size: 0.85rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

/* INFO */
.product-title {
  font-size: 0.98rem;
  margin: 12px 12px 8px;
  line-height: 1.3;
  height: 2.5em;
  overflow: hidden;
}
.product-prices {
  margin: 0 12px 12px;
  font-size: 0.95rem;
}
.price-sale { color: #e53946; font-weight: 700; margin-right: 8px; }
.price-regular { color: #777; text-decoration: line-through; }
.price-normal { color: #111; font-weight: 600; }

/* BUTTON */
.add-to-cart {
  display: inline-block;
  margin: 0 12px 12px;
  padding: 10px 14px;
  background: #ffdd00;
  color: #000;
  font-weight: 700;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.2s;
}
.add-to-cart:hover { background: #000; color: #fff; }

/* LOAD MORE */
.load-more-btn {
  background: #ffdd00;
  color: #000;
  border: none;
  font-weight: 700;
  font-size: 1rem;
  padding: 12px 30px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.25s;
}
.load-more-btn:hover { background: #000; color: #fff; }

/* RESPONSIVE */
@media (max-width: 768px) {
  .section-title { font-size: 1.5rem; }
  .product-image img { height: 180px; }
}
</style>
