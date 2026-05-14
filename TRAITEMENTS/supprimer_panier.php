<?php
session_start();

if (isset($_POST['nom_produit'])) {
    $nom_a_supprimer = $_POST['nom_produit'];

    if (isset($_SESSION['panier'])) {
        foreach ($_SESSION['panier'] as $key => $item) {
            if ($item['nom'] === $nom_a_supprimer) {
                unset($_SESSION['panier'][$key]);
                // Réindexer le tableau pour éviter des index manquants
                $_SESSION['panier'] = array_values($_SESSION['panier']);
                break;
            }
        }
    }
}

// Retour au panier (que l'on soit en mode édition ou commande classique)
header('Location: ../VUES/panier.php');
exit();
