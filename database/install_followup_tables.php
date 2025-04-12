<?php
/**
 * Script d'installation des tables de suivi
 * 
 * Ce script crée les tables nécessaires pour la fonctionnalité de suivi des prospects
 */

// Charger la configuration de la base de données
$dbConfig = require_once __DIR__ . '/../config/database.php';

// Vérifier si nous utilisons MySQL
if ($dbConfig['connection_type'] !== 'mysql') {
    echo "Ce script ne fonctionne qu'avec une connexion MySQL. Veuillez modifier la configuration.\n";
    exit(1);
}

// Récupérer la configuration MySQL
$mysqlConfig = $dbConfig['mysql'];

// Fonction pour exécuter un fichier SQL
function executeSqlFile($filename, $pdo) {
    $sql = file_get_contents($filename);
    
    try {
        $result = $pdo->exec($sql);
        echo "Exécution de $filename réussie.\n";
        return true;
    } catch (PDOException $e) {
        echo "Erreur lors de l'exécution de $filename: " . $e->getMessage() . "\n";
        return false;
    }
}

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . $mysqlConfig['host'] . ";dbname=" . $mysqlConfig['dbname'] . ";charset=" . $mysqlConfig['charset'],
        $mysqlConfig['username'],
        $mysqlConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "Connexion à la base de données réussie.\n";
    
    // Exécuter les fichiers SQL
    $files = [
        __DIR__ . '/followups.sql',
        __DIR__ . '/followup_interactions.sql'
    ];
    
    foreach ($files as $file) {
        executeSqlFile($file, $pdo);
    }
    
    echo "Installation des tables de suivi terminée.\n";
    
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    exit(1);
}
