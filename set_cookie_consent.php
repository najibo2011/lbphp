<?php
/**
 * Script pour gérer les consentements aux cookies
 */
require_once 'includes/Gdpr.php';

// Initialiser la classe GDPR
$gdpr = new Gdpr();

// Vérifier si la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer le consentement
    $consent = isset($_POST['consent']) && $_POST['consent'] === '1';
    
    // Définir le consentement
    $gdpr->setCookieConsent($consent);
    
    // Répondre avec un JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'reload' => true
    ]);
} else {
    // Répondre avec une erreur
    header('Content-Type: application/json');
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
