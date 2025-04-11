<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/CrmController.php';

// Récupérer l'action à partir des paramètres GET
$actionName = isset($_GET['action']) ? $_GET['action'] : 'index';

// Instancier le contrôleur CRM
$controller = new CrmController();

// Appeler l'action appropriée
if (method_exists($controller, $actionName)) {
    $controller->$actionName();
} else {
    // Action par défaut si l'action demandée n'existe pas
    $controller->index();
}
