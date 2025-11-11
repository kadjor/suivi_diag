<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;

class DeployController extends Controller
{
    private $appDir;
    private $logFile;

    public function __construct()
    {
        // Vérifier que l'utilisateur est admin
        if (!Auth::check() || Auth::user()['role_name'] !== 'admin') {
            Session::flash('error', 'Accès refusé - Réservé aux administrateurs');
            View::redirect('/dashboard');
        }

        $this->appDir = realpath(__DIR__ . '/../../');
        $this->logFile = $this->appDir . '/storage/logs/deploy.log';
    }

    /**
     * Page principale de déploiement
     */
    public function index()
    {
        // Vérifier si c'est un repo Git
        $isGitRepo = $this->isGitRepository();

        // Récupérer les informations Git si applicable
        $gitStatus = $isGitRepo ? $this->getGitStatus() : ['error' => 'Non-Git'];
        $currentBranch = $isGitRepo ? $this->getCurrentBranch() : 'N/A';
        $branches = $isGitRepo ? $this->getAllBranches() : [];
        $lastCommits = $isGitRepo ? $this->getLastCommits(10) : [];
        $hasChanges = $isGitRepo ? $this->hasLocalChanges() : false;
        $deployLogs = $this->getDeployLogs(20);

        // Récupérer les branches personnalisées (gérer le cas où la table n'existe pas encore)
        $customBranches = [];
        try {
            $customBranchModel = new \Models\CustomBranch();
            $customBranches = $customBranchModel->getAll();
        } catch (\Exception $e) {
            // La table n'existe pas encore - migration 008 doit être exécutée
            error_log('CustomBranch table not found: ' . $e->getMessage());
        }

        View::render('deploy.index', [
            'isGitRepo' => $isGitRepo,
            'gitStatus' => $gitStatus,
            'currentBranch' => $currentBranch,
            'branches' => $branches,
            'customBranches' => $customBranches,
            'lastCommits' => $lastCommits,
            'hasChanges' => $hasChanges,
            'deployLogs' => $deployLogs,
            'appDir' => $this->appDir
        ]);
    }

    /**
     * Vérifie si le répertoire est un dépôt Git
     */
    private function isGitRepository()
    {
        return is_dir($this->appDir . '/.git');
    }

    /**
     * Récupère le statut Git
     */
    private function getGitStatus()
    {
        $output = [];
        exec("cd {$this->appDir} && git status --porcelain 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            return ['error' => 'Impossible de récupérer le statut Git'];
        }

        return [
            'files' => $output,
            'clean' => empty($output)
        ];
    }

    /**
     * Récupère la branche actuelle
     */
    private function getCurrentBranch()
    {
        $output = [];
        exec("cd {$this->appDir} && git branch --show-current 2>&1", $output, $returnCode);

        if ($returnCode !== 0 || empty($output)) {
            return 'Inconnu';
        }

        return trim($output[0]);
    }

    /**
     * Récupère toutes les branches (locales et distantes)
     */
    private function getAllBranches()
    {
        $output = [];

        // Récupérer les branches locales et distantes
        exec("cd {$this->appDir} && git branch -a 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            return [];
        }

        $branches = [
            'local' => [],
            'remote' => []
        ];

        foreach ($output as $line) {
            $line = trim($line);

            // Ignorer les lignes vides ou avec HEAD
            if (empty($line) || strpos($line, 'HEAD') !== false) {
                continue;
            }

            // Enlever l'indicateur de branche courante (*)
            $line = preg_replace('/^\*\s+/', '', $line);

            // Vérifier si c'est une branche distante
            if (strpos($line, 'remotes/') === 0) {
                // Nettoyer le nom de la branche distante
                $branchName = str_replace('remotes/origin/', '', $line);
                $branches['remote'][] = $branchName;
            } else {
                $branches['local'][] = $line;
            }
        }

        return $branches;
    }

    /**
     * Récupère les derniers commits
     */
    private function getLastCommits($limit = 10)
    {
        $output = [];
        $format = '%h|%an|%ar|%s';
        exec("cd {$this->appDir} && git log --pretty=format:'{$format}' -n {$limit} 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            return [];
        }

        $commits = [];
        foreach ($output as $line) {
            $parts = explode('|', $line);
            if (count($parts) === 4) {
                $commits[] = [
                    'hash' => $parts[0],
                    'author' => $parts[1],
                    'date' => $parts[2],
                    'message' => $parts[3]
                ];
            }
        }

        return $commits;
    }

