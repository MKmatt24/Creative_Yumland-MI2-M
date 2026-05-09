<?php
session_start();

if (!isset($_SESSION['modification_id'])) {
    header('Location: ../VUES/panier.php');
    exit();
}

$id_commande = $_SESSION['modification_id'];
$total_initial = $_SESSION['modification_total_initial'];

// Calcul du nouveau total
$nouveau_total = 0;
foreach ($_SESSION['panier'] as $item) {
    $nouveau_total += ($item['prix'] * $item['quantite']);
}

// Application des coupons éventuels
$reduction = 0;
if (isset($_SESSION['coupon'])) {
    $c = $_SESSION['coupon'];
    $reduction = ($c['type'] === 'pourcentage') ? $nouveau_total * ($c['valeur'] / 100) : $c['valeur'];
}
$nouveau_total = max(0, $nouveau_total - $reduction);

$file = '../DATA/commande.json';
$commandes = json_decode(file_get_contents($file), true);

if ($nouveau_total > $total_initial) {
    // CAS 1 : C'est plus cher -> On doit payer la différence
    $_SESSION['paiement_complementaire'] = true;
    $_SESSION['difference_prix'] = $nouveau_total - $total_initial;
    header('Location: ../VUES/paiement.php'); // paiement.php devra utiliser difference_prix si elle existe
    exit();
} else {
    // CAS 2 : C'est moins cher ou identique
    if ($nouveau_total < $total_initial) {
        // Génération d'un coupon pour la différence
        $difference = $total_initial - $nouveau_total;
        $coupon_code = "REFUND-" . strtoupper(substr(md5(time()), 0, 6));
        
        $coupons_file = '../DATA/coupons.json';
        $coupons = json_decode(file_get_contents($coupons_file), true);
        $coupons[$coupon_code] = [
            "type" => "fixe",
            "valeur" => $difference,
            "description" => "Remboursement modification commande #" . $id_commande
        ];
        file_put_contents($coupons_file, json_encode($coupons, JSON_PRETTY_PRINT));
        $_SESSION['message_modification'] = "Commande modifiée. Un coupon de " . number_format($difference, 2) . "€ a été ajouté à votre profil : " . $coupon_code;
    }

    // Mise à jour de la commande dans le JSON
    foreach ($commandes as &$c) {
        if ($c['id'] == $id_commande) {
            $c['articles'] = $_SESSION['panier'];
            $c['prix_total'] = $nouveau_total;
            $c['date_modification'] = date('d/m/Y H:i');
            break;
        }
    }
    file_put_contents($file, json_encode($commandes, JSON_PRETTY_PRINT));

    // Nettoyage
    unset($_SESSION['panier']);
    unset($_SESSION['modification_id']);
    unset($_SESSION['modification_total_initial']);
    unset($_SESSION['coupon']);

    header('Location: ../VUES/suivi_commande.php?success=modifie');
    exit();
}
?>
