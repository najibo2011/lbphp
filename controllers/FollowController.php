<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/FollowModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';

/**
 * Contrôleur pour la gestion des suivis
 */
class FollowController extends Controller {
    private $followModel;
    private $profileModel;
    
    public function __construct() {
        parent::__construct();
        $this->followModel = new FollowModel();
        $this->profileModel = new ProfileModel();
    }
    
    /**
     * Afficher la page de suivi
     */
    public function index() {
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les suivis de l'utilisateur
        $follows = $this->followModel->getByUserId($userId);
        
        // Données à passer à la vue
        $data = [
            'title' => 'Suivi des profils',
            'follows' => $follows,
            'currentPage' => 'follow'
        ];
        
        // Afficher la vue
        $this->render('follow/index', $data);
    }
    
    /**
     * Ajouter un profil à suivre
     */
    public function addFollow() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/follow.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $profileId = $_POST['profile_id'] ?? 0;
        
        // Validation
        if (!$profileId) {
            $this->json([
                'success' => false,
                'message' => 'ID de profil requis'
            ]);
            return;
        }
        
        // Vérifier si le profil existe
        $profile = $this->profileModel->getById($profileId);
        if (!$profile) {
            $this->json([
                'success' => false,
                'message' => 'Profil non trouvé'
            ]);
            return;
        }
        
        // Vérifier si le profil est déjà suivi
        if ($this->followModel->isFollowing($userId, $profileId)) {
            $this->json([
                'success' => false,
                'message' => 'Vous suivez déjà ce profil'
            ]);
            return;
        }
        
        // Ajouter le suivi
        $followData = [
            'user_id' => $userId,
            'profile_id' => $profileId,
            'status' => 'pending'
        ];
        
        $result = $this->followModel->create($followData);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Profil ajouté aux suivis avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du profil aux suivis'
            ]);
        }
    }
    
    /**
     * Mettre à jour le statut d'un suivi
     */
    public function updateStatus() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/follow.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $followId = $_POST['follow_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        // Validation
        if (!$followId || !in_array($status, ['pending', 'following', 'unfollowed'])) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres invalides'
            ]);
            return;
        }
        
        // Vérifier si le suivi appartient à l'utilisateur
        $follow = $this->followModel->getById($followId);
        if (!$follow || $follow['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Suivi non trouvé ou accès non autorisé'
            ]);
            return;
        }
        
        // Mettre à jour le statut
        $result = $this->followModel->update($followId, ['status' => $status]);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut'
            ]);
        }
    }
    
    /**
     * Supprimer un suivi
     */
    public function removeFollow() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/follow.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $followId = $_POST['follow_id'] ?? 0;
        
        // Validation
        if (!$followId) {
            $this->json([
                'success' => false,
                'message' => 'ID de suivi requis'
            ]);
            return;
        }
        
        // Vérifier si le suivi appartient à l'utilisateur
        $follow = $this->followModel->getById($followId);
        if (!$follow || $follow['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Suivi non trouvé ou accès non autorisé'
            ]);
            return;
        }
        
        // Supprimer le suivi
        $result = $this->followModel->delete($followId);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Suivi supprimé avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du suivi'
            ]);
        }
    }
}
