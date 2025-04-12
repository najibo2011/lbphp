<?php
/**
 * Configuration de la base de données Supabase
 */

// Définition des constantes Supabase (uniquement si elles ne sont pas déjà définies)
if (!defined('SUPABASE_URL')) {
    define('SUPABASE_URL', 'https://ebguhpryimxugdwxozvl.supabase.co');
}
if (!defined('SUPABASE_KEY')) {
    define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImViZ3VocHJ5aW14dWdkd3hvenZsIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczODY2Mzc5MSwiZXhwIjoyMDU0MjM5NzkxfQ.hYIx5GA5iL7opSsH7evosy2ImdiFaqBNyCvqZTcZnIk');
}
if (!defined('SUPABASE_ANON_KEY')) {
    define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImViZ3VocHJ5aW14dWdkd3hvenZsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Mzg2NjM3OTEsImV4cCI6MjA1NDIzOTc5MX0.hYIx5GA5iL7opSsH7evosy2ImdiFaqBNyCvqZTcZnIk');
}

return [
    // Configuration MySQL locale (commentée)
    'mysql' => [
        'host' => '127.0.0.1',
        'dbname' => 'lb1',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8mb4',
        'port' => '8889'
    ],
    
    // Configuration Supabase (API uniquement)
    'supabase' => [
        'api_url' => SUPABASE_URL,
        'api_key' => SUPABASE_ANON_KEY,
        'service_key' => SUPABASE_KEY
    ],
    
    // Type de connexion à utiliser ('mysql' ou 'supabase')
    'connection_type' => 'supabase' // Utilisation de Supabase
];
