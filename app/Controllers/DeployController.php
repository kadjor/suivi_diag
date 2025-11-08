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
        // Récupérer les informations Git
        $gitStatus = $this->getGitStatus();
        $currentBranch = $this->getCurrentBranch();
        $lastCommits = $this->getLastCommits(10);
        $hasChanges = $this->hasLocalChanges();
        $deployLogs = $this->getDeployLogs(20);

        View::render('deploy.index', [
            'gitStatus' => $gitStatus,
            'currentBranch' => $currentBranch,
            'lastCommits' => $lastCommits,
            'hasChanges' => $hasChanges,
            'deployLogs' => $deployLogs,
            'appDir' => $this->appDir
        ]);
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
}
