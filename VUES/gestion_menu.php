<?php
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'restaurateur' && $_SESSION['role'] !== 'admin')) {
    header('Location: accueil.php'); 
    exit();
}

$json_path = '../DATA/menu.json';
$data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : ['plats' => [], 'menus' => []];
$all_plat_names = array_column($data['plats'], 'nom');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de la Carte | Los Pollos Hermanos</title>
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
    <link rel="stylesheet" href="../CSS/gestion_menu.css">
    <script>
        function editPlat(plat) {
            switchForm('plat');
            document.getElementById('form-title-main').innerText = "MODIFIER : " + plat.nom;
            document.getElementById('plat-id').value = plat.id;
            document.getElementById('plat-nom').value = plat.nom;
            document.getElementById('plat-cat').value = plat.cat;
            document.getElementById('plat-desc').value = plat.desc || '';
            document.getElementById('plat-prix').value = plat.prix;
            document.getElementById('plat-image-actuelle').value = plat.image || '';
            document.getElementById('plat-image-info').innerText = plat.image ? "Fichier actuel : " + plat.image.split('/').pop() : "";
            document.getElementById('plat-tags').value = (plat.tags || []).join(', ');
            // Reset file input for plat
            const platFileInput = document.getElementById('plat-image-file');
            if (platFileInput) platFileInput.value = '';
            document.getElementById('file-name-display').innerText = "Aucun fichier choisi";

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetPlatForm() {
            document.getElementById('form-title-main').innerText = "GESTION CARTE & MENUS";
            document.getElementById('plat-id').value = "";
            document.getElementById('plat-image-actuelle').value = "";
            document.getElementById('plat-image-info').innerText = "";
            document.getElementById('file-name-display').innerText = "Aucun fichier choisi";
            document.getElementById('form-plat-actual').reset();
        }

        function editMenu(menu) {
            switchForm('menu');
            document.getElementById('form-title-main').innerText = "MODIFIER : " + menu.nom;
            document.getElementById('menu-id').value = menu.id;
            document.getElementById('menu-nom').value = menu.nom;
            document.getElementById('menu-desc').value = menu.description || '';
            document.getElementById('menu-prix').value = menu.prix;
            document.getElementById('menu-min-pers').value = menu.min_personnes || 1;
            document.getElementById('menu-creneau').value = menu.creneau || '';
            document.getElementById('menu-heure-debut').value = menu.heure_debut || '00:00';
            document.getElementById('menu-heure-fin').value = menu.heure_fin || '23:59';

            // Select multiple plats
            const platSelect = document.getElementById('menu-liste-plats');
            const platButtons = document.querySelectorAll('.plat-choice-btn');
            
            for (let i = 0; i < platSelect.options.length; i++) {
                const isSelected = menu.liste_plats.includes(platSelect.options[i].value);
                platSelect.options[i].selected = isSelected;
                
                const btn = Array.from(platButtons).find(b => b.dataset.value === platSelect.options[i].value);
                if (btn) btn.classList.toggle('selected', isSelected);
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetMenuForm() {
            document.getElementById('form-title-main').innerText = "GESTION CARTE & MENUS";
            document.getElementById('menu-id').value = "";
            document.getElementById('form-menu-actual').reset();
            // Deselect all options in the multi-select
            const platSelect = document.getElementById('menu-liste-plats');
            for (let i = 0; i < platSelect.options.length; i++) {
                platSelect.options[i].selected = false;
            }
            document.querySelectorAll('.plat-choice-btn').forEach(btn => btn.classList.remove('selected'));
        }

        function togglePlatSelection(btn) {
            const val = btn.dataset.value;
            const select = document.getElementById('menu-liste-plats');
            const option = Array.from(select.options).find(opt => opt.value === val);
            
            if (option) {
                option.selected = !option.selected;
                btn.classList.toggle('selected', option.selected);
            }
        }

        function switchForm(type) {
            const platCont = document.getElementById('plat-form-container');
            const menuCont = document.getElementById('menu-form-container');
            const tabs = document.querySelectorAll('.form-tab-btn');
            
            if (type === 'plat') {
                platCont.style.display = 'block';
                menuCont.style.display = 'none';
                tabs[0].classList.add('active');
                tabs[1].classList.remove('active');
            } else {
                platCont.style.display = 'none';
                menuCont.style.display = 'block';
                tabs[1].classList.add('active');
                tabs[0].classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Écouteur pour mettre à jour le nom du fichier sélectionné
            const fileInput = document.getElementById('plat-image-file');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const fileName = this.files[0] ? this.files[0].name : "Aucun fichier choisi";
                    document.getElementById('file-name-display').innerText = fileName;
                });
            }
        });
    </script>
