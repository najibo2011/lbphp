<?php
/**
 * Classe de base pour tous les modèles
 */
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $isSupabase = false;
    protected $dbInstance;
    
    public function __construct() {
        $this->dbInstance = Database::getInstance();
        $this->isSupabase = $this->dbInstance->isSupabase();
        
        // Si nous n'utilisons pas Supabase, nous pouvons obtenir la connexion PDO
        if (!$this->isSupabase) {
            $this->db = $this->dbInstance->getConnection();
        }
    }
    
    /**
     * Récupérer tous les enregistrements
     */
    public function getAll($limit = null, $offset = 0, $orderBy = null, $order = 'ASC') {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un enregistrement par son ID
     */
    public function getById($id) {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer des enregistrements par condition
     */
    public function getWhere($conditions = [], $limit = null, $offset = 0, $orderBy = null, $order = 'ASC') {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $sql .= " AND {$column} = :{$column}";
            $params[":{$column}"] = $value;
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Créer un nouvel enregistrement
     */
    public function create($data) {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Mettre à jour un enregistrement
     */
    public function update($id, $data) {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $setParts = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = :{$column}";
        }
        
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Supprimer un enregistrement
     */
    public function delete($id) {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Compter le nombre d'enregistrements
     */
    public function count($conditions = []) {
        if ($this->isSupabase) {
            throw new Exception("Méthode non disponible pour Supabase. Utilisez la classe SupabaseAPI.");
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $sql .= " AND {$column} = :{$column}";
            $params[":{$column}"] = $value;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $result['count'];
    }
}
