<?php
/**
 * Footer template
 * @package pianonamdinh
 */
?>

<footer class="footer-expand-collapsed">
    <div class="footer-maininfo pt-5 bg-white">
        <div class="container">
            <div class="row">

                <!-- Cột 1: Logo + giới thiệu -->
                <div class="col-lg-3 col-md-6 mb-4 widget-footer">
                    <div class="logo-footer" title="Logo footer">
                        <?php if (has_custom_logo()) {
                            the_custom_logo();
                        } else {
                            bloginfo('name');
                        } ?>
                    </div>
                    <p>
                        Công Ty TNHH Piano Nam Định là nhà phân phối nhạc cụ, thiết bị âm thanh, ánh sáng
                        chuyên nghiệp và giáo dục đào tạo các bộ môn âm nhạc hàng đầu tại Việt Nam.
                    </p>
                    <div class="d-flex flex-wrap align-items-center mt-3">
                        <a href="http://online.gov.vn/Home/WebDetails/29527" target="_blank" class="mr-2">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo_bo_cong_thuong.png"
                                alt="Bộ Công Thương" height="40">
                        </a>
                    </div>
                </div>

                <!-- Cột 2: Địa chỉ -->
                <div class="col-lg-3 col-md-6 mb-4 widget-footer address-footer">
                    <h3 class="title-footer mb-3">Thông tin liên hệ</h3>
                    <ul class="list-unstyled mb-0">
                        <li><b>Địa chỉ:</b> Xóm 14, Xã Hải Hưng, Tỉnh Ninh Bình, Việt Nam</li>
                        <li><b>Điện thoại:</b> 070 3553 999</li>
                        <li><b>Email:</b> info@pianonamdinh.com.vn</li>
                        <li><b>Thời gian phục vụ:</b> 8:00 - 20:00</li>
                    </ul>
                    <div class="d-flex flex-wrap align-items-center mt-3">
                        <a target="_blank"
                            href="https://www.dmca.com/Protection/Status.aspx?ID=eef1ddde-2dff-4626-ac14-95eda3e1eddd">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/dmca_pianonamdinh.png"
                                alt="DMCA Protected" height="40">
                        </a>
                    </div>
                </div>

                <!-- Cột 3: Hỗ trợ khách hàng -->
                <div class="col-lg-3 col-md-6 mb-4 widget-footer">
                    <h3 class="title-footer mb-3">Hỗ trợ khách hàng</h3>
                    <ul class="list-unstyled footer-links">
                        <li><a href="/pages/gioi-thieu-cong-ty">Giới thiệu Công ty</a></li>
                        <li><a href="/pages/dieu-khoan-su-dung-website">Điều khoản sử dụng</a></li>
                        <li><a href="/pages/huong-dan-mua-hang">Hướng dẫn mua hàng</a></li>
                        <li><a href="/pages/chinh-sach-bao-hanh-san-pham">Chính sách bảo hành</a></li>
                        <li><a href="/pages/chinh-sach-giao-nhan">Chính sách giao nhận</a></li>
                        <li><a href="/pages/chinh-sach-dieu-khoan-thanh-toan-truc-tuyen">Chính sách thanh toán</a></li>
                        <li><a href="/pages/mua-tra-gop">Giải pháp trả góp</a></li>
                        <li><a href="/pages/chi-nhanh">Hệ thống cửa hàng</a></li>
                        <li><a href="/pages/lien-he">Liên hệ - Góp ý</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Hotline + social -->
                <div class="col-lg-3 col-md-6 mb-4 widget-footer">
                    <h3 class="title-footer mb-3">Gọi mua hàng</h3>
                    <p><strong>Tổng đài hỗ trợ:</strong> (Miễn phí gọi)</p>
                    <p>Gọi mua: <a href="tel:0703553999">070 3553 999</a> (8:00 - 20:00)</p>
                    <p>Bảo hành: <a href="tel:0703553999">070 3553 999</a> (8:30 - 17:30)</p>

                    <h3 class="title-footer mt-4">Kết nối với chúng tôi</h3>
                    <ul class="footerNav-social list-unstyled d-flex gap-2">
                        <li>
                            <a href="https://www.facebook.com/KhoNhacCuNamDinh/" target="_blank" rel="noopener"
                                title="Facebook" aria-label="Facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.instagram.com/khonhaccunamdinh/" target="_blank" rel="noopener"
                                title="Instagram" aria-label="Instagram">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </li>
                        <li>
                            <a href="/" target="_blank" rel="noopener" title="Google Plus" aria-label="Google Plus">
                                <i class="fa-brands fa-google-plus-g"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.youtube.com/user/khonhaccunamdinh" target="_blank" rel="noopener"
                                title="Youtube" aria-label="Youtube">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                        </li>
                    </ul>

                </div>

            </div>
        </div>
    </div>

    <div class="footer-copyright text-center py-3 border-top bg-white">
        <div class="container">
            <p class="mb-0 text-secondary">
                © 2025
                <a href="http://pianonamdinh.com.vn.local/" class="text-decoration-none">
                    Công Ty TNHH Piano Nam Định
                </a>
                — Đàn Piano | Organ | Guitar | Trống Chính Hãng
            </p>
        </div>
    </div>

</footer>

<?php wp_footer(); ?>
</body>

</html>