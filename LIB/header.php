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
            <li><a href="livraison.php">Livraisons</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profil.php">Mon Profil</a></li>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin.php" style="color: var(--orange);">Admin</a></li>
                <?php endif; ?>

                <li><a href="../TRAITEMENTS/deconnexion.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="connexion.php">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>