<?php
session_start();

/**
 * Ce script supprime le coupon de réduction actuellement stocké en session
 * puis redirige l'utilisateur vers la page du panier.
 */
unset($_SESSION['coupon']);

header('Location: ../VUES/panier.php');
exit();