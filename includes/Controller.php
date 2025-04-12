<?php
/**
 * Classe de base pour tous les contrôleurs
 */
class Controller {
    protected $config;
    
    public function __construct() {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->config = require_once __DIR__ . '/../config/config.php';
    }
    
    /**
     * Afficher une vue
     */
    protected function render($view, $data = []) {
        // Ajouter le contrôleur aux données
        $data['controller'] = $this;
        
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);
        
        // Chemin vers le fichier de vue
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        // Vérifier si le fichier existe
        if (!file_exists($viewPath)) {
            die("Vue non trouvée: {$view}");
        }
        
        // Vérifier si nous utilisons un layout
        $useLayout = !isset($data['use_layout']) || $data['use_layout'] !== false;
        
        if ($useLayout) {
            // Démarrer la mise en tampon de sortie pour la vue
            ob_start();
            
            // Inclure le fichier de vue
            include $viewPath;
            
            // Récupérer le contenu du tampon
            $viewContent = ob_get_clean();
            
            // Définir le chemin de la vue pour le layout
            $data['viewPath'] = $viewPath;
            
            // Extraire les données à nouveau pour le layout
            extract($data);
            
            // Démarrer la mise en tampon pour le layout
            ob_start();
            
            // Inclure le layout
            include __DIR__ . '/../views/layouts/main.php';
            
            // Récupérer le contenu du layout
            $content = ob_get_clean();
        } else {
            // Démarrer la mise en tampon de sortie
            ob_start();
            
            // Inclure le fichier de vue directement
            include $viewPath;
            
            // Récupérer le contenu du tampon
            $content = ob_get_clean();
        }
        
        // Afficher le contenu
        echo $content;
    }
    
    /**
     * Rediriger vers une URL
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Retourner une réponse JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Vérifier si la requête est une requête AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    protected function isLoggedIn() {
        // Authentification temporairement désactivée pour le développement
        return true;
        // Décommentez la ligne ci-dessous pour réactiver l'authentification
        // return isset($_SESSION['user_id']);
    }
    
    /**
     * Définir un message flash pour l'utilisateur
     */
    protected function setFlash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Récupérer les messages flash et les effacer
     */
    public function getFlash() {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Exiger une connexion utilisateur
     */
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect($this->config['app_url'] . '/login.php');
        }
    }
    
    /**
     * Obtenir l'utilisateur actuellement connecté
     */
    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $userModel = new UserModel();
            return $userModel->getById($_SESSION['user_id']);
        }
        return null;
    }
    
    /**
     * Générer un jeton CSRF
     */
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
