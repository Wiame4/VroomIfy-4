<?php 
session_start();
include_once "con_dbb.php";
$isLoggedIn = isset($_SESSION['user_id']);

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique</title>
    <link rel="stylesheet" href="../styles/panier.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style de base */
.modal.fade {
    background: rgba(0, 0, 0, 0.5);
    display: none;
    animation: modalFadeIn 0.4s ease-out;
}

.modal-dialog {
    margin: 1.75rem auto;
    max-width: 500px;
}

.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

/* Header */
.modal-header {
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    padding: 1rem 1.5rem;
    position: relative;
}

.modal-header h5::before {
    content: "🔐 ";
    margin-right: 8px;
}

.btn-close {
    position: absolute;
    right: 1.5rem;
    top: 1.2rem;
    opacity: 0.7;
    transition: all 0.3s;
}

.btn-close:hover {
    opacity: 1;
    transform: rotate(90deg);
}

/* Corps */
.modal-body {
    padding: 1.5rem;
    color: #495057;
    font-size: 1.05rem;
}

.modal-body::before {
    content: "🛒 ";
    margin-right: 10px;
    font-size: 1.3em;
    vertical-align: middle;
}

/* Footer */
.modal-footer {
    border-top: 2px solid #e9ecef;
    padding: 1rem 1.5rem;
    gap: 12px;
}

.btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.btn-secondary::before {
    content: "🚪 ";
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border: none;
}

.btn-primary::before {
    content: "🔑 ";
}

/* Animation */
@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Micro-interactions */
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    box-shadow: 0 5px 20px rgba(76, 175, 80, 0.3);
}
    </style>
</head>
<body>
    <div id="navbar-container"></div>

    <!-- Modale de connexion -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Connexion requise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Vous devez vous connecter pour ajouter des articles à votre panier.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a href="/VroomIfy-4/pages/connexion.php" class="btn btn-primary">Se connecter</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false'; ?>;
    </script>

    <section class="products_list">
        <?php 
        try {
            $stmt = $pdo->query("SELECT * FROM produit");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $imageData = $row['image'];
                $imageSrc = !empty($imageData) 
                    ? "data:image/png;base64,".base64_encode($imageData)
                    : "../images/default.png";
        ?>
        <div class="product">
            <div class="image_product">
                <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($row['nomProduit']) ?>">
            </div>
            <div class="content">
                <h4 class="name"><?= htmlspecialchars($row['nomProduit']) ?></h4>
                <h2 class="price"><?= number_format($row['prix'], 2) ?> MAD</h2>
                <a href="ajouter_panier.php?id=<?= $row['idProduit'] ?>" class="add-to-cart">Ajouter au panier</a>
            </div>
        </div>
        <?php 
            }
        } catch (PDOException $e) {
            echo "<p>Erreur : ".$e->getMessage()."</p>";
        }
        ?>
    </section>
    <div id="footer-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/boutique.js"></script>
</body>
</html>