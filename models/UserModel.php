<?php
require_once __DIR__ . '/../includes/Model.php';

/**
 * Modèle pour la gestion des utilisateurs
 */
class UserModel extends Model {
    protected $table = 'users';
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        // Hasher le mot de passe
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Ajouter la date de création
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        // Hasher le mot de passe si présent
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Ajouter la date de mise à jour
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::update($id, $data);
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($email, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Vérifier si un email existe déjà
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    /**
     * Générer un token de réinitialisation de mot de passe
     */
    public function generateResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 heure
        
        $sql = "UPDATE {$this->table} SET reset_token = :token, reset_expiry = :expiry WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        return $token;
    }
    
    /**
     * Vérifier un token de réinitialisation
     */
    public function verifyResetToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_expiry > :now";
        $now = date('Y-m-d H:i:s');
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
