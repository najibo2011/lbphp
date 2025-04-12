<?php
/**
 * Classe pour la gestion de la sécurité
 */
class Security {
    private $csrfToken;
    private $rateLimits = [];
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Initialiser la session si elle n'est pas déjà démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Générer un token CSRF s'il n'existe pas
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $this->csrfToken = $_SESSION['csrf_token'];
    }
    
    /**
     * Obtenir le token CSRF
     */
    public function getCsrfToken() {
        return $this->csrfToken;
    }
    
    /**
     * Générer un nouveau token CSRF
     */
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $this->csrfToken = $token;
        return $token;
    }
    
    /**
     * Générer un champ de formulaire CSRF
     */
    public function getCsrfField() {
        return '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
    }
    
    /**
     * Vérifier le token CSRF
     */
    public function verifyCsrfToken($token) {
        return hash_equals($this->csrfToken, $token);
    }
    
    /**
     * Nettoyer les données d'entrée
     */
    public function sanitizeInput($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitizeInput($value);
            }
            return $input;
        }
        
        // Supprimer les espaces en début et fin de chaîne
        $input = trim($input);
        
        // Convertir les caractères spéciaux en entités HTML
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return $input;
    }
    
    /**
     * Valider les données d'un formulaire
     */
    public function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if ($ruleValue && empty($value)) {
                            $errors[$field][] = 'Ce champ est requis.';
                        }
                        break;
                        
                    case 'email':
                        if ($ruleValue && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = 'Veuillez entrer une adresse email valide.';
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleValue) {
                            $errors[$field][] = 'Ce champ doit contenir au moins ' . $ruleValue . ' caractères.';
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleValue) {
                            $errors[$field][] = 'Ce champ ne doit pas dépasser ' . $ruleValue . ' caractères.';
                        }
                        break;
                        
                    case 'matches':
                        if (!empty($value) && $value !== $data[$ruleValue]) {
                            $errors[$field][] = 'Ce champ doit correspondre au champ ' . $ruleValue . '.';
                        }
                        break;
                        
                    case 'numeric':
                        if ($ruleValue && !empty($value) && !is_numeric($value)) {
                            $errors[$field][] = 'Ce champ doit être un nombre.';
                        }
                        break;
                        
                    case 'integer':
                        if ($ruleValue && !empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                            $errors[$field][] = 'Ce champ doit être un nombre entier.';
                        }
                        break;
                        
                    case 'url':
                        if ($ruleValue && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = 'Veuillez entrer une URL valide.';
                        }
                        break;
                        
                    case 'in':
                        if (!empty($value) && !in_array($value, $ruleValue)) {
                            $errors[$field][] = 'La valeur sélectionnée est invalide.';
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Échapper les données pour éviter les attaques XSS
     */
    public function escape($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escape($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * Vérifier le rate limiting
     */
    public function checkRateLimit($key, $limit, $period = 60) {
        // Initialiser le compteur s'il n'existe pas
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 0,
                'reset_time' => time() + $period
            ];
        }
        
        // Réinitialiser le compteur si la période est écoulée
        if ($_SESSION['rate_limits'][$key]['reset_time'] <= time()) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 0,
                'reset_time' => time() + $period
            ];
        }
        
        // Vérifier si la limite est atteinte
        if ($_SESSION['rate_limits'][$key]['count'] >= $limit) {
            return false;
        }
        
        // Incrémenter le compteur
        $_SESSION['rate_limits'][$key]['count']++;
        
        return true;
    }
    
    /**
     * Obtenir le temps restant avant la réinitialisation du rate limiting
     */
    public function getRateLimitResetTime($key) {
        if (!isset($_SESSION['rate_limits'][$key])) {
            return 0;
        }
        
        return max(0, $_SESSION['rate_limits'][$key]['reset_time'] - time());
    }
    
    /**
     * Journaliser une action de sécurité
     */
    public function logSecurityEvent($action, $details = []) {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Préparer les données du log
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'user_id' => $_SESSION['user_id'] ?? 'Guest',
            'action' => $action,
            'details' => $details
        ];
        
        // Écrire dans le fichier de log
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
}
