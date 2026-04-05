<?php
//Affichage "Aujourd'hui" ou "Hier" pour un meilleur rendu sinon on mets la date normale
function formatHeureAffichage($date_jour, $heure) {
    $aujourdhui = date('Y-m-d');
    $hier = date('Y-m-d', strtotime('-1 day'));
    
    if ($date_jour === $aujourdhui) {
        return "Aujourd'hui, " . $heure;
    } elseif ($date_jour === $hier) {
        return "Hier, " . $heure;
    } else {
        return date('d/m', strtotime($date_jour)) . ", " . $heure;
    }
}
?>