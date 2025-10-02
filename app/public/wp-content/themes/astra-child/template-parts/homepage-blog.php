<?php
$blog = new WP_Query(array('posts_per_page'=>3));
if ($blog->have_posts()) : ?>
<section class="hp-blog">
  <div class="container">
    <h2>Tin tức & Hướng dẫn</h2>
    <div class="hp-blog-grid">
      <?php while($blog->have_posts()): $blog->the_post(); ?>
        <article class="hp-blog-card">
          <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
          <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>
