<?php
// Script temporaire pour générer des mots de passe hashés
$password = "ton_mot_de_passe_admin"; // Change ici
echo password_hash($password, PASSWORD_DEFAULT);
?>