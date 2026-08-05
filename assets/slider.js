// Carrousel de la page d'accueil — thème Shift Pilot.
(function () {
  var index = 0;
  function rotate(slides) {
    if (!slides.length) return;
    slides[index].classList.remove("active");
    index = (index + 1) % slides.length;
    slides[index].classList.add("active");
  }
  document.addEventListener("DOMContentLoaded", function () {
    var slides = document.querySelectorAll(".slide");
    if (slides.length) setInterval(function () { rotate(slides); }, 5000);
  });
})();
