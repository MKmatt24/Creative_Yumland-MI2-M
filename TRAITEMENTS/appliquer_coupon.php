<?php
session_start();
$code = strtoupper($_POST['code_coupon'] ?? '');
$coupons = json_decode(file_get_contents('../DATA/coupons.json'), true);

if (isset($coupons[$code])) {
    $_SESSION['coupon'] = $coupons[$code]; // On stocke les infos du coupon en session
}
header('Location: ../VUES/panier.php');