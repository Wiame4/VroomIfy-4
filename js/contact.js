 // Charger la navbar
 fetch("../pages/client-barre.php")
 .then(response => {
     if (!response.ok) {
         throw new Error('Erreur HTTP ' + response.status);
     }
     return response.text();
 })
 .then(data => {
     const navbarContainer = document.getElementById("navbar-container");
     if (navbarContainer) {
         navbarContainer.innerHTML = data;
     } else {
         console.error("Élément #navbar-container non trouvé !");
     }
 })
 .catch(error => console.error("Erreur lors du chargement du navbar :", error));


  // Charger le pied de page
  fetch("../pages/pied-page.php")
  .then(response => {
      if (!response.ok) {
          throw new Error('Erreur HTTP ' + response.status);
      }
      return response.text();
  })
  .then(data => {
      const navbarContainer = document.getElementById("footer-container");
      if (navbarContainer) {
          navbarContainer.innerHTML = data;
      } else {
          console.error("Élément #navbar-container non trouvé !");
      }
  })
  .catch(error => console.error("Erreur lors du chargement du navbar :", error));
