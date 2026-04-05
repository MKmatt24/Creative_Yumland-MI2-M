<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cmd = $_POST['id_commande'];
    $nouveau_statut = $_POST['nouveau_statut'];
    $id_livreur = $_POST['id_livreur'] ?? null;

    $file = '../data/commande.json';
    $commandes = json_decode(file_get_contents($file), true);

    foreach ($commandes as &$c) {
        // On vérifie l'ID (attention au type string/int selon ton JSON)
        if ($c['id'] == $id_cmd) {
            $c['statut'] = $nouveau_statut;
            $c['statut_logistique'] = ($nouveau_statut === 'livraison') ? 'en_livraison' : $nouveau_statut;
            $c['livreur_id'] = $id_livreur;
            break;
        }
    }

    file_put_contents($file, json_encode($commandes, JSON_PRETTY_PRINT));
    
    // REDIRECTION : On retourne vers la page d'affichage des commandes
    header('Location: ../VUES/commande.php?updated=1');
    exit;
}