<?php
require_once __DIR__ . '/../includes/Model.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';
require_once __DIR__ . '/../includes/Database.php';

/**
 * Modèle de base pour les interactions avec Supabase
 */
class SupabaseModel extends Model {
    protected $supabase;
    protected $dbInstance;
    
    /**
     * Constructeur
     */
    public function __construct($table = '') {
        parent::__construct();
        $this->table = $table;
        
        // Obtenir l'instance de la base de données
        $this->dbInstance = Database::getInstance();
        
        // Initialiser le client Supabase si nous utilisons Supabase
        if ($this->dbInstance->isSupabase()) {
            $this->supabase = new SupabaseClient();
        }
    }
    
    /**
     * Récupérer des données avec filtres via l'API REST de Supabase
     */
    public function getWithFilters($filters = [], $select = '*', $order = null, $limit = null) {
        if (!$this->dbInstance->isSupabase()) {
            // Utiliser la méthode standard pour MySQL
            return $this->getWhere($filters, $limit);
        }
        
        // Construire les paramètres de requête pour l'API Supabase
        $params = [];
        
        // Sélection des colonnes
        if ($select !== '*') {
            $params['select'] = $select;
        }
        
        // Filtres
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                // Opérateur spécial (eq, neq, gt, lt, etc.)
                $operator = $value[0];
                $filterValue = $value[1];
                $params[$column] = "{$operator}.{$filterValue}";
            } else {
                // Égalité simple
                $params[$column] = "eq.{$value}";
            }
        }
        
        // Tri
        if ($order) {
            $params['order'] = $order;
        }
        
        // Limite
        if ($limit) {
            $params['limit'] = $limit;
        }
        
        // Effectuer la requête via l'API REST
        $endpoint = "/rest/v1/{$this->table}";
        $result = $this->supabase->get($endpoint, $params);
        
        return $result;
    }
    
    /**
     * Insérer des données via l'API REST de Supabase
     */
    public function insertViaApi($data) {
        if (!$this->dbInstance->isSupabase()) {
            // Utiliser la méthode standard pour MySQL
            return $this->create($data);
        }
        
        // Effectuer la requête via l'API REST
        $endpoint = "/rest/v1/{$this->table}";
        $result = $this->supabase->post($endpoint, $data);
        
        return $result;
    }
    
    /**
     * Mettre à jour des données via l'API REST de Supabase
     */
    public function updateViaApi($id, $data) {
        if (!$this->dbInstance->isSupabase()) {
            // Utiliser la méthode standard pour MySQL
            return $this->update($id, $data);
        }
        
        // Effectuer la requête via l'API REST
        $endpoint = "/rest/v1/{$this->table}";
        $params = ['id' => "eq.{$id}"];
        
        // Supabase utilise PATCH pour les mises à jour partielles
        $result = $this->supabase->patch($endpoint, $data, $params);
        
        return $result;
    }
    
    /**
     * Supprimer des données via l'API REST de Supabase
     */
    public function deleteViaApi($id) {
        if (!$this->dbInstance->isSupabase()) {
            // Utiliser la méthode standard pour MySQL
            return $this->delete($id);
        }
        
        // Effectuer la requête via l'API REST
        $endpoint = "/rest/v1/{$this->table}";
        $params = ['id' => "eq.{$id}"];
        
        $result = $this->supabase->delete($endpoint, $params);
        
        return $result;
    }
    
    /**
     * Recherche textuelle (spécifique à PostgreSQL)
     */
    public function textSearch($columns, $query, $limit = 20) {
        if (!$this->dbInstance->isSupabase()) {
            // Fallback pour MySQL (recherche LIKE simple)
            $sql = "SELECT * FROM {$this->table} WHERE ";
            $conditions = [];
            $params = [];
            
            foreach ($columns as $column) {
                $conditions[] = "{$column} LIKE ?";
                $params[] = "%{$query}%";
            }
            
            $sql .= '(' . implode(' OR ', $conditions) . ')';
            $sql .= " LIMIT {$limit}";
            
            return $this->dbInstance->fetchAll($sql, $params);
        }
        
        // Utiliser la recherche full-text de PostgreSQL
        $columnsStr = implode(', ', $columns);
        $endpoint = "/rest/v1/rpc/search_{$this->table}";
        
        $data = [
            'query_text' => $query,
            'limit_val' => $limit
        ];
        
        // Cette requête suppose que vous avez créé une fonction RPC dans Supabase
        // pour effectuer la recherche full-text
        $result = $this->supabase->post($endpoint, $data);
        
        return $result;
    }
}
