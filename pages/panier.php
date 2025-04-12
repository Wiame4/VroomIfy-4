<?php 
ob_start();
session_start();
include_once "con_dbb.php";

// Initialisation du panier
$_SESSION['panier'] = $_SESSION['panier'] ?? [];

// Gestion des messages
if (isset($_SESSION['message'])) {
    echo '<div class="message">'.$_SESSION['message'].'</div>';
    unset($_SESSION['message']);
}

// Suppression produit
if (isset($_GET['del'])) {
    $id_del = (int)$_GET['del'];
    unset($_SESSION['panier'][$id_del]);
    echo '<script>document.dispatchEvent(new Event("cartUpdated"))</script>';
}
?> 

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/panier.css">
</head>
<body class="panier">
    <div id="navbar-container"></div>
    
    <section class="content-container">
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php  
                $total = 0;
                $ids = array_keys($_SESSION['panier']);

                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("SELECT * FROM produit WHERE idProduit IN ($placeholders)");
                    $stmt->execute($ids);
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($products as $product) {
                        $id = $product['idProduit'];
                        $quantite = $_SESSION['panier'][$id];
                        $prix = $product['prix'];
                        $total += $prix * $quantite;
                        
                        $image = $product['image'] ? 
                            "data:image/png;base64,".base64_encode($product['image']) : 
                            '../images/default-image.png';
                ?>
                <tr>    
                    <td><img class="img-thumbnail" src="<?= $image ?>" style="width:80px"></td>
                    <td><?= htmlspecialchars($product['nomProduit']) ?></td>
                    <td><?= number_format($prix, 2) ?>MAD</td>
                    <td>
                        <a href="modifier_quantite.php?action=dec&id=<?= $id ?>" class="btn btn-sm btn-outline-secondary">-</a>
                        <span class="mx-2"><?= $quantite ?></span>
                        <a href="modifier_quantite.php?action=inc&id=<?= $id ?>" class="btn btn-sm btn-outline-secondary">+</a>
                    </td>
                    <td><?= number_format($prix * $quantite, 2) ?>MAD</td>
                    <td><a href="panier.php?del=<?= $id ?>" class="btn btn-danger btn-sm">🗑</a></td>
                </tr>
                <?php }} else { ?>
                    <tr><td colspan="6" class="text-center">Votre panier est vide</td></tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <th colspan="4">Total général</th>
                    <td colspan="2" class="font-weight-bold"><?= number_format($total, 2) ?>MAD</td>
                </tr>
                <?php if(!empty($ids)) { ?>
                <tr>
                    <td colspan="6" class="text-end">
                        <form action="passer_commande.php" method="post" id="commandeForm">
                            <button type="submit" class="btn btn-lg btn-success">Valider la commande</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tfoot>
        </table>
    </section>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de commande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    🚚 Votre commande sera livrée dans un délai de 2 jours ouvrables. Souhaitez-vous confirmer ?
                    Remarque : Le paiement à la livraison
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmOrder">Confirmer</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Profil Incomplet -->
<div class="modal fade" id="incompleteProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Profil incomplet !</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ❗ Vous devez remplir toutes les informations de votre profil (coordonnées, véhicule et adresse) avant de passer commande.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-user-edit me-2"></i>Compléter mon profil
                </a>
            </div>
        </div>
    </div>
</div>
<div id="footer-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/panier.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('commandeForm');
    if(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                // Vérification du profil
                const response = await fetch('verifier_profile.php');
                const {complete} = await response.json();
                
                if (complete) {
                    new bootstrap.Modal(document.getElementById('confirmationModal')).show();
                } else {
                    new bootstrap.Modal(document.getElementById('incompleteProfileModal')).show();
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la vérification du profil');
            }
        });

        // Confirmation de commande
        document.getElementById('confirmOrder').addEventListener('click', () => {
            form.submit();
        });
    }
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>