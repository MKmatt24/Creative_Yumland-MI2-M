<?php
session_start();
require_once '../TRAITEMENTS/getapikey.php';

$vendeur = "TEST"; // Identifiant groupe
$api_key = trim(getAPIKey($vendeur));
$transaction = "TX" . time();

$total = 0;
foreach ($_SESSION['panier'] as $i) { $total += $i['prix'] * $i['quantite']; }
$montant = number_format($total, 2, '.', ''); 
$url_retour = "http://localhost:8000/VUES/retour_paiement.php";

// Hachage MD5 : clé#trans#montant#vendeur#retour#
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $url_retour . "#");
?>
<!DOCTYPE html>
<html>
<body onload="document.forms[0].submit()">
    <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?= $transaction ?>">
        <input type="hidden" name="montant" value="<?= $montant ?>">
        <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
        <input type="hidden" name="retour" value="<?= $url_retour ?>">
        <input type="hidden" name="control" value="<?= $control ?>">
    </form>
    <p>Redirection vers CY Bank...</p>
</body>
</html>