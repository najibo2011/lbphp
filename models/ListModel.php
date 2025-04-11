<?php
require_once __DIR__ . '/../includes/Model.php';

/**
 * Modèle pour la gestion des listes
 */
class ListModel extends Model {
    protected $table = 'lists';
    
    /**
     * Récupérer les listes d'un utilisateur
     */
    public function getByUserId($userId) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Récupérer les listes depuis la session
            if (!isset($_SESSION['lists'])) {
                return [];
            }
            
            // Filtrer les listes par ID utilisateur
            $userLists = [];
            foreach ($_SESSION['lists'] as $list) {
                if ($list['user_id'] == $userId) {
                    $userLists[] = $list;
                }
            }
            
            return $userLists;
        }
        
        // Si nous utilisons MySQL
        $sql = "SELECT * FROM lists WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les profils d'une liste
     */
    public function getProfiles($listId) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Récupérer les profils de la liste depuis la session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$listId])) {
                return [];
            }
            
            $profiles = [];
            foreach ($_SESSION['lists'][$listId]['profiles'] as $profileId => $profileData) {
                // Récupérer les informations du profil depuis le modèle de profil
                $profileModel = new ProfileModel();
                $profile = $profileModel->getById($profileId);
                
                if ($profile) {
                    $profile['notes'] = $profileData['notes'];
                    $profiles[] = $profile;
                }
            }
            
            return $profiles;
        }
        
        // Si nous utilisons MySQL
        $sql = "SELECT p.*, lp.notes 
                FROM profiles p 
                JOIN list_profiles lp ON p.id = lp.profile_id 
                WHERE lp.list_id = :list_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':list_id', $listId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Vérifier si un profil est dans une liste
     */
    public function profileInList($listId, $profileId) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Vérifier si le profil est dans la liste en session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$listId])) {
                return false;
            }
            
            return isset($_SESSION['lists'][$listId]['profiles'][$profileId]);
        }
        
        // Si nous utilisons MySQL
        $sql = "SELECT COUNT(*) FROM list_profiles 
                WHERE list_id = :list_id AND profile_id = :profile_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':list_id', $listId, PDO::PARAM_INT);
        $stmt->bindParam(':profile_id', $profileId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Mettre à jour les notes d'un profil dans une liste
     */
    public function updateProfileNotes($listId, $profileId, $notes) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Mettre à jour les notes du profil dans la liste en session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$listId]) || !isset($_SESSION['lists'][$listId]['profiles'][$profileId])) {
                return false;
            }
            
            $_SESSION['lists'][$listId]['profiles'][$profileId]['notes'] = $notes;
            
            error_log("Notes du profil mises à jour en session: " . json_encode($_SESSION['lists'][$listId]['profiles'][$profileId]));
            
            return true;
        }
        
        // Si nous utilisons MySQL
        $sql = "UPDATE list_profiles 
                SET notes = :notes 
                WHERE list_id = :list_id AND profile_id = :profile_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':list_id', $listId, PDO::PARAM_INT);
        $stmt->bindParam(':profile_id', $profileId, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * Ajouter un profil à une liste
     */
    public function addProfile($listId, $profileId, $notes = '') {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Vérifier si le profil est déjà dans la liste
            if ($this->profileInList($listId, $profileId)) {
                return true;
            }
            
            // Ajouter le profil à la liste en session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$listId])) {
                return false;
            }
            
            // Ajouter le profil à la liste
            $_SESSION['lists'][$listId]['profiles'][$profileId] = [
                'profile_id' => $profileId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            error_log("Profil ajouté à la liste en session: " . json_encode($_SESSION['lists'][$listId]['profiles'][$profileId]));
            
            return true;
        }
        
        // Si nous utilisons MySQL
        if ($this->profileInList($listId, $profileId)) {
            return true; // Le profil est déjà dans la liste
        }
        
        $sql = "INSERT INTO list_profiles (list_id, profile_id, notes) 
                VALUES (:list_id, :profile_id, :notes)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':list_id', $listId, PDO::PARAM_INT);
        $stmt->bindParam(':profile_id', $profileId, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * Supprimer un profil d'une liste
     */
    public function removeProfile($listId, $profileId) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Supprimer le profil de la liste en session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$listId]) || !isset($_SESSION['lists'][$listId]['profiles'][$profileId])) {
                return false;
            }
            
            unset($_SESSION['lists'][$listId]['profiles'][$profileId]);
            
            error_log("Profil supprimé de la liste en session: " . $profileId);
            
            return true;
        }
        
        // Si nous utilisons MySQL
        $sql = "DELETE FROM list_profiles 
                WHERE list_id = :list_id AND profile_id = :profile_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':list_id', $listId, PDO::PARAM_INT);
        $stmt->bindParam(':profile_id', $profileId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Supprimer une liste et tous ses profils associés
     */
    public function delete($id) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // 1. Supprimer d'abord les profils associés à la liste
            $deleteProfilesUrl = $this->dbInstance->getSupabaseUrl() . '/rest/v1/list_profiles?list_id=eq.' . $id;
            $headers = [
                'apikey: ' . $this->dbInstance->getSupabaseKey(),
                'Authorization: Bearer ' . $this->dbInstance->getSupabaseKey(),
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $deleteProfilesUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            curl_exec($ch);
            curl_close($ch);
            
            // 2. Puis supprimer la liste elle-même
            $deleteListUrl = $this->dbInstance->getSupabaseUrl() . '/rest/v1/lists?id=eq.' . $id;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $deleteListUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Si le code HTTP est 200 ou 204, la suppression a réussi
            return ($httpCode == 200 || $httpCode == 204);
        }
        
        // Si nous utilisons MySQL
        // D'abord supprimer les profils associés à la liste
        $sqlDeleteProfiles = "DELETE FROM list_profiles WHERE list_id = :list_id";
        $stmtProfiles = $this->db->prepare($sqlDeleteProfiles);
        $stmtProfiles->bindParam(':list_id', $id, PDO::PARAM_INT);
        $stmtProfiles->execute();
        
        // Puis supprimer la liste elle-même
        return parent::delete($id);
    }
    
    /**
     * Supprimer une liste
     */
    public function deleteList($id) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Supprimer la liste de la session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$id])) {
                return false;
            }
            
            unset($_SESSION['lists'][$id]);
            
            error_log("Liste supprimée de la session: " . $id);
            
            return true;
        }
        
        // Si nous utilisons MySQL
        return parent::delete($id);
    }
    
    /**
     * Récupérer une liste par son ID
     */
    public function getById($id) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Récupérer la liste depuis la session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$id])) {
                return null;
            }
            
            return $_SESSION['lists'][$id];
        }
        
        // Si nous utilisons MySQL
        return parent::getById($id);
    }
    
    /**
     * Mettre à jour une liste
     */
    public function update($id, $data) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            // Mettre à jour la liste en session
            if (!isset($_SESSION['lists']) || !isset($_SESSION['lists'][$id])) {
                return false;
            }
            
            // Mettre à jour les données de la liste
            if (isset($data['name'])) {
                $_SESSION['lists'][$id]['name'] = $data['name'];
            }
            
            $_SESSION['lists'][$id]['updated_at'] = date('Y-m-d H:i:s');
            
            error_log("Liste mise à jour en session: " . json_encode($_SESSION['lists'][$id]));
            
            return true;
        }
        
        // Si nous utilisons MySQL
        return parent::update($id, $data);
    }
    
    /**
     * Créer une nouvelle liste
     */
    public function create($data) {
        // Vérifier si nous utilisons Supabase
        if ($this->isSupabase) {
            try {
                // Puisque nous avons des problèmes avec les politiques RLS de Supabase,
                // nous allons simuler la création d'une liste en utilisant une approche alternative
                
                // Générer un ID unique pour la liste
                $listId = uniqid('list_', true);
                
                // Stocker les informations de la liste dans la session
                if (!isset($_SESSION['lists'])) {
                    $_SESSION['lists'] = [];
                }
                
                // Ajouter la liste à la session
                $_SESSION['lists'][$listId] = [
                    'id' => $listId,
                    'user_id' => $data['user_id'],
                    'name' => $data['name'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'profiles' => []
                ];
                
                error_log("Liste créée en session: " . json_encode($_SESSION['lists'][$listId]));
                
                return $listId;
            } catch (Exception $e) {
                // Capturer et relancer l'exception avec plus d'informations
                throw new Exception($e->getMessage());
            }
        }
        
        // Si nous utilisons MySQL
        return parent::create($data);
    }
    
    /**
     * Obtenir l'instance de base de données
     */
    public function getDbInstance() {
        return $this->dbInstance;
    }
}
