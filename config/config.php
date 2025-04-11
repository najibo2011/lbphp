<?php
/**
 * Configuration générale de l'application
 */

return [
    // Informations de base
    'app_name' => 'LeadsBuilder PHP',
    'app_url' => 'http://localhost:8888/lb1',
    'app_version' => '1.0.0',
    
    // Configuration des emails
    'email' => [
        'from_email' => 'contact@leadsbuilder.co',
        'from_name' => 'LeadsBuilder PHP',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_username' => 'username',
        'smtp_password' => 'password',
        'smtp_secure' => 'tls'
    ],
    
    // Paramètres de sécurité
    'security' => [
        'hash_cost' => 10,
        'session_lifetime' => 3600,
        'token_lifetime' => 86400
    ],
    
    // Limites et quotas
    'limits' => [
        'max_forms' => 10,
        'max_landing_pages' => 5,
        'max_contacts' => 1000,
        'max_file_size' => 5 * 1024 * 1024 // 5MB
    ]
];
