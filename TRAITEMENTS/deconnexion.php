<?php
session_start();
session_destroy();
header('Location: ../VUES/connexion.php');
exit;
?>