<?php
include_once "con_dbb.php";

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT c.*, cd.*, p.nomProduit, p.image 
                      FROM commandes c
                      JOIN commande_details cd ON c.idCommande = cd.idCommande
                      JOIN produit p ON cd.idProduit = p.idProduit
                      WHERE c.idCommande = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();

if (empty($details)) exit('Aucun détail trouvé');

echo '<h4>Produits commandés</h4>';
echo '<div class="row">';
foreach ($details as $row) {
    $image = $row['image'] ? 'data:image/png;base64,'.base64_encode($row['image']) : 'default-image.jpg';
    echo '<div class="col-md-4 mb-3">
            <div class="card">
                <img src="'.$image.'" class="card-img-top product-img">
                <div class="card-body">
                    <h5>'.$row['nomProduit'].'</h5>
                    <div>Quantité: '.$row['quantite'].'</div>
                    <div>Prix unitaire: '.number_format($row['prix_unitaire'], 2).' MAD</div>
                </div>
            </div>
          </div>';
}
echo '</div>';
echo '<hr><h5>Total: '.number_format($details[0]['montantTotal'], 2).' MAD</h5>';