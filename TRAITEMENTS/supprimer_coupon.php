<?php
session_start();

/**
 * Ce script supprime le coupon de réduction actuellement stocké en session
 * puis redirige l'utilisateur vers la page du panier.
 */
unset($_SESSION['coupon']);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    
    $total = 0;
    $panier = $_SESSION['panier'] ?? [];
    foreach ($panier as $item) {
        $total += (($item['prix'] ?? 0) * ($item['quantite'] ?? 0));
    }
    
    echo json_encode([
        'success' => true,
        'coupon' => null,
        'new_total' => number_format($total, 2, '.', '') . ' €'
    ]);
    exit;
}

header('Location: ../VUES/panier.php');
exit();