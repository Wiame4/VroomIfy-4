<?php
session_start();
include_once "con_dbb.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['complete' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Vérification des champs obligatoires
    $stmt = $pdo->prepare("SELECT 
        nomClient, prenomClient, emailClient, 
        telClient, adresseClient, 
        marque, modele, carburant 
        FROM client WHERE idClient = ?");
    $stmt->execute([$userId]);
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);

    $complete = true;
    foreach ($profil as $value) {
        if (empty(trim($value))) {
            $complete = false;
            break;
        }
    }

    echo json_encode(['complete' => $complete]);
    
} catch (Exception $e) {
    echo json_encode(['complete' => false]);
}