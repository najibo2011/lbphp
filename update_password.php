<?php
/**
 * Script pour mettre à jour le mot de passe de l'utilisateur
 */
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
$security = new Security();
$db = new Database();
$userModel = new UserModel($db);

// Vérifier si la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['password_errors'] = ['Jeton de sécurité invalide. Veuillez réessayer.'];
        header('Location: profile.php');
        exit;
    }
    
    // Récupérer les données du formulaire
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Récupérer les informations de l'utilisateur
    $user = $userModel->getById($_SESSION['user_id']);
    
    // Valider les données
    $errors = [];
    
    if (empty($currentPassword)) {
        $errors[] = 'Le mot de passe actuel est requis.';
    } else if (!password_verify($currentPassword, $user['password'])) {
        $errors[] = 'Le mot de passe actuel est incorrect.';
    }
    
    if (empty($newPassword)) {
        $errors[] = 'Le nouveau mot de passe est requis.';
    } else if (strlen($newPassword) < 8) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
    } else if (!preg_match('/[A-Z]/', $newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins une lettre majuscule.';
    } else if (!preg_match('/[a-z]/', $newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins une lettre minuscule.';
    } else if (!preg_match('/[0-9]/', $newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins un chiffre.';
    } else if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins un caractère spécial.';
    }
    
    if (empty($confirmPassword)) {
        $errors[] = 'La confirmation du mot de passe est requise.';
    } else if ($newPassword !== $confirmPassword) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }
    
    // S'il y a des erreurs, rediriger vers la page de profil avec les erreurs
    if (!empty($errors)) {
        $_SESSION['password_errors'] = $errors;
        header('Location: profile.php');
        exit;
    }
    
    // Mettre à jour le mot de passe
    $result = $userModel->updatePassword($_SESSION['user_id'], $newPassword);
    
    if ($result) {
        // Journaliser la mise à jour du mot de passe
        $logFile = __DIR__ . '/logs/password_changes.log';
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
            'user_id' => $_SESSION['user_id']
        ];
        
        // Écrire dans le fichier de log
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
        
        // Rediriger vers la page de profil avec un message de succès
        $_SESSION['password_success'] = 'Votre mot de passe a été mis à jour avec succès.';
    } else {
        // En cas d'erreur, rediriger vers la page de profil avec un message d'erreur
        $_SESSION['password_errors'] = ['Une erreur est survenue lors de la mise à jour de votre mot de passe. Veuillez réessayer.'];
    }
    
    header('Location: profile.php');
} else {
    // Si la méthode n'est pas POST, rediriger vers la page de profil
    header('Location: profile.php');
}
