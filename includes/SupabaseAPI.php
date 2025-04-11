<?php
/**
 * Classe pour interagir avec l'API Supabase
 */
class SupabaseAPI {
    private static $instance = null;
    private $apiUrl;
    private $apiKey;
    private $serviceKey;
    private $headers;
    
    /**
     * Constructeur privé (pattern Singleton)
     */
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        $this->apiUrl = $config['supabase']['api_url'];
        $this->apiKey = $config['supabase']['api_key'];
        $this->serviceKey = $config['supabase']['service_key'];
        
        // Headers par défaut avec la clé anonyme
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey
        ];
    }
    
    /**
     * Obtenir l'instance unique
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Utiliser la clé de service pour les opérations privilégiées
     */
    public function useServiceKey() {
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'apikey: ' . $this->serviceKey,
            'Authorization: Bearer ' . $this->serviceKey
        ];
        return $this;
    }
    
    /**
     * Revenir à la clé anonyme
     */
    public function useAnonKey() {
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey
        ];
        return $this;
    }
    
    /**
     * Effectuer une requête GET
     */
    public function get($endpoint, $params = []) {
        $url = $this->apiUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->request('GET', $url);
    }
    
    /**
     * Effectuer une requête POST
     */
    public function post($endpoint, $data = []) {
        $url = $this->apiUrl . $endpoint;
        return $this->request('POST', $url, $data);
    }
    
    /**
     * Effectuer une requête PATCH
     */
    public function patch($endpoint, $data = [], $params = []) {
        $url = $this->apiUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->request('PATCH', $url, $data);
    }
    
    /**
     * Effectuer une requête PUT
     */
    public function put($endpoint, $data = []) {
        $url = $this->apiUrl . $endpoint;
        return $this->request('PUT', $url, $data);
    }
    
    /**
     * Effectuer une requête DELETE
     */
    public function delete($endpoint, $params = []) {
        $url = $this->apiUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->request('DELETE', $url);
    }
    
    /**
     * Effectuer une requête HTTP
     */
    private function request($method, $url, $data = null) {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->headers,
        ];
        
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($err) {
            throw new Exception("Erreur cURL: " . $err);
        }
        
        $responseData = json_decode($response, true);
        
        // Gérer les erreurs HTTP
        if ($httpCode >= 400) {
            $errorMessage = isset($responseData['error']) ? $responseData['error'] : "Erreur HTTP {$httpCode}";
            throw new Exception("Erreur API Supabase: " . $errorMessage);
        }
        
        return $responseData;
    }
    
    /**
     * S'inscrire avec email et mot de passe
     */
    public function signUp($email, $password) {
        return $this->post('/auth/v1/signup', [
            'email' => $email,
            'password' => $password
        ]);
    }
    
    /**
     * Se connecter avec email et mot de passe
     */
    public function signIn($email, $password) {
        return $this->post('/auth/v1/token?grant_type=password', [
            'email' => $email,
            'password' => $password
        ]);
    }
    
    /**
     * Récupérer l'utilisateur actuel
     */
    public function getUser($token) {
        $headers = $this->headers;
        $headers[] = 'Authorization: Bearer ' . $token;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/auth/v1/user',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            throw new Exception("Erreur cURL: " . $err);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Déconnexion
     */
    public function signOut($token) {
        $headers = $this->headers;
        $headers[] = 'Authorization: Bearer ' . $token;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/auth/v1/logout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            throw new Exception("Erreur cURL: " . $err);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Recherche avancée de profils
     */
    public function searchProfiles($name = null, $bio = null, $minFollowers = null, $maxFollowers = null, $limit = 20, $offset = 0) {
        // Utiliser une requête REST standard puisque la fonction RPC n'existe pas encore
        $endpoint = "/rest/v1/profiles";
        $params = [];
        
        if ($name !== null && !empty($name)) {
            // Utiliser la syntaxe correcte pour la recherche par texte
            $params['username'] = "ilike.%" . str_replace('%', '', $name) . "%";
        }
        
        if ($bio !== null && !empty($bio)) {
            // Utiliser la syntaxe correcte pour la recherche par texte
            $params['bio'] = "ilike.%" . str_replace('%', '', $bio) . "%";
        }
        
        if ($minFollowers !== null && $minFollowers > 0) {
            $params['followers'] = "gte.{$minFollowers}";
        }
        
        if ($maxFollowers !== null && $maxFollowers < PHP_INT_MAX) {
            if (isset($params['followers'])) {
                // Si on a déjà une condition sur followers, il faut la combiner avec la nouvelle
                $minValue = str_replace('gte.', '', $params['followers']);
                $params['followers'] = "and(gte.{$minValue},lte.{$maxFollowers})";
            } else {
                $params['followers'] = "lte.{$maxFollowers}";
            }
        }
        
        // Ajouter des paramètres par défaut pour éviter les erreurs
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        $params['order'] = 'followers.desc';
        
        try {
            return $this->get($endpoint, $params);
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide plutôt que de lancer une exception
            error_log("Erreur lors de la recherche de profils: " . $e->getMessage());
            return [];
        }
    }
}
