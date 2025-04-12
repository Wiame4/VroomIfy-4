<?php
$database_path = realpath(__DIR__ . '/../base de donnee/VroomIfy.db');

try {
    if (!file_exists($database_path)) {
        throw new Exception("Base de données introuvable !");
    }

    $pdo = new PDO("sqlite:" . $database_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>