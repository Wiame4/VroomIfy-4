<?php
//HAAADAA TBDEEEL 
// Activez ceci pour le débogage (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // N'affiche pas les erreurs au client

// Premier header absolument
header('Content-Type: application/json; charset=utf-8');

// Chemin DB - échappez les espaces
$dbPath = __DIR__ . '/../base de donnee/VroomIfy.db';
if (!file_exists($dbPath)) {
    echo json_encode([
        'success' => false,
        'error' => 'Base de données introuvable',
        'path' => $dbPath
    ]);
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de connexion à la base de données',
        'details' => $e->getMessage()
    ]);
    exit;
}

// Récupérer l'action
$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (empty($action)) {
    echo json_encode([
        'success' => false,
        'error' => 'Action non spécifiée'
    ]);
    exit;
}

// Gérer les actions
try {
    switch ($action) {
        case 'get_orders':
            // Vérifier si les tables existent
            $tablesExist = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('commandes', 'commande_details')")->fetchAll();
            if (count($tablesExist) < 2) {
                throw new Exception('Tables manquantes dans la base de données');
            }

            // Récupérer les commandes
            $ordersQuery = "SELECT 
            c.idCommande, 
            c.dateCommande, 
            c.statut,
            cl.nomClient,
            cl.prenomClient,
            cl.telClient,
            cl.adresseClient,
            cl.marque,
            cl.modele,
            cl.carburant,
            COUNT(d.idDetail) as nbProduits,
            COALESCE(SUM(d.quantite), 0) as totalQuantite,
            COALESCE(SUM(d.quantite * d.prix_unitaire), 0) as montantTotal
            FROM commandes c
            JOIN client cl ON c.idClient = cl.idClient
            LEFT JOIN commande_details d ON c.idCommande = d.idCommande
            GROUP BY c.idCommande
            ORDER BY c.dateCommande DESC";
            $orders = $db->query($ordersQuery)->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques
            $statsQuery = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN statut = 'en attente' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN strftime('%m', dateCommande) = strftime('%m', 'now') 
                     AND strftime('%Y', dateCommande) = strftime('%Y', 'now') 
                     THEN montantTotal ELSE 0 END) as month_revenue
                FROM commandes";
            $stats = $db->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'orders' => $orders,
                'stats' => $stats ?: [
                    'total_orders' => 0,
                    'pending_orders' => 0,
                    'month_revenue' => 0
                ]
            ]);
            break;

        /*case 'get_order_details':
            $orderId = $_GET['id'] ?? 0;
            if (empty($orderId)) {
                throw new Exception('ID de commande manquant');
            }

            // Détails de la commande
            $orderQuery = "SELECT * FROM commandes WHERE idCommande = ?";
            $stmt = $db->prepare($orderQuery);
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new Exception('Commande non trouvée');
            }

            // Produits de la commande
            $productsQuery = "SELECT 
                d.*, 
                p.nomProduit 
                FROM commande_details d
                JOIN produit p ON d.idProduit = p.idProduit
                WHERE d.idCommande = ?";
            $stmt = $db->prepare($productsQuery);
            $stmt->execute([$orderId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcul du total
            $total = array_reduce($products, function($sum, $product) {
                return $sum + ($product['prix_unitaire'] * $product['quantite']);
            }, 0);

            echo json_encode([
                'success' => true,
                'order' => [
                    'idCommande' => $order['idCommande'],
                    'dateCommande' => $order['dateCommande'],
                    'statut' => $order['statut'] ?? 'en attente',
                    'montantTotal' => $total,
                    'produits' => $products
                ]
            ]);
            break;*/
            case 'get_order_details':
                $orderId = $_GET['id'] ?? 0;
                if (empty($orderId)) {
                    throw new Exception('ID de commande manquant');
                }
            
                // Détails de la commande avec infos client
                $orderQuery = "SELECT c.*, cl.nomClient, cl.prenomClient, cl.telClient, 
                              cl.adresseClient, cl.marque, cl.modele, cl.carburant 
                              FROM commandes c
                              JOIN client cl ON c.idClient = cl.idClient
                              WHERE c.idCommande = ?";
                $stmt = $db->prepare($orderQuery);
                $stmt->execute([$orderId]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
                if (!$order) {
                    throw new Exception('Commande non trouvée');
                }
            
                // Produits de la commande
                $productsQuery = "SELECT 
                    d.*, 
                    p.nomProduit 
                    FROM commande_details d
                    JOIN produit p ON d.idProduit = p.idProduit
                    WHERE d.idCommande = ?";
                $stmt = $db->prepare($productsQuery);
                $stmt->execute([$orderId]);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                // Calcul du total
                $total = array_reduce($products, function($sum, $product) {
                    return $sum + ($product['prix_unitaire'] * $product['quantite']);
                }, 0);
            
                echo json_encode([
                    'success' => true,
                    'order' => [
                        'idCommande' => $order['idCommande'],
                        'dateCommande' => $order['dateCommande'],
                        'statut' => $order['statut'] ?? 'en attente',
                        'montantTotal' => $total,
                        'nomClient' => $order['nomClient'],
                        'prenomClient' => $order['prenomClient'],
                        'telClient' => $order['telClient'],
                        'adresseClient' => $order['adresseClient'],
                        'marque' => $order['marque'],
                        'modele' => $order['modele'],
                        'carburant' => $order['carburant'],
                        'produits' => $products
                    ]
                ]);
                break;

        case 'update_status':
            $orderId = $_POST['id'] ?? 0;
            $status = $_POST['statut'] ?? '';

            if (empty($orderId) || empty($status)) {
                throw new Exception('Paramètres manquants');
            }

            $query = "UPDATE commandes SET statut = ? WHERE idCommande = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$status, $orderId]);

            echo json_encode([
                'success' => true,
                'message' => 'Statut mis à jour'
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Action non reconnue'
            ]);
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>