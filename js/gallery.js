// Get modal and elements
const modal = document.getElementById("imageModal");
const modalImg = document.getElementById("modalImg");
const closeBtn = document.querySelector(".close");

// Attach click event sa bawat card image
document.querySelectorAll(".card img").forEach(img => {
  img.addEventListener("click", () => {
    modal.style.display = "block";
    modalImg.src = img.src; // set clicked image as modal image
  });
});

// Close modal kapag pinindot ang X
closeBtn.onclick = () => {
  modal.style.display = "none";
};

// Close modal kapag nag-click sa labas ng image
window.onclick = (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
};
