<?php
/**
 * Initialisation de l'application
 */

// Démarrer la session
session_start();

// Charger les configurations
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/stripe.php';

// Charger l'autoloader de Composer (pour Stripe)
require_once __DIR__ . '/../vendor/autoload.php';

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Définir l'encodage
mb_internal_encoding('UTF-8');

// Activer l'affichage des erreurs en développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonctions utilitaires
require_once __DIR__ . '/functions.php';
