<?php
/**
 * Endpoint AJAX pour récupérer les listes de l'utilisateur
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../controllers/ListController.php';

// Initialiser le contrôleur
$controller = new ListController();

// Appeler la méthode pour récupérer les listes
$controller->getUserLists();
