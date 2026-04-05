<?php
session_start();

// On vérifie que les données arrivent bien du formulaire
if (isset($_POST['nom'], $_POST['prix'])) {
    
    // Initialisation du panier s'il n'existe pas
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    $nom = $_POST['nom'];
    $prix = (float)$_POST['prix'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;

    // Est-ce que le produit est déjà dans le panier ?
    $trouve = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['nom'] === $nom) {
            $item['quantite'] += $quantite;
            $trouve = true;
            break;
        }
    }

    // Si c'est un nouveau produit, on l'ajoute
    if (!$trouve) {
        $_SESSION['panier'][] = [
            'nom' => $nom,
            'prix' => $prix,
            'quantite' => $quantite
        ];
    }
}

// Redirection vers le menu pour voir le compteur s'actualiser
header('Location: ../VUES/menu.php');
exit();