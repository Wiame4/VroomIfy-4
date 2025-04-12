document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const productsGrid = document.getElementById('products-grid');
    const searchInput = document.getElementById('search-input');
    const addProductBtn = document.getElementById('add-product-btn');
    const productModal = document.getElementById('product-modal');
    const confirmModal = document.getElementById('confirm-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const productForm = document.getElementById('product-form');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    const cancelEditBtn = document.getElementById('cancel-edit');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    
    // Variables d'état
    let allProducts = [];
    let filteredProducts = [];
    let currentPage = 1;
    const productsPerPage = 4;
    let currentProductId = null;
    let productToDelete = null;

    // Charger les produits au démarrage
    loadProducts();

    // Écouteurs d'événements
    searchInput.addEventListener('input', filterAndPaginate);
    addProductBtn.addEventListener('click', showAddForm);
    closeModalBtns.forEach(btn => btn.addEventListener('click', closeAllModals));
    productForm.addEventListener('submit', saveProduct);
    cancelEditBtn.addEventListener('click', closeAllModals);
    prevPageBtn.addEventListener('click', goToPrevPage);
    nextPageBtn.addEventListener('click', goToNextPage);
    cancelDeleteBtn.addEventListener('click', closeAllModals);
    confirmDeleteBtn.addEventListener('click', deleteProduct);

    // Charger les produits depuis le serveur
    function loadProducts() {
        fetch('Admin-produits.php?action=get_products')
            .then(response => response.json())
            .then(data => {
                allProducts = data;
                filterAndPaginate();
            })
            .catch(error => {
                console.error('Erreur:', error);
                productsGrid.innerHTML = '<div class="error">Erreur lors du chargement des produits</div>';
            });
    }

    // Filtrer et paginer les produits
    function filterAndPaginate() {
        const searchTerm = searchInput.value.toLowerCase();
        
        // Filtrer les produits
        filteredProducts = allProducts.filter(product => {
            return (
                product.nomProduit.toLowerCase().includes(searchTerm) ||
                product.description.toLowerCase().includes(searchTerm)
            );
        });
        
        // Réinitialiser à la première page après un filtre
        currentPage = 1;
        updatePagination();
        displayCurrentPage();
    }

    // Afficher la page courante
    function displayCurrentPage() {
        const startIndex = (currentPage - 1) * productsPerPage;
        const endIndex = startIndex + productsPerPage;
        const productsToDisplay = filteredProducts.slice(startIndex, endIndex);
        
        productsGrid.innerHTML = '';
        
        if (productsToDisplay.length === 0) {
            productsGrid.innerHTML = '<div class="no-products">Aucun produit trouvé</div>';
            return;
        }
        
        productsToDisplay.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            productCard.innerHTML = `
                <div class="product-image-container">
                    ${product.image 
                        ? `<img src="data:image/jpeg;base64,${product.image}" class="product-image" alt="${product.nomProduit}">` 
                        : `<div class="product-image no-image"><i class="fas fa-box-open"></i></div>`
                    }
                </div>
                <div class="product-info">
                    <div class="product-name">${product.nomProduit}</div>
                    <div class="product-desc">${product.description}</div>
                    <div class="product-meta">
                        <span class="product-price">${parseFloat(product.prix).toFixed(2)} MAD</span>
                        <span class="product-stock">Stock: ${product.stock}</span>
                    </div>
                </div>
                <div class="product-actions">
                    <button class="edit-btn" data-id="${product.idProduit}">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button class="delete-btn" data-id="${product.idProduit}">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            `;
            
            // Ajouter les écouteurs d'événements
            productCard.querySelector('.edit-btn').addEventListener('click', () => editProduct(product.idProduit));
            productCard.querySelector('.delete-btn').addEventListener('click', () => confirmDelete(product.idProduit));
            
            productsGrid.appendChild(productCard);
        });
    }

    // Mettre à jour la pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
        
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
        const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayCurrentPage();
            updatePagination();
        }
    }

    // Afficher le formulaire d'ajout
    function showAddForm() {
        document.getElementById('modal-title').textContent = 'Ajouter un produit';
        document.getElementById('product-id').value = '';
        document.getElementById('product-name').value = '';
        document.getElementById('product-desc').value = '';
        document.getElementById('product-price').value = '';
        document.getElementById('product-stock').value = '';
        document.getElementById('product-image').value = '';
        document.getElementById('image-preview').innerHTML = '';
        
        currentProductId = null;
        productModal.style.display = 'flex';
    }

    // Afficher le formulaire de modification
    function editProduct(id) {
        const product = allProducts.find(p => p.idProduit == id);
        if (!product) return;
        
        document.getElementById('modal-title').textContent = 'Modifier le produit';
        document.getElementById('product-id').value = product.idProduit;
        document.getElementById('product-name').value = product.nomProduit;
        document.getElementById('product-desc').value = product.description;
        document.getElementById('product-price').value = product.prix;
        document.getElementById('product-stock').value = product.stock;
        
        const imagePreview = document.getElementById('image-preview');
        imagePreview.innerHTML = '';
        if (product.image) {
            const img = document.createElement('img');
            img.src = `data:image/jpeg;base64,${product.image}`;
            imagePreview.appendChild(img);
        }
        
        currentProductId = id;
        productModal.style.display = 'flex';
    }

    // Confirmer la suppression
    function confirmDelete(id) {
        productToDelete = id;
        document.getElementById('confirm-message').textContent = 'Êtes-vous sûr de vouloir supprimer ce produit ?';
        confirmModal.style.display = 'flex';
    }

    // Supprimer le produit
    function deleteProduct() {
        if (!productToDelete) return;
        
        fetch('Admin-produits.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_product&id=${productToDelete}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadProducts();
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

    // Enregistrer le produit (ajout ou modification)
    function saveProduct(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', currentProductId ? 'update_product' : 'add_product');
        formData.append('id', document.getElementById('product-id').value);
        formData.append('nom', document.getElementById('product-name').value);
        formData.append('desc', document.getElementById('product-desc').value);
        formData.append('prix', document.getElementById('product-price').value);
        formData.append('stock', document.getElementById('product-stock').value);
        
        const imageInput = document.getElementById('product-image');
        if (imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }
        
        fetch('Admin-produits.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadProducts();
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
        productModal.style.display = 'none';
        confirmModal.style.display = 'none';
        productToDelete = null;
    }

    // Prévisualisation de l'image
    document.getElementById('product-image').addEventListener('change', function(e) {
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
