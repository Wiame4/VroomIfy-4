<?php
session_start();

// Vérification que l'utilisateur est connecté
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

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $carburant = $_POST['carburant'];

    // Mise à jour des informations sur le véhicule dans la base de données
    $query = $pdo->prepare("UPDATE client SET marque = ?, modele = ?, carburant = ? WHERE idClient = ?");
    $query->execute([$marque, $modele, $carburant, $user_id]);

    // Rediriger vers la page de profil avec un message de succès
    header("Location: profile.php");
    exit();
}
?>
