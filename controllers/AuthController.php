<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Contrôleur pour la gestion de l'authentification
 */
class AuthController extends Controller {
    private $userModel;
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    /**
     * Afficher le formulaire de connexion
     */
    public function login() {
        $data = [
            'title' => 'Connexion',
            'currentPage' => 'login'
        ];
        
        $this->render('auth/login', $data);
    }
    
    /**
     * Traiter la soumission du formulaire de connexion
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login.php');
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('login.php');
            return;
        }
        
        $user = $this->userModel->authenticate($email, $password);
        
        if ($user) {
            // Vérifier si l'email est vérifié
            if (!$user['email_verified']) {
                $this->setFlash('warning', 'Veuillez vérifier votre adresse email avant de vous connecter.');
                $this->redirect('login.php');
                return;
            }
            
            // Démarrer la session et enregistrer les informations de l'utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_subscription'] = $user['subscription_plan'];
            $_SESSION['last_activity'] = time();
            
            // Si "Se souvenir de moi" est coché, créer un cookie
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
                
                // Stocker le token en base de données
                $this->userModel->storeRememberToken($user['id'], $token, $expiry);
                
                // Définir le cookie
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }
            
            $this->setFlash('success', 'Connexion réussie. Bienvenue !');
            $this->redirect('index.php');
        } else {
            $this->setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('login.php');
        }
    }
    
    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() {
        // Supprimer le cookie "Se souvenir de moi" s'il existe
        if (isset($_COOKIE['remember_token'])) {
            $this->userModel->deleteRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Détruire la session
        session_unset();
        session_destroy();
        
        $this->redirect('login.php');
    }
    
    /**
     * Afficher le formulaire d'inscription
     */
    public function register() {
        $data = [
            'title' => 'Inscription',
            'currentPage' => 'register'
        ];
        
        $this->render('auth/register', $data);
    }
    
