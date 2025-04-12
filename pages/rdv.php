<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connexion à SQLite
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupération des informations de l'utilisateur connecté
$nomUtilisateur = isset($_SESSION['nom']) ? htmlspecialchars($_SESSION['nom']) : "";
$emailUtilisateur = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : "";

// Récupérer la liste des services
try {
    $stmt = $pdo->query("SELECT nomService FROM service");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $services = [];
}

// Vérifier si un service est passé en paramètre dans l'URL
$serviceSelectionne = isset($_GET['service']) ? htmlspecialchars($_GET['service']) : "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomRdv      = $_POST["nom"];
    $emailRdv    = $_POST["email"];
    $serviceRdv  = $_POST["service"];
    $dateRdv     = $_POST["date"];
    $addresse    = $_POST["addresse"];
    $vehicule    = $_POST["vehicule"];
    $commentaire = isset($_POST["commentaire"]) ? $_POST["commentaire"] : "";
    $etat        = "en attente"; // État du RDV

    // Gestion de l'upload d'image
    // Gestion de l'upload d'image (stockage en base de données)
$photo = null;
if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
    $photo = file_get_contents($_FILES["photo"]["tmp_name"]); // Convertir l'image en données binaires
}


    try {
        // Insertion dans la base de données
        $stmt = $pdo->prepare("INSERT INTO rdv (nomRdv, emailRdv, serviceRdv, dateRdv, addresse, vehicule, commentaire, photo, etat) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bindParam(1, $nomRdv);
$stmt->bindParam(2, $emailRdv);
$stmt->bindParam(3, $serviceRdv);
$stmt->bindParam(4, $dateRdv);
$stmt->bindParam(5, $addresse);
$stmt->bindParam(6, $vehicule);
$stmt->bindParam(7, $commentaire);
$stmt->bindParam(8, $photo, PDO::PARAM_LOB);
$stmt->bindParam(9, $etat);
$stmt->execute();    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de l\'enregistrement : " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }

    // Affichage du modal de confirmation puis redirection
    $rdvId = $pdo->lastInsertId(); // Récupérer l'ID du dernier RDV inséré
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8">
      <title>Confirmation du Rendez-vous</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    </head>
    <body>
     
<script>
    Swal.fire({
        title: 'Confirmation',
        text: 'Rendez-vous enregistré avec succès !',
        icon: 'success',
        showDenyButton: true,
        confirmButtonText: 'Voir les détails',
        denyButtonText: 'Retour aux services',
        customClass: {
            confirmButton: 'swal-confirm-btn',
            denyButton: 'swal-deny-btn'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/VroomIfy-4/pages/voir_rdv.php?id=<?= $rdvId ?>`;
        } else if (result.isDenied) {
            window.location.href = '/VroomIfy-4/pages/service.html';
        }
    });
</script>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prendre un Rendez-vous</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../styles/rdv.css">
</head>
<body id="body">
  <div id="navbar-container" class="navbar-container"></div>
  <div class="container-rdv">
    <div class="title">Prendre un rdv</div>
    <div class="content">
      <form action="rdv.php" method="POST" enctype="multipart/form-data">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Nom complet</span>
            <input type="text" name="nom" placeholder="Entrez votre nom complet" value="<?= $nomUtilisateur ?>" required oninvalid="this.setCustomValidity('Veuillez entrer un nom')" oninput="this.setCustomValidity('')" title="Veuillez entrer un nom'">
          </div>
          <div class="input-box">
            <span class="details">Email</span>
            <input type="email" name="email" placeholder="Entrez votre e-mail" value="<?= $emailUtilisateur ?>" required oninvalid="this.setCustomValidity('Veuillez entrer un email valide')" oninput="this.setCustomValidity('')" title="Veuillez entrer un email !">
          </div>
          <div class="input-box">
            <span class="details">Type de Service</span>
            <select name="service" id="service" class="input" required>
              <option value="" disabled <?= empty($serviceSelectionne) ? 'selected' : '' ?>>Sélectionnez un service</option>
              <?php foreach ($services as $service): ?>
                <option value="<?= htmlspecialchars($service['nomService']) ?>"
                        <?= ($service['nomService'] == $serviceSelectionne) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($service['nomService']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Adresse</span>
            <input type="text" name="addresse" placeholder="Entrez votre adresse" required oninvalid="this.setCustomValidity('Veuillez entrer une addresse')" oninput="this.setCustomValidity('')" title="Veuillez entrer une addresse">
          </div>
          <div class="input-box">
            <span class="details">Date et heure souhaitées</span>
            <input type="datetime-local" name="date" required title="Veuillez choisir une date">
          </div>
          <div class="input-box">
            <span class="details">Marque du véhicule</span>
            <input type="text" name="vehicule" placeholder="Entrez la marque du véhicule" required oninvalid="this.setCustomValidity('Veuillez entrer une marque du véhicule')" oninput="this.setCustomValidity('')" title="Veuillez entrer une marque du véhicule">
          </div>
        </div>
        <div class="input-box">
          <span class="details">Commentaire</span>
          <textarea name="commentaire" rows="5" cols="50" class="input" title="Veuillez ajouter un commentaire"></textarea>        
        </div>
        <div class="input-box">
          <span class="details">Image</span>
          <input type="file" class="input" name="photo" accept="image/png, image/jpeg">
        </div>
        <div class="button">
          <input type="submit" value="Prendre RDV">
        </div>
      </form>
    </div>
  </div>
  <div id="footer-container" class="footer-container"></div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/rdv.js"></script>
</body>
</html>
