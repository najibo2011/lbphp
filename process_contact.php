<?php
/**
 * Script pour traiter les formulaires de contact
 */
require_once 'includes/Security.php';
require_once 'includes/Database.php';

// Initialiser la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialiser les classes nécessaires
$security = new Security();
$db = new Database();

// Vérifier si la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['contact_errors'] = ['Jeton de sécurité invalide. Veuillez réessayer.'];
        header('Location: contact.php');
        exit;
    }
    
    // Récupérer et nettoyer les données du formulaire
    $name = $security->sanitizeInput($_POST['name'] ?? '');
    $email = $security->sanitizeInput($_POST['email'] ?? '');
    $subject = $security->sanitizeInput($_POST['subject'] ?? '');
    $message = $security->sanitizeInput($_POST['message'] ?? '');
    $privacyConsent = isset($_POST['privacy_consent']);
    
    // Sauvegarder les données du formulaire dans la session en cas d'erreur
    $_SESSION['contact_data'] = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'privacy_consent' => $privacyConsent
    ];
    
    // Valider les données
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Le nom est requis.';
    }
    
    if (empty($email)) {
        $errors[] = 'L\'adresse email est requise.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Le sujet est requis.';
    }
    
    if (empty($message)) {
        $errors[] = 'Le message est requis.';
    }
    
    if (!$privacyConsent) {
        $errors[] = 'Vous devez accepter la politique de confidentialité.';
    }
    
    // S'il y a des erreurs, rediriger vers la page de contact avec les erreurs
    if (!empty($errors)) {
        $_SESSION['contact_errors'] = $errors;
        header('Location: contact.php');
        exit;
    }
    
    // Enregistrer le message dans la base de données
    $result = $db->query(
        "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())",
        [$name, $email, $subject, $message]
    );
    
    if (!$result) {
        $_SESSION['contact_errors'] = ['Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.'];
        header('Location: contact.php');
        exit;
    }
    
    // Envoyer un email de notification
    $to = 'contact@leadsbuilder.com';
    $emailSubject = 'Nouveau message de contact - ' . $subject;
    
    $emailBody = "
    <html>
    <head>
        <title>Nouveau message de contact</title>
    </head>
    <body>
        <h2>Nouveau message de contact</h2>
        <p><strong>Nom :</strong> {$name}</p>
        <p><strong>Email :</strong> {$email}</p>
        <p><strong>Sujet :</strong> {$subject}</p>
        <p><strong>Message :</strong></p>
        <p>{$message}</p>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: LeadsBuilder <noreply@leadsbuilder.com>',
        'Reply-To: ' . $email,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    mail($to, $emailSubject, $emailBody, implode("\r\n", $headers));
    
    // Envoyer un email de confirmation à l'utilisateur
    $userSubject = 'Confirmation de votre message - LeadsBuilder';
    
    $userBody = "
    <html>
    <head>
        <title>Confirmation de votre message</title>
    </head>
    <body>
        <h2>Confirmation de votre message</h2>
        <p>Cher(e) {$name},</p>
        <p>Nous avons bien reçu votre message et nous vous en remercions. Notre équipe va l'examiner et vous répondra dans les plus brefs délais.</p>
        <p><strong>Sujet :</strong> {$subject}</p>
        <p><strong>Message :</strong></p>
        <p>{$message}</p>
        <p>Cordialement,<br>L'équipe LeadsBuilder</p>
    </body>
    </html>
    ";
    
    $userHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: LeadsBuilder <noreply@leadsbuilder.com>',
        'Reply-To: contact@leadsbuilder.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    mail($email, $userSubject, $userBody, implode("\r\n", $userHeaders));
    
    // Journaliser le message de contact
    $logFile = __DIR__ . '/logs/contact_messages.log';
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
        'name' => $name,
        'email' => $email,
        'subject' => $subject
    ];
    
    // Écrire dans le fichier de log
    $logLine = json_encode($logData) . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND);
    
    // Nettoyer les données du formulaire de la session
    unset($_SESSION['contact_data']);
    
    // Rediriger vers la page de contact avec un message de succès
    $_SESSION['contact_success'] = 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';
    header('Location: contact.php');
} else {
    // Si la méthode n'est pas POST, rediriger vers la page de contact
    header('Location: contact.php');
}
