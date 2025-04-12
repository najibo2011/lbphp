<?php
/**
 * Script pour traiter les demandes RGPD
 */
require_once 'includes/Gdpr.php';
require_once 'includes/Database.php';
require_once 'includes/Security.php';

// Initialiser les classes nécessaires
$gdpr = new Gdpr();
$security = new Security();

// Vérifier si la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header('Location: gdpr_request.php?error=csrf');
        exit;
    }
    
    // Récupérer et nettoyer les données du formulaire
    $name = $security->sanitizeInput($_POST['name'] ?? '');
    $email = $security->sanitizeInput($_POST['email'] ?? '');
    $requestType = $security->sanitizeInput($_POST['request_type'] ?? '');
    $message = $security->sanitizeInput($_POST['message'] ?? '');
    $consent = isset($_POST['consent']);
    
    // Valider les données
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Le nom est requis';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Une adresse email valide est requise';
    }
    
    if (empty($requestType)) {
        $errors[] = 'Le type de demande est requis';
    }
    
    if (empty($message)) {
        $errors[] = 'Les détails de la demande sont requis';
    }
    
    if (!$consent) {
        $errors[] = 'Vous devez confirmer que les informations fournies sont exactes';
    }
    
    // S'il y a des erreurs, rediriger vers le formulaire avec les erreurs
    if (!empty($errors)) {
        $_SESSION['gdpr_request_errors'] = $errors;
        $_SESSION['gdpr_request_data'] = [
            'name' => $name,
            'email' => $email,
            'request_type' => $requestType,
            'message' => $message
        ];
        
        header('Location: gdpr_request.php');
        exit;
    }
    
    // Traiter la demande RGPD
    $result = $gdpr->processGdprRequest([
        'name' => $name,
        'email' => $email,
        'request_type' => $requestType,
        'message' => $message,
        'consent' => $consent
    ]);
    
    // Rediriger en fonction du résultat
    if ($result['success']) {
        $_SESSION['gdpr_request_success'] = $result['message'];
        header('Location: gdpr_request.php?success=1');
    } else {
        $_SESSION['gdpr_request_errors'] = [$result['message']];
        $_SESSION['gdpr_request_data'] = [
            'name' => $name,
            'email' => $email,
            'request_type' => $requestType,
            'message' => $message
        ];
        
        header('Location: gdpr_request.php');
    }
} else {
    // Rediriger vers le formulaire si la méthode n'est pas POST
    header('Location: gdpr_request.php');
}
