<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/SearchController.php';

// Instancier le contrôleur de recherche
$controller = new SearchController();

// Traiter la recherche
$controller->search();
