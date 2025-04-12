<?php
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../includes/Database.php';

/**
 * Tests unitaires pour la classe UserModel
 */
class UserModelTest extends TestCase {
    private $userModel;
    private $db;
    private $testUserId;
    
    /**
     * Initialisation avant chaque test
     */
    public function setUp() {
        $this->db = new Database();
        $this->userModel = new UserModel($this->db);
        
        // Créer un utilisateur de test
        $testUserData = [
            'name' => 'Utilisateur Test',
            'email' => 'test_' . time() . '@example.com',
            'password' => 'MotDePasse123!'
        ];
        
        $this->testUserId = $this->userModel->create($testUserData);
    }
    
    /**
     * Nettoyage après chaque test
     */
    public function tearDown() {
        // Supprimer l'utilisateur de test
        if ($this->testUserId) {
            $this->db->query("DELETE FROM users WHERE id = ?", [$this->testUserId]);
        }
    }
    
    /**
     * Test de la méthode create
     */
    public function testCreate() {
        $userData = [
            'name' => 'Nouvel Utilisateur',
            'email' => 'nouveau_' . time() . '@example.com',
            'password' => 'MotDePasse456!'
        ];
        
        $userId = $this->userModel->create($userData);
        
        $this->assertNotNull($userId, "L'ID de l'utilisateur ne devrait pas être null");
        
        // Vérifier que l'utilisateur a bien été créé
        $user = $this->db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
        
        $this->assertNotNull($user, "L'utilisateur devrait exister dans la base de données");
        $this->assertEquals($userData['name'], $user['name'], "Le nom de l'utilisateur ne correspond pas");
        $this->assertEquals($userData['email'], $user['email'], "L'email de l'utilisateur ne correspond pas");
        
        // Vérifier que le mot de passe a bien été hashé
        $this->assertTrue(password_verify($userData['password'], $user['password']), "Le mot de passe n'a pas été correctement hashé");
        
        // Nettoyer
        $this->db->query("DELETE FROM users WHERE id = ?", [$userId]);
    }
    
    /**
     * Test de la méthode authenticate avec des identifiants valides
     */
    public function testAuthenticateWithValidCredentials() {
        // Récupérer l'utilisateur de test
        $testUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        // Authentifier avec les identifiants corrects
        $user = $this->userModel->authenticate($testUser['email'], 'MotDePasse123!');
        
        $this->assertNotNull($user, "L'authentification devrait réussir avec des identifiants valides");
        $this->assertEquals($testUser['id'], $user['id'], "L'ID de l'utilisateur ne correspond pas");
        $this->assertEquals($testUser['name'], $user['name'], "Le nom de l'utilisateur ne correspond pas");
        $this->assertEquals($testUser['email'], $user['email'], "L'email de l'utilisateur ne correspond pas");
    }
    
    /**
     * Test de la méthode authenticate avec un email invalide
     */
    public function testAuthenticateWithInvalidEmail() {
        $user = $this->userModel->authenticate('email_inexistant@example.com', 'MotDePasse123!');
        
        $this->assertNull($user, "L'authentification devrait échouer avec un email invalide");
    }
    
    /**
     * Test de la méthode authenticate avec un mot de passe invalide
     */
    public function testAuthenticateWithInvalidPassword() {
        // Récupérer l'utilisateur de test
        $testUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        // Authentifier avec un mot de passe incorrect
        $user = $this->userModel->authenticate($testUser['email'], 'MotDePasseIncorrect');
        
        $this->assertNull($user, "L'authentification devrait échouer avec un mot de passe invalide");
    }
    
    /**
     * Test de la méthode getById
     */
    public function testGetById() {
        $user = $this->userModel->getById($this->testUserId);
        
        $this->assertNotNull($user, "L'utilisateur devrait être trouvé par son ID");
        $this->assertEquals($this->testUserId, $user['id'], "L'ID de l'utilisateur ne correspond pas");
    }
    
