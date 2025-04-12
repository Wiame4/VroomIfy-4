<?php
header('Content-Type: application/json');
// Configurer la base de données SQLite
try {
    $db = new PDO('sqlite:../base de donnee/VroomIfy.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Erreur de connexion à la base de données']));
}

// Gérer les différentes actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_clients':
        getClients($db);
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}

// Récupérer tous les clients
function getClients($db) {
    try {
        $query = "SELECT * FROM client ORDER BY nomClient, prenomClient";
        $stmt = $db->query($query);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($clients);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>