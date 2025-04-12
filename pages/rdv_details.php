<?php
include_once "con_dbb.php";

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM rdv WHERE idRdv = ?");
$stmt->execute([$id]);
$rdv = $stmt->fetch();

if (!$rdv) exit('RDV introuvable');

echo '<div class="row">';
echo '<div class="col-md-6">
        <p><strong>Service:</strong> '.$rdv['serviceRdv'].'</p>
        <p><strong>Date:</strong> '.date('d/m/Y H:i', strtotime($rdv['dateRdv'])).'</p>
        <p><strong>Adresse:</strong> '.$rdv['addresse'].'</p>
        <p><strong>Véhicule:</strong> '.$rdv['vehicule'].'</p>
        <p><strong>Statut:</strong> <span class="badge bg-'.getStatusColor($rdv['etat']).'">'.$rdv['etat'].'</span></p>
      </div>';

if ($rdv['photo']) {
    echo '<div class="col-md-6">
            <h5>Photo</h5>
            <img src="data:image/jpeg;base64,'.base64_encode($rdv['photo']).'" class="img-fluid rdv-photo">
          </div>';
}

if (!empty($rdv['commentaire'])) {
    echo '<div class="mt-3">
            <h5>Commentaire</h5>
            <p>'.$rdv['commentaire'].'</p>
          </div>';
}

function getStatusColor($status) {
    $colors = [
        'en attente' => 'warning',
        'confirmé' => 'success',
        'annulé' => 'danger',
        'terminé' => 'primary'
    ];
    return $colors[$status] ?? 'secondary';
}