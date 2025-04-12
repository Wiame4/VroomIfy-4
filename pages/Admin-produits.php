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
    case 'get_products':
        getProducts($db);
        break;
    case 'add_product':
        addProduct($db);
        break;
    case 'update_product':
        updateProduct($db);
        break;
    case 'delete_product':
        deleteProduct($db);
        break;
    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}

// Récupérer tous les produits
function getProducts($db) {
    try {
        $query = "SELECT * FROM produit ORDER BY nomProduit";
        $stmt = $db->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir l'image en base64 si elle existe
        foreach ($products as &$product) {
            if (!empty($product['image'])) {
                $product['image'] = base64_encode($product['image']);
            }
        }
        
        echo json_encode($products);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Ajouter un produit
function addProduct($db) {
    try {
        $image = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
        }
        
        $query = "INSERT INTO produit (nomProduit, description, prix, stock, image) 
                  VALUES (:nom, :desc, :prix, :stock, :image)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':desc', $_POST['desc']);
        $stmt->bindParam(':prix', $_POST['prix']);
        $stmt->bindParam(':stock', $_POST['stock']);
        $stmt->bindParam(':image', $image, $image ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Modifier un produit
function updateProduct($db) {
    try {
        // Récupérer l'image actuelle si aucune nouvelle image n'est fournie
        $currentImage = null;
        if (empty($_FILES['image']['tmp_name'])) {
            $query = "SELECT image FROM produit WHERE idProduit = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();
            $currentImage = $stmt->fetchColumn();
        } else {
            $currentImage = file_get_contents($_FILES['image']['tmp_name']);
        }
        
        $query = "UPDATE produit SET 
                  nomProduit = :nom,
                  description = :desc,
                  prix = :prix,
                  stock = :stock,
                  image = :image
                  WHERE idProduit = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':desc', $_POST['desc']);
        $stmt->bindParam(':prix', $_POST['prix']);
        $stmt->bindParam(':stock', $_POST['stock']);
        $stmt->bindParam(':image', $currentImage, $currentImage ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Supprimer un produit
function deleteProduct($db) {
    try {
        $query = "DELETE FROM produit WHERE idProduit = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>