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
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_rdv':
        getRendezVous($db);
        break;
        
    case 'update_rdv':
        updateRendezVous($db);
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}

// Récupérer tous les rendez-vous
function getRendezVous($db) {
    try {
        $query = "SELECT * FROM rdv ORDER BY dateRdv DESC";
        $stmt = $db->query($query);
        $rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir les données binaires de la photo en base64 si elle existe
        foreach ($rdvs as &$rdv) {
            if (!empty($rdv['photo'])) {
                $rdv['photo'] = base64_encode($rdv['photo']);
            }
        }
        
        echo json_encode($rdvs);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Mettre à jour un rendez-vous
function updateRendezVous($db) {
    if (!isset($_POST['id']) || !isset($_POST['etat'])) {
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
        return;
    }
    
    try {
        $query = "UPDATE rdv SET etat = :etat WHERE idRdv = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':etat', $_POST['etat']);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>