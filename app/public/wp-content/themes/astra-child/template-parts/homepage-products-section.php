<?php
/**
 * Template Part: Homepage Products Section (AJAX + Tabs + LazyLoad)
 */

if (!defined('ABSPATH'))
    exit;

$args = wp_parse_args($args ?? [], [
    'title' => 'Sản phẩm nổi bật',
    'subtitle' => '',
    'type' => 'featured',
]);

error_log(print_r($args, true));

$title = $args['title'];
$subtitle = $args['subtitle'];
$type = $args['type'];

$categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
?>

<section class="homepage-section" data-type="<?= esc_attr($type) ?>">
    <div class="container">
        <h2 class="section-title"><?= esc_html($title) ?></h2>
        <?php if ($subtitle): ?>
            <p class="section-subtitle"><?= esc_html($subtitle) ?></p><?php endif; ?>

        <div class="category-tabs">
            <ul class="tabs">
                <li class="tab active" data-cat="all">Tất cả</li>
                <?php foreach ($categories as $cat): ?>
                    <li class="tab" data-cat="<?= esc_attr($cat->slug) ?>"><?= esc_html($cat->name) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="ajax-products">
            <div class="loading-spinner"></div>
            <div class="product-grid"></div>
            <div class="pagination"></div>
        </div>
    </div>
</section>

<script>
    jQuery(document).ready(function ($) {
        function loadProducts($section, cat = "all", page = 1) {
            const type = $section.data("type");
            const $grid = $section.find(".product-grid");
            const $pagination = $section.find(".pagination");
            const $spinner = $section.find(".loading-spinner");

            $grid.addClass("loading");
            $spinner.addClass("active");

            $grid.fadeOut(200);
            $.post(
                wpData.ajaxurl,
                {
                    action: "load_homepage_products",
                    type: type,
                    category: cat,
                    paged: page,
                },
                function (response) {
                    try {
                        const data = JSON.parse(response);
                        $grid
                            .removeClass("loading")
                            .hide()
                            .html(data.html)
                            .fadeIn(400)
                            .addClass("fade-in");
                        $pagination.html(data.pagination);
                    } catch (e) {
                        console.error("Lỗi parse JSON:", e, response);
                    }
                    $spinner.removeClass("active");
                }
            );
        }

        // Khi click chọn category
        $(document).on("click", ".category-tabs .tab", function () {
            const $this = $(this);
            const $section = $this.closest(".homepage-section");
            const cat = $this.data("cat");
            

            if ($this.hasClass("active")) return;

            $this.addClass("active").siblings().removeClass("active");
            loadProducts($section, cat, 1);
        });

        // Khi click chuyển trang
        $(document).on("click", ".pagination a", function (e) {
            e.preventDefault();
            const $this = $(this);
            const page = $this.data("page");
            if (!page) return;
            const $section = $this.closest(".homepage-section");
            const activeCat = $section.find(".category-tabs .tab.active").data("cat");
            loadProducts($section, activeCat, page);
        });

        // Khi load trang lần đầu -> load tất cả
        $(".homepage-section").each(function () {
            const $section = $(this);
            loadProducts($section, "all", 1);
        });
    });
</script>