    /**
     * Vérifie s'il y a des changements locaux
     */
    private function hasLocalChanges()
    {
        $output = [];
        exec("cd {$this->appDir} && git status --porcelain 2>&1", $output);
        return !empty($output);
    }

    /**
     * Effectue un git pull
     */
    public function pull()
    {
        $this->log('===== DÉBUT DÉPLOIEMENT =====');
        $this->log('Utilisateur: ' . Auth::user()['email']);
        $this->log('Date: ' . date('Y-m-d H:i:s'));

        $results = [
            'success' => false,
            'messages' => [],
            'errors' => []
        ];

        try {
            // 1. Créer un backup avant le pull
            $this->log('Création du backup...');
            $backupResult = $this->createBackup();
            if (!$backupResult['success']) {
                throw new \Exception('Échec de la création du backup: ' . $backupResult['error']);
            }
            $results['messages'][] = 'Backup créé: ' . $backupResult['file'];
            $this->log('✓ Backup créé: ' . $backupResult['file']);

            // 2. Vérifier les changements locaux
            if ($this->hasLocalChanges()) {
                $this->log('⚠ Changements locaux détectés');
                $results['messages'][] = 'Avertissement: Changements locaux détectés';
            }

            // 3. Fetch les mises à jour
            $this->log('Récupération des mises à jour...');
            $output = [];
            exec("cd {$this->appDir} && git fetch origin 2>&1", $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Erreur git fetch: ' . implode("\n", $output));
            }
            $this->log('✓ Fetch terminé');

            // 4. Git pull
            $this->log('Application des mises à jour...');
            $output = [];
            exec("cd {$this->appDir} && git pull origin " . $this->getCurrentBranch() . " 2>&1", $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Erreur git pull: ' . implode("\n", $output));
            }

            $pullOutput = implode("\n", $output);
            $results['messages'][] = $pullOutput;
            $this->log('✓ Pull terminé: ' . $pullOutput);

            // 5. Restaurer les permissions
            $this->log('Restauration des permissions...');
            $permissionsResult = $this->restorePermissions();
            if ($permissionsResult['success']) {
                $results['messages'][] = 'Permissions restaurées';
                $this->log('✓ Permissions restaurées');
            } else {
                $results['messages'][] = 'Avertissement: ' . $permissionsResult['error'];
                $this->log('⚠ Erreur permissions: ' . $permissionsResult['error']);
            }

            // 6. Vider le cache si le dossier existe
            if (is_dir($this->appDir . '/storage/cache')) {
                $this->log('Nettoyage du cache...');
                $this->clearCache();
                $results['messages'][] = 'Cache nettoyé';
                $this->log('✓ Cache nettoyé');
            }

            $results['success'] = true;
            $this->log('===== DÉPLOIEMENT RÉUSSI =====');

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log('✗ ERREUR: ' . $e->getMessage());
            $this->log('===== DÉPLOIEMENT ÉCHOUÉ =====');
        }

        View::json($results);
    }

