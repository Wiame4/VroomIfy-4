<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    die("ID du rendez-vous non spécifié.");
}

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$rdvId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM rdv WHERE idRdv = ?");
$stmt->execute([$rdvId]);
$rdv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rdv) {
    die("Rendez-vous non trouvé.");
}

// Conversion de l'image BLOB en base64
$photoData = !empty($rdv['photo']) ? base64_encode($rdv['photo']) : '';
$photoSrc = $photoData ? "data:image/jpeg;base64," . $photoData : '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">    <!-- Ajouter ce style dans la balise <style> -->
<style>
    .print-btn {
        background: #2696e9;
        color: white;
        padding: 12px 24px;
        border-radius: 25px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 20px auto 10px auto;
    }

    .print-btn:hover {
        background:#2696e9;
        transform: scale(1.05);
    }

    .print-btn i {
        font-size: 1.2em;
    }
</style>

</head>
<body>
    <div id="navbar-container"></div>
    <div class="container mt-5 print-section">
        <h1 class="mb-4">Détails du Rendez-vous</h1>
        <div class="card shadow">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Nom :</strong> <?= htmlspecialchars($rdv['nomRdv']) ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($rdv['emailRdv']) ?></p>
                        <p><strong>Service :</strong> <?= htmlspecialchars($rdv['serviceRdv']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($rdv['dateRdv'])) ?></p>
                        <p><strong>Adresse :</strong> <?= htmlspecialchars($rdv['addresse']) ?></p>
                        <p><strong>Véhicule :</strong> <?= htmlspecialchars($rdv['vehicule']) ?></p>
                    </div>
                </div>
                
                <?php if (!empty($rdv['commentaire'])): ?>
                <div class="mb-3">
                    <h5>Commentaire :</h5>
                    <p><?= nl2br(htmlspecialchars($rdv['commentaire'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($photoSrc): ?>
                <div class="mb-3">
                    <h5>Photo du véhicule :</h5>
                    <img src="<?= $photoSrc ?>" alt="Photo du véhicule" class="img-thumbnail" style="max-width: 400px;">
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-4 no-print" style="text-align: center">
    <button onclick="window.print()" class="print-btn">
        <i class="bi bi-printer"></i> Imprimer le RDV
    </button>
    <a href="/VroomIfy-4/pages/service.html" style="color: #6c757d; text-decoration: none; margin-top: 15px; display: inline-block">
        <i class="bi bi-arrow-left"></i> Retour aux services
    </a>
</div>

    </div>
    <div id="footer-container"></div>
    <script>
        
// Charger la footer dynamiquement
document.addEventListener("DOMContentLoaded", function() {
    fetch("../pages/client-barre.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("navbar-container").innerHTML = data;
            // Forcer le recalcul des styles
            setTimeout(() => {
                document.getElementById("navbar-container").offsetHeight;
            }, 0);
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
    fetch("../pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});

    </script>
</body>
</html>