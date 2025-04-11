<?php
require_once 'includes/SupabaseAPI.php';

// SQL pour créer la fonction de recherche dans Supabase
$createFunctionSQL = <<<SQL
CREATE OR REPLACE FUNCTION search_profiles(
    search_name TEXT DEFAULT NULL,
    search_bio TEXT DEFAULT NULL,
    min_followers INT DEFAULT NULL,
    max_followers INT DEFAULT NULL,
    limit_val INT DEFAULT 20
) RETURNS SETOF profiles AS $$
DECLARE
    query TEXT := 'SELECT * FROM profiles WHERE 1=1';
BEGIN
    IF search_name IS NOT NULL THEN
        query := query || ' AND name ILIKE ''%' || search_name || '%''';
    END IF;
    
    IF search_bio IS NOT NULL THEN
        query := query || ' AND bio ILIKE ''%' || search_bio || '%''';
    END IF;
    
    IF min_followers IS NOT NULL THEN
        query := query || ' AND followers >= ' || min_followers;
    END IF;
    
    IF max_followers IS NOT NULL THEN
        query := query || ' AND followers <= ' || max_followers;
    END IF;
    
    query := query || ' ORDER BY followers DESC LIMIT ' || limit_val;
    
    RETURN QUERY EXECUTE query;
END;
$$ LANGUAGE plpgsql;
SQL;

try {
    echo "<h1>Création de la fonction RPC dans Supabase</h1>";
    
    // Utiliser l'API Supabase avec la clé de service
    $supabase = SupabaseAPI::getInstance()->useServiceKey();
    
    // Exécuter la requête SQL via l'API REST
    $result = $supabase->post('/rest/v1/rpc/create_sql_function', [
        'sql_query' => $createFunctionSQL
    ]);
    
    echo "<p>Fonction créée avec succès !</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Erreur</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    echo "<h3>Solution alternative</h3>";
    echo "<p>Si vous rencontrez une erreur, vous devrez peut-être créer cette fonction manuellement dans l'interface Supabase SQL Editor.</p>";
    echo "<p>Voici le SQL à exécuter :</p>";
    echo "<pre>";
    echo htmlspecialchars($createFunctionSQL);
    echo "</pre>";
}
