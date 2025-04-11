<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/ListModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';
require_once __DIR__ . '/../includes/Database.php';

/**
 * Contrôleur pour la gestion du suivi des prospects
 */
class FollowupController extends Controller {
    private $listModel;
    private $profileModel;
    
    public function __construct() {
        parent::__construct();
        $this->listModel = new ListModel();
        $this->profileModel = new ProfileModel();
    }
    
    /**
     * Afficher la page de suivi des prospects
     */
    public function index() {
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les listes de l'utilisateur
        $lists = $this->listModel->getByUserId($userId);
        
        // Récupérer les profils suivis
        $followedProfiles = $this->getFollowedProfiles($userId);
        
        // Données à passer à la vue
        $data = [
            'title' => 'Suivi des prospects',
            'lists' => $lists,
            'profiles' => $followedProfiles,
            'currentPage' => 'followup'
        ];
        
        // Afficher la vue avec les données
        $this->render('followup/index', $data);
    }
    
    /**
     * Récupérer les profils suivis par l'utilisateur
     */
    private function getFollowedProfiles($userId) {
        // Cette méthode serait normalement utilisée pour récupérer les profils
        // depuis la base de données ou l'API
        // Pour l'instant, nous utilisons des données simulées dans la vue
        
        return [];
    }
    
    /**
     * Mettre à jour le statut d'un prospect
     */
    public function updateStatus() {
        // Cette méthode serait appelée via AJAX pour mettre à jour le statut d'un prospect
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profileId = $_POST['profile_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $date = $_POST['date'] ?? date('Y-m-d');
            
            // Logique pour mettre à jour le statut
            // ...
            
            // Répondre en JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Rediriger vers la page de suivi si accès direct
        $this->redirect($this->config['app_url'] . '/followup.php');
    }
}
