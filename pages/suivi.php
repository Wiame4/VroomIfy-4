<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

try {
    // Connexion à SQLite
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupération des commandes de l'utilisateur
// Récupérer les commandes
$commandes = [];
try {
    $stmt = $pdo->prepare("
    SELECT c.idCommande, c.dateCommande, c.statut, c.montantTotal
    FROM commandes c
    WHERE c.idClient = ? 
    AND c.statut != 'annulee' 
    GROUP BY c.idCommande
    ORDER BY c.dateCommande DESC
");
    $stmt->execute([$_SESSION['user_id']]);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des commandes";
}

// Récupération des rendez-vous de l'utilisateur
$rdvs = [];
try {
    $stmt = $pdo->prepare("
    SELECT * FROM rdv 
    WHERE emailRdv = ? 
    ORDER BY dateRdv DESC
");

    $stmt->execute([$_SESSION['email']]);
    $rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rdvs = [];
}

// Vérifier si on affiche les détails d'une commande ou d'un rdv
$detailsCommande = null;
$detailsRdv = null;

if (isset($_GET['commande_id'])) {
    try {
        // Récupérer les détails de la commande
        $stmt = $pdo->prepare("SELECT c.*, cd.*, p.nomProduit, p.image 
        FROM commandes c
        JOIN commande_details cd ON c.idCommande = cd.idCommande
        JOIN produit p ON cd.idProduit = p.idProduit
        WHERE c.idCommande = ? AND c.idClient = ?");
        $stmt->execute([$_GET['commande_id'], $_SESSION['user_id']]);
        $detailsCommande = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $detailsCommande = null;
    }
}

if (isset($_GET['rdv_id'])) {
    try {
        // Récupérer les détails du rdv
        $stmt = $pdo->prepare("SELECT * FROM rdv WHERE idRdv = ? AND emailRdv = ?");
        $stmt->execute([$_GET['rdv_id'], $_SESSION['email']]);
        $detailsRdv = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $detailsRdv = null;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Commandes et Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-pills .nav-link {
            color: #0d6efd;
        }
        .card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .badge {
            color : black;
            background-color : transparent;
        }
        .badge-en-attente {
            background: grey;
    color: white;
}
.badge-en-cours {
    background-color: blue;
    color: white;
}
.badge-termine {
    background-color:green;
    color: white;
}
.badge-annule {
    background-color: orange;
    color: white;
}
        .details-container {
            display: none;
        }
        .print-only {
            display: none;
        }
    
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            body {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div id="navbar-container" class="navbar-container"></div>
    
    <div class="container mt-4 mb-5">
        <!--<h2 class="mb-4">Suivi des Commandes et Rendez-vous</h2>-->
        
        <!-- Onglets de navigation -->
        <ul class="nav nav-pills justify-content-center mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-commandes-tab" data-bs-toggle="pill" 
                    data-bs-target="#pills-commandes" type="button" role="tab" 
                    aria-controls="pills-commandes" aria-selected="true">
                    Commandes (<?= count($commandes) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-rdv-tab" data-bs-toggle="pill" 
                    data-bs-target="#pills-rdv" type="button" role="tab" 
                    aria-controls="pills-rdv" aria-selected="false">
                    Rendez-vous (<?= count($rdvs) ?>)
                </button>
            </li>
        </ul>
        
        <!-- Contenu des onglets -->
        <div class="tab-content" id="pills-tabContent">
            <!-- Onglet Commandes -->
            <div class="tab-pane fade show active" id="pills-commandes" role="tabpanel" aria-labelledby="pills-commandes-tab">
                <?php if (!empty($detailsCommande)): ?>
                    <!-- Détails d'une commande spécifique -->
                    <div class="details-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Détails de la commande #<?= $detailsCommande[0]['idCommande'] ?></h3>
                            <button class="btn btn-outline-primary no-print" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimer
                            </button>
                        </div>
                        
                        <div class="print-only text-center mb-4">
                            <h4>VroomIfy - Détails de commande</h4>
                            <p>Date: <?= date('d/m/Y H:i', strtotime($detailsCommande[0]['dateCommande'])) ?></p>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Informations de la commande</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Numéro de commande:</strong> <?= $detailsCommande[0]['idCommande'] ?></p>
                                        <p><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($detailsCommande[0]['dateCommande'])) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                    <p><strong>Statut:</strong> 
                                    <span class="badge <?= ($detailsCommande[0]['statut'] == 'en attente') ? 'badge-en-attente' : '' ?>
                 <?= ($detailsCommande[0]['statut'] == 'expediee') ? 'badge-en-cours' : '' ?>
                 <?= ($detailsCommande[0]['statut'] == 'livree') ? 'badge-termine' : '' ?>
                 <?= ($detailsCommande[0]['statut'] == 'traitement') ? 'badge-annule' : '' ?>">
    <?= ucfirst($detailsCommande[0]['statut']) ?>
</span>
</p>

                                        <p><strong>Total:</strong> <?= number_format($detailsCommande[0]['montantTotal'], 2) ?> MAD</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Produits commandés</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Prix unitaire</th>
                                                <th>Quantité</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($detailsCommande as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($item['image']): ?>
                                                                <img src="data:image/png;base64,<?= base64_encode($item['image']) ?>" 
                                                                     alt="<?= htmlspecialchars($item['nomProduit']) ?>" 
                                                                     class="img-thumbnail me-3" style="width: 50px;">
                                                            <?php endif; ?>
                                                            <span><?= htmlspecialchars($item['nomProduit']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?= number_format($item['prix_unitaire'], 2) ?> MAD</td>
                                                    <td><?= $item['quantite'] ?></td>
                                                    <td><?= number_format($item['prix_unitaire'] * $item['quantite'], 2) ?> MAD</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td><strong><?= number_format($detailsCommande[0]['montantTotal'], 2) ?> MAD</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-secondary no-print" onclick="history.back()">
                            <i class="bi bi-arrow-left"></i> Retour
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Liste des commandes -->
                    <?php if (empty($commandes)): ?>
                        <div class="alert alert-info">
                            Vous n'avez passé aucune commande pour le moment.
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <?php foreach ($commandes as $commande): ?>
                                <div class="col">
                                    <div class="card h-100" 
                                         onclick="window.location.href='?commande_id=<?= $commande['idCommande'] ?>'">
                                        <div class="card-header d-flex justify-content-between">
                                            <span>Commande #<?= $commande['idCommande'] ?></span>
                                            
                                            <span class="badge 
                                                <?= $commande['statut'] == 'en attente' ? 'badge-en-attente' : '' ?>
                                                <?= $commande['statut'] == 'expediee' ? 'badge-en-cours' : '' ?>
                                                <?= $commande['statut'] == 'livree' ? 'badge-termine' : '' ?>
                                                <?= $commande['statut'] == 'traitement' ? 'badge-annule' : '' ?>">
                                                <?= ucfirst($commande['statut']) ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= number_format($commande['montantTotal'], 2) ?> MAD</h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($commande['dateCommande'])) ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <a href="?commande_id=<?= $commande['idCommande'] ?>" class="btn btn-sm btn-outline-primary">
                                                Voir détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Onglet Rendez-vous -->
            <div class="tab-pane fade" id="pills-rdv" role="tabpanel" aria-labelledby="pills-rdv-tab">
                <?php if (!empty($detailsRdv)): ?>
                    <!-- Détails d'un rendez-vous spécifique -->
                    <div class="details-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Détails du rendez-vous #<?= $detailsRdv['idRdv'] ?></h3>
                            <div>
                                <button class="btn btn-outline-primary no-print me-2" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Imprimer
                                </button>
                                <button class="btn btn-secondary no-print" onclick="history.back()">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </button>
                            </div>
                        </div>
                        
                        <div class="print-only text-center mb-4">
                            <h4>VroomIfy - Détails de rendez-vous</h4>
                            <p>Date: <?= date('d/m/Y H:i', strtotime($detailsRdv['dateRdv'])) ?></p>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Informations du rendez-vous</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Numéro de RDV:</strong> <?= $detailsRdv['idRdv'] ?></p>
                                        <p><strong>Nom:</strong> <?= htmlspecialchars($detailsRdv['nomRdv']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($detailsRdv['emailRdv']) ?></p>
                                        <p><strong>Service:</strong> <?= htmlspecialchars($detailsRdv['serviceRdv']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($detailsRdv['dateRdv'])) ?></p>
                                        <p><strong>Adresse:</strong> <?= htmlspecialchars($detailsRdv['addresse']) ?></p>
                                        <p><strong>Véhicule:</strong> <?= htmlspecialchars($detailsRdv['vehicule']) ?></p>
                                        <p><strong>Statut:</strong> 
                                            <span class="badge 
                                                <?= $detailsRdv['etat'] == 'en attente' ? 'bg-secondary' : '' ?>
                                                <?= $detailsRdv['etat'] == 'en cours' ? 'badge-en-cours' : '' ?>
                                                <?= $detailsRdv['etat'] == 'terminé' ? 'bg-success' : '' ?>
                                                <?= $detailsRdv['etat'] == 'annulé' ? 'bg-danger' : '' ?>">
                                                <?= ucfirst($detailsRdv['etat']) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if (!empty($detailsRdv['commentaire'])): ?>
                                    <div class="mt-3">
                                        <h6>Commentaire:</h6>
                                        <p><?= nl2br(htmlspecialchars($detailsRdv['commentaire'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($detailsRdv['photo'])): ?>
                                    <div class="mt-3">
                                        <h6>Photo:</h6>
                                        <img src="data:image/png;base64,<?= base64_encode($detailsRdv['photo']) ?>" 
                                             alt="Photo du véhicule" class="img-thumbnail" style="max-width: 300px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Liste des rendez-vous -->
                    <?php if (empty($rdvs)): ?>
                        <div class="alert alert-info">
                            Vous n'avez pris aucun rendez-vous pour le moment.
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <?php foreach ($rdvs as $rdv): ?>
                                <div class="col">
                                    <div class="card h-100" 
                                         onclick="window.location.href='?rdv_id=<?= $rdv['idRdv'] ?>'">
                                        <div class="card-header d-flex justify-content-between">
                                            <span>RDV #<?= $rdv['idRdv'] ?></span>
                                            <span class="badge 
                                                <?= $rdv['etat'] == 'en attente' ? 'bg-secondary' : '' ?>
                                                <?= $rdv['etat'] == 'en cours' ? 'bg-primary' : '' ?>
                                                <?= $rdv['etat'] == 'terminé' ? 'bg-success' : '' ?>
                                                <?= $rdv['etat'] == 'annulé' ? 'bg-danger' : '' ?>">
                                                <?= ucfirst($rdv['etat']) ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($rdv['serviceRdv']) ?></h5>
                                            <p class="card-text">
                                                <strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($rdv['dateRdv'])) ?><br>
                                                <strong>Véhicule:</strong> <?= htmlspecialchars($rdv['vehicule']) ?>
                                            </p>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <a href="?rdv_id=<?= $rdv['idRdv'] ?>" class="btn btn-sm btn-outline-primary">
                                                Voir détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div id="footer-container" class="footer-container"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   <script>
     // Charger la navbar
 fetch("/VroomIfy-4/pages/client-barre.php")
 .then(response => {
     if (!response.ok) {
         throw new Error('Erreur HTTP ' + response.status);
     }
     return response.text();
 })
 .then(data => {
     const navbarContainer = document.getElementById("navbar-container");
     if (navbarContainer) {
         navbarContainer.innerHTML = data;
     } else {
         console.error("Élément #navbar-container non trouvé !");
     }
 })
 .catch(error => console.error("Erreur lors du chargement du navbar :", error));


  // Charger le pied de page
  fetch("/VroomIfy-4/pages/pied-page.php")
  .then(response => {
      if (!response.ok) {
          throw new Error('Erreur HTTP ' + response.status);
      }
      return response.text();
  })
  .then(data => {
      const navbarContainer = document.getElementById("footer-container");
      if (navbarContainer) {
          navbarContainer.innerHTML = data;
      } else {
          console.error("Élément #navbar-container non trouvé !");
      }
  })
  .catch(error => console.error("Erreur lors du chargement du navbar :", error));

   </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('commande_id')) {
        document.querySelector('#pills-commandes-tab').click();
        const detailsContainer = document.querySelector('#pills-commandes .details-container');
        if (detailsContainer) {
            detailsContainer.style.display = 'block';
            // Masquer la liste des commandes
            document.querySelector('#pills-commandes .row-cols-1').style.display = 'none';
        }
    } 
    else if (urlParams.has('rdv_id')) {
        document.querySelector('#pills-rdv-tab').click();
        const detailsContainer = document.querySelector('#pills-rdv .details-container');
        if (detailsContainer) {
            detailsContainer.style.display = 'block';
            // Masquer la liste des RDV
            document.querySelector('#pills-rdv .row-cols-1').style.display = 'none';
        }
    }
});
    </script>
    <script>
        // Afficher les détails si on est sur une page de détails
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('commande_id')) {
                document.querySelector('#pills-commandes-tab').click();
                document.querySelector('#pills-commandes .details-container').style.display = 'block';
            } 
            else if (urlParams.has('rdv_id')) {
                document.querySelector('#pills-rdv-tab').click();
                document.querySelector('#pills-rdv .details-container').style.display = 'block';
            }
        });
    </script>
</body>
</html>