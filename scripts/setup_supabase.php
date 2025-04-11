<?php
/**
 * Script pour configurer les tables dans Supabase
 * Ce script vérifie si les tables nécessaires existent dans Supabase et les crée si nécessaire
 */

require_once __DIR__ . '/../includes/Database.php';

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

// Fonction pour exécuter une requête SQL sur Supabase
function executeSupabaseQuery($db, $query) {
    $url = $db->getSupabaseUrl() . '/rest/v1/rpc/exec_sql';
    $headers = [
        'apikey: ' . $db->getSupabaseKey(),
        'Authorization: Bearer ' . $db->getSupabaseKey(),
        'Content-Type: application/json'
    ];
    
    $data = [
        'query' => $query
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => $response,
        'httpCode' => $httpCode
    ];
}

// Vérifier si la table 'lists' existe
$structure = $db->getSupabaseTableStructure('lists');
if ($structure === null) {
    echo "Création de la table 'lists'...<br>";
    
    $query = "
    CREATE TABLE IF NOT EXISTS lists (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        is_public BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    
    $result = executeSupabaseQuery($db, $query);
    
    if ($result['httpCode'] == 200) {
        echo "Table 'lists' créée avec succès.<br>";
    } else {
        echo "Erreur lors de la création de la table 'lists': " . $result['response'] . "<br>";
    }
} else {
    echo "La table 'lists' existe déjà.<br>";
}

// Vérifier si la table 'list_profiles' existe
$structure = $db->getSupabaseTableStructure('list_profiles');
if ($structure === null) {
    echo "Création de la table 'list_profiles'...<br>";
    
    $query = "
    CREATE TABLE IF NOT EXISTS list_profiles (
        list_id INTEGER NOT NULL,
        profile_id INTEGER NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (list_id, profile_id)
    );";
    
    $result = executeSupabaseQuery($db, $query);
    
    if ($result['httpCode'] == 200) {
        echo "Table 'list_profiles' créée avec succès.<br>";
    } else {
        echo "Erreur lors de la création de la table 'list_profiles': " . $result['response'] . "<br>";
    }
} else {
    echo "La table 'list_profiles' existe déjà.<br>";
}

echo "Configuration terminée.";
