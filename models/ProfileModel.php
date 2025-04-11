<?php
require_once __DIR__ . '/../includes/Model.php';
require_once __DIR__ . '/../includes/SupabaseAPI.php';

/**
 * Modèle pour la gestion des profils
 */
class ProfileModel extends Model {
    protected $table = 'profiles';
    protected $supabaseAPI;
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        
        // Initialiser l'API Supabase si nécessaire
        if ($this->isSupabase) {
            $this->supabaseAPI = SupabaseAPI::getInstance();
        }
    }
    
    /**
     * Rechercher des profils selon les critères
     */
    public function search($nameKeywords = [], $bioKeywords = [], $minFollowers = 0, $maxFollowers = PHP_INT_MAX) {
        // Si nous utilisons Supabase, utiliser l'API REST
        if ($this->isSupabase) {
            // Convertir les tableaux de mots-clés en chaînes
            $name = is_array($nameKeywords) ? implode(' ', $nameKeywords) : $nameKeywords;
            $bio = is_array($bioKeywords) ? implode(' ', $bioKeywords) : $bioKeywords;
            
            return $this->supabaseAPI->searchProfiles($name, $bio, $minFollowers, $maxFollowers);
        }
        
        // Sinon, utiliser MySQL
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        // Recherche par mots-clés dans le nom
        if (!empty($nameKeywords)) {
            $nameClauses = [];
            foreach ($nameKeywords as $index => $keyword) {
                $paramName = ":name_keyword_{$index}";
                $nameClauses[] = "name LIKE {$paramName}";
                $params[$paramName] = "%{$keyword}%";
            }
            if (!empty($nameClauses)) {
                $sql .= " AND (" . implode(" OR ", $nameClauses) . ")";
            }
        }
        
        // Recherche par mots-clés dans la bio
        if (!empty($bioKeywords)) {
            $bioClauses = [];
            foreach ($bioKeywords as $index => $keyword) {
                $paramName = ":bio_keyword_{$index}";
                $bioClauses[] = "bio LIKE {$paramName}";
                $params[$paramName] = "%{$keyword}%";
            }
            if (!empty($bioClauses)) {
                $sql .= " AND (" . implode(" OR ", $bioClauses) . ")";
            }
        }
        
        // Filtrer par nombre de followers
        $sql .= " AND followers >= :min_followers AND followers <= :max_followers";
        $params[':min_followers'] = $minFollowers;
        $params[':max_followers'] = $maxFollowers;
        
        // Ordonner par pertinence (nombre de followers par défaut)
        $sql .= " ORDER BY followers DESC";
        
        // Exécuter la requête
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir les profils populaires
     */
    public function getPopular($limit = 10) {
        // Si nous utilisons Supabase, utiliser l'API REST
        if ($this->isSupabase) {
            $params = [
                'order' => 'followers.desc',
                'limit' => $limit
            ];
            
            return $this->supabaseAPI->get("/rest/v1/{$this->table}", $params);
        }
        
        // Sinon, utiliser MySQL
        $sql = "SELECT * FROM {$this->table} ORDER BY followers DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir les profils récemment ajoutés
     */
    public function getRecent($limit = 10) {
        // Si nous utilisons Supabase, utiliser l'API REST
        if ($this->isSupabase) {
            $params = [
                'order' => 'created_at.desc',
                'limit' => $limit
            ];
            
            return $this->supabaseAPI->get("/rest/v1/{$this->table}", $params);
        }
        
        // Sinon, utiliser MySQL
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
