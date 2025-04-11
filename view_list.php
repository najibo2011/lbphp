<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/ListController.php';

// Récupérer l'ID de la liste
$listId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Vérifier si l'ID est valide
if (!$listId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'ID de liste invalide'
    ];
    header('Location: lists.php');
    exit;
}

// Instancier le contrôleur de listes
$controller = new ListController();

// Afficher la liste
$controller->view($listId);
