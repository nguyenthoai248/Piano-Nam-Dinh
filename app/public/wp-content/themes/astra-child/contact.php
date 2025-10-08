<?php
/*
Template Name: Liên hệ
*/

get_header(); ?>

<section class="contact-page">
  <div class="contact-hero">
    <h1>Liên hệ với chúng tôi</h1>
    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7. Hãy gửi tin nhắn hoặc gọi cho chúng tôi ngay!</p>
  </div>

  <div class="contact-grid">
    <!-- Form -->
    <form class="contact-form" id="contactForm">
      <input type="text" name="name" placeholder="Họ và tên" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="tel" name="phone" placeholder="Số điện thoại">
      <textarea name="message" placeholder="Tin nhắn..." rows="5" required></textarea>
      <button type="submit" class="btn-submit">Gửi tin nhắn</button>
    </form>

    <!-- Thông tin liên hệ -->
    <div class="contact-info">
      <!-- Địa chỉ -->
            <div class="info-item">
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon lucide lucide-map-pin" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div>
                    <h3>Địa chỉ</h3>
                    <p>Xóm 14, Xã Hải Hưng, Tỉnh Ninh Bình, Việt Nam</p>
                </div>
            </div>

            <!-- Điện thoại -->
            <div class="info-item">
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon lucide lucide-phone" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.1 5.18 2 2 0 0 1 5.11 3h3a2 2 0 0 1 2 1.72c.12.81.37 1.6.73 2.34a2 2 0 0 1-.45 2.11l-1.27 1.27a16 16 0 0 0 6.29 6.29l1.27-1.27a2 2 0 0 1 2.11-.45c.74.36 1.53.61 2.34.73A2 2 0 0 1 22 16.92z" />
                    </svg>
                </div>
                <div>
                    <h3>Điện thoại</h3>
                    <p>+84 70 355 3999</p>
                </div>
            </div>

            <!-- Email -->
            <div class="info-item">
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon lucide lucide-mail" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M4 4h16v16H4z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <div>
                    <h3>Email</h3>
                    <p>info@pianonamdinh.com.vn</p>
                </div>
            </div>
      <div class="info-item map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d429.1423299889776!2d106.3237120446699!3d20.222990899023227!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x313609ba6d94da99%3A0x447434d3ce37183e!2zQ8O0bmcgVHkgUGlhbm8gTmFtIMSQ4buLbmhfTmjhuqFjIEPhu6UgR2nDoSBU4buRdA!5e0!3m2!1sen!2s!4v1759828575272!5m2!1sen!2s" width="600" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
