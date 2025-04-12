document.addEventListener("DOMContentLoaded", () => {
    // Chargement asynchrone de la barre de navigation
    const loadNavbar = async () => {
        try {
            const response = await fetch('../pages/client-barre.php');
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            const navbarHTML = await response.text();
            const container = document.getElementById('navbar-container');

            if (!container) {
                console.warn('Container de navigation introuvable');
                return;
            }

            container.innerHTML = navbarHTML;
            console.log('Barre de navigation chargée avec succès');

        } catch (error) {
            console.error('Échec du chargement de la navbar:', error);
            // Option: Afficher un message d'erreur dans l'interface
        }
    };

    // Lance le chargement
    loadNavbar();

     // Chargement asynchrone de la barre de navigation
     const loadFooter = async () => {
        try {
            const response = await fetch('/VroomIfy-4/pages/pied-page.php');
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            const navbarHTML = await response.text();
            const container = document.getElementById('footer-container');

            if (!container) {
                console.warn('Container de navigation introuvable');
                return;
            }

            container.innerHTML = navbarHTML;
            console.log('Barre de navigation chargée avec succès');

        } catch (error) {
            console.error('Échec du chargement de la navbar:', error);
            // Option: Afficher un message d'erreur dans l'interface
        }
    };

    // Lance le chargement
    loadFooter();
});