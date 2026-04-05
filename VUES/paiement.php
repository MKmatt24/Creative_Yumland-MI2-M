<?php
session_start();
require_once '../TRAITEMENTS/getapikey.php';

$vendeur = "TEST"; 
$api_key = trim(getAPIKey($vendeur)); // Nettoyage de la clé
$transaction = "T" . time();

// 1. Calcul du total brut (somme des articles)
$total = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total += ($item['prix'] * $item['quantite']);
    }
}

// 2. Application du coupon si présent en session
$reduction = 0;
if (isset($_SESSION['coupon'])) {
    $c = $_SESSION['coupon'];
    if ($c['type'] === 'pourcentage') {
        $reduction = $total * ($c['valeur'] / 100);
    } else {
        $reduction = $c['valeur'];
    }
}

// 3. Montant final à payer (ne peut pas être inférieur à 0)
$total_apres_reduction = max(0, $total - $reduction);

// 4. Formatage pour la banque (2 décimales, point comme séparateur)
$montant = number_format($total_apres_reduction, 2, '.', '');

$url_retour = "http://localhost:8000/VUES/retour_paiement.php";

// LE CALCUL CRUCIAL (MD5 + #)
$sep = "#";
$chaine = $api_key . $sep . $transaction . $sep . $montant . $sep . $vendeur . $sep . $url_retour . $sep;
$control = md5($chaine);
?>
<!DOCTYPE html>
<html>
<body onload="document.forms[0].submit()">
    <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
        <input type="hidden" name="montant" value="<?= $montant ?>">
        <input type="hidden" name="transaction" value="<?= $transaction ?>"> 
        <input type="hidden" name="retour" value="<?= $url_retour ?>">
        <input type="hidden" name="control" value="<?= $control ?>">
    </form>
</body>
</html>