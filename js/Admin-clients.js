document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const clientsBody = document.getElementById('clients-body');
    const searchInput = document.getElementById('search-input');
    const refreshBtn = document.getElementById('refresh-btn');
    const detailsModal = document.getElementById('details-modal');
    const closeModal = document.querySelector('.close-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    
    // Variables d'état
    let allClients = [];
    let filteredClients = [];
    let currentPage = 1;
    const clientsPerPage = 10;
    
    // Charger les clients au démarrage
    loadClients();
    
    // Écouteurs d'événements
    searchInput.addEventListener('input', filterAndPaginate);
    refreshBtn.addEventListener('click', loadClients);
    closeModal.addEventListener('click', closeDetailsModal);
    closeModalBtn.addEventListener('click', closeDetailsModal);
    prevPageBtn.addEventListener('click', goToPrevPage);
    nextPageBtn.addEventListener('click', goToNextPage);
    
    // Fonction pour charger les clients depuis le serveur
    function loadClients() {
        fetch('Admin-clients.php?action=get_clients')
            .then(response => response.json())
            .then(data => {
                allClients = data;
                filterAndPaginate();
            })
            .catch(error => {
                console.error('Erreur:', error);
                clientsBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Erreur lors du chargement des clients</td></tr>';
            });
    }
    
    // Filtrer et paginer les clients
    function filterAndPaginate() {
        const searchTerm = searchInput.value.toLowerCase();
        
        // Filtrer les clients par nom ou prénom
        filteredClients = allClients.filter(client => {
            const fullName = `${client.prenomClient} ${client.nomClient}`.toLowerCase();
            return fullName.includes(searchTerm);
        });
        
        // Réinitialiser à la première page après un filtre
        currentPage = 1;
        updatePagination();
        displayCurrentPage();
    }
    
    // Afficher la page courante
    function displayCurrentPage() {
        const startIndex = (currentPage - 1) * clientsPerPage;
        const endIndex = startIndex + clientsPerPage;
        const clientsToDisplay = filteredClients.slice(startIndex, endIndex);
        
        clientsBody.innerHTML = '';
        
        if (clientsToDisplay.length === 0) {
            clientsBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Aucun client trouvé</td></tr>';
            return;
        }
        
        clientsToDisplay.forEach(client => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${client.idClient}</td>
                <td>${client.nomClient}</td>
                <td>${client.prenomClient}</td>
                <td>${client.emailClient}</td>
                <td>${client.telClient || '-'}</td>
                <td>${client.marque} ${client.modele}</td>
                <td>
                    <button class="action-btn view-btn" data-id="${client.idClient}">
                        <i class="fas fa-eye"></i> Voir
                    </button>
                </td>
            `;
            row.querySelector('.view-btn').addEventListener('click', () => showClientDetails(client));
            clientsBody.appendChild(row);
        });
    }
    
    // Mettre à jour la pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredClients.length / clientsPerPage);
        
        // Mettre à jour l'info de la page
        pageInfo.textContent = `Page ${currentPage} sur ${totalPages > 0 ? totalPages : 1}`;
        
        // Désactiver les boutons si nécessaire
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
    }
    
    // Aller à la page précédente
    function goToPrevPage() {
        if (currentPage > 1) {
            currentPage--;
            displayCurrentPage();
            updatePagination();
        }
    }
    
    // Aller à la page suivante
    function goToNextPage() {
        const totalPages = Math.ceil(filteredClients.length / clientsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayCurrentPage();
            updatePagination();
        }
    }
    
    // Afficher les détails d'un client
    function showClientDetails(client) {
        document.getElementById('detail-id').textContent = client.idClient;
        document.getElementById('detail-nom').textContent = client.nomClient;
        document.getElementById('detail-prenom').textContent = client.prenomClient;
        document.getElementById('detail-email').textContent = client.emailClient;
        document.getElementById('detail-tel').textContent = client.telClient || 'Non renseigné';
        document.getElementById('detail-adresse').textContent = client.adresseClient || 'Non renseignée';
        document.getElementById('detail-marque').textContent = client.marque || 'Non renseignée';
        document.getElementById('detail-modele').textContent = client.modele || 'Non renseigné';
        document.getElementById('detail-carburant').textContent = client.carburant || 'Non renseigné';
        
        detailsModal.style.display = 'flex';
    }
    
    // Fermer le modal des détails
    function closeDetailsModal() {
        detailsModal.style.display = 'none';
    }
});
document.addEventListener("DOMContentLoaded", function() {
    fetch("../pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});
