document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const ordersBody = document.getElementById('orders-body');
    const searchInput = document.getElementById('search-input');
    const dateFilter = document.getElementById('date-filter');
    const customDateRange = document.getElementById('custom-date-range');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    const orderModal = document.getElementById('order-modal');
    const closeModal = document.querySelector('.close-modal');
    const saveStatusBtn = document.getElementById('save-status');
    const printOrderBtn = document.getElementById('print-order');
    const orderStatus = document.getElementById('order-status');
    
    // Dashboard elements
    const totalOrdersEl = document.getElementById('total-orders');
    const pendingOrdersEl = document.getElementById('pending-orders');
    
    // Variables d'état
    let allOrders = [];
    let filteredOrders = [];
    let currentPage = 1;
    const ordersPerPage = 10;
    let currentOrderId = null;
    
    // Initialisation
    initDates();
    loadOrders();
    
    // Écouteurs d'événements
    //searchInput.addEventListener('input', filterAndPaginate);
    dateFilter.addEventListener('change', toggleDateRange);
    startDate.addEventListener('change', filterAndPaginate);
    endDate.addEventListener('change', filterAndPaginate);
    prevPageBtn.addEventListener('click', goToPrevPage);
    nextPageBtn.addEventListener('click', goToNextPage);
    closeModal.addEventListener('click', closeOrderModal);
    saveStatusBtn.addEventListener('click', updateOrderStatus);
    printOrderBtn.addEventListener('click', printOrder);
    
    // Initialiser les dates
    function initDates() {
        const today = new Date().toISOString().split('T')[0];
        startDate.value = today;
        endDate.value = today;
        startDate.max = today;
        endDate.max = today;
    }
    
    // Charger les commandes depuis le serveur
    function loadOrders() {
        fetch('../pages/Admin-commandes.php?action=get_orders')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Données reçues:', data); // Ajout pour débogage
                
                if (!data || !data.success) {
                    throw new Error(data?.error || 'Réponse serveur invalide');
                }
                
                allOrders = Array.isArray(data.orders) ? data.orders : [];
                
                console.log('Commandes chargées:', allOrders); // Ajout pour débogage
                
                // Statistiques avec valeurs par défaut
                const stats = {
                    total_orders: data.stats?.total_orders || allOrders.length,
                    pending_orders: data.stats?.pending_orders || allOrders.filter(o => o.statut === 'en attente').length,
                    month_revenue: data.stats?.month_revenue || 0
                };
                
                updateDashboard(stats);
                filterAndPaginate();
            })
            .catch(error => {
                console.error('Erreur de chargement:', error);
                showError(error.message);
                
                // Afficher des valeurs par défaut
                updateDashboard({
                    total_orders: 0,
                    pending_orders: 0,
                    month_revenue: 0
                });
            });
    }
    
    // Afficher un message d'erreur
    function showError(message) {
        ordersBody.innerHTML = `
            <tr>
                <td colspan="7" class="error-message">
                    ${message}
                    <button onclick="window.location.reload()">Réessayer</button>
                </td>
            </tr>
        `;
    }
    
    // Mettre à jour le dashboard
    function updateDashboard(stats) {
        totalOrdersEl.textContent = stats.total_orders || 0;
        pendingOrdersEl.textContent = stats.pending_orders || 0;
    }
    
    // Filtrer et paginer les commandes
    function filterAndPaginate() {
        //const searchTerm = searchInput.value.toLowerCase();
        const dateFilterValue = dateFilter.value;
        
        filteredOrders = allOrders.filter(order => {
            // Filtre de recherche - UNIQUEMENT par numéro de commande
            //const matchesSearch = searchTerm === '' ||  // Si champ vide, on affiche tout
                              //   order.idCommande?.toString().toLowerCase().includes(searchTerm);
            
            // Filtre de date
            let matchesDate = true;
            if (dateFilterValue !== 'all') {
                const orderDate = new Date(order.dateCommande);
                if (isNaN(orderDate.getTime())) return false;
                
                const today = new Date();
                
                switch (dateFilterValue) {
                    case 'today':
                        matchesDate = orderDate.toDateString() === today.toDateString();
                        break;
                    case 'week':
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        matchesDate = orderDate >= weekStart;
                        break;
                    case 'month':
                        matchesDate = orderDate.getMonth() === today.getMonth() && 
                                      orderDate.getFullYear() === today.getFullYear();
                        break;
                    case 'custom':
                        if (startDate.value && endDate.value) {
                            const start = new Date(startDate.value);
                            const end = new Date(endDate.value);
                            end.setDate(end.getDate() + 1);
                            matchesDate = orderDate >= start && orderDate < end;
                        }
                        break;
                }
            }
            
            return  matchesDate;
        });
        
        currentPage = 1;
        updatePagination();
        displayCurrentPage();
    }
    
    // Afficher/masquer la plage de dates personnalisée
    function toggleDateRange() {
        customDateRange.style.display = dateFilter.value === 'custom' ? 'flex' : 'none';
        filterAndPaginate();
    }
    // Afficher la page courante
    function displayCurrentPage() {
        const startIndex = (currentPage - 1) * ordersPerPage;
        const endIndex = startIndex + ordersPerPage;
        const ordersToDisplay = filteredOrders.slice(startIndex, endIndex);
        
        ordersBody.innerHTML = '';
        
        if (ordersToDisplay.length === 0) {
            ordersBody.innerHTML = '<tr><td colspan="7">Aucune commande trouvée</td></tr>';
            return;
        }
        
        // Calculer le numéro de départ en fonction de la page
        const startNumber = (currentPage - 1) * ordersPerPage + 1;
        
        ordersToDisplay.forEach((order, index) => {
            const row = document.createElement('tr');
           // Remplacer le contenu du row.innerHTML par :
row.innerHTML = `
<td>${startNumber + index}</td>
<td>${formatDate(order.dateCommande)}</td>
<td>
    <div class="client-info">
        <strong>${order.nomClient} ${order.prenomClient}</strong><br>
        <small>${order.marque} ${order.modele} (${order.carburant})</small>
    </div>
</td>
<td>
    <div class="contact-info">
        <i class="fas fa-phone"></i> ${order.telClient}<br>
        <i class="fas fa-map-marker-alt"></i> ${order.adresseClient}
    </div>
</td>
<td>${order.nbProduits} produit(s)</td>
<td>${order.montantTotal.toFixed(2)} MAD</td>
<td><span class="status-badge status-${order.statut.replace(' ', '-')}">${order.statut}</span></td>
<td>
    <button class="action-btn view-btn" data-id="${order.idCommande}">
        <i class="fas fa-eye"></i> Détails
    </button>
</td>
`;
            
            row.querySelector('.view-btn').addEventListener('click', () => showOrderDetails(order.idCommande));
            ordersBody.appendChild(row);
        });
    }
    
    // Afficher les détails d'une commande
    function showOrderDetails(orderId) {
        fetch(`../pages/Admin-commandes.php?action=get_order_details&id=${orderId}`)
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(data => {
                if (!data || !data.success) {
                    throw new Error(data?.error || 'Détails non disponibles');
                }
                displayOrderModal(data.order);
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur: ' + error.message);
            });
    }
    
    // Afficher le modal avec les détails de la commande
    function displayOrderModal(order) {
        if (!order) {
            alert('Aucune donnée de commande reçue');
            return;
        }
        
        document.getElementById('order-id').textContent = order.idCommande || 'N/A';
        document.getElementById('order-date').textContent = formatDate(order.dateCommande, true);
        document.getElementById('order-total').textContent = (order.montantTotal || 0).toFixed(2);
        // LES INFOS CLIENT
    document.getElementById('client-name').textContent = `${order.nomClient} ${order.prenomClient}`;
    document.getElementById('client-phone').textContent = order.telClient || 'Non renseigné';
    document.getElementById('client-address').textContent = order.adresseClient || 'Adresse non disponible';
    document.getElementById('client-vehicle').textContent = 
        `${order.marque || ''} ${order.modele || ''} ${order.carburant ? `(${order.carburant})` : ''}`.trim() || 'Aucun véhicule enregistré';

        
        const productsBody = document.getElementById('order-products-body');
        productsBody.innerHTML = '';
        
        if (Array.isArray(order.produits)) {
            order.produits.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.nomProduit || 'Produit inconnu'}</td>
                    <td>${(product.prix_unitaire || 0).toFixed(2)} MAD</td>
                    <td>${product.quantite || 0}</td>
                    <td>${((product.prix_unitaire || 0) * (product.quantite || 0)).toFixed(2)} MAD</td>
                `;
                productsBody.appendChild(row);
            });
        }
        
        orderStatus.value = order.statut || 'en attente';
        currentOrderId = order.idCommande;
        orderModal.style.display = 'flex';
    }
    
    // Fermer le modal
    function closeOrderModal() {
        orderModal.style.display = 'none';
        currentOrderId = null;
    }
    
    // Mettre à jour le statut de la commande
    function updateOrderStatus() {
        if (!currentOrderId) {
            alert('Aucune commande sélectionnée');
            return;
        }
        
        const newStatus = orderStatus.value;
        
        fetch('/VroomIfy-4/pages/Admin-commandes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_status&id=${currentOrderId}&statut=${encodeURIComponent(newStatus)}`
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            if (!data || !data.success) {
                throw new Error(data?.error || 'Échec de la mise à jour');
            }
            loadOrders();
            closeOrderModal();
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur: ' + error.message);
        });
    }
    
    // Imprimer la commande
    function printOrder() {
        window.print();
    }
    
    // Mettre à jour la pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredOrders.length / ordersPerPage) || 1;
        
        pageInfo.textContent = `Page ${currentPage} sur ${totalPages}`;
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages;
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
        const totalPages = Math.ceil(filteredOrders.length / ordersPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayCurrentPage();
            updatePagination();
        }
    }
    
    // Formater la date
    function formatDate(dateString, withTime = false) {
        if (!dateString) return 'Date inconnue';
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';
        
        const options = { 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit',
            hour: withTime ? '2-digit' : undefined,
            minute: withTime ? '2-digit' : undefined,
            hour12: false
        };
        
        return date.toLocaleDateString('fr-FR', options);
    }
});
document.addEventListener("DOMContentLoaded", function() {
    fetch("/VroomIfy-4/pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});
