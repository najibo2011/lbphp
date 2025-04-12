<?php
/**
 * Script pour créer les tables de suivi dans Supabase
 */

// Charger la configuration de la base de données
$dbConfig = require_once __DIR__ . '/../config/database.php';

// Vérifier si nous utilisons Supabase
if ($dbConfig['connection_type'] !== 'supabase') {
    echo "Ce script ne fonctionne qu'avec une connexion Supabase. Veuillez modifier la configuration.\n";
    exit(1);
}

// Récupérer la configuration Supabase
$supabaseConfig = $dbConfig['supabase'];
$supabaseUrl = $supabaseConfig['api_url'];
$supabaseKey = $supabaseConfig['service_key']; // Utiliser la clé de service pour les opérations admin

// Fonction pour exécuter une requête SQL via l'API Supabase
function executeSqlQuery($supabaseUrl, $supabaseKey, $query) {
    $url = $supabaseUrl . '/rest/v1/rpc/exec_sql';
    
    $data = [
        'query' => $query
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey
    ]);
    
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($statusCode >= 200 && $statusCode < 300) {
        echo "Requête SQL exécutée avec succès.\n";
        return true;
    } else {
        echo "Erreur lors de l'exécution de la requête SQL: " . $response . "\n";
        return false;
    }
}

// Lire les fichiers SQL
$followupsSQL = file_get_contents(__DIR__ . '/followups.sql');
$interactionsSQL = file_get_contents(__DIR__ . '/followup_interactions.sql');

// Exécuter les requêtes SQL
echo "Création de la table followups...\n";
if (executeSqlQuery($supabaseUrl, $supabaseKey, $followupsSQL)) {
    echo "Table followups créée avec succès.\n";
} else {
    echo "Erreur lors de la création de la table followups.\n";
}

echo "Création de la table followup_interactions...\n";
if (executeSqlQuery($supabaseUrl, $supabaseKey, $interactionsSQL)) {
    echo "Table followup_interactions créée avec succès.\n";
} else {
    echo "Erreur lors de la création de la table followup_interactions.\n";
}

echo "Configuration des tables Supabase terminée.\n";
