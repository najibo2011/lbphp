<?php
/**
 * Script pour mettre à jour le profil utilisateur
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
        $_SESSION['update_errors'] = ['Jeton de sécurité invalide. Veuillez réessayer.'];
        header('Location: profile.php');
        exit;
    }
    
    // Récupérer et nettoyer les données du formulaire
    $name = $security->sanitizeInput($_POST['name'] ?? '');
    $email = $security->sanitizeInput($_POST['email'] ?? '');
    $bio = $security->sanitizeInput($_POST['bio'] ?? '');
    
    // Récupérer les informations actuelles de l'utilisateur
    $user = $userModel->getById($_SESSION['user_id']);
    
    // Valider les données
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Le nom est requis.';
    }
    
    if (empty($email)) {
        $errors[] = 'L\'adresse email est requise.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    } else if ($email !== $user['email'] && $userModel->getByEmail($email)) {
        $errors[] = 'Cette adresse email est déjà utilisée par un autre compte.';
    }
    
    // S'il y a des erreurs, rediriger vers la page de profil avec les erreurs
    if (!empty($errors)) {
        $_SESSION['update_errors'] = $errors;
        header('Location: profile.php');
        exit;
    }
    
    // Préparer les données à mettre à jour
    $updateData = [
        'name' => $name,
        'email' => $email,
        'bio' => $bio,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Mettre à jour le profil
    $result = $userModel->update($_SESSION['user_id'], $updateData);
    
    if ($result) {
        // Mettre à jour l'email dans la session si nécessaire
        if ($email !== $user['email']) {
            $_SESSION['user_email'] = $email;
        }
        
        // Rediriger vers la page de profil avec un message de succès
        $_SESSION['update_success'] = 'Votre profil a été mis à jour avec succès.';
    } else {
        // En cas d'erreur, rediriger vers la page de profil avec un message d'erreur
        $_SESSION['update_errors'] = ['Une erreur est survenue lors de la mise à jour de votre profil. Veuillez réessayer.'];
    }
    
    header('Location: profile.php');
} else {
    // Si la méthode n'est pas POST, rediriger vers la page de profil
    header('Location: profile.php');
}
