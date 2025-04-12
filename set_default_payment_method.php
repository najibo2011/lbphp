<?php
/**
 * Script pour définir une méthode de paiement par défaut
 */
require_once 'controllers/SubscriptionController.php';

// Initialiser le contrôleur
$controller = new SubscriptionController();

// Traiter la définition de la méthode de paiement par défaut
$controller->setDefaultPaymentMethod();
