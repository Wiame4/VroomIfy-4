document.addEventListener('DOMContentLoaded', function() {
    const btns = document.querySelectorAll(".nav-btn");
    const slides = document.querySelectorAll(".video-slide");
    let currentIndex = 0;

    // Fonction pour changer la vidéo
    const sliderNav = (index) => {
        btns.forEach((btn) => btn.classList.remove("active"));
        slides.forEach((slide) => slide.classList.remove("active"));

        btns[index].classList.add("active");
        slides[index].classList.add("active");
    };

    // Initialisation
    sliderNav(0);

    // Ajouter un écouteur d'événement à chaque bouton
    btns.forEach((btn, i) => {
        btn.addEventListener("click", () => {
            currentIndex = i;
            sliderNav(i);
        });
    });

    // Fonction pour passer automatiquement au slide suivant
    const autoSlide = () => {
        currentIndex = (currentIndex + 1) % slides.length;
        sliderNav(currentIndex);
    };

    // Lancer le changement automatique toutes les 4 secondes
    setInterval(autoSlide, 4000);
});
document.addEventListener("DOMContentLoaded", function() {
    fetch("../pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});
