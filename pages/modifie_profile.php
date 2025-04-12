<?php
session_start();

// Vérifier si l'utilisateur est connecté, sinon rediriger vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.html");
    exit();
}

// Connexion à la base de données SQLite
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Vérifier si la requête est de type POST (soumission du formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
;
    // Préparer et exécuter la requête de mise à jour
    $query = $pdo->prepare("UPDATE client SET nomClient = ?, prenomClient = ?, emailClient = ? WHERE idClient = ?");
    $query->execute([$nom ,$prenom ,$email , $user_id]);

    // Rediriger l'utilisateur vers son profil après la mise à jour
    header("Location: profile.php");
    exit();
}
?>
