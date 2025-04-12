<?php
/**
 * Page de gestion des méthodes de paiement
 */
require_once 'controllers/SubscriptionController.php';

// Initialiser le contrôleur
$controller = new SubscriptionController();

// Afficher la page des méthodes de paiement
$controller->paymentMethods();
