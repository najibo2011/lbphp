<?php
/**
 * Script pour supprimer le compte utilisateur (conformité RGPD)
 */
require_once 'includes/Gdpr.php';
require_once 'includes/Security.php';
require_once 'models/UserModel.php';
require_once 'includes/Database.php';

// Initialiser la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialiser les classes nécessaires
$gdpr = new Gdpr();
$security = new Security();
$db = new Database();
$userModel = new UserModel($db);

// Vérifier si la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['delete_errors'] = ['Jeton de sécurité invalide. Veuillez réessayer.'];
        header('Location: profile.php');
        exit;
    }
    
    // Récupérer et nettoyer les données du formulaire
    $confirmEmail = $security->sanitizeInput($_POST['confirm_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmDeletion = isset($_POST['confirm_deletion']);
    
    // Récupérer les informations de l'utilisateur
    $user = $userModel->getById($_SESSION['user_id']);
    
    // Valider les données
    $errors = [];
    
    if (empty($confirmEmail)) {
        $errors[] = 'L\'adresse email est requise pour confirmer la suppression.';
    } else if ($confirmEmail !== $user['email']) {
        $errors[] = 'L\'adresse email ne correspond pas à votre compte.';
    }
    
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis pour confirmer la suppression.';
    } else if (!password_verify($password, $user['password'])) {
        $errors[] = 'Le mot de passe est incorrect.';
    }
    
    if (!$confirmDeletion) {
        $errors[] = 'Vous devez confirmer que vous comprenez les conséquences de la suppression.';
    }
    
    // S'il y a des erreurs, rediriger vers la page de profil avec les erreurs
    if (!empty($errors)) {
        $_SESSION['delete_errors'] = $errors;
        header('Location: profile.php');
        exit;
    }
    
    // Supprimer les données de l'utilisateur
    $result = $gdpr->deleteUserData($_SESSION['user_id']);
    
    if ($result) {
        // Journaliser la suppression du compte
        $logFile = __DIR__ . '/logs/account_deletions.log';
        $logDir = dirname($logFile);
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Préparer les données du log
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'user_id' => $_SESSION['user_id'],
            'email' => $user['email']
        ];
        
        // Écrire dans le fichier de log
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
        
        // Détruire la session
        session_unset();
        session_destroy();
        
        // Rediriger vers la page de connexion avec un message de succès
        $_SESSION['login_message'] = 'Votre compte a été supprimé avec succès. Nous espérons vous revoir bientôt !';
        header('Location: login.php');
    } else {
        // En cas d'erreur, rediriger vers la page de profil avec un message d'erreur
        $_SESSION['delete_errors'] = ['Une erreur est survenue lors de la suppression de votre compte. Veuillez réessayer ou contacter le support.'];
        header('Location: profile.php');
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers la page de profil
    header('Location: profile.php');
}
