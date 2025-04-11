<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/ListModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';
require_once __DIR__ . '/../includes/Database.php';

/**
 * Contrôleur pour la gestion du tableau de bord CRM
 */
class CrmController extends Controller {
    private $listModel;
    private $profileModel;
    
    public function __construct() {
        parent::__construct();
        $this->listModel = new ListModel();
        $this->profileModel = new ProfileModel();
    }
    
    /**
     * Afficher le tableau de bord CRM
     */
    public function index() {
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les listes de l'utilisateur
        $lists = $this->listModel->getByUserId($userId);
        
        // Récupérer les statistiques CRM
        $crmStats = $this->getCrmStats($lists);
        
        // Données à passer à la vue
        $data = [
            'title' => 'Tableau de bord CRM',
            'lists' => $lists,
            'crmStats' => $crmStats,
            'currentPage' => 'crm'
        ];
        
        // Afficher la vue avec les données
        $this->render('crm/index', $data);
    }
    
    /**
     * Calculer les statistiques CRM pour les listes
     */
    private function getCrmStats($lists) {
        // Cette méthode serait normalement utilisée pour calculer les statistiques
        // depuis la base de données ou l'API
        // Pour l'instant, nous utilisons des données simulées dans la vue
        
        return [];
    }
    
    /**
     * Mettre à jour les statistiques CRM
     */
    public function refreshStats() {
        // Cette méthode serait appelée via AJAX pour rafraîchir les statistiques
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Logique pour recalculer les statistiques
            // ...
            
            // Répondre en JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Rediriger vers la page CRM si accès direct
        $this->redirect($this->config['app_url'] . '/crm.php');
    }
}
