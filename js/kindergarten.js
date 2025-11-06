// Animate feature boxes when scrolled into view
const boxes = document.querySelectorAll('.feature-box');

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('show');
    }
  });
}, { threshold: 0.3 });

boxes.forEach(box => observer.observe(box));
