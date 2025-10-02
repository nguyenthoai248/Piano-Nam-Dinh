<?php
/**
 * Custom Search Form with dropdown
 */
?>
<form role="search" method="get" class="custom-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">

  <!-- Dropdown chọn loại nội dung -->
  <select name="post_type" class="search-type">
    <option value="">Tất cả</option>
    <option value="product">Sản phẩm</option>
    <option value="post">Bài viết</option>
    <option value="page">Trang</option>
  </select>

  <!-- Ô search -->
  <input type="search" class="search-field" placeholder="Tìm kiếm..." value="<?php echo get_search_query(); ?>" name="s" />

  <!-- Nút submit -->
  <button type="submit" class="search-submit" aria-label="Tìm kiếm">
    <i class="fas fa-search"></i>
  </button>
</form>
