document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const servicesGrid = document.getElementById('services-grid');
    const searchInput = document.getElementById('search-input');
    const addServiceBtn = document.getElementById('add-service-btn');
    const serviceModal = document.getElementById('service-modal');
    const confirmModal = document.getElementById('confirm-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const serviceForm = document.getElementById('service-form');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    const cancelEditBtn = document.getElementById('cancel-edit');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    
    // Variables d'état
    let allServices = [];
    let filteredServices = [];
    let currentPage = 1;
    const servicesPerPage = 4;
    let currentServiceId = null;
    let serviceToDelete = null;

    // Charger les services au démarrage
    loadServices();

    // Écouteurs d'événements
    searchInput.addEventListener('input', filterAndPaginate);
    addServiceBtn.addEventListener('click', showAddForm);
    closeModalBtns.forEach(btn => btn.addEventListener('click', closeAllModals));
    serviceForm.addEventListener('submit', saveService);
    cancelEditBtn.addEventListener('click', closeAllModals);
    prevPageBtn.addEventListener('click', goToPrevPage);
    nextPageBtn.addEventListener('click', goToNextPage);
    cancelDeleteBtn.addEventListener('click', closeAllModals);
    confirmDeleteBtn.addEventListener('click', deleteService);

    // Charger les services 
    function loadServices() {
        fetch('../pages/Admin-services.php?action=get_services')
            .then(response => response.json())
            .then(data => {
                allServices = data;
                filterAndPaginate();
            })
            .catch(error => {
                console.error('Erreur:', error);
                servicesGrid.innerHTML = '<div class="error">Erreur lors du chargement des services</div>';
            });
    }

    function filterAndPaginate() {
        const searchTerm = searchInput.value.toLowerCase();
        
        // Filtrer les services
        filteredServices = allServices.filter(service => {
            return (
                service.nomService.toLowerCase().includes(searchTerm) ||
                service.descriptionService.toLowerCase().includes(searchTerm)
            );
        });
        
        // Réinitialiser à la première page après un filtre
        currentPage = 1;
        updatePagination();
        displayCurrentPage();
    }

    // Afficher la page courante
    function displayCurrentPage() {
        const startIndex = (currentPage - 1) * servicesPerPage;
        const endIndex = startIndex + servicesPerPage;
        const servicesToDisplay = filteredServices.slice(startIndex, endIndex);
        
        servicesGrid.innerHTML = '';
        
        if (servicesToDisplay.length === 0) {
            servicesGrid.innerHTML = '<div class="no-services">Aucun service trouvé</div>';
            return;
        }
        
        servicesToDisplay.forEach(service => {
            const serviceCard = document.createElement('div');
            serviceCard.className = 'service-card';
            serviceCard.innerHTML = `
                <div class="service-image-container">
                    ${service.ImageService 
                        ? `<img src="data:image/jpeg;base64,${service.ImageService}" class="service-image" alt="${service.nomService}">` 
                        : `<div class="service-image no-image"><i class="fas fa-concierge-bell"></i></div>`
                    }
                </div>
                <div class="service-info">
                    <div class="service-name">${service.nomService}</div>
                    <div class="service-desc">${service.descriptionService}</div>
                </div>
                <div class="service-actions">
                    <button class="edit-btn" data-id="${service.idService}">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button class="delete-btn" data-id="${service.idService}">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            `;
            
            // Ajouter les écouteurs d'événements
            serviceCard.querySelector('.edit-btn').addEventListener('click', () => editService(service.idService));
            serviceCard.querySelector('.delete-btn').addEventListener('click', () => confirmDelete(service.idService));
            
            servicesGrid.appendChild(serviceCard);
        });
    }

    // Mettre à jour la pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredServices.length / servicesPerPage);
        
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
        const totalPages = Math.ceil(filteredServices.length / servicesPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayCurrentPage();
            updatePagination();
        }
    }

    // Afficher le formulaire d'ajout
    function showAddForm() {
        document.getElementById('modal-title').textContent = 'Ajouter un service';
        document.getElementById('service-id').value = '';
        document.getElementById('service-name').value = '';
        document.getElementById('service-desc').value = '';
        document.getElementById('service-image').value = '';
        document.getElementById('image-preview').innerHTML = '';
        
        currentServiceId = null;
        serviceModal.style.display = 'flex';
    }

    // Afficher le formulaire de modification
    function editService(id) {
        const service = allServices.find(s => s.idService == id);
        if (!service) return;
        
        document.getElementById('modal-title').textContent = 'Modifier le service';
        document.getElementById('service-id').value = service.idService;
        document.getElementById('service-name').value = service.nomService;
        document.getElementById('service-desc').value = service.descriptionService;
        
        const imagePreview = document.getElementById('image-preview');
        imagePreview.innerHTML = '';
        if (service.ImageService) {
            const img = document.createElement('img');
            img.src = `data:image/jpeg;base64,${service.ImageService}`;
            imagePreview.appendChild(img);
        }
        
        currentServiceId = id;
        serviceModal.style.display = 'flex';
    }

    // Confirmer la suppression
    function confirmDelete(id) {
        serviceToDelete = id;
        document.getElementById('confirm-message').textContent = 'Êtes-vous sûr de vouloir supprimer ce service ?';
        confirmModal.style.display = 'flex';
    }

    // Supprimer le service
    function deleteService() {
        if (!serviceToDelete) return;
        
        fetch('../pages/Admin-services.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_service&id=${serviceToDelete}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadServices();
            } else {
                alert('Erreur lors de la suppression: ' + (data.error || ''));
            }
            closeAllModals();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la communication avec le serveur');
            closeAllModals();
        });
    }

    // Enregistrer le service (ajout ou modification)
    function saveService(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', currentServiceId ? 'update_service' : 'add_service');
        formData.append('id', document.getElementById('service-id').value);
        formData.append('nom', document.getElementById('service-name').value);
        formData.append('desc', document.getElementById('service-desc').value);
        
        const imageInput = document.getElementById('service-image');
        if (imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }
        
        fetch('../pages/Admin-services.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadServices();
                closeAllModals();
            } else {
                alert('Erreur: ' + (data.error || ''));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la communication avec le serveur');
        });
    }

    // Fermer tous les modals
    function closeAllModals() {
        serviceModal.style.display = 'none';
        confirmModal.style.display = 'none';
        serviceToDelete = null;
    }

    // Prévisualisation de l'image
    document.getElementById('service-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.createElement('img');
                img.src = event.target.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
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
