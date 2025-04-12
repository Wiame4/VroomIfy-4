document.getElementById('review-form').addEventListener('submit', function(e) {
    if (!isLoggedIn) {
        e.preventDefault();
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    }
});
function initAvisStars() {
    console.log("Initialisation des étoiles...");

    const stars = document.querySelectorAll(".star-rating span");
    const ratingInput = document.getElementById("rating");

    if (!stars.length || !ratingInput) {
        console.warn("Les étoiles ou l'input caché ne sont pas trouvés !");
        return;
    }

    stars.forEach((star, index) => {
        star.addEventListener("click", function () {
            let rating = index + 1;
            console.log("Étoile cliquée :", rating);

            ratingInput.value = rating;

            stars.forEach((s, i) => {
                s.textContent = i < rating ? "★" : "☆";
            });
        });
    });

    document.getElementById("review-form").addEventListener("submit", function (event) {
        if (ratingInput.value === "") {
            alert("Veuillez attribuer une note en étoiles.");
            event.preventDefault();
        }
    });
}

// Exécuter immédiatement si la page est chargée directement
document.addEventListener("DOMContentLoaded", initAvisStars);
