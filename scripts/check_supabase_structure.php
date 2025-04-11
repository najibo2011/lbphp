<?php
/**
 * Script pour vérifier la structure des tables dans Supabase
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

// Fonction pour obtenir les informations sur une table Supabase
function getTableInfo($db, $tableName) {
    $url = $db->getSupabaseUrl() . '/rest/v1/' . $tableName . '?limit=1';
    $headers = [
        'apikey: ' . $db->getSupabaseKey(),
        'Authorization: Bearer ' . $db->getSupabaseKey(),
        'Accept: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => $response,
        'httpCode' => $httpCode
    ];
}

// Obtenir les informations sur la table 'lists'
echo "<h2>Vérification de la table 'lists'</h2>";
$listsInfo = getTableInfo($db, 'lists');
echo "Code HTTP: " . $listsInfo['httpCode'] . "<br>";
echo "Réponse: " . $listsInfo['response'] . "<br><br>";

// Obtenir les informations sur la table 'list_profiles'
echo "<h2>Vérification de la table 'list_profiles'</h2>";
$listProfilesInfo = getTableInfo($db, 'list_profiles');
echo "Code HTTP: " . $listProfilesInfo['httpCode'] . "<br>";
echo "Réponse: " . $listProfilesInfo['response'] . "<br><br>";

// Obtenir la structure de la table 'lists'
echo "<h2>Structure de la table 'lists'</h2>";
$structure = $db->getSupabaseTableStructure('lists');
echo "<pre>";
print_r($structure);
echo "</pre>";

// Obtenir la structure de la table 'list_profiles'
echo "<h2>Structure de la table 'list_profiles'</h2>";
$structure = $db->getSupabaseTableStructure('list_profiles');
echo "<pre>";
print_r($structure);
echo "</pre>";

// Vérifier les paramètres de connexion Supabase
echo "<h2>Paramètres de connexion Supabase</h2>";
echo "URL: " . $db->getSupabaseUrl() . "<br>";
echo "Clé API: " . substr($db->getSupabaseKey(), 0, 5) . "..." . substr($db->getSupabaseKey(), -5) . "<br>";
