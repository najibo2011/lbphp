<?php
/**
 * Script pour exporter les données personnelles de l'utilisateur (conformité RGPD)
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
    header('Content-Type: application/json');
    http_response_code(401); // Non autorisé
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour exporter vos données'
    ]);
    exit;
}

// Initialiser les classes nécessaires
$gdpr = new Gdpr();
$security = new Security();
$db = new Database();
$userModel = new UserModel($db);

// Vérifier si la requête est une requête AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Si ce n'est pas une requête AJAX, rediriger vers la page de profil
if (!$isAjax) {
    header('Location: profile.php');
    exit;
}

// Récupérer l'ID de l'utilisateur
$userId = $_SESSION['user_id'];

// Exporter les données de l'utilisateur
$userData = $gdpr->exportUserData($userId);

// Vérifier si l'exportation a réussi
if (!$userData) {
    header('Content-Type: application/json');
    http_response_code(500); // Erreur interne du serveur
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'exportation de vos données'
    ]);
    exit;
}

// Journaliser l'exportation des données
$logFile = __DIR__ . '/logs/data_exports.log';
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
    'user_id' => $userId,
    'action' => 'export_data'
];

// Écrire dans le fichier de log
$logLine = json_encode($logData) . PHP_EOL;
file_put_contents($logFile, $logLine, FILE_APPEND);

// Renvoyer les données au format JSON
header('Content-Type: application/json');
echo json_encode($userData);
