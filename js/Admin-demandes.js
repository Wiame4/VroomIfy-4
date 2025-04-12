document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const rdvBody = document.getElementById('rdv-body');
    const filterState = document.getElementById('filter-state');
    const searchInput = document.getElementById('search-input');
    const resetFilters = document.getElementById('reset-filters');
    const detailsModal = document.getElementById('details-modal');
    const closeModal = document.querySelector('.close-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const saveChangesBtn = document.getElementById('save-changes');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    
    // Variables d'état
    let currentAppointmentId = null;
    let allAppointments = [];
    let filteredAppointments = [];
    let currentPage = 1;
    const appointmentsPerPage = 8;
    
    // Charger les rendez-vous au démarrage
    loadAppointments();
    
    // Écouteurs d'événements
    filterState.addEventListener('change', filterAndPaginate);
    searchInput.addEventListener('input', filterAndPaginate);
    resetFilters.addEventListener('click', resetAllFilters);
    closeModal.addEventListener('click', closeDetailsModal);
    closeModalBtn.addEventListener('click', closeDetailsModal);
    saveChangesBtn.addEventListener('click', saveAppointmentChanges);
    prevPageBtn.addEventListener('click', goToPrevPage);
    nextPageBtn.addEventListener('click', goToNextPage);
    
    // Fonction pour charger les rendez-vous depuis le serveur
    function loadAppointments() {
        fetch('Admin-demandes.php?action=get_rdv')
            .then(response => response.json())
            .then(data => {
                allAppointments = data;
                filterAndPaginate();
            })
            .catch(error => console.error('Erreur:', error));
            
    }
    
    // Filtrer et paginer les rendez-vous
    function filterAndPaginate() {
        const searchTerm = searchInput.value.toLowerCase();
        const stateFilter = filterState.value;
        
        // Filtrer les rendez-vous
        filteredAppointments = allAppointments.filter(rdv => {
            const matchesSearch = 
                rdv.nomRdv.toLowerCase().includes(searchTerm) ||
                rdv.serviceRdv.toLowerCase().includes(searchTerm) ||
                rdv.vehicule.toLowerCase().includes(searchTerm) ||
                rdv.addresse.toLowerCase().includes(searchTerm);
            
            const matchesState = stateFilter === 'all' || rdv.etat === stateFilter;
            
            return matchesSearch && matchesState;
        });
        
        // Réinitialiser à la première page après un filtre
        currentPage = 1;
        updatePagination();
        displayCurrentPage();
    }
    
    // Afficher la page courante
    function displayCurrentPage() {
        const startIndex = (currentPage - 1) * appointmentsPerPage;
        const endIndex = startIndex + appointmentsPerPage;
        const appointmentsToDisplay = filteredAppointments.slice(startIndex, endIndex);
        
        rdvBody.innerHTML = '';
        
        if (appointmentsToDisplay.length === 0) {
            rdvBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Aucun rendez-vous trouvé</td></tr>';
            return;
        }
        
        appointmentsToDisplay.forEach(rdv => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${rdv.idRdv}</td>
                <td>${rdv.nomRdv}</td>
                <td>${rdv.serviceRdv}</td>
                <td>${formatDateTime(rdv.dateRdv)}</td>
                <td>${rdv.vehicule}</td>
                <td class="etat-${rdv.etat.replace(' ', '-')}">${rdv.etat}</td>
                <td>
                    <button class="action-btn view-btn" data-id="${rdv.idRdv}" title="Voir les détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn" data-id="${rdv.idRdv}" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            `;
            rdvBody.appendChild(row);
        });
        
        // Ajouter les écouteurs aux boutons
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                viewAppointmentDetails(this.getAttribute('data-id'));
            });
        });
        
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                editAppointment(this.getAttribute('data-id'));
            });
        });
    }
    
    // Mettre à jour la pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredAppointments.length / appointmentsPerPage);
        
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
        const totalPages = Math.ceil(filteredAppointments.length / appointmentsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayCurrentPage();
            updatePagination();
        }
    }
    
    // Réinitialiser les filtres
    function resetAllFilters() {
        searchInput.value = '';
        filterState.value = 'all';
        filterAndPaginate();
    }
    
    // Voir les détails d'un rendez-vous
    function viewAppointmentDetails(id) {
        const rdv = allAppointments.find(r => r.idRdv == id);
        if (!rdv) return;
        
        document.getElementById('detail-id').textContent = rdv.idRdv;
        document.getElementById('detail-nom').textContent = rdv.nomRdv;
        document.getElementById('detail-email').textContent = rdv.emailRdv;
        document.getElementById('detail-service').textContent = rdv.serviceRdv;
        document.getElementById('detail-date').textContent = formatDateTime(rdv.dateRdv);
        document.getElementById('detail-adresse').textContent = rdv.addresse;
        document.getElementById('detail-vehicule').textContent = rdv.vehicule;
        document.getElementById('detail-commentaire').textContent = rdv.commentaire || 'Aucun commentaire';
        document.getElementById('detail-etat').value = rdv.etat;
        
        // Afficher la photo si elle existe
        const photoImg = document.getElementById('detail-photo');
        if (rdv.photo) {
            photoImg.src = 'data:image/jpeg;base64,' + rdv.photo;
            document.getElementById('photo-container').style.display = 'block';
        } else {
            document.getElementById('photo-container').style.display = 'none';
        }
        
        detailsModal.style.display = 'flex';
        currentAppointmentId = null;
        saveChangesBtn.style.display = 'none';
        document.getElementById('detail-etat').disabled = true;
    }
    
    // Modifier un rendez-vous
    function editAppointment(id) {
        viewAppointmentDetails(id);
        currentAppointmentId = id;
        saveChangesBtn.style.display = 'block';
        document.getElementById('detail-etat').disabled = false;
    }
    
    // Fermer le modal des détails
    function closeDetailsModal() {
        detailsModal.style.display = 'none';
    }
    
    // Enregistrer les modifications
    function saveAppointmentChanges() {
        if (!currentAppointmentId) return;
        
        const newEtat = document.getElementById('detail-etat').value;
        
        fetch('Admin-demandes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_rdv&id=${currentAppointmentId}&etat=${encodeURIComponent(newEtat)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                //alert('État mis à jour avec succès');
                Swal.fire({
                    title: 'Succès',
                    text: 'Modification enregistrer avec succès !',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                loadAppointments();
                detailsModal.style.display = 'none';
            } else {
                //alert('Erreur lors de la mise à jour: ' + (data.error || ''));
                Swal.fire({
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors de la mise à jour !',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            //alert('Erreur lors de la communication avec le serveur');
            Swal.fire({
                title: 'Erreur',
                text: 'Erreur lors de la communication avec le serveur',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    }
    
    // Formater la date et l'heure
    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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
