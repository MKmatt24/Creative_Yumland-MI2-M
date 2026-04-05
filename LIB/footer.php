<footer>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'livreur'): ?>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Aide au Livreur</h3>
                    <p>Un problème ? Contactez le support :<br>01 06 26 60 66</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Los Pollos Hermanos. Espace Livreur.</p>
            </div>
            
        <?php else: ?>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>À propos</h3>
                    <p>Los Pollos Hermanos - Le meilleur poulet frit en ville</p>
                </div>
                <div class="footer-section">
                    <h3>Nous contacter</h3>
                    <p>Email : contact@lospolloshermanos.fr</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Los Pollos Hermanos. Tous droits réservés.</p>
            </div>
        <?php endif; ?>
    </footer>
</body>
</html>