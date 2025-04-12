document.addEventListener("DOMContentLoaded", function () {
    fetch("../pages/client-barre.php")
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors du chargement de la navbar");
            }
            return response.text();
        })
        .then(html => {
            document.getElementById("navbar-container").innerHTML = html;
        })
        .catch(error => console.error("Erreur :", error));
        fetch("../pages/pied-page.php")
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors du chargement de la navbar");
            }
            return response.text();
        })
        .then(html => {
            document.getElementById("footer-container").innerHTML = html;
        })
        .catch(error => console.error("Erreur :", error));
});
