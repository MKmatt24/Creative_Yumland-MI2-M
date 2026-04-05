<?php
session_start();

/**
 * TRAITEMENT DE LA PLANIFICATION
 * Ce script intercepte les données du panier avant l'envoi à la banque.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Récupération des choix du formulaire
    $type_commande = $_POST['type_commande'] ?? 'immediate';
    $heure_choisie = $_POST['heure_programmee'] ?? '';

    // 2. Validation et stockage en Session
    // On crée une structure propre pour la planification
    $_SESSION['planification'] = [
        'type' => $type_commande, // 'immediate' ou 'programmee'
        'horaire' => ($type_commande === 'programmee' && !empty($heure_choisie)) ? $heure_choisie : 'ASAP',
        'date_demande' => date('Y-m-d') 
    ];

    /**
     * Note : On peut ajouter ici une vérification si l'heure choisie 
     * est bien dans les horaires d'ouverture (ex: 11h-23h).
     */

    // 3. Redirection vers la page de paiement CY Bank
    header('Location: ../VUES/paiement.php');
    exit();

} else {
    // Si on tente d'accéder au fichier sans poster le formulaire, retour au panier
    header('Location: ../VUES/panier.php');
    exit();
}