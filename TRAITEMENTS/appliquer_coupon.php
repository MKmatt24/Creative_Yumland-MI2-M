<?php
session_start();
$code = strtoupper($_POST['code_coupon'] ?? '');
$coupons = json_decode(file_get_contents('../DATA/coupons.json'), true);

if (isset($coupons[$code])) {
    // Condition spécifique pour le coupon GUSTAVO5
    if ($code === 'GUSTAVO5') {
        $current_cart_total = 0;
        if (isset($_SESSION['panier'])) {
            foreach ($_SESSION['panier'] as $item) {
                $current_cart_total += (($item['prix'] ?? 0) * ($item['quantite'] ?? 0));
            }
        }

        if ($current_cart_total < 30) {
            $_SESSION['coupon_error'] = 'Votre panier actuel est de ' . number_format($current_cart_total, 2, ',', ' ') . ' €. Le code GUSTAVO5 est valide à partir de 30 €.';
            header('Location: ../VUES/panier.php');
            exit;
        }
    }

    $_SESSION['coupon'] = $coupons[$code];
    $_SESSION['coupon']['code'] = $code;
} else {
    $_SESSION['coupon_error'] = 'Code promo invalide.';
}
header('Location: ../VUES/panier.php');
exit;