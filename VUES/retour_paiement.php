<?php
// RÉCUPÉRATION DU SID DEPUIS L'URL (Crucial pour localhost après redirection externe)
if (isset($_GET['sid']) && !empty($_GET['sid'])) {
    session_id($_GET['sid']);
}
// Fix crucial : Autorise la récupération du cookie de session après une redirection externe (Banque)
session_set_cookie_params(['samesite' => 'Lax']);
session_start();
require_once '../TRAITEMENTS/getapikey.php';

// 1. RÉCUPÉRATION DES PARAMÈTRES RENVOYÉS PAR CYBANK
$transaction  = $_GET['transaction'] ?? "";
$montant      = $_GET['montant'] ?? "";
$vendeur      = $_GET['vendeur'] ?? "";
$statut       = $_GET['status'] ?? $_GET['statut'] ?? "";
$control_rx   = $_GET['control'] ?? "";

// 2. VÉRIFICATION DE LA SIGNATURE (MD5)

// A. On récupère la clé API et on prépare la chaîne (Ordre : Clé#Trans#Montant#Vendeur#Statut#)
$api_key = trim(getAPIKey($vendeur));
$hash_string = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";

// B. On calcule le hash localement pour comparaison
$control_calcule = md5($hash_string);

// C. (L'AJOUT) On normalise le statut pour ne pas rater une validation à cause d'une minuscule
$statut_check = strtoupper($statut);

// D. On vérifie si TOUT est bon : la signature ET le code de succès
$paiement_valide = (
    $control_rx === $control_calcule && 
    ($statut_check === "V" || $statut_check === "OK" || $statut_check === "ACCEPTED")
);

/**
 * 3. TRAITEMENT ET SAUVEGARDE DE LA COMMANDE
 */
