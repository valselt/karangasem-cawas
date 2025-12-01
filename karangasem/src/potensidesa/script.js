
document.addEventListener("DOMContentLoaded", function () {
  const navbar = document.querySelector(".navbar");
  if (!navbar) return; 

  const section1 = document.querySelector(".section-1");
  if (section1) {
    const triggerHeight = section1.offsetHeight;
    window.addEventListener("scroll", function () {
      if (window.scrollY > triggerHeight) {
        navbar.classList.add("navbar-scrolled");
      } else {
        navbar.classList.remove("navbar-scrolled");
      }
    });
    
  } else {
    navbar.classList.add("navbar-scrolled");
  }

  const hamburgerBtn = document.querySelector(".hamburger-menu");

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", function() {
      navbar.classList.toggle("nav-open");
      const isExpanded = navbar.classList.contains("nav-open");
      hamburgerBtn.setAttribute("aria-expanded", isExpanded);
    });
  }
});