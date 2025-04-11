<?php
require_once __DIR__ . '/../includes/Model.php';
require_once __DIR__ . '/../includes/SupabaseAPI.php';
require_once __DIR__ . '/../includes/Database.php';

/**
 * Modèle pour la recherche avancée de profils
 */
class ProfileSearchModel extends Model {
    protected $supabaseAPI;
    protected $dbInstance;
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'profiles';
        
        // Obtenir l'instance de la base de données
        $this->dbInstance = Database::getInstance();
        
        // Initialiser l'API Supabase
        $this->supabaseAPI = SupabaseAPI::getInstance();
    }
    
    /**
     * Recherche avancée de profils
     * 
     * @param string|null $name Recherche par nom
     * @param string|null $bio Recherche dans la bio
     * @param int|null $minFollowers Nombre minimum de followers
     * @param int|null $maxFollowers Nombre maximum de followers
     * @param int $limit Limite de résultats
     * @param int $offset Décalage pour la pagination
     * @return array Résultats de la recherche
     */
    public function searchProfiles($name = null, $bio = null, $minFollowers = null, $maxFollowers = null, $limit = 20, $offset = 0) {
        $config = require __DIR__ . '/../config/database.php';
        
        // Si nous utilisons Supabase via l'API
        if ($config['connection_type'] === 'supabase') {
            return $this->supabaseAPI->searchProfiles($name, $bio, $minFollowers, $maxFollowers, $limit, $offset);
        } 
        
        // Fallback pour MySQL
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($name !== null && !empty($name)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%{$name}%";
        }
        
        if ($bio !== null && !empty($bio)) {
            $sql .= " AND bio LIKE ?";
            $params[] = "%{$bio}%";
        }
        
        if ($minFollowers !== null && $minFollowers > 0) {
            $sql .= " AND followers >= ?";
            $params[] = $minFollowers;
        }
        
        if ($maxFollowers !== null && $maxFollowers < PHP_INT_MAX) {
            $sql .= " AND followers <= ?";
            $params[] = $maxFollowers;
        }
        
        $sql .= " ORDER BY followers DESC LIMIT {$limit} OFFSET {$offset}";
        
        return $this->dbInstance->fetchAll($sql, $params);
    }
    
    /**
     * Compter le nombre total de résultats pour une recherche
     * 
     * @param string|null $name Recherche par nom
     * @param string|null $bio Recherche dans la bio
     * @param int|null $minFollowers Nombre minimum de followers
     * @param int|null $maxFollowers Nombre maximum de followers
     * @return int Nombre total de résultats
     */
    public function countSearchResults($name = null, $bio = null, $minFollowers = null, $maxFollowers = null) {
        $config = require __DIR__ . '/../config/database.php';
        
        // Si nous utilisons Supabase via l'API
        if ($config['connection_type'] === 'supabase') {
            // Pour Supabase, nous devons faire une requête de comptage spéciale
            $params = [];
            
            if ($name !== null && !empty($name)) {
                $params['username'] = "ilike.%" . str_replace('%', '', $name) . "%";
            }
            
            if ($bio !== null && !empty($bio)) {
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
            
            // Ajouter le paramètre de comptage
            $params['select'] = 'id';
            
            try {
                $results = $this->supabaseAPI->get('/rest/v1/profiles', $params);
                return count($results);
            } catch (Exception $e) {
                error_log("Erreur lors du comptage des résultats: " . $e->getMessage());
                return 0;
            }
        } 
        
        // Fallback pour MySQL
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($name !== null && !empty($name)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%{$name}%";
        }
        
        if ($bio !== null && !empty($bio)) {
            $sql .= " AND bio LIKE ?";
            $params[] = "%{$bio}%";
        }
        
        if ($minFollowers !== null && $minFollowers > 0) {
            $sql .= " AND followers >= ?";
            $params[] = $minFollowers;
        }
        
        if ($maxFollowers !== null && $maxFollowers < PHP_INT_MAX) {
            $sql .= " AND followers <= ?";
            $params[] = $maxFollowers;
        }
        
        return (int) $this->dbInstance->fetchColumn($sql, $params);
    }
    
    /**
     * Compter le nombre de recherches effectuées par un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de recherches
     */
    public function countSearches($userId) {
        $config = require __DIR__ . '/../config/database.php';
        
        if ($config['connection_type'] === 'supabase') {
            $params = [
                'user_id' => "eq.{$userId}",
                'select' => 'count'
            ];
            
            $result = $this->supabaseAPI->get('/rest/v1/user_searches', $params);
            return count($result);
        }
        
        $sql = "SELECT COUNT(*) FROM user_searches WHERE user_id = ?";
        return (int) $this->dbInstance->fetchColumn($sql, [$userId]);
    }
    
    /**
     * Enregistrer une recherche
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $searchParams Paramètres de recherche
     * @return int ID de la recherche
     */
    public function saveSearch($userId, $searchParams) {
        $config = require __DIR__ . '/../config/database.php';
        
        if ($config['connection_type'] === 'supabase') {
            $data = [
                'user_id' => $userId,
                'search_params' => json_encode($searchParams),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->supabaseAPI->post('/rest/v1/user_searches', $data);
            return $result['id'] ?? null;
        }
        
        // Fallback pour MySQL
        $data = [
            'user_id' => $userId,
            'search_params' => json_encode($searchParams),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->dbInstance->insert('user_searches', $data);
    }
}
