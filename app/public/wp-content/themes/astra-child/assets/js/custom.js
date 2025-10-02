/**
 * custom.js - Mini header scroll effect
 */
document.addEventListener("DOMContentLoaded", function () {
  var lastScrollTop = 0;
  var topbar = document.querySelector(".site-topbar");
  var menu = document.querySelector(".site-main-navigation");
  var mainHeader = document.querySelector(".site-mainheader");
  var placeholder = document.createElement("div");

  if (!topbar || !menu || !mainHeader) return;

  // set chiều cao placeholder = mainHeader để không bị jump
  placeholder.style.height = mainHeader.offsetHeight + "px";

  window.addEventListener("scroll", function () {
    var st = window.pageYOffset || document.documentElement.scrollTop;

    if (st > lastScrollTop && st > 150) {
      // Scroll xuống -> ẩn topbar + menu
      topbar.classList.add("shrink");
      menu.classList.add("shrink");
      mainHeader.classList.add("sticky");
      // chèn placeholder trước mainHeader để giữ layout
      mainHeader.parentNode.insertBefore(placeholder, mainHeader);
      // ẩn topbar
      if (topbar) topbar.style.transform = "translateY(-100%)";
    } else {
      if (st < 150) {
        // Scroll lên -> hiện topbar + menu
        topbar.classList.remove("shrink");
        menu.classList.remove("shrink");
        if (mainHeader.classList.contains("sticky")) {
          mainHeader.classList.remove("sticky");

          // remove placeholder
          if (placeholder.parentNode) {
            placeholder.parentNode.removeChild(placeholder);
          }

          // hiện topbar
          if (topbar) topbar.style.transform = "translateY(0)";
        }
      }
    }

    lastScrollTop = st <= 0 ? 0 : st;
  });
});
