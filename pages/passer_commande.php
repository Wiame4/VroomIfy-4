<?php
ob_start();
session_start();
include_once "con_dbb.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Veuillez vous connecter pour commander";
    header("Location: connexion.php");
    exit;
}

// Vérifier panier non vide
if (empty($_SESSION['panier'])) {
    $_SESSION['message'] = "Panier vide !";
    header("Location: panier.php");
    exit;
}

try {
    $pdo->beginTransaction();
    $idClient = $_SESSION['user_id'];
    $total = 0;

    // Calcul du total
    foreach ($_SESSION['panier'] as $id => $qty) {
        $stmt = $pdo->prepare("SELECT prix FROM produit WHERE idProduit = ?");
        $stmt->execute([$id]);
        $prix = $stmt->fetchColumn();
        $total += $prix * $qty;
    }

    // Création commande
    $stmt = $pdo->prepare("INSERT INTO commandes (idClient, montantTotal) VALUES (?, ?)");
    $stmt->execute([$idClient, $total]);
    $idCommande = $pdo->lastInsertId();

    // Détails commande
    foreach ($_SESSION['panier'] as $id => $qty) {
        $stmt = $pdo->prepare("SELECT prix FROM produit WHERE idProduit = ?");
        $stmt->execute([$id]);
        $prix = $stmt->fetchColumn();

        $stmt = $pdo->prepare("INSERT INTO commande_details 
            (idCommande, idProduit, quantite, prix_unitaire) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$idCommande, $id, $qty, $prix]);
    }

    $pdo->commit();
    unset($_SESSION['panier']);

    // Affichage de la confirmation
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
    Swal.fire({
        title: 'Commande validée !',
        text: 'Votre commande n°<?= $idCommande ?> est confirmée',
        icon: 'success',
        showDenyButton: true,
        confirmButtonText: 'Voir ma commande',
        denyButtonText: 'Retour à la boutique',
        customClass: {
            confirmButton: 'btn btn-success',
            denyButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'details_commande.php?id=<?= $idCommande ?>';
        } else {
            window.location.href = 'boutique.php';
        }
    });
    </script>
    </body>
    </html>
    <?php
    exit; // Arrêt immédiat après affichage

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Erreur : ".$e->getMessage();
    header("Location: panier.php");
}

ob_end_flush();
exit;
?>