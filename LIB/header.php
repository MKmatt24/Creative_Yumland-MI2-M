<?php
// Vérification de sécurité : si l'utilisateur est connecté, on vérifie s'il n'est pas banni
if (isset($_SESSION['user_id'])) {
    $check_users = json_decode(file_get_contents(__DIR__ . '/../DATA/users.json'), true);
    foreach ($check_users as $u) {
        if ($u['id'] == $_SESSION['user_id'] && ($u['statut'] === 'suspendu' || $u['statut'] === 'inactif')) {
            session_destroy();
            header('Location: connexion.php?error=account_disabled');
            exit;
        }
    }
}
?>
<header>
    <nav>
        <div class="logo">
            <div class="logo-box">
                <a href="accueil.php">
                    <img src="../IMAGES/logo.png" alt="Logo Los Pollos" class="nav-logo">
                </a>
            </div>
        </div>

        <button class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>


        <ul>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="accueil.php#contact">Contact</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'livreur'): ?>
                    <li><a href="livraisons_en_attente.php">Livraisons en attente</a></li>
                    <li><a href="profil.php">Mon Profil</a></li>
                    <li><a href="rewards.php">Mes Rémunérations</a></li>
                <?php elseif ($_SESSION['role'] === 'restaurateur' || $_SESSION['role'] === 'admin'): ?>
                    <li><a href="commande.php">Commandes</a></li>
                    <li><a href="profil.php">Profil</a></li>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php" style="color: var(--orange);">Admin</a></li>
                    <?php endif; ?>
                <?php else: // Client ?>
                    <li><a href="livraison.php">Livraisons</a></li>
                    <li><a href="profil.php">Profil</a></li>
                <?php endif; ?>

                <li><a href="../TRAITEMENTS/deconnexion.php">Déconnexion</a></li>

            <?php else: ?>
                <li><a href="livraison.php">Livraisons</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <script>

        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=/";
        }

        function getCookie(name) {
            let nameEQ = name + "=";
            let ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function updateButtonText(themeName) {
            const btn = document.getElementById('theme-toggle-btn');
            if (!btn) return; // Si le bouton n'est pas sur la page, on ne fait rien

            // Correspondance entre le nom du fichier CSS et le texte propre
            let displayName = "Par défaut";
            switch(themeName) {
                case 'light': displayName = "Clair"; break;
                case 'contrast': displayName = "Contraste Élevé"; break;
                case 'accessible': displayName = "Accessible"; break;
                case 'light2': displayName = "Clair 2"; break;
                default: displayName = "Par défaut"; break;
            }

            // On change le texte du bouton
            btn.innerHTML = `🎨 Thème : ${displayName}`;
        }

        function applyTheme(themeName) {
            let themeLink = document.getElementById('dynamic-theme-css');
            const body = document.body;

            // On nettoie la classe spécifique au mode accessible
            body.classList.remove('theme-accessible');
            
            if (!themeName || themeName === 'default') {
                if (themeLink) themeLink.remove();
                setCookie('selected-theme', 'default', 30);
                updateButtonText('default'); // <--- Ajouté ici
                return;
            }

            if (!themeLink) {
                themeLink = document.createElement('link');
                themeLink.id = 'dynamic-theme-css';
                themeLink.rel = 'stylesheet';
                document.head.appendChild(themeLink);
            }
            
            themeLink.href = `../CSS/${themeName}.css`;

            // Gestion spécifique du mode accessible (agrandissement police)
            if (themeName === 'accessible') {
                body.classList.add('theme-accessible');
            }

            setCookie('selected-theme', themeName, 30);
            updateButtonText(themeName); // <--- Ajouté ici
        }

        function cycleTheme() {
            const themes = ['default', 'light', 'contrast', 'accessible', 'light2'];
            let currentTheme = getCookie('selected-theme') || 'default';
            
            let currentIndex = themes.indexOf(currentTheme);
            let nextIndex = (currentIndex + 1) % themes.length;
            let nextTheme = themes[nextIndex];
            
            console.log("Passage au thème : " + nextTheme);
            applyTheme(nextTheme);
        }

        document.addEventListener('DOMContentLoaded', function() {
        
            // Appliquer le thème sauvegardé
            const savedTheme = getCookie('selected-theme');
            if (savedTheme) {
                applyTheme(savedTheme);
            }
        });
    </script>
</header>