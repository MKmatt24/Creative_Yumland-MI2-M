<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cmd = $_POST['id_commande'];
    $nouveau_statut = $_POST['nouveau_statut'];
    $id_livreur = $_POST['id_livreur'] ?? null;

    $file = '../DATA/commande.json';
    $commandes = json_decode(file_get_contents($file), true);

    foreach ($commandes as &$c) {
        if ($c['id'] == $id_cmd) {
            $c['statut'] = $nouveau_statut;
            if ($nouveau_statut === 'livraison') {
                $c['statut_logistique'] = 'en_livraison';
                $c['livreur_id'] = intval($id_livreur);
            } else {
                $c['statut_logistique'] = $nouveau_statut;
            }
            break;
        }
    }

    file_put_contents($file, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header('Location: ../VUES/commande.php?updated=1');
    exit;
}