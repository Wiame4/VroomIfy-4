const btns = document.querySelectorAll(".nav-btn"); // Sélectionner tous les boutons
const slides = document.querySelectorAll(".video-slide"); // Sélectionner toutes les vidéos
let currentIndex = 0; // Index actuel du slide

// Fonction pour changer la vidéo
const sliderNav = (index) => {
    // Supprimer la classe active de tous les boutons et vidéos
    btns.forEach((btn) => btn.classList.remove("active"));
    slides.forEach((slide) => slide.classList.remove("active"));

    // Ajouter la classe active au bouton et à la vidéo sélectionnés
    btns[index].classList.add("active");
    slides[index].classList.add("active");

    // Masquer toutes les vidéos sauf celle sélectionnée
    slides.forEach((slide) => (slide.style.display = "none"));
    slides[index].style.display = "block";

    // Mettre la vidéo en lecture automatique
    slides[index].play();
};

// Masquer toutes les vidéos sauf la première au chargement
slides.forEach((slide, index) => {
    if (index !== 0) slide.style.display = "none";
});

// Afficher la première vidéo automatiquement au chargement
sliderNav(0);

// Ajouter un écouteur d'événement à chaque bouton
btns.forEach((btn, i) => {
    btn.addEventListener("click", () => {
        currentIndex = i; // Mettre à jour l'index actuel
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

// importer le navbar : 

        // Charger la navbar dynamiquement
        document.addEventListener("DOMContentLoaded", function() {
          // Charger la navbar dynamiquement
          fetch('/VroomIfy-4/pages/client-barre.php')
          .then(response => response.text())
          .then(html => {
            document.getElementById('navbar-container').innerHTML = html;
          })
          .catch(error => console.error('Error loading navbar:', error));
        });
    
        // Charger la navbar dynamiquement
        document.addEventListener("DOMContentLoaded", function() {
            fetch("/VroomIfy-4/pages/pied-page.php")
                .then(response => response.text())
                .then(data => {
                    document.getElementById("footer-container").innerHTML = data;
                })
                .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
        });
    