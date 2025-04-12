<?php
/**
 * Page de contact
 */
require_once 'includes/Security.php';
require_once 'includes/Database.php';

// Initialiser la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialiser les classes nécessaires
$security = new Security();

// Générer un jeton CSRF
$csrfToken = $security->generateCsrfToken();

// Récupérer les messages de succès ou d'erreur de la session
$success = $_SESSION['contact_success'] ?? null;
$errors = $_SESSION['contact_errors'] ?? null;
$formData = $_SESSION['contact_data'] ?? null;

// Nettoyer les messages de la session
unset($_SESSION['contact_success']);
unset($_SESSION['contact_errors']);
unset($_SESSION['contact_data']);

// Définir le titre de la page
$title = 'Contact - LeadsBuilder';
$currentPage = 'contact';

// Inclure la vue
include 'views/contact.php';
