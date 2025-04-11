<?php
/**
 * Classe client pour interagir avec l'API Supabase
 */
class SupabaseClient {
    private $apiUrl;
    private $apiKey;
    private $headers;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Utiliser les constantes définies dans le fichier de configuration
        $this->apiUrl = SUPABASE_URL;
        $this->apiKey = SUPABASE_ANON_KEY;
        
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey
        ];
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
     * Effectuer une requête PATCH (pour les mises à jour partielles)
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
        
        curl_close($curl);
        
        if ($err) {
            throw new Exception("Erreur cURL: " . $err);
        }
        
        return json_decode($response, true);
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
}
