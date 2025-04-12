<?php
require_once __DIR__ . '/controllers/SubscriptionController.php';

// Désactiver la mise en mémoire tampon de sortie
ob_end_clean();

// Traiter le webhook
$controller = new SubscriptionController();
$controller->handleWebhook();
?>
