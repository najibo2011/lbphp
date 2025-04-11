<?php
/**
 * Endpoint AJAX pour ajouter des profils à une liste
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../controllers/ListController.php';

// Initialiser le contrôleur
$controller = new ListController();

// Appeler la méthode pour ajouter des profils
$controller->addProfiles();
