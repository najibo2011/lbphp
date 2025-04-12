<?php
/**
 * Script pour ajouter une nouvelle méthode de paiement
 */
require_once 'controllers/SubscriptionController.php';

// Initialiser le contrôleur
$controller = new SubscriptionController();

// Traiter l'ajout de la méthode de paiement
$controller->addPaymentMethod();
