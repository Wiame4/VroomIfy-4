document.addEventListener("DOMContentLoaded", () => {
    let currentPage = 1;
    let totalPages = 1; // Variable pour stocker le nombre total de pages
    const messagesParPage = 5;
    const pageInfo = document.getElementById("pageNumber"); // Changé de pageNumber à pageInfo
    const prevPage = document.getElementById("prevPage");
    const nextPage = document.getElementById("nextPage");

    function chargerMessages(page) {
        fetch(`../pages/Admin_contact.php?action=get_messages&page=${page}`)
            .then(response => response.json())
            .then(data => {
                const conteneur = document.getElementById("conteneur-messages");
                conteneur.innerHTML = "";

                data.messages.forEach(msg => {
                    const div = document.createElement("div");
                    div.classList.add("message");
                    div.innerHTML = `
                        <p><strong>${msg.nomC}</strong> (${msg.emailC})</p>
                        <p>${msg.messageC}</p>
                        <button class='bouton-repondre' data-id="${msg.idContact}" data-email="${msg.emailC}">Répondre</button>
                    `;
                    conteneur.appendChild(div);
                });

                // Mise à jour de la pagination avec le nouveau format
                totalPages = data.total_pages;
                pageInfo.textContent = `Page ${page} sur ${totalPages}`;
                prevPage.disabled = page === 1;
                nextPage.disabled = page >= totalPages;
            })
            .catch(error => console.error("Erreur lors du chargement des messages:", error));
    }

    prevPage.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            chargerMessages(currentPage);
        }
    });

    nextPage.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            chargerMessages(currentPage);
        }
    });

    chargerMessages(currentPage);

    // ✅ Gestion de la modale pour répondre au client
    const modal = document.getElementById("modal-reponse");
    modal.style.display = "none";
    const fermerModal = document.querySelector(".fermer-modal");

    document.getElementById("conteneur-messages").addEventListener("click", (e) => {
        if (e.target.classList.contains("bouton-repondre")) {
            modal.style.display = "flex";
            document.getElementById("email-client").value = e.target.dataset.email;
            document.getElementById("id_message").value = e.target.dataset.id;

            fetch("../pages/Admin_contact.php?action=get_admin_email")
                .then(response => response.text())
                .then(email => document.getElementById("email-admin").value = email)
                .catch(error => console.error("Erreur récupération email admin:", error));
        }
    });

    if (fermerModal) {
        fermerModal.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    document.getElementById("formulaire-email").addEventListener("submit", (e) => {
        e.preventDefault();

        fetch("../pages/Admin_contact.php?action=send_email", {
            method: "POST",
            body: new FormData(e.target)
        })
        .then(response => response.text())
        .then(resultat => {
            /*alert(resultat);*/
            Swal.fire({
                title: 'Succès',
                text: resultat,
                icon: 'success',
                confirmButtonText: 'OK'
            });
    
            modal.style.display = "none";
            chargerMessages(currentPage);
        })
        .catch(error => console.error("Erreur lors de l'envoi de l'email:", error));
        Swal.fire({
            title: 'Erreur',
            text: 'Une erreur est survenue lors de l\'envoi de l\'email.',
            icon: 'Erreur!',
            confirmButtonText: 'OK'
        });
    });
});
document.addEventListener("DOMContentLoaded", function() {
    fetch("../pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});
