<?php
/**
 * Endpoint AJAX pour créer une nouvelle liste
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../controllers/ListController.php';

// Initialiser le contrôleur
$controller = new ListController();

// Appeler la méthode pour créer une liste
$controller->createAjax();
