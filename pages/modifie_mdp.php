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

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Erreur : Vous devez être connecté pour modifier votre mot de passe.'); window.location.href='connexion.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $mdpAncien = $_POST["mdpAncien"];
    $mdpNouveaux = $_POST["mdpNouveaux"];
    $mdpNouveauxConfirme = $_POST["mdpNouveauxConfirme"];

    // Vérifier si le mot de passe actuel est correct
    $stmt = $pdo->prepare("SELECT mot_de_passeClient FROM client WHERE idClient = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($mdpAncien, $user["mot_de_passeClient"])) {
        echo "<script>alert('Erreur : Mot de passe actuel incorrect.'); window.history.back();</script>";
        exit();
    }

    // Vérifier si les nouveaux mots de passe correspondent
    if ($mdpNouveaux !== $mdpNouveauxConfirme) {
        echo "<script>alert('Erreur : Les nouveaux mots de passe ne correspondent pas.'); window.history.back();</script>";
        exit();
    }

    // Vérifier si le nouveau mot de passe respecte les critères de sécurité
    if (strlen($mdpNouveaux) < 8 || !preg_match("/[A-Z]/", $mdpNouveaux) || !preg_match("/[0-9]/", $mdpNouveaux)) {
        echo "<script>alert('Erreur : Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule et un chiffre.'); window.history.back();</script>";
        exit();
    }

    // Hacher le nouveau mot de passe avant de le stocker
    $mdpNouveauxHash = password_hash($mdpNouveaux, PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe dans la base de données
    try {
        $stmt = $pdo->prepare("UPDATE client SET mot_de_passeClient = ? WHERE idClient = ?");
        $stmt->execute([$mdpNouveauxHash, $user_id]);

        echo "<script>alert('Votre mot de passe a été mis à jour avec succès.'); window.location.href='profile.php';</script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de la mise à jour du mot de passe : " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }
}
?>
