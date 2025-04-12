<?php
/**
 * Script pour vider le cache de l'application
 * Accessible uniquement aux administrateurs
 */
require_once 'includes/Cache.php';
require_once 'models/UserModel.php';
require_once 'includes/Database.php';

// Initialiser la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et est administrateur
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $userModel = new UserModel($db);
    $user = $userModel->getById($_SESSION['user_id']);
    $isAdmin = $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

// Si l'utilisateur n'est pas administrateur, rediriger vers la page d'accueil
if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

// Initialiser la classe de cache
$cache = new Cache();

// Vider le cache
$result = $cache->clearAll();

// Définir le message de résultat
if ($result) {
    $_SESSION['cache_message'] = 'Le cache a été vidé avec succès.';
} else {
    $_SESSION['cache_message'] = 'Une erreur est survenue lors de la vidange du cache.';
}

// Rediriger vers la page de santé
header('Location: health.php');
exit;
