<?php
ob_start();
session_start();
include_once "con_dbb.php";

if (isset($_GET['id'], $_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'inc') {
        $_SESSION['panier'][$id]++;
    } elseif ($action === 'dec') {
        $_SESSION['panier'][$id] = max(0, $_SESSION['panier'][$id] - 1);
        if ($_SESSION['panier'][$id] === 0) {
            unset($_SESSION['panier'][$id]);
        }
    }
    
    echo '<script>document.dispatchEvent(new Event("cartUpdated"))</script>';
    header("Location: panier.php");
    ob_end_flush();
    exit;
}
?>