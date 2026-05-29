<?php
session_start();

// Sécurité : Seul le restaurateur ou l'admin peut modifier la carte
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'restaurateur' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../VUES/accueil.php');
    exit();
}

$json_path = '../DATA/menu.json';
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : ['plats' => [], 'menus' => []];

$action = $_POST['action'] ?? '';

if ($action === 'save_plat') {
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $cat = $_POST['cat'] ?? '';
    $prix = (float)($_POST['prix'] ?? 0);
    $desc = $_POST['desc'] ?? '';
    // Nettoyage des tags : transforme la chaîne "tag1, tag2" en tableau ["tag1", "tag2"]
    $tags = !empty($_POST['tags']) ? array_filter(array_map('trim', explode(',', $_POST['tags']))) : [];

    // Gestion de l'image
    $image_path = $_POST['image_actuelle'] ?? '../IMAGES/default.png';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../IMAGES/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $new_file_name = 'plat_' . time() . '_' . uniqid() . '.' . $file_extension;
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destination)) {
            $image_path = $destination;
        }
    }

    if (!empty($id)) {
        // Modification d'un plat existant
        foreach ($data['plats'] as &$p) {
            if ($p['id'] == $id) {
                $p['nom'] = $nom;
                $p['cat'] = $cat;
                $p['prix'] = $prix;
                $p['desc'] = $desc;
                $p['image'] = $image_path;
                $p['tags'] = array_values($tags);
                break;
            }
        }
    } else {
        // Création d'un nouveau plat
        $new_id = !empty($data['plats']) ? max(array_column($data['plats'], 'id')) + 1 : 1;
        $data['plats'][] = [
            'id' => $new_id,
            'nom' => $nom,
            'desc' => $desc,
            'prix' => $prix,
            'cat' => $cat,
            'image' => $image_path,
            'tags' => array_values($tags),
            'ventes' => 0
        ];
    }
} elseif ($action === 'delete_plat') {
    $id = $_POST['id'] ?? '';
    $data['plats'] = array_values(array_filter($data['plats'], fn($p) => $p['id'] != $id));

} elseif ($action === 'save_menu') {
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prix = (float)($_POST['prix'] ?? 0);
    $min_personnes = (int)($_POST['min_personnes'] ?? 1);
    $description = $_POST['description'] ?? '';
    $creneau = $_POST['creneau'] ?? '';
    $heure_debut = $_POST['heure_debut'] ?? '00:00';
    $heure_fin = $_POST['heure_fin'] ?? '23:59';
    $liste_plats = $_POST['liste_plats'] ?? [];

    $menu_item = [
        'id' => !empty($id) ? (int)$id : (!empty($data['menus']) ? max(array_column($data['menus'], 'id')) + 1 : 101),
        'nom' => $nom,
        'description' => $description,
        'prix' => $prix,
        'min_personnes' => $min_personnes,
        'creneau' => $creneau,
        'heure_debut' => $heure_debut,
        'heure_fin' => $heure_fin,
        'liste_plats' => $liste_plats
    ];

    if (!empty($id)) {
        foreach ($data['menus'] as &$m) { if ($m['id'] == $id) { $m = $menu_item; break; } }
    } else {
        $data['menus'][] = $menu_item;
    }
} elseif ($action === 'delete_menu') {
    $id = $_POST['id'] ?? '';
    $data['menus'] = array_values(array_filter($data['menus'], fn($m) => $m['id'] != $id));
}

file_put_contents($json_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: ../VUES/gestion_menu.php?success=1');
exit();