<?php 
// Démarrer la session avant tout traitement
session_start();

// Réinitialiser complètement la session si elle est vide
if (empty($_SESSION)) {
    session_unset();
    session_destroy();
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/VroomIfy-4/styles/client-barre.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">VroomIfy</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <button class="btn-close d-lg-none position-absolute top-0 end-0 m-3" data-bs-toggle="collapse" data-bs-target="#navbarNav"></button>
                
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <!-- Vos liens de navigation... -->
                    <li class="nav-item">
                        <a class="nav-link" href="/VroomIfy-4/pages/accueil.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/VroomIfy-4/pages/service.html">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/VroomIfy-4/pages/boutique.php">Boutique</a>
                    </li>
                    <?php if($isLoggedIn): ?>
                        <li class="nav-item">
                        <a class="nav-link" href="/VroomIfy-4/pages/suivi.php">Suivi</a>
                    </li>
                        <?php endif; ?>
                    <?php if($isLoggedIn): ?>
                        <li class="nav-item">
                        <a class="nav-link" href="/VroomIfy-4/pages/contact.php">Contact</a>
                    </li>
                        <?php endif; ?>
                   

                </ul>
                
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <!--<li class="nav-item me-3">
                        <a href="panier.php" class="btn btn-outline-dark position-relative">
                            🛒 Panier
                            <span id="cartCount" class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
                        </a>
                    </li>-->
                    
                    <?php if($isLoggedIn): ?>
    <li class="nav-item me-3">
        <a href="/VroomIfy-4/pages/panier.php" class="btn btn-outline-dark position-relative">
            🛒 Panier
            <span id="cartCount" class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
        </a>
    </li>
<?php endif; ?>
                    <li class="nav-item dropdown">
                        <?php if($isLoggedIn): ?>
                            <img src="/VroomIfy-4/images/user-profil.jfif" alt="Profil" 
                                class="user-profile dropdown-toggle" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <ul class="dropdown-menu dropdown-menu-end p-2">
                                <li>
                                    <p class="dropdown-item text-muted mb-2">Bonjour <?= htmlspecialchars($_SESSION['nom']) ?>...</p>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="/VroomIfy-4/pages/profile.php">
                                        <span class="material-icons-outlined">manage_accounts</span>
                                        Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="/VroomIfy-4/pages/deconnexion.php">
                                        <span class="material-icons-outlined">logout</span>
                                        Déconnexion
                                    </a>
                                </li>
                            </ul>
                        <?php else: ?>
                            <a href="/VroomIfy-4/pages/connexion.php" class="btn btn-outline-primary">
                                Connexion
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!--script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></-script>-->
</body>
</html>