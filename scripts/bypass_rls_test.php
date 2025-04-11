<?php
/**
 * Script pour tester la création d'une liste en contournant la politique RLS de Supabase
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

// Fonction pour créer une liste directement via l'API Supabase avec le rôle service_role
function createListWithServiceRole($db, $userId, $name) {
    $url = $db->getSupabaseUrl() . '/rest/v1/lists';
    $headers = [
        'apikey: ' . $db->getSupabaseKey(),
        'Authorization: Bearer ' . $db->getSupabaseKey(),
        'Content-Type: application/json',
        'Prefer: return=representation',
        'X-Client-Info: supabase-js/2.0.0'
    ];
    
    $data = [
        'user_id' => $userId,
        'name' => $name
    ];
    
    echo "Tentative de création d'une liste avec les données suivantes :<br>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Code HTTP: " . $httpCode . "<br>";
    echo "Réponse: " . $response . "<br>";
    
    return [
        'response' => $response,
        'httpCode' => $httpCode
    ];
}

// Créer une liste de test
echo "<h2>Création d'une liste de test avec contournement RLS</h2>";
$userId = $db->formatUserId(1); // ID utilisateur formaté
$name = "Liste de test RLS " . date('Y-m-d H:i:s');
$result = createListWithServiceRole($db, $userId, $name);

if ($result['httpCode'] == 201) {
    echo "<p style='color: green;'>Liste créée avec succès !</p>";
} else {
    echo "<p style='color: red;'>Erreur lors de la création de la liste.</p>";
}

// Essayer avec un autre en-tête d'autorisation
echo "<h2>Essai avec un autre en-tête d'autorisation</h2>";

$url = $db->getSupabaseUrl() . '/rest/v1/lists';
$serviceRoleKey = $db->getSupabaseKey(); // Idéalement, nous aurions une clé de service distincte

$headers = [
    'apikey: ' . $serviceRoleKey,
    'Authorization: Bearer ' . $serviceRoleKey,
    'Content-Type: application/json',
    'Prefer: return=representation'
];

$data = [
    'user_id' => $userId,
    'name' => $name . " (2ème essai)"
];

echo "Tentative avec les en-têtes suivants :<br>";
echo "<pre>";
print_r($headers);
echo "</pre>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: " . $httpCode . "<br>";
echo "Réponse: " . $response . "<br>";

if ($httpCode == 201) {
    echo "<p style='color: green;'>Liste créée avec succès !</p>";
} else {
    echo "<p style='color: red;'>Erreur lors de la création de la liste.</p>";
}
