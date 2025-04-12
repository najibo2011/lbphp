<?php
/**
 * Script pour supprimer une méthode de paiement
 */
require_once 'controllers/SubscriptionController.php';

// Initialiser le contrôleur
$controller = new SubscriptionController();

// Traiter la suppression de la méthode de paiement
$controller->deletePaymentMethod();
