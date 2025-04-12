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
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_services':
        getServices($db);
        break;
    case 'add_service':
        addService($db);
        break;
    case 'update_service':
        updateService($db);
        break;
    case 'delete_service':
        deleteService($db);
        break;
    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}

// Récupérer tous les services
function getServices($db) {
    try {
        $query = "SELECT * FROM service ORDER BY nomService";
        $stmt = $db->query($query);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir l'image en base64 si elle existe
        foreach ($services as &$service) {
            if (!empty($service['ImageService'])) {
                $service['ImageService'] = base64_encode($service['ImageService']);
            }
        }
        
        echo json_encode($services);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Ajouter un service
function addService($db) {
    try {
        $image = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
        }
        
        $query = "INSERT INTO service (nomService, descriptionService, ImageService) 
                  VALUES (:nom, :desc, :image)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':desc', $_POST['desc']);
        $stmt->bindParam(':image', $image, $image ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Modifier un service
function updateService($db) {
    try {
        // Récupérer l'image actuelle si aucune nouvelle image n'est fournie
        $currentImage = null;
        if (empty($_FILES['image']['tmp_name'])) {
            $query = "SELECT ImageService FROM service WHERE idService = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();
            $currentImage = $stmt->fetchColumn();
        } else {
            $currentImage = file_get_contents($_FILES['image']['tmp_name']);
        }
        
        $query = "UPDATE service SET 
                  nomService = :nom,
                  descriptionService = :desc,
                  ImageService = :image
                  WHERE idService = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':desc', $_POST['desc']);
        $stmt->bindParam(':image', $currentImage, $currentImage ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Supprimer un service
function deleteService($db) {
    try {
        $query = "DELETE FROM service WHERE idService = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>