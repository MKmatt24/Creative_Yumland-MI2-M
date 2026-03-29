<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../HTML/connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer toutes les données du formulaire
    $notation = [
        'id' => time(), // ID unique basé sur le timestamp
        'user_id' => $_SESSION['user_id'],
        'commande_id' => $_POST['commande_id'] ?? 0,
        'date_notation' => date('Y-m-d H:i:s'),
        
        // Livraison
        'note_livraison' => $_POST['note-livraison'] ?? 0,
        'ponctualite' => $_POST['ponctualite'] ?? '',
        'note_livreur' => $_POST['note-livreur'] ?? 0,
        'commentaire_livraison' => $_POST['commentaire-livraison'] ?? '',
        
        // Produits
        'note_produits' => $_POST['note-produits'] ?? 0,
        'temperature' => $_POST['temperature'] ?? '',
        'qualite_emballage' => $_POST['qualite-emballage'] ?? '',
        'gout' => $_POST['gout'] ?? 0,
        'presentation' => $_POST['presentation'] ?? 0,
        'commande_complete' => $_POST['commande-complete'] ?? '',
        'elements_manquants' => $_POST['elements-manquants'] ?? '',
        'commentaire_produits' => $_POST['commentaire-produits'] ?? '',
        
        // Recommandation
        'recommandation' => $_POST['recommandation'] ?? '',
        'commentaire_general' => $_POST['commentaire-general'] ?? ''
    ];
    
    // Charger les notations existantes (ou créer un tableau vide)
    $fichier_notations = '../DATA/notations.json';
    
    if (file_exists($fichier_notations)) {
        $notations = json_decode(file_get_contents($fichier_notations), true);
    } else {
        $notations = [];
    }
    
    // Ajouter la nouvelle notation
    $notations[] = $notation;
    
    // Sauvegarder dans le fichier JSON
    file_put_contents($fichier_notations, json_encode($notations, JSON_PRETTY_PRINT));
    
    // Redirection avec message de succès
    header('Location: ../HTML/notation.php?success=1&commande=' . $notation['commande_id']);
    exit;
}

// Si accès direct sans POST
header('Location: ../HTML/notation.php');
exit;
?>