    /**
     * Change de branche Git
     */
    public function checkoutBranch()
    {
        $branch = $_POST['branch'] ?? null;

        if (!$branch) {
            View::json(['success' => false, 'error' => 'Branche non spécifiée']);
            return;
        }

        $this->log('===== CHANGEMENT DE BRANCHE =====');
        $this->log('Utilisateur: ' . Auth::user()['email']);
        $this->log('Branche cible: ' . $branch);
        $this->log('Date: ' . date('Y-m-d H:i:s'));

        $results = [
            'success' => false,
            'messages' => [],
            'errors' => []
        ];

        try {
            // 1. Créer un backup avant le changement
            $this->log('Création du backup...');
            $backupResult = $this->createBackup();
            if (!$backupResult['success']) {
                throw new \Exception('Échec de la création du backup: ' . $backupResult['error']);
            }
            $results['messages'][] = 'Backup créé: ' . $backupResult['file'];
            $this->log('✓ Backup créé: ' . $backupResult['file']);

            // 2. Vérifier les changements locaux
            if ($this->hasLocalChanges()) {
                $this->log('⚠ Changements locaux détectés - stash');
                exec("cd {$this->appDir} && git stash 2>&1", $output, $returnCode);
                $results['messages'][] = 'Changements locaux mis de côté (stash)';
            }

            // 3. Fetch pour s'assurer d'avoir toutes les branches
            $this->log('Récupération des branches distantes...');
            exec("cd {$this->appDir} && git fetch origin 2>&1", $output, $returnCode);
            if ($returnCode !== 0) {
                throw new \Exception('Erreur git fetch: ' . implode("\n", $output));
            }
            $this->log('✓ Fetch terminé');

            // 4. Checkout de la branche
            $this->log('Changement vers la branche ' . $branch . '...');
            $output = [];

            // Si c'est une branche distante, créer une branche locale qui la suit
            if (strpos($branch, 'origin/') === 0) {
                $localBranch = str_replace('origin/', '', $branch);
                exec("cd {$this->appDir} && git checkout -b {$localBranch} {$branch} 2>&1", $output, $returnCode);
            } else {
                // C'est une branche locale, checkout direct
                exec("cd {$this->appDir} && git checkout {$branch} 2>&1", $output, $returnCode);
            }

            if ($returnCode !== 0) {
                throw new \Exception('Erreur git checkout: ' . implode("\n", $output));
            }

            $checkoutOutput = implode("\n", $output);
            $results['messages'][] = $checkoutOutput;
            $this->log('✓ Checkout terminé: ' . $checkoutOutput);

            // 5. Pull pour mettre à jour
            $this->log('Mise à jour de la branche...');
            exec("cd {$this->appDir} && git pull origin {$branch} 2>&1", $output, $returnCode);
            if ($returnCode !== 0) {
                $this->log('⚠ Pull échoué (peut-être déjà à jour)');
            } else {
                $this->log('✓ Pull terminé');
            }

            // 6. Restaurer les permissions
            $this->log('Restauration des permissions...');
            $permissionsResult = $this->restorePermissions();
            if ($permissionsResult['success']) {
                $results['messages'][] = 'Permissions restaurées';
                $this->log('✓ Permissions restaurées');
            }

            // 7. Vider le cache si le dossier existe
            if (is_dir($this->appDir . '/storage/cache')) {
                $this->log('Nettoyage du cache...');
                $this->clearCache();
                $results['messages'][] = 'Cache nettoyé';
                $this->log('✓ Cache nettoyé');
            }

            $results['success'] = true;
            $results['messages'][] = 'Changement vers la branche ' . $branch . ' réussi !';
            $this->log('===== CHANGEMENT DE BRANCHE RÉUSSI =====');

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log('✗ ERREUR: ' . $e->getMessage());
            $this->log('===== CHANGEMENT DE BRANCHE ÉCHOUÉ =====');
        }

        View::json($results);
    }

