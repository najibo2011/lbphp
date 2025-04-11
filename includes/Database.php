<?php
/**
 * Classe de gestion de la base de données
 */
class Database {
    private static $instance = null;
    private $connection = null;
    private $config = null;
    private $connectionType = null;
    private $useSupabase = false;
    
    /**
     * Constructeur privé (pattern Singleton)
     */
    private function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->connectionType = $this->config['connection_type'];
        $this->useSupabase = $this->connectionType === 'supabase';
        
        if ($this->connectionType === 'mysql') {
            $this->connectToMySQL();
        } 
        // Pour Supabase, nous n'établissons plus de connexion directe
        // Nous utilisons l'API REST via la classe SupabaseAPI
    }
    
    /**
     * Obtenir l'instance unique de la base de données
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Établir une connexion à MySQL
     */
    private function connectToMySQL() {
        $config = $this->config['mysql'];
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']};port={$config['port']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à MySQL: " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir la connexion PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Vérifier si nous utilisons Supabase
     */
    public function isSupabase() {
        return $this->useSupabase;
    }
    
    /**
     * Obtenir la configuration de Supabase
     */
    public function getSupabaseConfig() {
        return $this->config['supabase'];
    }
    
    /**
     * Obtenir l'URL de l'API Supabase
     */
    public function getSupabaseUrl() {
        return $this->config['supabase']['api_url'];
    }
    
    /**
     * Obtenir la clé API de Supabase
     */
    public function getSupabaseKey() {
        return $this->config['supabase']['api_key'];
    }
    
    /**
     * Obtenir la clé de service de Supabase
     */
    public function getSupabaseServiceKey() {
        return $this->config['supabase']['service_key'];
    }
    
    /**
     * Exécuter une requête préparée
     */
    public function execute($sql, $params = []) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erreur d'exécution de la requête: " . $e->getMessage());
        }
    }
    
    /**
     * Récupérer une seule ligne
     */
    public function fetch($sql, $params = []) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer toutes les lignes
     */
    public function fetchAll($sql, $params = []) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une valeur unique
     */
    public function fetchColumn($sql, $params = [], $column = 0) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn($column);
    }
    
    /**
     * Insérer des données et retourner l'ID
     */
    public function insert($table, $data) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->execute($sql, array_values($data));
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Mettre à jour des données
     */
    public function update($table, $data, $where, $whereParams = []) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $setClauses = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $setClause = implode(', ', $setClauses);
        $params = array_merge($params, $whereParams);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Supprimer des données
     */
    public function delete($table, $where, $params = []) {
        if ($this->isSupabase()) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Obtenir la structure d'une table Supabase
     */
    public function getSupabaseTableStructure($tableName) {
        if (!$this->useSupabase) {
            return null;
        }
        
        $url = $this->getSupabaseUrl() . '/rest/v1/' . $tableName;
        $headers = [
            'apikey: ' . $this->getSupabaseKey(),
            'Authorization: Bearer ' . $this->getSupabaseKey(),
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Obtenir l'ID utilisateur au format approprié pour la base de données
     */
    public function formatUserId($userId) {
        if ($this->useSupabase) {
            // Pour Supabase, nous devons utiliser un UUID
            // Si l'ID utilisateur est un entier, nous le convertissons en UUID basé sur cet entier
            if (is_numeric($userId)) {
                // Créer un UUID déterministe basé sur l'ID utilisateur
                $uuid = sprintf(
                    '%08x-%04x-%04x-%04x-%012x',
                    $userId,
                    $userId % 0xffff,
                    ($userId + 1) % 0xffff,
                    ($userId + 2) % 0xffff,
                    ($userId + 3) % 0xffffffffffff
                );
                return $uuid;
            }
            return $userId;
        }
        
        // Pour MySQL, nous utilisons simplement l'ID utilisateur tel quel
        return $userId;
    }
}
