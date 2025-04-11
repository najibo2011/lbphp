<?php
/**
 * Endpoint AJAX pour mettre à jour les notes d'un profil dans une liste
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../controllers/ListController.php';

// Initialiser le contrôleur
$controller = new ListController();

// Appeler la méthode pour mettre à jour les notes
$controller->updateProfileNotes();
