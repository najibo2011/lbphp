<?php
/**
 * Script pour tester la création d'une liste avec l'ID utilisateur formaté
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../models/ListModel.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtenir l'instance de base de données
$db = Database::getInstance();

// Vérifier si nous utilisons Supabase
if (!$db->isSupabase()) {
    die("Ce script ne doit être exécuté que lorsque Supabase est configuré comme base de données.");
}

// Créer une instance du modèle de liste
$listModel = new ListModel();

// Tester la création d'une liste
echo "<h2>Test de création d'une liste avec ID utilisateur formaté</h2>";

// ID utilisateur de test (entier)
$userId = 1;

// Formater l'ID utilisateur pour Supabase
$formattedUserId = $db->formatUserId($userId);
echo "ID utilisateur original: " . $userId . "<br>";
echo "ID utilisateur formaté: " . $formattedUserId . "<br><br>";

// Données de la liste
$listData = [
    'user_id' => $userId,
    'name' => 'Liste de test formatée ' . date('Y-m-d H:i:s')
];

echo "Tentative de création d'une liste avec les données suivantes :<br>";
echo "<pre>";
print_r($listData);
echo "</pre>";

try {
    // Créer la liste
    $listId = $listModel->create($listData);
    
    echo "<p style='color: green;'>Liste créée avec succès ! ID de la liste: " . $listId . "</p>";
    
    // Récupérer les listes de l'utilisateur
    echo "<h3>Listes de l'utilisateur</h3>";
    $lists = $listModel->getByUserId($userId);
    
    echo "<pre>";
    print_r($lists);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur lors de la création de la liste: " . $e->getMessage() . "</p>";
}
