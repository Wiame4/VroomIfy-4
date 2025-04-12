// Sélectionne toutes les cartes
const cards = document.querySelectorAll('.card');

// Parcourt chaque carte
cards.forEach(card => {
    // Ajoute un écouteur d'événement de clic à chaque carte
    card.addEventListener('click', () => {
        // Utilise toggle pour ajouter ou enlever la classe 'show-click'
        card.classList.toggle('show-click');
    });
});


// Charger la footer dynamiquement
document.addEventListener("DOMContentLoaded", function() {
    fetch("../pages/client-barre.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("navbar-container").innerHTML = data;
            // Forcer le recalcul des styles
            setTimeout(() => {
                document.getElementById("navbar-container").offsetHeight;
            }, 0);
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
    fetch("../pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});
