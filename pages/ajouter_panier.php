<?php
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $_SESSION['panier'][$id] = ($_SESSION['panier'][$id] ?? 0) + 1;
    
    // Déclencher la mise à jour
    echo '<script>document.dispatchEvent(new CustomEvent("cartUpdated"))</script>';
}

header("Location: boutique.php");
exit;
?>