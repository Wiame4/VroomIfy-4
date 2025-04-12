<?php
session_start(); // Démarre la session

// Connexion à la base de données SQLite
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Veuillez vous connecter pour accéder à votre profil.');
        window.location.href = 'accueil.html';
      </script>";
    exit();
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur dans la base de données
$query = $pdo->prepare("SELECT * FROM client WHERE idClient = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/profile.css">
    
    <title>Profile</title>
</head>
<body class="body">
    <div id ="navbar-container"></div>
     <div class="container-profile">
     <div class="leftbox">
            <nav>
                <a onclick="tabs(0)" class="tab active"><i class="fa-solid fa-user"></i></a>
                <a onclick="tabs(1)" class="tab"><i class="fa-solid fa-car"></i></a>
                <a onclick="tabs(2)" class="tab"><i class="fa-solid fa-lock"></i></a>
                <a onclick="tabs(3)" class="tab"><i class="fa-solid fa-house"></i></a>
            </nav>
        </div>
        <div class="rightbox">
            <!--premiere tab : -->
            <div class="profile tabShow">
                <form action="modifie_profile.php" method="POST">
                   <h1 class="grand-titre">Informations Personnelles</h1>

                   <h2 class="petit-titre">Nom</h2>
                   <input type="text" class="input" name="nom" value="<?php echo htmlspecialchars($user['nomClient']); ?>" required oninvalid="this.setCustomValidity('Veuillez entrer un nom')" oninput="this.setCustomValidity('')">

                   <h2 class="petit-titre">Prénom</h2>    
                   <input type="text" class="input" name="prenom" value="<?php echo htmlspecialchars($user['prenomClient']); ?>" required oninvalid="this.setCustomValidity('Veuillez entrer un prénom')" oninput="this.setCustomValidity('')">

                   <h2 class="petit-titre">Email</h2>
                   <input type="email" class="input" name="email" value="<?php echo htmlspecialchars($user['emailClient']); ?>" required oninvalid="this.setCustomValidity('Veuillez entrer un email valide')" oninput="this.setCustomValidity('')">

                   <button class="btnn" type="submit">Enregistrer les modifications</button>
                </form>
             </div>
             <!--deusieme tab : Modification des informations sur le véhicule-->
             <div class="payment tabShow">
                <form action="modifie_vehicule.php" method="POST">
                    <h1  class="grand-titre">Informations sur le véhicule</h1>

                     <h2 class="petit-titre">Marque de la véhicule</h2>
                       <input type="text" class="input" name="marque" value="<?php echo htmlspecialchars($user['marque']); ?>" required oninvalid="this.setCustomValidity('Veuillez entrer une marque du véhicule')" oninput="this.setCustomValidity('')">

                     <h2 class="petit-titre">Modèle de la véhicule</h2>
                      <input type="text" class="input" name="modele" value="<?php echo htmlspecialchars($user['modele']); ?>" required oninvalid="this.setCustomValidity('Veuillez entrer un modèle du véhicule')" oninput="this.setCustomValidity('')">

                     <h2 class="petit-titre">Type de carburant</h2>
                       <select name="carburant" id="carburant" class="input" required>
                              <option value="essence" <?php echo ($user['carburant'] == 'essence') ? 'selected' : ''; ?>>Essence</option>
                              <option value="diesel" <?php echo ($user['carburant'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                              <option value="electrique" <?php echo ($user['carburant'] == 'electrique') ? 'selected' : ''; ?>>Electrique</option>
                              <option value="hybride" <?php echo ($user['carburant'] == 'hybride') ? 'selected' : ''; ?>>Hybride</option>
                       </select>
       
                          <button class="btnn" type="submit">Enregistrer les modifications</button>
                 </form>
            </div>
            <!--troisieme tab : Modification du mot de passe -->
<div class="subscription tabShow">
    <form action="modifie_mdp.php" method="POST">
        <h1 class="grand-titre">Modification du Mot de Passe</h1>

        <h2 class="petit-titre">Ancien mot de passe</h2>   
        <input type="password" class="input" name="mdpAncien" placeholder="Ancien mot de passe..." required oninvalid="this.setCustomValidity('Veuillez entrer un mot de passe')" oninput="this.setCustomValidity('')">

        <h2 class="petit-titre">Nouveau mot de passe</h2>   
        <input type="password" class="input" name="mdpNouveaux" placeholder="Nouveau mot de passe..." required>

        <h2 class="petit-titre">Confirmer le nouveau mot de passe</h2>
        <input type="password" class="input" name="mdpNouveauxConfirme" placeholder="Confirmer le nouveau mot de passe..." required oninvalid="this.setCustomValidity('Veuillez confirmer le mot de passe')" oninput="this.setCustomValidity('')">

        <button class="btnn" type="submit">Enregistrer les modifications</button>
    </form>
</div>
            <!--fourth tab : Modification du telephone et adresse du client -->
<div class="contact tabShow">
    <form action="modifie_contact.php" method="POST">
        <h1 class="grand-titre">Coordonnées</h1>

        <h2 class="petit-titre">Téléphone</h2>
        <input type="tel" class="input" name="telephone" value="<?php echo htmlspecialchars($user['telClient']); ?>" required 
               pattern="[0-9]{10}" 
               oninvalid="this.setCustomValidity('Veuillez entrer un numéro de téléphone valide (10 chiffres)')" 
               oninput="this.setCustomValidity('')">

        <h2 class="petit-titre">Adresse</h2>    
        <input type="text" class="input" name="adresse" value="<?php echo htmlspecialchars($user['adresseClient']); ?>" required 
               oninvalid="this.setCustomValidity('Veuillez entrer une adresse')" 
               oninput="this.setCustomValidity('')">

        <button class="btnn" type="submit">Enregistrer les modifications</button>
    </form>
</div>
        </div>
    </div>

  <div id="footer-container"></div>
    <script>
        const tabBtn = document.querySelectorAll(".tab");
        const tab = document.querySelectorAll(".tabShow");

        function tabs(panelIndex) {
            tab.forEach(function(node) {
                node.style.display = "none";
            });
            tab[panelIndex].style.display = "block";
        }
        tabs(0);    
    </script>

    <script>
       document.querySelectorAll(".tab").forEach(tab => {
        tab.addEventListener("click", function() {
        document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
        this.classList.add("active");
    });
});

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/profile.js"></script>
</body>
</html>