if ($paiement_valide) {
    // On garde une copie des articles pour le bouton "Commander à nouveau" avant de vider la session
    $articles_pour_recommande = $_SESSION['panier'] ?? [];
    $file = '../DATA/commande.json';
    $commandesData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    if (isset($_SESSION['modification_id'])) {
        // Cas d'une modification : on met à jour la commande existante
        foreach ($commandesData as &$c) {
            if ($c['id'] == $_SESSION['modification_id']) {
                $c['articles'] = $_SESSION['panier'] ?? [];
                // Nouveau total = Ancien total payé + complément
                $c['prix_total'] = ($_SESSION['modification_total_initial'] ?? 0) + (float)$montant;
                $c['date_modification'] = date('d/m/Y H:i');
                $c['statut'] = "a_preparer"; // Repasse en file d'attente
                break;
            }
        }
    } else {
        // On prépare la nouvelle commande
        $nouvelle_commande = [
            "id" => $transaction,
            "date" => date('d/m/Y H:i'),
            "client" => ($_SESSION['prenom'] ?? 'Client') . " " . ($_SESSION['nom'] ?? 'Anonyme'),
            "user_id" => $_SESSION['user_id'] ?? null,
            "prix_total" => (float)$montant,
            "statut" => "a_preparer",
            "articles" => $_SESSION['panier'] ?? [],
            "type" => $_SESSION['planification']['type'] ?? 'direct',
            "horaire" => $_SESSION['planification']['horaire'] ?? 'ASAP',
            "commentaire" => $_SESSION['commentaire_livreur'] ?? ''
        ];

        if (isset($_SESSION['coupon'])) {
            $nouvelle_commande['coupon_utilise'] = $_SESSION['coupon']['valeur'];
        }

        $commandesData[] = $nouvelle_commande;
    }

    file_put_contents($file, json_encode($commandesData, JSON_PRETTY_PRINT));

    // --- SUPPRESSION DU COUPON FIDÉLITÉ DE COUPONS.JSON (USAGE UNIQUE) ---
    if (isset($_SESSION['coupon']['code']) && strpos($_SESSION['coupon']['code'], 'FIDELITE-') === 0) {
        $fichierCoupons = '../DATA/coupons.json';
        $couponsData = json_decode(file_get_contents($fichierCoupons), true) ?? [];
        unset($couponsData[$_SESSION['coupon']['code']]);
        file_put_contents($fichierCoupons, json_encode($couponsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // --- NETTOYAGE APRÈS ACHAT ---
    unset($_SESSION['panier']);
    unset($_SESSION['coupon']);
    unset($_SESSION['planification']);
    unset($_SESSION['commentaire_livreur']);
    unset($_SESSION['modification_id']);
    unset($_SESSION['modification_total_initial']);
    unset($_SESSION['paiement_complementaire']);
    unset($_SESSION['difference_prix']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation | Los Pollos Hermanos</title>
    <link rel="shortcut icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="icon" type="image/png" href="../IMAGES/logo.png">
    <link rel="stylesheet" href="../CSS/accueil.css">
    <link rel="stylesheet" href="../CSS/menu.css">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <script defer>
    document.addEventListener('DOMContentLoaded', function() {
        const reorderBtn = document.querySelector('.reorder-now-btn');
        if (reorderBtn) {
            reorderBtn.addEventListener('click', function() {
                let articles = [];
                try {
                    const raw = this.getAttribute('data-articles');
                    articles = (raw && raw !== 'null') ? JSON.parse(raw) : [];
                } catch(e) { console.error("Erreur JSON", e); }

                if (!articles || articles.length === 0) {
                    alert("Désolé, les détails de la commande n'ont pas pu être récupérés pour recommander.");
                    return;
                }

                this.disabled = true;
                const originalText = this.textContent;
                this.textContent = '⏳ PRÉPARATION...';

                (async () => {
                    try {
                        for (const article of articles) {
                            const formData = new FormData();
                            formData.append('nom', article.nom);
                            formData.append('prix', article.prix || 0);
                            formData.append('quantite', article.quantite || 1);
                            
                            if (article.modifications) {
                                formData.append('modifications', JSON.stringify(article.modifications));
                            }

                            await fetch('../TRAITEMENTS/ajouter_panier.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData }); //
                        }
                        window.location.href = 'menu.php'; //
                    } catch (err) {
                        console.error(err);
                        this.disabled = false;
                        this.textContent = originalText;
                    }
                })();
            });
        }
    });
    </script>
</head>
<body class="page-dark">

<?php include '../LIB/header.php'; ?>

    <div class="theme-switcher-container">
        <button onclick="cycleTheme()" id="theme-toggle-btn" class="theme-switcher-btn">
            🎨 Changer de style
        </button>
    </div>

<main class="feedback-container">
    <div class="feedback-card">
        
        <?php if ($paiement_valide): ?>
            <div class="feedback-icon">🍗</div>
            <h2 class="text-success">✓ PAIEMENT RÉUSSI</h2>
            <p class="mb-10">Merci pour votre confiance. L'excellence est en préparation.</p>
            <p>Référence transaction : <strong class="text-orange"><?= htmlspecialchars($transaction) ?></strong></p>
            
            <div class="feedback-actions">
                <a href="suivi_commande.php" class="btn-full">
                    SUIVRE MA COMMANDE EN DIRECT
                </a>

                <!-- Nouveau bouton pour dupliquer la commande -->
                <button type="button" class="btn-secondary-full reorder-now-btn" data-articles='<?= htmlspecialchars(json_encode($articles_pour_recommande), ENT_QUOTES) ?>' style="margin-top: 15px; width: 100%;">
                    🔁 RECOMMANDEZ
                </button>

                <a href="accueil.php" class="btn-secondary-full">
                    Retourner à l'accueil
                </a>
            </div>

        <?php else: ?>
            <div class="feedback-icon">⚠️</div>
            <h2 class="text-orange">ÉCHEC DU PAIEMENT</h2>
            <p class="mb-25">La transaction a été refusée ou la signature est invalide.</p>
            <a href="panier.php" class="btn-full">RÉESSAYER LE PAIEMENT</a>
        <?php endif; ?>

    </div>
</main>

<?php include '../LIB/footer.php'; ?>

</body>
</html>
