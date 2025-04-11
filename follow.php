<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/FollowController.php';

// Instancier le contrôleur de suivi
$controller = new FollowController();

// Traiter la requête
$controller->index();
