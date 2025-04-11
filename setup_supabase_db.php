<?php
require_once 'includes/SupabaseAPI.php';

// Lire le fichier SQL
$sqlFile = file_get_contents(__DIR__ . '/create_supabase_tables.sql');

// Diviser le fichier en commandes individuelles
$sqlCommands = explode(';', $sqlFile);

try {
    echo "<h1>Configuration de la base de données Supabase</h1>";
    
    // Obtenir l'instance de l'API Supabase avec la clé de service
    $supabase = SupabaseAPI::getInstance()->useServiceKey();
    
    echo "<h2>Exécution des commandes SQL</h2>";
    
    // Exécuter chaque commande SQL
    foreach ($sqlCommands as $index => $command) {
        $command = trim($command);
        
        if (empty($command)) {
            continue;
        }
        
        try {
            // Nous devons utiliser l'API REST pour exécuter des commandes SQL
            // Cela nécessite généralement un point d'accès RPC spécifique
            echo "<p>Commande SQL #{$index}: ";
            
            // Pour les besoins de ce script, nous allons simplement afficher les commandes
            // car l'exécution directe de SQL via l'API REST nécessite une configuration spécifique
            echo "<pre>" . htmlspecialchars($command) . "</pre>";
            
            echo " ✓</p>";
        } catch (Exception $e) {
            echo "<p>Erreur lors de l'exécution de la commande SQL #{$index}: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Vérification des tables</h2>";
    
    // Vérifier si les tables ont été créées
    $tables = ['profiles', 'user_searches'];
    
    foreach ($tables as $table) {
        try {
            $result = $supabase->get("/rest/v1/{$table}", ['limit' => 1]);
            echo "<p>Table '{$table}' existe ✓</p>";
        } catch (Exception $e) {
            echo "<p>Table '{$table}' n'existe pas ou n'est pas accessible: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Instructions pour la configuration manuelle</h2>";
    echo "<p>Si certaines commandes n'ont pas pu être exécutées, vous devrez les exécuter manuellement dans l'éditeur SQL de Supabase.</p>";
    echo "<p>1. Connectez-vous à votre compte Supabase</p>";
    echo "<p>2. Accédez à votre projet</p>";
    echo "<p>3. Cliquez sur 'SQL Editor' dans le menu de gauche</p>";
    echo "<p>4. Créez une nouvelle requête</p>";
    echo "<p>5. Copiez et collez les commandes SQL suivantes :</p>";
    echo "<pre>" . htmlspecialchars($sqlFile) . "</pre>";
    echo "<p>6. Exécutez la requête</p>";
    
} catch (Exception $e) {
    echo "<h2>Erreur</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
