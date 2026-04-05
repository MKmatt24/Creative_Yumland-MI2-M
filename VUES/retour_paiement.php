<?php
session_start();
require_once '../TRAITEMENTS/getapikey.php';

$transaction = $_GET['transaction'] ?? '';
$montant     = $_GET['montant']     ?? '';
$vendeur     = $_GET['vendeur']     ?? '';
$statut      = $_GET['statut']      ?? ''; // accepted ou declined [cite: 64]
$control_rx  = $_GET['control']     ?? '';

// Recalcul du hachage de retour [cite: 66, 71]
$api_key = trim(getAPIKey($vendeur));
$chaine_verif = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";
$control_verif = md5($chaine_verif);

if ($statut === "accepted" && $control_rx === $control_verif) {
    // Sauvegarde dans le JSON [cite: 19]
    $file = '../DATA/commande.json';
    $data = json_decode(file_get_contents($file), true) ?? [];
    $data[] = [
        "id" => $transaction,
        "total" => $montant,
        "articles" => $_SESSION['panier'],
        "date" => date('d/m/Y H:i')
    ];
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    
    unset($_SESSION['panier']);
    echo "<h1>Paiement Accepté !</h1>";
} else {
    echo "<h1>Erreur de paiement ou de signature.</h1>";
}
?>
<a href="menu.php">Retour au menu</a>