<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/SearchController.php';

// Récupérer le contrôleur et l'action à partir des paramètres GET
$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'search';
$actionName = isset($_GET['action']) ? $_GET['action'] : 'index';

// Instancier le contrôleur approprié
switch ($controllerName) {
    case 'search':
    default:
        $controller = new SearchController();
        break;
    // Ajouter d'autres contrôleurs au besoin
}

// Appeler l'action appropriée
if (method_exists($controller, $actionName)) {
    $controller->$actionName();
} else {
    // Action par défaut si l'action demandée n'existe pas
    $controller->index();
}
