document.addEventListener("DOMContentLoaded", function () {
  const container = document.querySelector(".section-2-potensi");

  
  fetch("get_potensidesa.php")
    .then((response) => response.json())
    .then((data) => {
      
      if (data.length === 0) {
        container.innerHTML = "<p class='text-center'>Belum ada data potensi.</p>";
        return;
      }

      let htmlContent = "";
      
      
      data.forEach((item, index) => {
        
        const uniqueId = index + 1;

        
        let buttonHtml = "";
        if (item.button_config.show) {
          buttonHtml = `
            <a class="potensi-button" target="_blank" rel="noopener noreferrer" href="${item.button_config.url}">
                <span class="material-symbols-rounded">${item.button_config.icon}</span>
            </a>
          `;
        }

        
        htmlContent += `
            <div class="potensi-informasi">
              <div class="potensi-informasi-1">
                <img src="${item.path_foto_potensi}" loading="lazy" width="896" height="504"/>
              </div>
              <div class="potensi-informasi-2">
                <div class="potensi-informasi-2-title">
                  <span class="text-title">${item.nama_potensi}</span>
                  
                  ${buttonHtml}

                  <a class="potensi-button" id="accordion-opener-${uniqueId}">
                    <span class="material-symbols-rounded">expand_content</span>
                    <span class="material-symbols-rounded">collapse_content</span>
                  </a>
                </div>
                <div class="potensi-informasi-2-content">
                  ${item.deskripsi_potensi}
                </div>
              </div>
            </div>
        `;
      });

      
      container.innerHTML = htmlContent;

      
      initAccordionLogic();
    })
    .catch((error) => console.error("Error loading data:", error));
});

/**
 * FUNGSI ACCORDION
 * Dipindahkan ke sini agar bisa berjalan setelah data dimuat via AJAX
 */
function initAccordionLogic() {
  const accordionButtons = document.querySelectorAll('a[id^="accordion-opener-"]');

  accordionButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      const parentCard = button.closest(".potensi-informasi");
      const wasOpen = parentCard.classList.contains("open");

      
      document.querySelectorAll(".potensi-informasi.open").forEach((openCard) => {
        if (openCard !== parentCard) {
          openCard.classList.remove("open");
          resetCardStyles(openCard);
        }
      });

      
      if (wasOpen) {
        parentCard.classList.remove("open");
        resetCardStyles(parentCard);
      } else {
        parentCard.classList.add("open");
        setTimeout(() => {
          animateCardOpen(parentCard);
        }, 0);
      }
    });
  });

  
  window.addEventListener("resize", () => {
    document.querySelectorAll(".potensi-informasi").forEach((card) => {
      resetCardStyles(card); 
      if (card.classList.contains("open")) {
        
        const imageContainer = card.querySelector(".potensi-informasi-1");
        const textContent = card.querySelector(".potensi-informasi-2-content");
        
        
        imageContainer.style.height = null;
        imageContainer.style.maxHeight = null;
        textContent.style.maxHeight = null;
        
        
        calculateOpenStyles(card);
      }
    });
  });
}



function calculateOpenStyles(cardElement) {
  const imageContainer = cardElement.querySelector(".potensi-informasi-1");
  const textContent = cardElement.querySelector(".potensi-informasi-2-content");

  if (window.innerWidth <= 768) {
    
    imageContainer.style.maxHeight = "1000px";
    textContent.style.maxHeight = textContent.scrollHeight + "px";
  } else {
    
    const newWidth = imageContainer.offsetWidth;
    const targetHeight = newWidth * (9 / 16);
    imageContainer.style.height = targetHeight + "px";
    textContent.style.maxHeight = textContent.scrollHeight + "px";
  }
}

function animateCardOpen(cardElement) {
  const imageContainer = cardElement.querySelector(".potensi-informasi-1");
  if (window.innerWidth > 768) {
    const currentHeight = imageContainer.offsetHeight;
    imageContainer.style.height = currentHeight + "px";
  }
  requestAnimationFrame(() => {
    calculateOpenStyles(cardElement);
  });
}

function resetCardStyles(cardElement) {
  const imageContainer = cardElement.querySelector(".potensi-informasi-1");
  const textContent = cardElement.querySelector(".potensi-informasi-2-content");
  const textContainer = cardElement.querySelector(".potensi-informasi-2");

  if (window.innerWidth <= 768) {
    imageContainer.style.maxHeight = null;
    textContent.style.maxHeight = null;
  } else {
    textContent.style.transition = "none";
    textContent.style.maxHeight = null;
    const finalHeight = textContainer.offsetHeight;
    textContent.style.transition = "";

    setTimeout(() => {
      textContent.style.maxHeight = null;
      imageContainer.style.height = finalHeight + "px";
    }, 0);

    setTimeout(() => {
      if (!cardElement.classList.contains("open")) {
        imageContainer.style.height = null;
      }
    }, 400);
  }
}