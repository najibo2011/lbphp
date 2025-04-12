<?php
/**
 * Initialisation du modèle de suivi des prospects pour l'affichage des alertes dans le menu
 */

// Charger le modèle de suivi si ce n'est pas déjà fait
if (!class_exists('FollowupModel')) {
    require_once __DIR__ . '/../models/FollowupModel.php';
}

// Initialiser le modèle de suivi pour l'affichage des alertes
$followupModel = new FollowupModel();
