<?php
/**
 * Page de profil utilisateur
 */
require_once 'includes/Security.php';
require_once 'models/UserModel.php';
require_once 'models/SubscriptionModel.php';
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
$subscriptionModel = new SubscriptionModel($db);

// Générer un jeton CSRF
$csrfToken = $security->generateCsrfToken();

// Récupérer les informations de l'utilisateur
$user = $userModel->getById($_SESSION['user_id']);

// Récupérer les informations d'abonnement
$subscription = $subscriptionModel->getUserSubscription($_SESSION['user_id']);

// Récupérer les messages de succès ou d'erreur de la session
$updateSuccess = $_SESSION['update_success'] ?? null;
$updateErrors = $_SESSION['update_errors'] ?? null;
$passwordSuccess = $_SESSION['password_success'] ?? null;
$passwordErrors = $_SESSION['password_errors'] ?? null;

// Nettoyer les messages de la session
unset($_SESSION['update_success']);
unset($_SESSION['update_errors']);
unset($_SESSION['password_success']);
unset($_SESSION['password_errors']);

// Définir le titre de la page
$title = 'Mon Profil - LeadsBuilder';
$currentPage = 'profile';

// Inclure la vue
include 'views/profile.php';
