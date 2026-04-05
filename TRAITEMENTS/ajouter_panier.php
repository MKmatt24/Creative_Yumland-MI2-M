<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../VUES/connexion.php?error=auth_required');
    exit();
}

if (isset($_POST['nom'], $_POST['prix'])) {
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    
    $trouve = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['nom'] === $_POST['nom']) {
            $item['quantite']++;
            $trouve = true;
            break;
        }
    }
    if (!$trouve) {
        $_SESSION['panier'][] = ['nom' => $_POST['nom'], 'prix' => (float)$_POST['prix'], 'quantite' => 1];
    }
}
header('Location: ../VUES/menu.php');