// Tunggu hingga seluruh halaman HTML dimuat
document.addEventListener("DOMContentLoaded", function () {
  
  // 1. Selalu ambil elemen navbar
  const navbar = document.querySelector(".navbar");
  
  // Jika tidak ada navbar di halaman ini, hentikan script
  if (!navbar) return; 

  // 2. Coba temukan ".section-1"
  const section1 = document.querySelector(".section-1");

  // 3. Buat Pengecekan
  if (section1) {
    // --- KITA BERADA DI INDEX.HTML ---
    // (Karena .section-1 ditemukan)
    // Jalankan logic scroll listener Anda yang lama

    const triggerHeight = section1.offsetHeight;

    window.addEventListener("scroll", function () {
      if (window.scrollY > triggerHeight) {
        navbar.classList.add("navbar-scrolled");
      } else {
        navbar.classList.remove("navbar-scrolled");
      }
    });
    
  } else {
    // --- KITA BERADA DI HALAMAN LAIN ---
    // (Karena .section-1 TIDAK ditemukan)
    // Langsung buat navbar-nya "scrolled"
    
    navbar.classList.add("navbar-scrolled");
  }

  const hamburgerBtn = document.querySelector(".hamburger-menu");

  if (hamburgerBtn) {
    // 2. Tambahkan 'click' listener
    hamburgerBtn.addEventListener("click", function() {
      
      // 3. Toggle class 'nav-open' di navbar
      navbar.classList.toggle("nav-open");

      // 4. Perbarui atribut ARIA untuk aksesibilitas
      const isExpanded = navbar.classList.contains("nav-open");
      hamburgerBtn.setAttribute("aria-expanded", isExpanded);
    });
  }
});