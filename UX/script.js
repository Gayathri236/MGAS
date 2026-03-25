
const reveals = document.querySelectorAll(".reveal");

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, index) => {
    if (entry.isIntersecting) {
      setTimeout(() => {
        entry.target.classList.add("active");
      }, index * 200); // delay each card
    }
  });
}, {
  threshold: 0.2
});

reveals.forEach((reveal) => {
  observer.observe(reveal);
});
