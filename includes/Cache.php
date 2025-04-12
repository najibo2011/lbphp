<?php
/**
 * Classe pour la gestion du cache
 */
class Cache {
    private $cachePath;
    private $cacheEnabled;
    private $defaultExpiry;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->cachePath = __DIR__ . '/../cache/';
        $this->cacheEnabled = true; // Peut être configuré via un fichier de configuration
        $this->defaultExpiry = 3600; // 1 heure par défaut
        
        // Créer le répertoire de cache s'il n'existe pas
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * Générer une clé de cache
     */
    private function generateKey($key) {
        return md5($key);
    }
    
    /**
     * Obtenir le chemin complet d'un fichier de cache
     */
    private function getFilePath($key) {
        $hashedKey = $this->generateKey($key);
        return $this->cachePath . $hashedKey . '.cache';
    }
    
    /**
     * Vérifier si une clé existe dans le cache
     */
    public function has($key) {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        $data = unserialize($content);
        
        // Vérifier si le cache a expiré
        if ($data['expiry'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Récupérer une valeur du cache
     */
    public function get($key, $default = null) {
        if (!$this->has($key)) {
            return $default;
        }
        
        $filePath = $this->getFilePath($key);
        $content = file_get_contents($filePath);
        $data = unserialize($content);
        
        return $data['value'];
    }
    
    /**
     * Stocker une valeur dans le cache
     */
    public function set($key, $value, $expiry = null) {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        $expiry = $expiry ?: $this->defaultExpiry;
        $expiryTime = time() + $expiry;
        
        $data = [
            'value' => $value,
            'expiry' => $expiryTime
        ];
        
        $filePath = $this->getFilePath($key);
        
        return file_put_contents($filePath, serialize($data)) !== false;
    }
    
    /**
     * Supprimer une valeur du cache
     */
    public function delete($key) {
        $filePath = $this->getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Vider tout le cache
     */
    public function flush() {
        $files = glob($this->cachePath . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Récupérer une valeur du cache ou l'y stocker si elle n'existe pas
     */
    public function remember($key, $callback, $expiry = null) {
        if ($this->has($key)) {
            return $this->get($key);
        }
        
        $value = $callback();
        $this->set($key, $value, $expiry);
        
        return $value;
    }
    
    /**
     * Vider tout le cache
     * 
     * @return bool Succès de l'opération
     */
    public function clearAll() {
        if (!$this->cacheEnabled || !is_dir($this->cachePath)) {
            return false;
        }
        
        $success = true;
        
        // Parcourir tous les fichiers du répertoire de cache
        $files = glob($this->cachePath . '*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                // Supprimer le fichier
                if (!unlink($file)) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
}
