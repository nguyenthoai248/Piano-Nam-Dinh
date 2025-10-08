<?php
$blog = new WP_Query([
  'posts_per_page' => 4, // hiển thị tối đa 4 bài viết
  'post_status' => 'publish'
]);

if ($blog->have_posts()): ?>
  <section class="hp-blog">
    <div class="container">

      <h2 class="section-title text-center">Tin tức & Hướng dẫn</h2>

      <div class="hp-blog-grid">
        <?php while ($blog->have_posts()):
          $blog->the_post(); ?>
          <article class="hp-blog-card">
            <a href="<?php the_permalink(); ?>" class="thumb">
              <?php
              if (has_post_thumbnail()) {
                the_post_thumbnail('medium_large', ['loading' => 'lazy']);
              } else {
                $placeholder = 'http://pianonamdinh.com.vn.local/wp-content/uploads/2025/10/cat-placeholder.png';
                echo '<img src="' . esc_url($placeholder) . '" alt="No image" loading="lazy">';
              }
              ?>
            </a>
            <div class="hp-blog-content">
              <h3 class="hp-blog-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h3>
              <p class="hp-blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 22); ?></p>
              <div class="hp-blog-meta">
                <span class="date"><?php echo get_the_date(); ?></span>
                <a href="<?php the_permalink(); ?>" class="readmore">Đọc thêm</a>
              </div>
            </div>
          </article>
        <?php endwhile;
        wp_reset_postdata(); ?>
      </div>

      <div class="hp-blog-footer">
        <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>"
          class="btn btn-outline-primary btn-view-all">Xem tất cả bài viết →</a>
      </div>

    </div>
  </section>
<?php endif; ?>