    /**
     * Crée un backup du répertoire
     */
    private function createBackup()
    {
        try {
            $backupDir = $this->appDir . '/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.tar.gz';

            // Exclure les dossiers volumineux
            $excludes = '--exclude=backups --exclude=storage/logs --exclude=storage/cache --exclude=node_modules --exclude=.git';

            $cmd = "cd {$this->appDir} && tar {$excludes} -czf {$backupFile} . 2>&1";
            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0) {
                return [
                    'success' => false,
                    'error' => implode("\n", $output)
                ];
            }

            // Garder seulement les 5 derniers backups
            $this->cleanOldBackups($backupDir, 5);

            return [
                'success' => true,
                'file' => basename($backupFile)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Nettoie les anciens backups
     */
    private function cleanOldBackups($dir, $keep = 5)
    {
        $files = glob($dir . '/backup_*.tar.gz');
        if (count($files) <= $keep) {
            return;
        }

        // Trier par date de modification
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Supprimer les anciens
        $toDelete = array_slice($files, $keep);
        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }

    /**
     * Restaure les permissions
     */
    private function restorePermissions()
    {
        try {
            $writableDirs = [
                'storage',
                'storage/logs',
                'storage/cache',
                'storage/sessions',
                'storage/uploads',
                'public/uploads',
                'public/reports',
                'config'
            ];

            foreach ($writableDirs as $dir) {
                $path = $this->appDir . '/' . $dir;
                if (is_dir($path)) {
                    chmod($path, 0775);
                }
            }

            // Fichiers de configuration
            $configFiles = ['config/app.php', 'config/database.php', 'config/email.php'];
            foreach ($configFiles as $file) {
                $path = $this->appDir . '/' . $file;
                if (file_exists($path)) {
                    chmod($path, 0600);
                }
            }

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vide le cache
     */
    private function clearCache()
    {
        $cacheDir = $this->appDir . '/storage/cache';
        if (!is_dir($cacheDir)) {
            return;
        }

        $files = glob($cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    /**
     * Récupère les logs de déploiement
     */
    private function getDeployLogs($lines = 20)
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $output = [];
        exec("tail -n {$lines} {$this->logFile} 2>&1", $output);
        return $output;
    }

    /**
     * Écrit dans le fichier de log
     */
    private function log($message)
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Affiche les différences avec la version distante
     */
    public function diff()
    {
        $output = [];
        $branch = $this->getCurrentBranch();
        exec("cd {$this->appDir} && git fetch origin && git diff HEAD origin/{$branch} --stat 2>&1", $output);

        View::json([
            'diff' => implode("\n", $output)
        ]);
    }

    /**
     * Réinitialise les changements locaux
     */
    public function reset()
    {
        $this->log('===== RESET DES CHANGEMENTS =====');
        $this->log('Utilisateur: ' . Auth::user()['email']);

        try {
            $output = [];
            exec("cd {$this->appDir} && git reset --hard HEAD 2>&1", $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Erreur git reset: ' . implode("\n", $output));
            }

            $this->log('✓ Reset terminé');
            View::json([
                'success' => true,
                'message' => 'Changements locaux réinitialisés'
            ]);

        } catch (\Exception $e) {
            $this->log('✗ Erreur: ' . $e->getMessage());
            View::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Liste les backups disponibles
     */
    public function backups()
    {
        $backupDir = $this->appDir . '/backups';
        if (!is_dir($backupDir)) {
            View::json(['backups' => []]);
            return;
        }

        $files = glob($backupDir . '/backup_*.tar.gz');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('d/m/Y H:i:s', filemtime($file))
            ];
        }

        // Trier par date décroissante
        usort($backups, function($a, $b) {
            return strcmp($b['name'], $a['name']);
        });

        View::json(['backups' => $backups]);
    }

    /**
     * Formate les octets en format lisible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Télécharge depuis GitHub sans utiliser Git
     */
    public function downloadFromGithub()
    {
        $this->log('===== TÉLÉCHARGEMENT DEPUIS GITHUB =====');
        $this->log('Utilisateur: ' . Auth::user()['email']);
        $this->log('Date: ' . date('Y-m-d H:i:s'));

        $results = [
            'success' => false,
            'messages' => [],
            'errors' => []
        ];

        try {
            // Paramètres GitHub
            $githubUser = $_POST['github_user'] ?? 'kadjor';
            $githubRepo = $_POST['github_repo'] ?? 'suivi_diag';
            $githubBranch = $_POST['github_branch'] ?? 'main';

            $this->log("GitHub: {$githubUser}/{$githubRepo} (branche: {$githubBranch})");

            // 1. Créer un backup
            $this->log('Création du backup...');
            $backupResult = $this->createBackup();
            if (!$backupResult['success']) {
                throw new \Exception('Échec de la création du backup: ' . $backupResult['error']);
            }
            $results['messages'][] = 'Backup créé: ' . $backupResult['file'];
            $this->log('✓ Backup créé: ' . $backupResult['file']);

            // 2. Télécharger le ZIP depuis GitHub
            $this->log('Téléchargement depuis GitHub...');

            // Encoder correctement le nom de branche (gérer les slashes)
            $encodedBranch = str_replace('/', '%2F', $githubBranch);
            $zipUrl = "https://github.com/{$githubUser}/{$githubRepo}/archive/refs/heads/{$encodedBranch}.zip";

            $this->log("URL de téléchargement: {$zipUrl}");
            $zipFile = $this->appDir . '/temp_download.zip';

            // Utiliser curl avec User-Agent pour éviter les blocages
            $ch = curl_init($zipUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

            $zipContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            $this->log("HTTP Code: {$httpCode}");
            $this->log("URL finale: {$finalUrl}");

            if ($httpCode !== 200) {
                $errorMsg = "Échec du téléchargement (HTTP {$httpCode})";

                if ($httpCode === 404) {
                    $errorMsg .= "\n\nVérifiez que :";
                    $errorMsg .= "\n- Le dépôt existe : https://github.com/{$githubUser}/{$githubRepo}";
                    $errorMsg .= "\n- La branche existe : {$githubBranch}";
                    $errorMsg .= "\n- Le dépôt est PUBLIC";
                }

                if ($curlError) {
                    $errorMsg .= "\nErreur cURL: {$curlError}";
                }

                throw new \Exception($errorMsg);
            }

            if (!$zipContent) {
                throw new \Exception("Contenu ZIP vide après téléchargement");
            }

            file_put_contents($zipFile, $zipContent);
            $this->log('✓ Téléchargement terminé (' . $this->formatBytes(strlen($zipContent)) . ')');
            $results['messages'][] = 'Fichiers téléchargés depuis GitHub (' . $this->formatBytes(strlen($zipContent)) . ')';

            // 3. Décompresser
            $this->log('Décompression...');
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) !== true) {
                throw new \Exception('Impossible d\'ouvrir le fichier ZIP');
            }

            $extractPath = $this->appDir . '/temp_extract';
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $zip->extractTo($extractPath);
            $zip->close();
            $this->log('✓ Décompression terminée');

            // 4. Trouver le bon dossier extrait (gérer les noms avec slashes)
            $this->log('Recherche du dossier source...');

            // GitHub remplace les slashes par des tirets dans les noms de dossiers
            $cleanBranchName = str_replace('/', '-', $githubBranch);
            $sourceDir = $extractPath . "/{$githubRepo}-{$cleanBranchName}";

            // Si pas trouvé, chercher n'importe quel dossier
            if (!is_dir($sourceDir)) {
                $this->log("Dossier attendu non trouvé: {$sourceDir}");
                $dirs = glob($extractPath . '/*', GLOB_ONLYDIR);
                if (!empty($dirs)) {
                    $sourceDir = $dirs[0];
                    $this->log("Utilisation du dossier trouvé: {$sourceDir}");
                } else {
                    throw new \Exception("Aucun dossier trouvé après extraction");
                }
            }

            if (!is_dir($sourceDir)) {
                throw new \Exception("Dossier source introuvable: {$sourceDir}");
            }

            // 5. Copier les fichiers (en excluant certains dossiers)
            $this->log('Copie des fichiers...');
            $excludes = ['.git', 'backups', 'storage/logs', 'config/database.php', 'config/email.php'];
            $this->copyDirectory($sourceDir, $this->appDir, $excludes);

            $this->log('✓ Fichiers copiés');
            $results['messages'][] = 'Fichiers mis à jour';

            // 6. Nettoyer les fichiers temporaires
            $this->log('Nettoyage...');
            @unlink($zipFile);
            $this->deleteDirectory($extractPath);
            $this->log('✓ Nettoyage terminé');

            // 7. Restaurer les permissions
            $this->log('Restauration des permissions...');
            $permissionsResult = $this->restorePermissions();
            if ($permissionsResult['success']) {
                $results['messages'][] = 'Permissions restaurées';
                $this->log('✓ Permissions restaurées');
            }

            // 8. Vider le cache
            if (is_dir($this->appDir . '/storage/cache')) {
                $this->clearCache();
                $results['messages'][] = 'Cache nettoyé';
                $this->log('✓ Cache nettoyé');
            }

            $results['success'] = true;
            $this->log('===== TÉLÉCHARGEMENT RÉUSSI =====');

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log('✗ ERREUR: ' . $e->getMessage());
            $this->log('===== TÉLÉCHARGEMENT ÉCHOUÉ =====');
        }

        View::json($results);
    }

    /**
     * Copie un répertoire récursivement en excluant certains fichiers
     */
    private function copyDirectory($source, $dest, $excludes = [])
    {
        if (!is_dir($source)) {
            return;
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // Vérifier les exclusions
            $excluded = false;
            foreach ($excludes as $exclude) {
                if (strpos($source . '/' . $file, $exclude) !== false) {
                    $excluded = true;
                    break;
                }
            }

            if ($excluded) {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($srcPath)) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                $this->copyDirectory($srcPath, $destPath, $excludes);
            } else {
                copy($srcPath, $destPath);
            }
        }
        closedir($dir);
    }

    /**
     * Supprime un répertoire récursivement
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * Applique les permissions uniquement (endpoint public)
     */
    public function applyPermissions()
    {
        $this->log('===== APPLICATION DES PERMISSIONS =====');
        $this->log('Utilisateur: ' . Auth::user()['email']);

        try {
            $result = $this->restorePermissions();

            if ($result['success']) {
                $this->log('✓ Permissions appliquées avec succès');
                View::json([
                    'success' => true,
                    'message' => 'Permissions restaurées avec succès'
                ]);
            } else {
                throw new \Exception($result['error']);
            }

        } catch (\Exception $e) {
            $this->log('✗ Erreur: ' . $e->getMessage());
            View::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Diagnostic - voir le contenu brut retourné
     */
    public function migrationsDiagnostic()
    {
        // Désactiver TOUTE sortie automatique
        @ini_set('display_errors', '0');
        error_reporting(0);

        // Nettoyer TOUT
        while (ob_get_level()) {
            ob_end_clean();
        }

        echo "=== DIAGNOSTIC MIGRATIONS ===\n\n";

        try {
            $migrationsDir = $this->appDir . '/database/migrations';
            echo "Répertoire migrations: $migrationsDir\n";
            echo "Existe: " . (is_dir($migrationsDir) ? 'OUI' : 'NON') . "\n\n";

            if (is_dir($migrationsDir)) {
                $files = glob($migrationsDir . '/*.sql');
                echo "Nombre de fichiers: " . count($files) . "\n";
                echo "Fichiers trouvés:\n";
                foreach ($files as $file) {
                    echo "  - " . basename($file) . "\n";
                }
            }

            echo "\n=== TEST CONNEXION DB ===\n";
            $db = \Core\Database::getConnection();
            echo "Connexion DB: OK\n";

            echo "\n=== FIN DIAGNOSTIC ===\n";

        } catch (\Exception $e) {
            echo "ERREUR: " . $e->getMessage() . "\n";
            echo "Trace: " . $e->getTraceAsString() . "\n";
        }

        exit;
    }

    /**
     * Liste les migrations disponibles
     */
    public function migrations()
    {
        // ULTRA ROBUSTE - Désactiver TOUTE sortie PHP
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');
        @ini_set('log_errors', '1');
        @ini_set('html_errors', '0');
        @error_reporting(0);

        // Nettoyer ABSOLUMENT TOUT avant de commencer
        while (@ob_get_level() > 0) {
            @ob_end_clean();
        }

        // NE PAS utiliser ob_start - envoyer directement

        try {
            $migrationsDir = $this->appDir . '/database/migrations';
            $migrations = [];

            if (!is_dir($migrationsDir)) {
                header('Content-Type: application/json; charset=utf-8', true);
                die(json_encode(['migrations' => [], 'debug' => 'dir not found']));
            }

            $files = @glob($migrationsDir . '/*.sql');

            if ($files === false || $files === null) {
                header('Content-Type: application/json; charset=utf-8', true);
                die(json_encode(['migrations' => [], 'debug' => 'glob failed']));
            }

            sort($files);

            foreach ($files as $file) {
                $name = basename($file);
                $executed = false;

                // Vérifier si exécutée SANS générer d'erreurs
                try {
                    $executed = $this->isMigrationExecutedSafe($name);
                } catch (\Exception $e) {
                    // Ignorer silencieusement
                }

                $migrations[] = [
                    'name' => $name,
                    'path' => $file,
                    'size' => $this->formatBytes(@filesize($file) ?: 0),
                    'executed' => $executed
                ];
            }

            // Envoyer le JSON directement
            header('Content-Type: application/json; charset=utf-8', true);
            die(json_encode(['migrations' => $migrations, 'success' => true]));

        } catch (\Exception $e) {
            // Nettoyer TOUT en cas d'erreur
            while (@ob_get_level() > 0) {
                @ob_end_clean();
            }

            $this->log('✗ Erreur liste migrations: ' . $e->getMessage());

            header('Content-Type: application/json; charset=utf-8', true);
            die(json_encode([
                'error' => $e->getMessage(),
                'migrations' => [],
                'success' => false
            ]));
        }
    }

    /**
     * Version sécurisée de isMigrationExecuted qui ne génère pas d'erreurs
     */
    private function isMigrationExecutedSafe($migrationName)
    {
        try {
            $db = \Core\Database::getConnection();

            // Vérifier si la table migrations existe
            $result = @$db->query("SHOW TABLES LIKE 'migrations'");

            if (!$result || $result->num_rows === 0) {
                // Table n'existe pas - ne pas la créer ici pour éviter les sorties
                return false;
            }

            // Vérifier si cette migration a été exécutée
            $stmt = @$db->prepare("SELECT id FROM migrations WHERE migration = ? LIMIT 1");
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param('s', $migrationName);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result && $result->num_rows > 0;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Vérifie si une migration a déjà été exécutée
     */
    private function isMigrationExecuted($migrationName)
    {
        try {
            $db = \Core\Database::getConnection();

            // Vérifier si la table migrations existe
            $result = $db->query("SHOW TABLES LIKE 'migrations'");
            if ($result->num_rows === 0) {
                // Table n'existe pas, créer la table de tracking
                $this->createMigrationsTable();
                return false;
            }

            // Vérifier si cette migration a été exécutée
            $stmt = $db->prepare("SELECT id FROM migrations WHERE migration = ? LIMIT 1");
            $stmt->bind_param('s', $migrationName);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->num_rows > 0;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Crée la table de suivi des migrations
     */
    private function createMigrationsTable()
    {
        try {
            $db = \Core\Database::getConnection();
            $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) NOT NULL,
                `executed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migration` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->query($sql);
            $this->log('✓ Table migrations créée');

        } catch (\Exception $e) {
            $this->log('✗ Erreur création table migrations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Exécute les migrations SQL
     */
    public function runMigrations()
    {
        try {
            // Nettoyer TOUS les buffers de sortie
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Démarrer un nouveau buffer
            ob_start();

            $this->log('===== EXÉCUTION DES MIGRATIONS =====');
            $this->log('Utilisateur: ' . Auth::user()['email']);
            $this->log('Date: ' . date('Y-m-d H:i:s'));

            $results = [
                'success' => false,
                'executed' => [],
                'skipped' => [],
                'errors' => []
            ];

            $migrationsDir = $this->appDir . '/database/migrations';

            if (!is_dir($migrationsDir)) {
                throw new \Exception('Dossier migrations introuvable');
            }

            // S'assurer que la table migrations existe
            $this->createMigrationsTable();

            // Récupérer toutes les migrations
            $files = glob($migrationsDir . '/*.sql');

            if ($files === false || empty($files)) {
                $results['errors'][] = 'Aucune migration trouvée';
                $this->log('✗ Aucune migration trouvée');
                ob_end_clean();
                View::json($results);
                return;
            }

            sort($files);
            $db = \Core\Database::getConnection();

            foreach ($files as $file) {
                $migrationName = basename($file);

                // Vérifier si déjà exécutée
                if ($this->isMigrationExecuted($migrationName)) {
                    $results['skipped'][] = $migrationName;
                    $this->log("⊘ Migration déjà exécutée: {$migrationName}");
                    continue;
                }

                $this->log("Exécution de: {$migrationName}");

                try {
                    // Lire le fichier SQL
                    $sql = file_get_contents($file);

                    if ($sql === false) {
                        throw new \Exception("Impossible de lire le fichier");
                    }

                    // Supprimer les commentaires SQL
                    $sql = preg_replace('/--.*$/m', '', $sql);

                    // Séparer les instructions SQL
                    $statements = $this->splitSqlStatements($sql);

                    // Exécuter chaque instruction
                    foreach ($statements as $statement) {
                        $statement = trim($statement);
                        if (empty($statement)) {
                            continue;
                        }

                        if (!$db->query($statement)) {
                            throw new \Exception("Erreur SQL: " . $db->error . "\nRequête: " . substr($statement, 0, 100));
                        }
                    }

                    // Enregistrer la migration comme exécutée
                    $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
                    $stmt->bind_param('s', $migrationName);
                    $stmt->execute();

                    $results['executed'][] = $migrationName;
                    $this->log("✓ Migration exécutée: {$migrationName}");

                } catch (\Exception $e) {
                    $error = "Erreur dans {$migrationName}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    $this->log("✗ {$error}");
                    // Continuer avec les autres migrations
                }
            }

            $results['success'] = empty($results['errors']);

            if ($results['success']) {
                $this->log('===== MIGRATIONS RÉUSSIES =====');
            } else {
                $this->log('===== MIGRATIONS TERMINÉES AVEC ERREURS =====');
            }

            ob_end_clean();
            View::json($results);

        } catch (\Exception $e) {
            // Nettoyer TOUS les buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            $this->log('✗ ERREUR CRITIQUE: ' . $e->getMessage());
            $this->log('===== MIGRATIONS ÉCHOUÉES =====');

            View::json([
                'success' => false,
                'executed' => [],
                'skipped' => [],
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Sépare les instructions SQL multiples
     */
    private function splitSqlStatements($sql)
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prevChar = $i > 0 ? $sql[$i - 1] : '';

            // Gérer les chaînes de caractères
            if (($char === '"' || $char === "'") && $prevChar !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            // Si on trouve un point-virgule hors d'une chaîne
            if ($char === ';' && !$inString) {
                $current .= $char;
                $statement = trim($current);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        // Ajouter la dernière instruction s'il y en a une
        $statement = trim($current);
        if (!empty($statement)) {
            $statements[] = $statement;
        }

        return $statements;
    }

    /**
     * Réinitialise les migrations (DANGER - à utiliser avec précaution)
     */
    public function resetMigrations()
    {
        $this->log('===== RÉINITIALISATION DES MIGRATIONS =====');
        $this->log('Utilisateur: ' . Auth::user()['email']);

        try {
            $db = \Core\Database::getConnection();
            $db->query("DROP TABLE IF EXISTS migrations");
            $this->createMigrationsTable();

            $this->log('✓ Migrations réinitialisées');
            View::json([
                'success' => true,
                'message' => 'Table migrations réinitialisée'
            ]);

        } catch (\Exception $e) {
            $this->log('✗ Erreur: ' . $e->getMessage());
            View::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ajoute une branche personnalisée
     */
    public function addCustomBranch()
    {
        $branchName = $_POST['branch_name'] ?? null;
        $description = $_POST['description'] ?? '';

        if (!$branchName) {
            View::json(['success' => false, 'error' => 'Nom de branche requis'], 400);
            return;
        }

        // Valider le nom de branche (caractères alphanumériques, tirets, underscores, slashes)
        if (!preg_match('/^[a-zA-Z0-9\/_-]+$/', $branchName)) {
            View::json(['success' => false, 'error' => 'Nom de branche invalide'], 400);
            return;
        }

        try {
            $customBranchModel = new \Models\CustomBranch();
            $result = $customBranchModel->addBranch($branchName, $description, Auth::id());
            View::json($result);
        } catch (\Exception $e) {
            View::json([
                'success' => false,
                'error' => 'La table custom_branches n\'existe pas. Veuillez exécuter la migration 008.'
            ], 500);
        }
    }

    /**
     * Supprime une branche personnalisée
     */
    public function deleteCustomBranch()
    {
        $branchId = $_POST['branch_id'] ?? null;

        if (!$branchId) {
            View::json(['success' => false, 'error' => 'ID de branche requis'], 400);
            return;
        }

        try {
            $customBranchModel = new \Models\CustomBranch();
            $result = $customBranchModel->deleteBranch($branchId);
            View::json($result);
        } catch (\Exception $e) {
            View::json([
                'success' => false,
                'error' => 'La table custom_branches n\'existe pas. Veuillez exécuter la migration 008.'
            ], 500);
        }
    }
}