    /**
     * Traiter la soumission du formulaire d'inscription
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('register.php');
            return;
        }
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation de base
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('register.php');
            return;
        }
        
        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('register.php');
            return;
        }
        
        // Vérifier si l'email existe déjà
        if ($this->userModel->emailExists($email)) {
            $this->setFlash('error', 'Cette adresse email est déjà utilisée.');
            $this->redirect('register.php');
            return;
        }
        
        // Générer un token de vérification d'email
        $verificationToken = bin2hex(random_bytes(32));
        
        // Créer l'utilisateur
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'user',
            'subscription_plan' => 'free',
            'email_verified' => 0,
            'email_verification_token' => $verificationToken,
            'email_verification_sent_at' => date('Y-m-d H:i:s')
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            // Envoyer l'email de vérification
            $this->sendVerificationEmail($email, $verificationToken);
            
            $this->setFlash('success', 'Inscription réussie ! Veuillez vérifier votre adresse email pour activer votre compte.');
            $this->redirect('login.php');
        } else {
            $this->setFlash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            $this->redirect('register.php');
        }
    }
    
    /**
     * Vérifier l'email de l'utilisateur
     */
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlash('error', 'Token de vérification invalide.');
            $this->redirect('login.php');
            return;
        }
        
        $user = $this->userModel->findByVerificationToken($token);
        
        if (!$user) {
            $this->setFlash('error', 'Token de vérification invalide ou expiré.');
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier si le token n'est pas expiré (24 heures)
        $tokenSentAt = strtotime($user['email_verification_sent_at']);
        $now = time();
        
        if ($now - $tokenSentAt > 24 * 60 * 60) {
            $this->setFlash('error', 'Le token de vérification a expiré. Veuillez demander un nouveau lien de vérification.');
            $this->redirect('login.php');
            return;
        }
        
        // Marquer l'email comme vérifié
        $this->userModel->update($user['id'], [
            'email_verified' => 1,
            'email_verification_token' => null,
            'email_verification_sent_at' => null
        ]);
        
        $this->setFlash('success', 'Votre adresse email a été vérifiée avec succès. Vous pouvez maintenant vous connecter.');
        $this->redirect('login.php');
    }
    
    /**
     * Renvoyer l'email de vérification
     */
    public function resendVerificationEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login.php');
            return;
        }
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $this->setFlash('error', 'Veuillez fournir votre adresse email.');
            $this->redirect('login.php');
            return;
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            $this->setFlash('error', 'Aucun compte n\'est associé à cette adresse email.');
            $this->redirect('login.php');
            return;
        }
        
        if ($user['email_verified']) {
            $this->setFlash('info', 'Votre adresse email est déjà vérifiée. Vous pouvez vous connecter.');
            $this->redirect('login.php');
            return;
        }
        
        // Générer un nouveau token
        $verificationToken = bin2hex(random_bytes(32));
        
        // Mettre à jour le token dans la base de données
        $this->userModel->update($user['id'], [
            'email_verification_token' => $verificationToken,
            'email_verification_sent_at' => date('Y-m-d H:i:s')
        ]);
        
        // Envoyer l'email de vérification
        $this->sendVerificationEmail($email, $verificationToken);
        
        $this->setFlash('success', 'Un nouveau lien de vérification a été envoyé à votre adresse email.');
        $this->redirect('login.php');
    }
    
    /**
     * Afficher le formulaire de réinitialisation de mot de passe
     */
    public function forgotPassword() {
        $data = [
            'title' => 'Mot de passe oublié',
            'currentPage' => 'forgot_password'
        ];
        
        $this->render('auth/forgot_password', $data);
    }
    
    /**
     * Traiter la demande de réinitialisation de mot de passe
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('forgot_password.php');
            return;
        }
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $this->setFlash('error', 'Veuillez fournir votre adresse email.');
            $this->redirect('forgot_password.php');
            return;
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // Pour des raisons de sécurité, ne pas révéler si l'email existe ou non
            $this->setFlash('success', 'Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation.');
            $this->redirect('login.php');
            return;
        }
        
        // Générer un token de réinitialisation
        $resetToken = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 heure
        
        // Enregistrer le token dans la base de données
        $this->userModel->storeResetToken($user['id'], $resetToken, $expiry);
        
        // Envoyer l'email de réinitialisation
        $this->sendPasswordResetEmail($email, $resetToken);
        
        $this->setFlash('success', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
        $this->redirect('login.php');
    }
    
    /**
     * Afficher le formulaire de réinitialisation de mot de passe
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlash('error', 'Token de réinitialisation invalide.');
            $this->redirect('login.php');
            return;
        }
        
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user) {
            $this->setFlash('error', 'Token de réinitialisation invalide ou expiré.');
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier si le token n'est pas expiré
        if (strtotime($user['reset_token_expiry']) < time()) {
            $this->setFlash('error', 'Le token de réinitialisation a expiré. Veuillez demander un nouveau lien de réinitialisation.');
            $this->redirect('forgot_password.php');
            return;
        }
        
        $data = [
            'title' => 'Réinitialiser le mot de passe',
            'currentPage' => 'reset_password',
            'token' => $token
        ];
        
        $this->render('auth/reset_password', $data);
    }
    
    /**
     * Traiter la réinitialisation de mot de passe
     */
    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login.php');
            return;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('reset_password.php?token=' . $token);
            return;
        }
        
        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('reset_password.php?token=' . $token);
            return;
        }
        
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user) {
            $this->setFlash('error', 'Token de réinitialisation invalide ou expiré.');
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier si le token n'est pas expiré
        if (strtotime($user['reset_token_expiry']) < time()) {
            $this->setFlash('error', 'Le token de réinitialisation a expiré. Veuillez demander un nouveau lien de réinitialisation.');
            $this->redirect('forgot_password.php');
            return;
        }
        
        // Mettre à jour le mot de passe
        $this->userModel->update($user['id'], [
            'password' => $password,
            'reset_token' => null,
            'reset_token_expiry' => null
        ]);
        
        $this->setFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
        $this->redirect('login.php');
    }
    
    /**
     * Envoyer un email de vérification
     */
    private function sendVerificationEmail($email, $token) {
        // URL de vérification
        $verificationUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/verify_email.php?token=' . $token;
        
        // Sujet de l'email
        $subject = 'Vérification de votre adresse email - LeadsBuilder';
        
        // Corps de l'email
        $message = "
        <html>
        <head>
            <title>Vérification de votre adresse email</title>
        </head>
        <body>
            <h2>Bienvenue sur LeadsBuilder !</h2>
            <p>Merci de vous être inscrit. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href='{$verificationUrl}'>Vérifier mon adresse email</a></p>
            <p>Si vous n'avez pas créé de compte sur LeadsBuilder, vous pouvez ignorer cet email.</p>
            <p>Ce lien expirera dans 24 heures.</p>
            <p>Cordialement,<br>L'équipe LeadsBuilder</p>
        </body>
        </html>
        ";
        
        // En-têtes de l'email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: LeadsBuilder <noreply@leadsbuilder.com>',
            'Reply-To: support@leadsbuilder.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Envoyer l'email
        mail($email, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Envoyer un email de réinitialisation de mot de passe
     */
    private function sendPasswordResetEmail($email, $token) {
        // URL de réinitialisation
        $resetUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/reset_password.php?token=' . $token;
        
        // Sujet de l'email
        $subject = 'Réinitialisation de votre mot de passe - LeadsBuilder';
        
        // Corps de l'email
        $message = "
        <html>
        <head>
            <title>Réinitialisation de votre mot de passe</title>
        </head>
        <body>
            <h2>Réinitialisation de votre mot de passe</h2>
            <p>Vous avez demandé à réinitialiser votre mot de passe. Veuillez cliquer sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
            <p><a href='{$resetUrl}'>Réinitialiser mon mot de passe</a></p>
            <p>Si vous n'avez pas demandé à réinitialiser votre mot de passe, vous pouvez ignorer cet email.</p>
            <p>Ce lien expirera dans 1 heure.</p>
            <p>Cordialement,<br>L'équipe LeadsBuilder</p>
        </body>
        </html>
        ";
        
        // En-têtes de l'email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: LeadsBuilder <noreply@leadsbuilder.com>',
            'Reply-To: support@leadsbuilder.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Envoyer l'email
        mail($email, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Vérifier si la session est expirée
     */
    public function checkSessionExpiry() {
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];
            $sessionTimeout = 30 * 60; // 30 minutes
            
            if ($inactiveTime > $sessionTimeout) {
                // Session expirée, déconnecter l'utilisateur
                session_unset();
                session_destroy();
                
                // Rediriger vers la page de connexion avec un message
                $this->setFlash('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
                $this->redirect('login.php');
                return true;
            }
            
            // Mettre à jour le temps d'activité
            $_SESSION['last_activity'] = time();
        }
        
        return false;
    }
}
