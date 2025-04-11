<?php
/**
 * Script pour créer une liste de test dans Supabase avec UUID
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

// Fonction pour générer un UUID v4
function generateUuidV4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Fonction pour créer une liste directement via l'API Supabase
function createList($db, $userId, $name) {
    $url = $db->getSupabaseUrl() . '/rest/v1/lists';
    $headers = [
        'apikey: ' . $db->getSupabaseKey(),
        'Authorization: Bearer ' . $db->getSupabaseKey(),
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $data = [
        'user_id' => $userId, // UUID au lieu d'un entier
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
echo "<h2>Création d'une liste de test avec UUID</h2>";
$userId = generateUuidV4(); // Générer un UUID pour l'ID utilisateur
$name = "Liste de test " . date('Y-m-d H:i:s');
$result = createList($db, $userId, $name);

if ($result['httpCode'] == 201) {
    echo "<p style='color: green;'>Liste créée avec succès !</p>";
} else {
    echo "<p style='color: red;'>Erreur lors de la création de la liste.</p>";
}
