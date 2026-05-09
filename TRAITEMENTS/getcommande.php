<?php
/**
 * Récupère une commande spécifique par son ID depuis le fichier de stockage JSON.
 * 
 * @param mixed $id L'identifiant de la commande à chercher (peut être un entier ou une chaîne T...).
 * @return array|null Retourne le tableau de la commande si trouvée, sinon null.
 */
function getCommandeById($id) {
    $file_path = __DIR__ . '/../DATA/commande.json';

    if (!file_exists($file_path)) {
        return null;
    }

    $commandes = json_decode(file_get_contents($file_path), true);

    if (is_array($commandes)) {
        foreach ($commandes as $commande) {
            // Utilisation de == pour autoriser la comparaison entre string et int
            if (isset($commande['id']) && $commande['id'] == $id) {
                return $commande;
            }
        }
    }

    return null;
}