    /**
     * Test de la méthode getByEmail
     */
    public function testGetByEmail() {
        // Récupérer l'utilisateur de test
        $testUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        $user = $this->userModel->getByEmail($testUser['email']);
        
        $this->assertNotNull($user, "L'utilisateur devrait être trouvé par son email");
        $this->assertEquals($testUser['id'], $user['id'], "L'ID de l'utilisateur ne correspond pas");
        $this->assertEquals($testUser['email'], $user['email'], "L'email de l'utilisateur ne correspond pas");
    }
    
    /**
     * Test de la méthode update
     */
    public function testUpdate() {
        $updatedData = [
            'name' => 'Nom Mis à Jour',
            'bio' => 'Nouvelle biographie'
        ];
        
        $result = $this->userModel->update($this->testUserId, $updatedData);
        
        $this->assertTrue($result, "La mise à jour devrait réussir");
        
        // Vérifier que les données ont bien été mises à jour
        $updatedUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        $this->assertEquals($updatedData['name'], $updatedUser['name'], "Le nom n'a pas été mis à jour correctement");
        $this->assertEquals($updatedData['bio'], $updatedUser['bio'], "La biographie n'a pas été mise à jour correctement");
    }
    
    /**
     * Test de la méthode updatePassword
     */
    public function testUpdatePassword() {
        $newPassword = 'NouveauMotDePasse789!';
        
        $result = $this->userModel->updatePassword($this->testUserId, $newPassword);
        
        $this->assertTrue($result, "La mise à jour du mot de passe devrait réussir");
        
        // Vérifier que le mot de passe a bien été mis à jour
        $updatedUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        $this->assertTrue(password_verify($newPassword, $updatedUser['password']), "Le mot de passe n'a pas été correctement mis à jour et hashé");
    }
    
    /**
     * Test de la méthode generateResetToken
     */
    public function testGenerateResetToken() {
        // Récupérer l'utilisateur de test
        $testUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        $token = $this->userModel->generateResetToken($testUser['email']);
        
        $this->assertNotNull($token, "Un token devrait être généré");
        
        // Vérifier que le token a bien été enregistré
        $updatedUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        
        $this->assertEquals($token, $updatedUser['reset_token'], "Le token n'a pas été correctement enregistré");
        $this->assertNotNull($updatedUser['reset_token_expiry'], "La date d'expiration du token devrait être définie");
    }
    
    /**
     * Test de la méthode verifyResetToken avec un token valide
     */
    public function testVerifyResetTokenWithValidToken() {
        // Générer un token pour l'utilisateur de test
        $testUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        $token = $this->userModel->generateResetToken($testUser['email']);
        
        $user = $this->userModel->verifyResetToken($token);
        
        $this->assertNotNull($user, "Le token devrait être valide");
        $this->assertEquals($this->testUserId, $user['id'], "L'ID de l'utilisateur ne correspond pas");
    }
    
    /**
     * Test de la méthode verifyResetToken avec un token invalide
     */
    public function testVerifyResetTokenWithInvalidToken() {
        $user = $this->userModel->verifyResetToken('token_invalide');
        
        $this->assertNull($user, "Le token devrait être invalide");
    }
    
    /**
     * Test de la méthode verifyResetToken avec un token expiré
     */
    public function testVerifyResetTokenWithExpiredToken() {
        // Générer un token pour l'utilisateur de test
        $testUser = $this->db->query("SELECT * FROM users WHERE id = ?", [$this->testUserId])->fetch();
        $token = $this->userModel->generateResetToken($testUser['email']);
        
        // Faire expirer le token
        $this->db->query(
            "UPDATE users SET reset_token_expiry = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE id = ?",
            [$this->testUserId]
        );
        
        $user = $this->userModel->verifyResetToken($token);
        
        $this->assertNull($user, "Le token devrait être expiré");
    }
}
