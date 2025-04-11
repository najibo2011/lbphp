<?php
/**
 * Script pour tester la gestion des listes en session
 */

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../models/ListModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtenir l'instance de base de données
$db = Database::getInstance();

// Créer une instance du modèle de liste
$listModel = new ListModel();

// Fonction pour afficher les listes en session
function displaySessionLists() {
    echo "<h3>Listes en session</h3>";
    if (!isset($_SESSION['lists']) || empty($_SESSION['lists'])) {
        echo "<p>Aucune liste en session.</p>";
        return;
    }
    
    echo "<pre>";
    print_r($_SESSION['lists']);
    echo "</pre>";
}

// Afficher les listes actuelles en session
displaySessionLists();

// Tester la création d'une liste
echo "<h2>Test de création d'une liste</h2>";

// ID utilisateur de test
$userId = 1;

// Données de la liste
$listData = [
    'user_id' => $userId,
    'name' => 'Liste de test session ' . date('Y-m-d H:i:s')
];

echo "Tentative de création d'une liste avec les données suivantes :<br>";
echo "<pre>";
print_r($listData);
echo "</pre>";

try {
    // Créer la liste
    $listId = $listModel->create($listData);
    
    echo "<p style='color: green;'>Liste créée avec succès ! ID de la liste: " . $listId . "</p>";
    
    // Afficher les listes en session
    displaySessionLists();
    
    // Tester l'ajout d'un profil à la liste
    echo "<h2>Test d'ajout d'un profil à la liste</h2>";
    
    $profileId = 123; // ID de profil de test
    $notes = "Notes de test pour le profil";
    
    $result = $listModel->addProfile($listId, $profileId, $notes);
    
    if ($result) {
        echo "<p style='color: green;'>Profil ajouté avec succès à la liste !</p>";
    } else {
        echo "<p style='color: red;'>Erreur lors de l'ajout du profil à la liste.</p>";
    }
    
    // Afficher les listes en session
    displaySessionLists();
    
    // Tester la vérification si un profil est dans une liste
    echo "<h2>Test de vérification si un profil est dans une liste</h2>";
    
    $isInList = $listModel->profileInList($listId, $profileId);
    
    if ($isInList) {
        echo "<p style='color: green;'>Le profil est bien dans la liste.</p>";
    } else {
        echo "<p style='color: red;'>Le profil n'est pas dans la liste.</p>";
    }
    
    // Tester la mise à jour des notes d'un profil
    echo "<h2>Test de mise à jour des notes d'un profil</h2>";
    
    $newNotes = "Nouvelles notes pour le profil";
    
    $result = $listModel->updateProfileNotes($listId, $profileId, $newNotes);
    
    if ($result) {
        echo "<p style='color: green;'>Notes du profil mises à jour avec succès !</p>";
    } else {
        echo "<p style='color: red;'>Erreur lors de la mise à jour des notes du profil.</p>";
    }
    
    // Afficher les listes en session
    displaySessionLists();
    
    // Tester la suppression d'un profil d'une liste
    echo "<h2>Test de suppression d'un profil d'une liste</h2>";
    
    $result = $listModel->removeProfile($listId, $profileId);
    
    if ($result) {
        echo "<p style='color: green;'>Profil supprimé avec succès de la liste !</p>";
    } else {
        echo "<p style='color: red;'>Erreur lors de la suppression du profil de la liste.</p>";
    }
    
    // Afficher les listes en session
    displaySessionLists();
    
    // Tester la mise à jour d'une liste
    echo "<h2>Test de mise à jour d'une liste</h2>";
    
    $updateData = [
        'name' => 'Liste de test mise à jour ' . date('Y-m-d H:i:s')
    ];
    
    $result = $listModel->update($listId, $updateData);
    
    if ($result) {
        echo "<p style='color: green;'>Liste mise à jour avec succès !</p>";
    } else {
        echo "<p style='color: red;'>Erreur lors de la mise à jour de la liste.</p>";
    }
    
    // Afficher les listes en session
    displaySessionLists();
    
    // Tester la suppression d'une liste
    echo "<h2>Test de suppression d'une liste</h2>";
    
    $result = $listModel->deleteList($listId);
    
    if ($result) {
        echo "<p style='color: green;'>Liste supprimée avec succès !</p>";
    } else {
        echo "<p style='color: red;'>Erreur lors de la suppression de la liste.</p>";
    }
    
    // Afficher les listes en session
    displaySessionLists();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
}
