<?php
session_start();
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Controller.php';
require_once __DIR__ . '/controllers/ListController.php';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lists.php');
    exit;
}

// Récupérer l'ID de la liste
$listId = isset($_POST['list_id']) ? (int)$_POST['list_id'] : 0;

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

// Supprimer la liste
$controller->delete($listId);
