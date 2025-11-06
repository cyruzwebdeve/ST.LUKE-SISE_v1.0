// FAQ Toggle
const faqs = document.querySelectorAll(".faq-question");

faqs.forEach(btn => {
  btn.addEventListener("click", () => {
    const answer = btn.nextElementSibling;
    answer.style.display = answer.style.display === "block" ? "none" : "block";
  });
});

// Random Bible Photo Section
const biblePhoto = document.getElementById("bible-photo");

// List of bible background photos (pwede dagdagan)
const bibleImages = [
  "photo/bible4.png",
  "photo/bible2.jpg",
  "photo/bible3.jpg",

];

// Pick random image tuwing load
function setRandomBiblePhoto() {
  const randomImg = bibleImages[Math.floor(Math.random() * bibleImages.length)];
  biblePhoto.style.backgroundImage = `url(${randomImg})`;
}

// Call function kapag nag-load ang page
window.addEventListener("DOMContentLoaded", setRandomBiblePhoto);

// Lightbox popup for gallery
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");
const closeBtn = document.querySelector(".lightbox .close");

const galleryCards = document.querySelectorAll(".gallery-container .card img");

// Open lightbox on image click
galleryCards.forEach(img => {
  img.addEventListener("click", () => {
    lightbox.style.display = "flex";
    lightboxImg.src = img.src;
  });
});

// Close lightbox on X click
closeBtn.addEventListener("click", () => {
  lightbox.style.display = "none";
});

// Close lightbox when clicking outside the image
lightbox.addEventListener("click", (e) => {
  if (e.target === lightbox) {
    lightbox.style.display = "none";
  }
});



// Toggle See More / See Less in History
const seeMoreBtn = document.getElementById("see-more-btn");
const moreText = document.getElementById("more-text");

seeMoreBtn.addEventListener("click", () => {
  if (moreText.style.display === "none") {
    moreText.style.display = "inline";
    seeMoreBtn.textContent = "See Less";
  } else {
    moreText.style.display = "none";
    seeMoreBtn.textContent = "See More";
  }
});

const faqItems = document.querySelectorAll(".faq-item");

faqItems.forEach((item, index) => {
  const question = item.querySelector(".faq-question");

  question.addEventListener("click", () => {
    faqItems.forEach((i, idx) => {
      if (idx === index) {
        i.classList.toggle("active");
      } else {
        i.classList.remove("active");
      }
    });
  });
});


