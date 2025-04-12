<?php
require_once __DIR__ . '/../includes/Model.php';

/**
 * Modèle pour la gestion du suivi des prospects
 */
class FollowupModel extends Model {
    protected $table = 'followups';
    protected $interactionsTable = 'followup_interactions';
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Récupérer les suivis pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $page Page actuelle
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Filtres à appliquer
     * @param array $sort Options de tri
     * @return array Suivis de l'utilisateur
     */
    public function getByUserId($userId, $page = 1, $limit = 10, $filters = [], $sort = ['sort_by' => 'last_interaction', 'sort_order' => 'desc']) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT f.*, p.username, p.description, l.name as list_name 
                FROM {$this->table} f
                JOIN profiles p ON f.profile_id = p.id
                JOIN lists l ON f.list_id = l.id
                WHERE f.user_id = :user_id";
        
        $params = [':user_id' => $userId];
        
        // Ajouter les filtres
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['list_id'])) {
            $sql .= " AND f.list_id = :list_id";
            $params[':list_id'] = $filters['list_id'];
        }
        
        // Ajouter le tri
        $sortField = $sort['sort_by'];
        $sortOrder = $sort['sort_order'];
        
        // Mapper les champs de tri à leurs équivalents SQL
        $sortFieldMap = [
            'last_interaction' => 'f.last_interaction',
            'username' => 'p.username',
            'status' => 'f.status',
            'created_at' => 'f.created_at'
        ];
        
        $sortField = isset($sortFieldMap[$sortField]) ? $sortFieldMap[$sortField] : 'f.last_interaction';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY {$sortField} {$sortOrder}";
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compte le nombre de suivis pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $filters Filtres à appliquer
     * @return int Nombre de suivis
     */
    public function countByUserId($userId, $filters = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table} f
                WHERE f.user_id = :user_id";
        
        $params = [':user_id' => $userId];
        
        // Ajouter les filtres
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['list_id'])) {
            $sql .= " AND f.list_id = :list_id";
            $params[':list_id'] = $filters['list_id'];
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Récupère tous les suivis d'un utilisateur sans pagination
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Suivis de l'utilisateur
     */
    public function getAllByUserId($userId) {
        $sql = "SELECT f.*, p.username, p.description, l.name as list_name 
                FROM {$this->table} f
                JOIN profiles p ON f.profile_id = p.id
                JOIN lists l ON f.list_id = l.id
                WHERE f.user_id = :user_id
                ORDER BY f.last_interaction DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un suivi par son ID
     */
    public function getById($id) {
        $sql = "SELECT f.*, p.username, p.description, l.name as list_name 
                FROM {$this->table} f
                JOIN profiles p ON f.profile_id = p.id
                JOIN lists l ON f.list_id = l.id
                WHERE f.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer un suivi par l'ID du profil et l'ID de l'utilisateur
     */
    public function getByProfileAndUser($profileId, $userId) {
        $sql = "SELECT * FROM {$this->table} WHERE profile_id = :profile_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':profile_id', $profileId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Vérifie si un profil est déjà suivi par un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $profileId ID du profil
     * @return bool True si le profil est déjà suivi, false sinon
     */
    public function exists($userId, $profileId) {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = :user_id AND profile_id = :profile_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':profile_id', $profileId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Créer un nouveau suivi
     */
    public function create($data) {
        // Ajouter la date de création
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return parent::create($data);
    }
    
    /**
     * Mettre à jour un suivi
     */
    public function update($id, $data) {
        // Ajouter la date de mise à jour
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return parent::update($id, $data);
    }
    
    /**
     * Mettre à jour le statut d'un suivi
     */
    public function updateStatus($id, $status) {
        return $this->update($id, [
            'status' => $status,
            'last_status_change' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Ajouter une interaction à un suivi
     */
    public function addInteraction($data) {
        if (is_array($data)) {
            $sql = "INSERT INTO {$this->interactionsTable} (followup_id, type, notes, interaction_date, created_at) 
                    VALUES (:followup_id, :type, :notes, :interaction_date, :created_at)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':followup_id', $data['followup_id'], PDO::PARAM_INT);
            $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
            $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);
            $stmt->bindParam(':interaction_date', $data['interaction_date'], PDO::PARAM_STR);
            $stmt->bindParam(':created_at', $data['created_at'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // Mettre à jour la date de dernière interaction du suivi
                $this->update($data['followup_id'], [
                    'last_interaction' => $data['created_at']
                ]);
                
                return $this->db->lastInsertId();
            }
        } else {
            // Support pour l'ancienne signature de la méthode
            $followupId = func_get_arg(0);
            $type = func_get_arg(1);
            $date = func_get_arg(2);
            $notes = func_num_args() > 3 ? func_get_arg(3) : '';
            
            $sql = "INSERT INTO {$this->interactionsTable} (followup_id, type, interaction_date, notes, created_at) 
                    VALUES (:followup_id, :type, :date, :notes, :created_at)";
            
            $stmt = $this->db->prepare($sql);
            $createdAt = date('Y-m-d H:i:s');
            
            $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':created_at', $createdAt);
            
            if ($stmt->execute()) {
                // Mettre à jour la date de dernière interaction du suivi
                $this->update($followupId, [
                    'last_interaction' => $createdAt
                ]);
                
                return $this->db->lastInsertId();
            }
        }
        
        return false;
    }
    
    /**
     * Récupérer les interactions pour un suivi
     */
    public function getInteractions($followupId, $limit = 10) {
        $sql = "SELECT * FROM {$this->interactionsTable} 
                WHERE followup_id = :followup_id 
                ORDER BY interaction_date DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les interactions pour un suivi dans une plage de dates
     */
    public function getInteractionsByDateRange($followupId, $startDate, $endDate) {
        $sql = "SELECT * FROM {$this->interactionsTable} 
                WHERE followup_id = :followup_id 
                AND interaction_date BETWEEN :start_date AND :end_date
                ORDER BY interaction_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère une interaction par son ID
     * 
     * @param int $id ID de l'interaction
     * @return array|false Interaction ou false si non trouvée
     */
    public function getInteractionById($id) {
        $sql = "SELECT * FROM {$this->interactionsTable} WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les interactions d'un suivi pour une date donnée
     * 
     * @param int $followupId ID du suivi
     * @param string $date Date au format YYYY-MM-DD
     * @return array Interactions
     */
    public function getInteractionsByDate($followupId, $date) {
        $sql = "SELECT * FROM {$this->interactionsTable} 
                WHERE followup_id = :followup_id 
                AND DATE(interaction_date) = :date
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les interactions d'un utilisateur pour une période donnée
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $dates Tableau de dates
     * @return array Interactions organisées par suivi et par date
     */
    public function getInteractionsByUserId($userId, $dates) {
        $interactions = [];
        
        // Construire la liste des dates pour la requête SQL
        $dateValues = [];
        foreach ($dates as $date) {
            $dateValues[] = $date['value'];
        }
        
        $dateList = "'" . implode("','", $dateValues) . "'";
        
        $sql = "SELECT i.*, f.id as followup_id 
                FROM {$this->interactionsTable} i
                JOIN {$this->table} f ON i.followup_id = f.id
                WHERE f.user_id = :user_id 
                AND DATE(i.interaction_date) IN ({$dateList})
                ORDER BY i.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organiser les résultats par suivi et par date
        foreach ($results as $interaction) {
            $followupId = $interaction['followup_id'];
            $date = substr($interaction['interaction_date'], 0, 10); // Format YYYY-MM-DD
            
            if (!isset($interactions[$followupId])) {
                $interactions[$followupId] = [];
            }
            
            if (!isset($interactions[$followupId][$date])) {
                $interactions[$followupId][$date] = [];
            }
            
            $interactions[$followupId][$date][] = $interaction;
        }
        
        return $interactions;
    }
    
    /**
     * Récupère les interactions d'un suivi
     * 
     * @param int $followupId ID du suivi
     * @return array Interactions du suivi
     */
    public function getInteractionsByFollowupId($followupId) {
        $sql = "SELECT * FROM {$this->interactionsTable} 
                WHERE followup_id = :followup_id
                ORDER BY interaction_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Supprimer une interaction
     */
    public function deleteInteraction($interactionId) {
        $sql = "DELETE FROM {$this->interactionsTable} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $interactionId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Supprime toutes les interactions d'un suivi
     * 
     * @param int $followupId ID du suivi
     * @return bool True si la suppression a réussi, false sinon
     */
    private function deleteInteractionsByFollowupId($followupId) {
        $sql = "DELETE FROM {$this->interactionsTable} WHERE followup_id = :followup_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Récupère la dernière interaction d'un suivi
     * 
     * @param int $followupId ID du suivi
     * @return array|false Dernière interaction ou false si aucune
     */
    public function getLastInteraction($followupId) {
        $sql = "SELECT * FROM {$this->interactionsTable} 
                WHERE followup_id = :followup_id 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':followup_id', $followupId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Supprimer un suivi et toutes ses interactions
     */
    public function delete($id) {
        // Supprimer d'abord toutes les interactions associées
        $this->deleteInteractionsByFollowupId($id);
        
        // Puis supprimer le suivi
        return parent::delete($id);
    }
    
    /**
     * Compte le nombre d'interactions pour un utilisateur sur un mois
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $month Mois au format YYYY-MM (par défaut le mois en cours)
     * @return int Nombre d'interactions
     */
    public function countInteractionsByMonth($userId, $month = null) {
        if ($month === null) {
            $month = date('Y-m');
        }
        
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT COUNT(*) FROM {$this->interactionsTable} i
                JOIN {$this->table} f ON i.followup_id = f.id
                WHERE f.user_id = :user_id
                AND i.interaction_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Compte le nombre de suivis pour un utilisateur avec un statut spécifique
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $status Statut à compter
     * @return int Nombre de suivis
     */
    public function countByUserIdAndStatus($userId, $status) {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE user_id = :user_id AND status = :status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Récupère la distribution des statuts pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Distribution des statuts
     */
    public function getStatusDistribution($userId) {
        $sql = "SELECT status, COUNT(*) as count
                FROM {$this->table}
                WHERE user_id = :user_id
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $distribution = [];
        $total = 0;
        
        // Calculer le total
        foreach ($results as $row) {
            $total += $row['count'];
        }
        
        // Calculer les pourcentages
        foreach ($results as $row) {
            $percentage = $total > 0 ? ($row['count'] / $total) * 100 : 0;
            $distribution[$row['status']] = [
                'count' => (int) $row['count'],
                'percentage' => round($percentage, 1)
            ];
        }
        
        return $distribution;
    }
    
    /**
     * Récupère l'activité récente pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $days Nombre de jours à récupérer
     * @return array Activité récente
     */
    public function getRecentActivity($userId, $days = 7) {
        $activity = [];
        $maxCount = 0;
        
        // Générer les dates
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $displayDate = date('d/m', strtotime("-$i days"));
            
            $sql = "SELECT COUNT(*) FROM {$this->interactionsTable} i
                    JOIN {$this->table} f ON i.followup_id = f.id
                    WHERE f.user_id = :user_id
                    AND i.interaction_date = :date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':date', $date, PDO::PARAM_STR);
            $stmt->execute();
            
            $count = (int) $stmt->fetchColumn();
            $activity[$displayDate] = [
                'count' => $count,
                'date' => $date
            ];
            
            $maxCount = max($maxCount, $count);
        }
        
        // Calculer les pourcentages
        foreach ($activity as $date => $data) {
            $percentage = $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0;
            $activity[$date]['percentage'] = round($percentage);
        }
        
        return $activity;
    }
    
    /**
     * Récupère les prospects qui nécessitent un suivi
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $daysThreshold Nombre de jours sans interaction avant alerte
     * @return array Prospects à relancer
     */
    public function getProspectsToFollowUp($userId, $daysThreshold = 7) {
        $date = date('Y-m-d', strtotime("-{$daysThreshold} days"));
        
        $sql = "SELECT f.*, p.username, p.description, l.name as list_name 
                FROM {$this->table} f
                JOIN profiles p ON f.profile_id = p.id
                JOIN lists l ON f.list_id = l.id
                WHERE f.user_id = :user_id 
                AND (f.last_interaction < :date OR f.last_interaction IS NULL)
                AND f.status NOT IN ('pas intéressé', 'client')
                ORDER BY f.last_interaction ASC, f.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compte le nombre de prospects qui nécessitent un suivi
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $daysThreshold Nombre de jours sans interaction avant alerte
     * @return int Nombre de prospects à relancer
     */
    public function countProspectsToFollowUp($userId, $daysThreshold = 7) {
        $date = date('Y-m-d', strtotime("-{$daysThreshold} days"));
        
        $sql = "SELECT COUNT(*) FROM {$this->table} f
                WHERE f.user_id = :user_id 
                AND (f.last_interaction < :date OR f.last_interaction IS NULL)
                AND f.status NOT IN ('pas intéressé', 'client')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Récupère les prospects avec des interactions prévues aujourd'hui
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Prospects avec des interactions prévues
     */
    public function getScheduledFollowUps($userId) {
        $today = date('Y-m-d');
        
        $sql = "SELECT f.*, p.username, p.description, l.name as list_name, i.notes, i.type
                FROM {$this->table} f
                JOIN profiles p ON f.profile_id = p.id
                JOIN lists l ON f.list_id = l.id
                JOIN {$this->interactionsTable} i ON f.id = i.followup_id
                WHERE f.user_id = :user_id 
                AND i.scheduled_date = :today
                AND i.completed = 0
                ORDER BY i.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Met à jour une interaction
     * 
     * @param int $id ID de l'interaction
     * @param array $data Données à mettre à jour
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateInteraction($id, $data) {
        $columns = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $columns[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        
        $sql = "UPDATE {$this->interactionsTable} SET " . implode(', ', $columns) . " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        return $stmt->execute();
    }
}
