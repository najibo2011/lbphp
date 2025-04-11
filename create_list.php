<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/ListController.php';

// Instancier le contrôleur de listes
$controller = new ListController();

// Traiter la requête
$controller->create();
