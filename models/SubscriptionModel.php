<?php
require_once __DIR__ . '/../includes/Model.php';

/**
 * Modèle pour la gestion des abonnements
 */
class SubscriptionModel extends Model {
    protected $table = 'subscriptions';
    
    // Plans d'abonnement et leurs limites
    private $plans = [
        'free' => [
            'name' => 'Gratuit',
            'price' => 0,
            'search_limit' => 30,
            'list_limit' => 3,
            'profile_per_list_limit' => 100,
            'features' => [
                'search' => true,
                'lists' => true,
                'followup' => false,
                'crm' => false,
                'export' => false
            ]
        ],
        'basic' => [
            'name' => 'Basique',
            'price' => 19.99,
            'search_limit' => 100,
            'list_limit' => 10,
            'profile_per_list_limit' => 500,
            'features' => [
                'search' => true,
                'lists' => true,
                'followup' => true,
                'crm' => false,
                'export' => true
            ]
        ],
        'pro' => [
            'name' => 'Professionnel',
            'price' => 49.99,
            'search_limit' => 500,
            'list_limit' => 50,
            'profile_per_list_limit' => 2000,
            'features' => [
                'search' => true,
                'lists' => true,
                'followup' => true,
                'crm' => true,
                'export' => true
            ]
        ],
        'enterprise' => [
            'name' => 'Entreprise',
            'price' => 99.99,
            'search_limit' => 2000,
            'list_limit' => 200,
            'profile_per_list_limit' => 10000,
            'features' => [
                'search' => true,
                'lists' => true,
                'followup' => true,
                'crm' => true,
                'export' => true
            ]
        ]
    ];
    
    /**
     * Obtenir les détails d'un plan d'abonnement
     */
    public function getPlanDetails($planId) {
        if (isset($this->plans[$planId])) {
            return $this->plans[$planId];
        }
        
        // Plan par défaut (gratuit)
        return $this->plans['free'];
    }
    
    /**
     * Vérifier si un utilisateur a accès à une fonctionnalité
     */
    public function hasFeatureAccess($userId, $feature) {
        $user = $this->db->query("SELECT subscription_plan FROM users WHERE id = ?", [$userId])->fetch();
        
        if (!$user) {
            return false;
        }
        
        $planId = $user['subscription_plan'];
        $plan = $this->getPlanDetails($planId);
        
        return isset($plan['features'][$feature]) && $plan['features'][$feature];
    }
    
    /**
     * Vérifier si un utilisateur a atteint sa limite pour une ressource donnée
     */
    public function hasReachedLimit($userId, $limitType) {
        $user = $this->db->query("SELECT subscription_plan FROM users WHERE id = ?", [$userId])->fetch();
        
        if (!$user) {
            return true; // Par sécurité, considérer que la limite est atteinte
        }
        
        $planId = $user['subscription_plan'];
        $plan = $this->getPlanDetails($planId);
        
        // Vérifier le type de limite
        switch ($limitType) {
            case 'search':
                $usedSearches = $this->getUsedSearches($userId);
                return $usedSearches >= $plan['search_limit'];
                
            case 'list':
                $listCount = $this->getListCount($userId);
                return $listCount >= $plan['list_limit'];
                
            case 'profile_per_list':
                $listId = $_GET['list_id'] ?? null;
                if (!$listId) {
                    return false;
                }
                
                $profileCount = $this->getProfileCountInList($listId);
                return $profileCount >= $plan['profile_per_list_limit'];
                
            default:
                return false;
        }
    }
    
    /**
     * Obtenir le nombre de recherches utilisées par un utilisateur
     */
    private function getUsedSearches($userId) {
        $today = date('Y-m-d');
        
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM search_history WHERE user_id = ? AND DATE(created_at) = ?",
            [$userId, $today]
        )->fetch();
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Obtenir le nombre de listes créées par un utilisateur
     */
    private function getListCount($userId) {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM lists WHERE user_id = ?",
            [$userId]
        )->fetch();
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Obtenir le nombre de profils dans une liste
     */
    private function getProfileCountInList($listId) {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM list_profiles WHERE list_id = ?",
            [$listId]
        )->fetch();
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Incrémenter le compteur d'utilisation pour une ressource
     */
    public function incrementUsage($userId, $resourceType) {
        switch ($resourceType) {
            case 'search':
                $this->recordSearch($userId);
                break;
                
            // Autres types de ressources à implémenter selon les besoins
        }
    }
    
    /**
     * Enregistrer une recherche dans l'historique
     */
    private function recordSearch($userId) {
        $this->db->query(
            "INSERT INTO search_history (user_id, created_at) VALUES (?, NOW())",
            [$userId]
        );
    }
    
    /**
     * Créer un nouvel abonnement
     */
    public function createSubscription($data) {
        // Vérifier que les champs requis sont présents
        $requiredFields = ['user_id', 'plan_id', 'status', 'stripe_subscription_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        
        // Ajouter les dates
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
    }
    
    /**
     * Mettre à jour un abonnement
     */
    public function updateSubscription($id, $data) {
        // Ajouter la date de mise à jour
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::update($id, $data);
    }
    
    /**
     * Obtenir l'abonnement actif d'un utilisateur
     */
    public function getActiveSubscription($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Vérifier si un utilisateur a un abonnement actif
     */
    public function hasActiveSubscription($userId) {
        return $this->getActiveSubscription($userId) !== false;
    }
    
    /**
     * Annuler un abonnement
     */
    public function cancelSubscription($subscriptionId) {
        return $this->updateSubscription($subscriptionId, [
            'status' => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Obtenir tous les plans disponibles
     */
    public function getAllPlans() {
        return $this->plans;
    }
    
    /**
     * Récupérer un abonnement par son ID Stripe
     */
    public function findByStripeSubscriptionId($stripeSubscriptionId) {
        $sql = "SELECT * FROM {$this->table} WHERE stripe_subscription_id = :stripe_subscription_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':stripe_subscription_id', $stripeSubscriptionId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
