// Gestionnaire de clic global pour tout le document
document.addEventListener('click', function(e) {
    const cartLink = e.target.closest('a[href="panier.php"]');
    
    if (cartLink) {
        const isLoggedIn = cartLink.dataset.isLoggedIn === 'true';
        
        if (!isLoggedIn) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            modal.show();
        }
    }
});
