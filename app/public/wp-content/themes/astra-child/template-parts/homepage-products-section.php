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
            <p class="section-subtitle"><?= esc_html($subtitle) ?></p>
        <?php endif; ?>

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
            <div class="product-grid row g-0"></div>
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary load-more-btn" style="display:none;">Xem thêm sản phẩm</button>
            </div>
        </div>

    </div>
</section>


<script>
    jQuery(document).ready(function ($) {
        const productSelectors = '.product-card, .product-item, .product';

        function loadProducts($section, cat = "all", page = 1, append = false) {
            const type = $section.data("type");
            const $grid = $section.find(".product-grid");
            const $spinner = $section.find(".loading-spinner");
            const $button = $section.find(".load-more-btn");

            $spinner.addClass("active");
            $button.hide();

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
                        const data = typeof response === "object" ? response : JSON.parse(response);

                        if (append) {
                            // chỉ append khi có html mới
                            if ($.trim(data.html) !== "") {
                                $grid.append(data.html);
                            }
                        } else {
                            $grid.hide().html(data.html).fadeIn(300);
                        }

                        // cập nhật page cho section này
                        $section.data("paged", page);

                        // cập nhật trạng thái nút Xem thêm
                        if (data.has_more) {
                            $button.show();
                        } else {
                            $button.hide();
                        }

                    } catch (e) {
                        console.error("Lỗi parse JSON:", e, response);
                    }

                    $spinner.removeClass("active");
                }
            );
        }

        // Load ban đầu
        $(".homepage-section").each(function () {
            const $section = $(this);
            $section.data("paged", 1);
            loadProducts($section, "all", 1, false);
        });

        // Khi click chọn category
        $(document).on("click", ".category-tabs .tab", function () {
            const $this = $(this);
            const $section = $this.closest(".homepage-section");
            const cat = $this.data("cat");

            if ($this.hasClass("active")) return;

            // active tab mới
            $this.addClass("active").siblings().removeClass("active");

            // reset page về 1
            $section.data("paged", 1);

            loadProducts($section, cat, 1, false);
        });

        // Khi click "Xem thêm"
        $(document).off("click", ".load-more-btn").on("click", ".load-more-btn", function () {
            const $this = $(this);
            const $section = $this.closest(".homepage-section");
            const activeCat = $section.find(".category-tabs .tab.active").data("cat");

            // Tăng page riêng cho từng section
            let currentPage = $section.data("page") || 1;
            currentPage++;
            $section.data("page", currentPage);

            loadProducts($section, activeCat, currentPage, true);
        });

    });


</script>