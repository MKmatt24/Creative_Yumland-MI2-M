<?php
session_start();
header('Content-Type: application/json');

$code = strtoupper($_POST['code_coupon'] ?? '');
$coupons = json_decode(file_get_contents('../DATA/coupons.json'), true);

// Calcul du total actuel pour la vérification et le retour
$total = 0;
$panier = $_SESSION['panier'] ?? [];
foreach ($panier as $item) {
    $total += (($item['prix'] ?? 0) * ($item['quantite'] ?? 0));
}

$response = ['success' => false, 'message' => ''];

if (isset($coupons[$code])) {
    // Condition spécifique pour le coupon GUSTAVO5
    if ($code === 'GUSTAVO5') {
        if ($total < 30) {
            $response['message'] = 'Votre panier actuel est de ' . number_format($total, 2, ',', ' ') . ' €. Le code GUSTAVO5 est valide à partir de 30 €.';
            $response['new_total'] = number_format($total, 2, '.', '') . ' €';
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode($response);
                exit;
            }
            $_SESSION['coupon_error'] = $response['message'];
            header('Location: ../VUES/panier.php');
            exit;
        }
    }

    $_SESSION['coupon'] = $coupons[$code];
    $_SESSION['coupon']['code'] = $code;
    $response['success'] = true;
    $response['coupon'] = $_SESSION['coupon'];
} else {
    $response['message'] = 'Code promo invalide.';
    $_SESSION['coupon_error'] = $response['message'];
}

// Calcul du nouveau total final
$reduction = 0;
if (isset($_SESSION['coupon'])) {
    $reduction = ($_SESSION['coupon']['type'] === 'pourcentage') 
        ? $total * ($_SESSION['coupon']['valeur'] / 100) 
        : $_SESSION['coupon']['valeur'];
}
$response['new_total'] = number_format(max(0, $total - $reduction), 2, '.', '') . ' €';

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    echo json_encode($response);
    exit;
}

header('Location: ../VUES/panier.php');
exit;