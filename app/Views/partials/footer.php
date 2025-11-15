<?php
$versionFile = dirname(__DIR__, 3) . '/VERSION';
$version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '1';
?>
<footer class="site-footer">
    <div class="footer-container">
        <p>&copy; <?= date('Y') ?> Suivi Diagnostics - Tous droits réservés</p>
        <p>Version V<?= htmlspecialchars($version) ?> | <a href="/help">Aide</a> | <a href="/privacy">Confidentialité</a></p>
    </div>
</footer>