</head>
<body class="page-dark">
    <?php include '../LIB/header.php'; ?>
    
    <main class="container admin-main">
        <h2 class="huge-title admin-title">GESTION <span class="outline">CARTE</span></h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="msg-success">
                <span class="msg-icon">✅</span>
                <div class="msg-content">
                    <strong>Mise à jour réussie</strong>
                    <p>Les modifications ont été enregistrées et sont maintenant appliquées sur la carte du restaurant.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cadre de modification unique -->
        <section class="form-card">
            <div class="section-header">
                <h3 id="form-title-main" class="form-title">GESTION CARTE & MENUS</h3>
                <button onclick="resetPlatForm(); resetMenuForm();" class="btn-cancel">✖ Réinitialiser</button>
            </div>
            
            <div class="tab-container" style="border-bottom: none; margin-bottom: 25px;">
                <button class="tab-btn form-tab-btn active" onclick="switchForm('plat')">Élément à la carte</button>
                <button class="tab-btn form-tab-btn" onclick="switchForm('menu')">Formule Menu</button>
            </div>

            <div id="plat-form-container">
            <form action="../TRAITEMENTS/process_gestion_menu.php" method="POST" enctype="multipart/form-data" id="form-plat-actual">
                <input type="hidden" name="action" value="save_plat">
                <input type="hidden" name="id" id="plat-id">
                <input type="hidden" name="image_actuelle" id="plat-image-actuelle">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom du produit</label>
                        <input type="text" name="nom" id="plat-nom" required placeholder="ex: Albuquerque Bucket">
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="cat" id="plat-cat">
                            <option value="Poulet">Poulet</option>
                            <option value="Burgers">Burgers</option>
                            <option value="Spécialités">Spécialités</option>
                            <option value="Accompagnements">Accompagnements</option>
                            <option value="Desserts">Desserts</option>
                            <option value="Boissons">Boissons</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix" id="plat-prix" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description courte</label>
                    <textarea name="desc" id="plat-desc" rows="2" placeholder="Ingrédients et saveurs..."></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Image du produit (JPG, PNG)</label>
                        <div class="file-upload-wrapper">
                            <label for="plat-image-file" class="file-upload-btn">📁 PARCOURIR...</label>
                            <input type="file" name="image_file" id="plat-image-file" accept="image/*">
                            <span id="file-name-display" class="file-name-text">Aucun fichier choisi</span>
                        </div>
                        <small id="plat-image-info" class="tag-small"></small>
                    </div>
                    <div class="form-group">
                        <label>Tags (séparés par des virgules)</label>
                        <input type="text" name="tags" id="plat-tags" placeholder="épicé, végétarien, coup de coeur">
                    </div>
                </div>

                <button type="submit" class="btn-full">ENREGISTRER LE PRODUIT</button>
            </form>
            </div>

            <div id="menu-form-container" style="display: none;">
            <form action="../TRAITEMENTS/process_gestion_menu.php" method="POST" id="form-menu-actual">
                <input type="hidden" name="action" value="save_menu">
                <input type="hidden" name="id" id="menu-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom du menu</label>
                        <input type="text" name="nom" id="menu-nom" required placeholder="ex: Menu Los Pollos Classic">
                    </div>
                    <div class="form-group">
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix" id="menu-prix" required>
                    </div>
                    <div class="form-group">
                        <label>Min. personnes</label>
                        <input type="number" name="min_personnes" id="menu-min-pers" value="1" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="menu-desc" rows="2" placeholder="Description du menu..."></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Créneau (ex: Menu Midi)</label>
                        <input type="text" name="creneau" id="menu-creneau" placeholder="Toute la journée, Menu Midi (11h30-14h30)">
                    </div>
                    <div class="form-group">
                        <label>Heure début</label>
                        <input type="time" name="heure_debut" id="menu-heure-debut" value="00:00">
                    </div>
                    <div class="form-group">
                        <label>Heure fin</label>
                        <input type="time" name="heure_fin" id="menu-heure-fin" value="23:59">
                    </div>
                </div>

                <div class="form-group">
                    <label>Plats inclus dans le menu (Cliquez pour sélectionner)</label>
                    <div id="plat-buttons-container" class="plat-selection-grid">
                        <?php foreach ($all_plat_names as $plat_name): ?>
                            <button type="button" class="plat-choice-btn" data-value="<?= htmlspecialchars($plat_name) ?>" onclick="togglePlatSelection(this)">
                                <?= htmlspecialchars($plat_name) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <!-- Select invisible pour l'envoi du formulaire -->
                    <select name="liste_plats[]" id="menu-liste-plats" multiple hidden required>
                        <?php foreach ($all_plat_names as $plat_name): ?>
                            <option value="<?= htmlspecialchars($plat_name) ?>"><?= htmlspecialchars($plat_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-full">ENREGISTRER LE MENU</button>
            </form>
            </div>
        </section>

        <h3 class="section-subtitle-admin">PLATS À LA CARTE</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Tags</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['plats'] as $p): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($p['image'] ?? '../IMAGES/default.png') ?>" class="table-img"></td>
                    <td class="text-bold"><?= htmlspecialchars($p['nom']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($p['cat']) ?></span></td>
                    <td><?= number_format($p['prix'], 2) ?>€</td>
                    <td><?php foreach(($p['tags'] ?? []) as $t): ?><span class="tag-small">#<?= $t ?> </span><?php endforeach; ?></td>
                    <td class="action-btns">
                        <div class="action-btns-container">
                            <button class="btn-edit" onclick='editPlat(<?= json_encode($p, JSON_HEX_APOS) ?>)'>Modifier</button>
                            <form action="../TRAITEMENTS/process_gestion_menu.php" method="POST" onsubmit="return confirm('Supprimer ce plat ?');">
                                <input type="hidden" name="action" value="delete_plat">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-delete">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="section-subtitle-admin">LISTE DES MENUS</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom du Menu</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Créneau</th>
                    <th>Composants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['menus'] as $m): ?>
                <tr>
                    <td class="text-bold"><?= htmlspecialchars($m['nom']) ?></td>
                    <td style="font-size: 0.8rem;"><?= htmlspecialchars($m['description'] ?? '') ?></td>
                    <td><?= number_format($m['prix'], 2) ?>€</td>
                    <td><?= htmlspecialchars($m['creneau']) ?></td>
                    <td class="text-muted-small"><?= implode(', ', $m['liste_plats'] ?? []) ?></td>
                    <td class="action-btns">
                        <div class="action-btns-container">
                            <?php if($m['nom'] !== 'Le Menu Mystère'): ?>
                                <button type="button" class="btn-edit" onclick='editMenu(<?= json_encode($m, JSON_HEX_APOS) ?>)'>Modifier</button>
                            <?php endif; ?>
                            <form action="../TRAITEMENTS/process_gestion_menu.php" method="POST" onsubmit="return confirm('Supprimer ce menu ?');">
                                <input type="hidden" name="action" value="delete_menu">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn-delete">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>