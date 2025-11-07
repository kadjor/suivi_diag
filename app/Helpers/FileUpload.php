<?php

namespace Helpers;

/**
 * Gestionnaire d'upload de fichiers sécurisé
 */
class FileUpload
{
    private $file;
    private $uploadPath;
    private $allowedExtensions;
    private $allowedMimeTypes;
    private $maxSize;
    private $errors = [];

    /**
     * Constructeur
     */
    public function __construct($fileInputName, $uploadPath)
    {
        $this->file = $_FILES[$fileInputName] ?? null;
        $this->uploadPath = rtrim($uploadPath, '/');

        // Configuration par défaut depuis config
        $this->allowedExtensions = config('upload.allowed_extensions', []);
        $this->allowedMimeTypes = config('upload.allowed_mime_types', []);
        $this->maxSize = config('upload.max_size', 20971520); // 20 MB
    }

    /**
     * Définit les extensions autorisées
     */
    public function setAllowedExtensions($extensions)
    {
        $this->allowedExtensions = $extensions;
        return $this;
    }

    /**
     * Définit les types MIME autorisés
     */
    public function setAllowedMimeTypes($mimeTypes)
    {
        $this->allowedMimeTypes = $mimeTypes;
        return $this;
    }

    /**
     * Définit la taille maximale
     */
    public function setMaxSize($size)
    {
        $this->maxSize = $size;
        return $this;
    }

    /**
     * Valide le fichier uploadé
     */
    public function validate()
    {
        $this->errors = [];

        if (!$this->file || $this->file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->errors[] = "Aucun fichier n'a été uploadé.";
            return false;
        }

        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($this->file['error']);
            return false;
        }

        // Vérifier la taille
        if ($this->file['size'] > $this->maxSize) {
            $this->errors[] = "Le fichier est trop volumineux (max: " . formatFileSize($this->maxSize) . ").";
            return false;
        }

        // Vérifier l'extension
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = "L'extension .{$extension} n'est pas autorisée.";
            return false;
        }

        // Vérifier le type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $this->errors[] = "Le type de fichier n'est pas autorisé.";
            return false;
        }

        // Vérifier que c'est bien un fichier uploadé (sécurité)
        if (!is_uploaded_file($this->file['tmp_name'])) {
            $this->errors[] = "Erreur de sécurité lors de l'upload.";
            return false;
        }

        return true;
    }

    /**
     * Upload le fichier
     *
     * @param string|null $filename Nom personnalisé (si null, génère un nom unique)
     * @return array|false Informations du fichier uploadé ou false en cas d'erreur
     */
    public function upload($filename = null)
    {
        if (!$this->validate()) {
            return false;
        }

        // Créer le répertoire si nécessaire
        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0770, true)) {
                $this->errors[] = "Impossible de créer le répertoire de destination.";
                return false;
            }
        }

        // Générer le nom de fichier
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        $filename = $filename ?? uniqueFilename($this->file['name']);

        // S'assurer que le nom a l'extension correcte
        if (pathinfo($filename, PATHINFO_EXTENSION) !== $extension) {
            $filename .= '.' . $extension;
        }

        $destination = $this->uploadPath . '/' . $filename;

        // Déplacer le fichier
        if (!move_uploaded_file($this->file['tmp_name'], $destination)) {
            $this->errors[] = "Erreur lors du déplacement du fichier.";
            return false;
        }

        // Définir les permissions
        chmod($destination, 0660);

        // Scan antivirus si disponible
        if (config('upload.scan_antivirus') && $this->isAvailable('clamscan')) {
            if (!$this->scanVirus($destination)) {
                unlink($destination);
                $this->errors[] = "Le fichier a été rejeté par l'antivirus.";
                return false;
            }
        }

        return [
            'filename' => $filename,
            'original_filename' => $this->file['name'],
            'filepath' => $destination,
            'file_size' => $this->file['size'],
            'mime_type' => $this->file['type'],
            'extension' => $extension
        ];
    }

    /**
     * Message d'erreur d'upload
     */
    private function getUploadErrorMessage($errorCode)
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture sur le disque.',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier.',
        ];

        return $messages[$errorCode] ?? 'Erreur inconnue lors de l\'upload.';
    }

    /**
     * Vérifie si une commande est disponible
     */
    private function isAvailable($command)
    {
        $output = shell_exec("which $command 2>/dev/null");
        return !empty($output);
    }

    /**
     * Scan antivirus avec ClamAV
     */
    private function scanVirus($filepath)
    {
        $output = shell_exec("clamscan --no-summary " . escapeshellarg($filepath) . " 2>&1");
        return strpos($output, 'OK') !== false;
    }

    /**
     * Retourne les erreurs
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Retourne la première erreur
     */
    public function firstError()
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Supprime un fichier uploadé
     */
    public static function delete($filepath)
    {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Génère une URL sécurisée pour télécharger un fichier
     */
    public static function generateDownloadUrl($fileId, $type = 'report')
    {
        // Générer un token de téléchargement temporaire
        $token = bin2hex(random_bytes(32));
        $_SESSION['download_tokens'][$token] = [
            'file_id' => $fileId,
            'type' => $type,
            'expires' => time() + 300 // 5 minutes
        ];

        return url("/download/{$type}/{$fileId}?token={$token}");
    }

    /**
     * Vérifie un token de téléchargement
     */
    public static function verifyDownloadToken($token, $fileId, $type)
    {
        if (!isset($_SESSION['download_tokens'][$token])) {
            return false;
        }

        $tokenData = $_SESSION['download_tokens'][$token];

        // Vérifier l'expiration
        if ($tokenData['expires'] < time()) {
            unset($_SESSION['download_tokens'][$token]);
            return false;
        }

        // Vérifier la correspondance
        if ($tokenData['file_id'] != $fileId || $tokenData['type'] !== $type) {
            return false;
        }

        // Token utilisé, le supprimer
        unset($_SESSION['download_tokens'][$token]);

        return true;
    }
}
