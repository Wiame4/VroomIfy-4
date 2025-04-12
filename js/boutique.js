// Chargement de la navbar
fetch('../pages/client-barre.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('navbar-container').innerHTML = data;
    })
    
fetch('../pages/pied-page.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('footer-container').innerHTML = data;
    })
    
    .then(() => {
        // Gestion des clics sur le panier
        document.querySelectorAll('.add-to-cart').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!window.isLoggedIn) {
                    e.preventDefault();
                    new bootstrap.Modal(document.getElementById('loginModal')).show();
                }
            });
        });
    });