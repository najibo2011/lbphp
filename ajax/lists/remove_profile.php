<?php
/**
 * Endpoint AJAX pour supprimer un profil d'une liste
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../controllers/ListController.php';

// Initialiser le contrôleur
$controller = new ListController();

// Appeler la méthode pour supprimer un profil
$controller->removeProfileAjax();
