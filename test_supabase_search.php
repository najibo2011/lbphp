<?php
// Script de test pour vérifier l'intégration de Supabase

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers nécessaires
require_once __DIR__ . '/includes/SupabaseAPI.php';
require_once __DIR__ . '/models/ProfileSearchModel.php';

// Fonction pour afficher les résultats de manière lisible
function prettyPrint($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

// En-tête HTML
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test de recherche Supabase</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .result { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Test d\'intégration Supabase</h1>';

// Test 1: Vérifier la connexion à Supabase
echo '<h2>Test 1: Connexion à Supabase</h2>';
try {
    $supabaseAPI = SupabaseAPI::getInstance();
    echo '<p class="success">✅ Connexion à Supabase établie avec succès</p>';
} catch (Exception $e) {
    echo '<p class="error">❌ Erreur de connexion à Supabase: ' . $e->getMessage() . '</p>';
}

// Test 2: Recherche de profils
echo '<h2>Test 2: Recherche de profils</h2>';
try {
    $profileSearchModel = new ProfileSearchModel();
    
    // Recherche avec différents paramètres
    echo '<div class="result">';
    echo '<h3>Recherche sans filtres</h3>';
    $results = $profileSearchModel->searchProfiles();
    echo '<p>Nombre de résultats: ' . count($results) . '</p>';
    prettyPrint($results);
    echo '</div>';
    
    echo '<div class="result">';
    echo '<h3>Recherche par nom "John"</h3>';
    $results = $profileSearchModel->searchProfiles('John');
    echo '<p>Nombre de résultats: ' . count($results) . '</p>';
    prettyPrint($results);
    echo '</div>';
    
    echo '<div class="result">';
    echo '<h3>Recherche avec min followers = 1000</h3>';
    $results = $profileSearchModel->searchProfiles('', '', 1000);
    echo '<p>Nombre de résultats: ' . count($results) . '</p>';
    prettyPrint($results);
    echo '</div>';
    
    echo '<div class="result">';
    echo '<h3>Recherche combinée (nom + bio + followers)</h3>';
    $results = $profileSearchModel->searchProfiles('a', 'expert', 500, 10000);
    echo '<p>Nombre de résultats: ' . count($results) . '</p>';
    prettyPrint($results);
    echo '</div>';
    
} catch (Exception $e) {
    echo '<p class="error">❌ Erreur lors de la recherche: ' . $e->getMessage() . '</p>';
}

// Pied de page HTML
echo '</body></html>';
