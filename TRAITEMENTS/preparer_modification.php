<?php
session_start();
require_once 'getcommande.php';

if (isset($_GET['id'])) {
    $orderId = $_GET['id'];
    $commande = getCommandeById($orderId);

    if ($commande && ($commande['statut'] === 'paye' || $commande['statut'] === 'a_preparer' || $commande['statut'] === 'en préparation')) {
        // On stocke l'état initial pour comparer plus tard
        $_SESSION['modification_id'] = $orderId;
        $_SESSION['modification_total_initial'] = (float)$commande['prix_total'];
        
        // On injecte les articles dans le panier actuel
        $_SESSION['panier'] = $commande['articles'];
        
        header('Location: ../VUES/menu.php?mode=edition');
        exit();
    }
}

header('Location: ../VUES/suivi_commande.php');
exit();
?>
