<?php
session_start();
include_once "con_dbb.php";

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Aucune commande spécifiée";
    header("Location: boutique.php");
    exit;
}

try {
    $idCommande = $_GET['id'];
    
    // Récupération de la commande principale
    $stmt = $pdo->prepare("
    SELECT c.*, 
           cl.nomClient AS nom, 
           cl.emailClient AS email 
    FROM commandes c
    LEFT JOIN client cl ON c.idClient = cl.idClient
    WHERE c.idCommande = ?
");
    $stmt->execute([$idCommande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        throw new Exception("Commande introuvable");
    }

    // Récupération des détails produits
    $stmt = $pdo->prepare("
        SELECT cd.*, p.nomProduit, p.image
        FROM commande_details cd
        JOIN produit p ON cd.idProduit = p.idProduit
        WHERE cd.idCommande = ?
    ");
    $stmt->execute([$idCommande]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la commande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .statut-badge {
            font-size: 0.9em;
            padding: 0.35em 0.65em;
        }
        .product-img {
            width: 100px;
            /*max-width: 80px;*/
            height: 100px;
        }
        
    </style>
</head>
<body>
    <div id="navbar-container"></div>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Commande #<?= $commande['idCommande'] ?></h3>
            </div>
            <div class="card-body">
                <!-- Section Informations client -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Informations client</h5>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?? 'Non spécifié' ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($commande['email']) ?? '-' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Détails commande</h5>
                        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($commande['dateCommande'])) ?></p>
                        <p><strong>Statut :</strong> 
                            <span class="badge 
                                <?= match($commande['statut']) {
                                    'en attente' => 'bg-warning text-dark',
                                    'validée' => 'bg-success',
                                    'annulée' => 'bg-danger',
                                    default => 'bg-secondary'
                                } ?> statut-badge">
                                <?= ucfirst($commande['statut']) ?>
                            </span>
                        </p>
                        <p><strong>Total :</strong> <?= number_format($commande['montantTotal'], 2) ?> MAD</p>
                    </div>
                </div>

                <!-- Section Produits -->
                <h5 class="mt-4">Produits commandés</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $produit): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($produit['image'])): ?>
                                        <img src="data:image/png;base64,<?= base64_encode($produit['image']) ?>" 
                                             class="product-img img-thumbnail">
                                    <?php else: ?>
                                        <div class="text-muted">Aucune image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($produit['nomProduit']) ?></td>
                                <td><?= number_format($produit['prix_unitaire'], 2) ?> MAD</td>
                                <td><?= $produit['quantite'] ?></td>
                                <td><?= number_format($produit['prix_unitaire'] * $produit['quantite'], 2) ?> MAD</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="/VroomIfy-4/pages/boutique.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Retour à la boutique
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimer
            </button>
        </div>
    </div>

    <div id="footer-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Chargement dynamique des éléments
    document.addEventListener("DOMContentLoaded", () => {
        const loadComponent = (url, elementId) => {
            fetch(url)
                .then(r => r.text())
                .then(d => document.getElementById(elementId).innerHTML = d)
                .catch(e => console.error(`Erreur ${elementId}:`, e));
        };

        loadComponent("/VroomIfy-4/pages/client-barre.php", "navbar-container");
        loadComponent("/VroomIfy-4/pages/pied-page.php", "footer-container");
    });
    </script>
</body>
</html>