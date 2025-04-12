<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/ListModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';
require_once __DIR__ . '/../models/FollowupModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';

/**
 * Contrôleur pour la gestion du suivi des prospects
 */
class FollowupController extends Controller {
    private $listModel;
    private $profileModel;
    private $followupModel;
    private $userModel;
    private $security;
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        
        // Initialiser les modèles
        $this->followupModel = new FollowupModel();
        $this->listModel = new ListModel();
        $this->profileModel = new ProfileModel();
        $this->userModel = new UserModel();
        $this->security = new Security();
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login.php');
        }
    }
    
    /**
     * Affiche la page de suivi des prospects
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Récupérer l'ID de l'utilisateur connecté
        $userId = $_SESSION['user_id'];
        
        // Récupérer les paramètres de pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10; // Nombre de profils par page
        
        // Récupérer les paramètres de filtrage et de tri
        $filters = [
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'list_id' => isset($_GET['list_id']) ? (int)$_GET['list_id'] : null
        ];
        
        $sort = [
            'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : 'last_interaction',
            'sort_order' => isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc'
        ];
        
        // Valider les paramètres de tri
        $allowedSortFields = ['last_interaction', 'username', 'status', 'created_at'];
        if (!in_array($sort['sort_by'], $allowedSortFields)) {
            $sort['sort_by'] = 'last_interaction';
        }
        
        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sort['sort_order'], $allowedSortOrders)) {
            $sort['sort_order'] = 'desc';
        }
        
        // Récupérer les listes de l'utilisateur
        $lists = $this->listModel->getByUserId($userId);
        
        // Récupérer le nombre total de profils suivis
        $totalProfiles = $this->followupModel->countByUserId($userId, $filters);
        
        // Calculer le nombre total de pages
        $totalPages = ceil($totalProfiles / $limit);
        
        // Ajuster la page courante si nécessaire
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        
        // Récupérer les profils suivis avec pagination
        $followedProfiles = $this->followupModel->getByUserId($userId, $page, $limit, $filters, $sort);
        
        // Préparer les données de pagination
        $startItem = ($page - 1) * $limit + 1;
        $endItem = min($startItem + $limit - 1, $totalProfiles);
        
        $pagination = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'startItem' => $startItem,
            'endItem' => $endItem,
            'totalItems' => $totalProfiles
        ];
        
        // Afficher la vue
        $this->render('followup/index', [
            'profiles' => $followedProfiles,
            'lists' => $lists,
            'pagination' => $pagination,
            'filters' => $filters,
            'sort' => $sort
        ]);
    }
    
    /**
     * Ajoute un profil au suivi
     */
    public function addToFollowup() {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('followup.php');
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !$this->security->verifyCsrfToken($_POST['csrf_token'])) {
            $this->setFlash('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect('followup.php');
        }
        
        // Récupérer les données
        $profileId = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;
        $listId = isset($_POST['list_id']) ? (int)$_POST['list_id'] : 0;
        $userId = $_SESSION['user_id'];
        
        // Vérifier si le profil existe déjà dans le suivi
        if ($this->followupModel->exists($userId, $profileId)) {
            $this->setFlash('error', 'Ce profil est déjà dans votre suivi.');
            $this->redirect('lists.php?id=' . $listId);
        }
        
        // Ajouter le profil au suivi
        $result = $this->followupModel->create([
            'user_id' => $userId,
            'profile_id' => $profileId,
            'list_id' => $listId,
            'status' => 'non contacté',
            'created_at' => date('Y-m-d H:i:s'),
            'last_interaction' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->setFlash('success', 'Profil ajouté au suivi avec succès.');
        } else {
            $this->setFlash('error', 'Erreur lors de l\'ajout du profil au suivi.');
        }
        
        $this->redirect('lists.php?id=' . $listId);
    }
    
    /**
     * Met à jour le statut d'un profil suivi
     */
    public function updateStatus() {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !$this->security->verifyCsrfToken($_POST['csrf_token'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur de sécurité. Veuillez réessayer.']);
            return;
        }
        
        // Récupérer les données
        $followupId = isset($_POST['followup_id']) ? (int)$_POST['followup_id'] : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $userId = $_SESSION['user_id'];
        
        // Vérifier si le suivi existe et appartient à l'utilisateur
        $followup = $this->followupModel->getById($followupId);
        if (!$followup || $followup['user_id'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Suivi non trouvé ou non autorisé']);
            return;
        }
        
        // Mettre à jour le statut
        $result = $this->followupModel->update($followupId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Statut mis à jour avec succès']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut']);
        }
    }
    
    /**
     * Supprime un profil du suivi
     */
    public function deleteFollowup() {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !$this->security->verifyCsrfToken($_POST['csrf_token'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur de sécurité. Veuillez réessayer.']);
            return;
        }
        
        // Récupérer les données
        $followupId = isset($_POST['followup_id']) ? (int)$_POST['followup_id'] : 0;
        $userId = $_SESSION['user_id'];
        
        // Vérifier si le suivi existe et appartient à l'utilisateur
        $followup = $this->followupModel->getById($followupId);
        if (!$followup || $followup['user_id'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Suivi non trouvé ou non autorisé']);
            return;
        }
        
        // Supprimer le suivi
        $result = $this->followupModel->delete($followupId);
        
        if ($result) {
            $this->jsonResponse(['success' => true, 'message' => 'Profil supprimé du suivi avec succès']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression du profil du suivi']);
        }
    }
    
    /**
     * Ajoute une interaction à un profil suivi
     */
    public function addInteraction() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action.']);
            return;
        }
        
        // Vérifier le CSRF token
        if (!$this->verifyCsrfToken()) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            return;
        }
        
        // Récupérer les données du formulaire
        $followupId = isset($_POST['followup_id']) ? (int)$_POST['followup_id'] : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        $scheduledDate = isset($_POST['scheduled_date']) ? $_POST['scheduled_date'] : null;
        
        // Vérifier que les données sont valides
        if ($followupId <= 0 || empty($type)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            return;
        }
        
        // Vérifier que le suivi appartient à l'utilisateur
        $userId = $_SESSION['user_id'];
        $followup = $this->followupModel->getById($followupId);
        
        if (!$followup || $followup['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Ce suivi n\'existe pas ou ne vous appartient pas.']);
            return;
        }
        
        // Ajouter l'interaction
        $interactionData = [
            'followup_id' => $followupId,
            'type' => $type,
            'notes' => $notes,
            'interaction_date' => $date,
            'scheduled_date' => $scheduledDate,
            'completed' => $scheduledDate ? 0 : 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $interactionId = $this->followupModel->addInteraction($interactionData);
        
        if ($interactionId) {
            // Mettre à jour le statut du suivi si nécessaire
            if (isset($_POST['update_status']) && $_POST['update_status'] == 1) {
                $status = isset($_POST['status']) ? $_POST['status'] : '';
                if (!empty($status)) {
                    $this->followupModel->updateStatus($followupId, $status);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Interaction ajoutée avec succès.', 'interaction_id' => $interactionId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'interaction.']);
        }
    }
    
    /**
     * Récupère les interactions d'un profil suivi pour une date donnée
     */
    public function getInteractions() {
        // Vérifier si la requête est de type GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        // Récupérer les données
        $followupId = isset($_GET['followup_id']) ? (int)$_GET['followup_id'] : 0;
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $userId = $_SESSION['user_id'];
        
        // Vérifier si le suivi existe et appartient à l'utilisateur
        $followup = $this->followupModel->getById($followupId);
        if (!$followup || $followup['user_id'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Suivi non trouvé ou non autorisé']);
            return;
        }
        
        // Récupérer les interactions
        $interactions = $this->followupModel->getInteractionsByDate($followupId, $date);
        
        $this->jsonResponse([
            'success' => true,
            'interactions' => $interactions
        ]);
    }
    
    /**
     * Supprime une interaction
     */
    public function deleteInteraction() {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !$this->security->verifyCsrfToken($_POST['csrf_token'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur de sécurité. Veuillez réessayer.']);
            return;
        }
        
        // Récupérer les données
        $interactionId = isset($_POST['interaction_id']) ? (int)$_POST['interaction_id'] : 0;
        $userId = $_SESSION['user_id'];
        
        // Vérifier si l'interaction existe et appartient à l'utilisateur
        $interaction = $this->followupModel->getInteractionById($interactionId);
        if (!$interaction) {
            $this->jsonResponse(['success' => false, 'message' => 'Interaction non trouvée']);
            return;
        }
        
        // Vérifier si le suivi appartient à l'utilisateur
        $followup = $this->followupModel->getById($interaction['followup_id']);
        if (!$followup || $followup['user_id'] != $userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Interaction non autorisée']);
            return;
        }
        
        // Supprimer l'interaction
        $result = $this->followupModel->deleteInteraction($interactionId);
        
        if ($result) {
            // Mettre à jour la date de dernière interaction du suivi
            $lastInteraction = $this->followupModel->getLastInteraction($interaction['followup_id']);
            $lastInteractionDate = $lastInteraction ? $lastInteraction['created_at'] : date('Y-m-d H:i:s');
            
            $this->followupModel->update($interaction['followup_id'], [
                'last_interaction' => $lastInteractionDate,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->jsonResponse(['success' => true, 'message' => 'Interaction supprimée avec succès']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression de l\'interaction']);
        }
    }
    
    /**
     * Marque une interaction planifiée comme terminée
     */
    public function markInteractionComplete() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Vérifier le CSRF token
        if (!$this->verifyCsrfToken()) {
            $this->setFlash('error', 'Token CSRF invalide.');
            $this->redirect('followup.php?action=dashboard');
            return;
        }
        
        // Récupérer l'ID de l'interaction
        $interactionId = isset($_GET['interaction_id']) ? (int)$_GET['interaction_id'] : 0;
        
        // Vérifier que l'ID est valide
        if ($interactionId <= 0) {
            $this->setFlash('error', 'ID d\'interaction invalide.');
            $this->redirect('followup.php?action=dashboard');
            return;
        }
        
        // Récupérer l'interaction
        $interaction = $this->followupModel->getInteractionById($interactionId);
        
        // Vérifier que l'interaction existe et appartient à l'utilisateur
        if (!$interaction) {
            $this->setFlash('error', 'Cette interaction n\'existe pas.');
            $this->redirect('followup.php?action=dashboard');
            return;
        }
        
        // Récupérer le suivi associé à l'interaction
        $followup = $this->followupModel->getById($interaction['followup_id']);
        
        // Vérifier que le suivi appartient à l'utilisateur
        if (!$followup || $followup['user_id'] != $_SESSION['user_id']) {
            $this->setFlash('error', 'Cette interaction ne vous appartient pas.');
            $this->redirect('followup.php?action=dashboard');
            return;
        }
        
        // Marquer l'interaction comme terminée
        $result = $this->followupModel->updateInteraction($interactionId, [
            'completed' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->setFlash('success', 'Interaction marquée comme terminée avec succès.');
        } else {
            $this->setFlash('error', 'Erreur lors de la mise à jour de l\'interaction.');
        }
        
        $this->redirect('followup.php?action=dashboard');
    }
    
    /**
     * Exporte les données de suivi au format CSV
     */
    public function exportCsv() {
        // Récupérer l'ID de l'utilisateur connecté
        $userId = $_SESSION['user_id'];
        
        // Récupérer tous les profils suivis
        $followedProfiles = $this->followupModel->getAllByUserId($userId);
        
        // Préparer les en-têtes du fichier CSV
        $headers = [
            'ID',
            'Nom d\'utilisateur',
            'Description',
            'Liste',
            'Statut',
            'Date d\'ajout',
            'Dernière interaction'
        ];
        
        // Préparer les données
        $data = [];
        foreach ($followedProfiles as $profile) {
            $data[] = [
                $profile['id'],
                $profile['username'],
                $profile['description'],
                $profile['list_name'],
                $profile['status'],
                $profile['created_at'],
                $profile['last_interaction']
            ];
        }
        
        // Générer le fichier CSV
        $filename = 'suivi_prospects_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Ajouter le BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Ajouter les en-têtes
        fputcsv($output, $headers, ';');
        
        // Ajouter les données
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Récupère les interactions d'un suivi pour l'exportation
     */
    public function getInteractionsForExport() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action.']);
            return;
        }
        
        // Vérifier le CSRF token
        if (!$this->verifyCsrfToken()) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            return;
        }
        
        // Récupérer l'ID du suivi
        $followupId = isset($_GET['followup_id']) ? (int)$_GET['followup_id'] : 0;
        
        // Vérifier que l'ID est valide
        if ($followupId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de suivi invalide.']);
            return;
        }
        
        // Vérifier que le suivi appartient à l'utilisateur
        $userId = $_SESSION['user_id'];
        $followup = $this->followupModel->getById($followupId);
        
        if (!$followup || $followup['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Ce suivi n\'existe pas ou ne vous appartient pas.']);
            return;
        }
        
        // Récupérer les interactions
        $interactions = $this->followupModel->getInteractionsByFollowupId($followupId);
        
        // Retourner les interactions au format JSON
        echo json_encode([
            'success' => true,
            'interactions' => $interactions
        ]);
    }
    
    /**
     * Récupère tous les suivis d'un utilisateur pour l'exportation
     */
    public function getAllFollowups() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action.']);
            return;
        }
        
        // Vérifier le CSRF token
        if (!$this->verifyCsrfToken()) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
            return;
        }
        
        // Récupérer l'ID de l'utilisateur
        $userId = $_SESSION['user_id'];
        
        // Récupérer tous les suivis de l'utilisateur
        $followups = $this->followupModel->getAllByUserId($userId);
        
        // Retourner les suivis au format JSON
        echo json_encode([
            'success' => true,
            'followups' => $followups
        ]);
    }
    
    /**
     * Vérifie le token CSRF
     * 
     * @return bool
     */
    private function verifyCsrfToken() {
        if (!isset($_GET['csrf_token']) && !isset($_POST['csrf_token'])) {
            return false;
        }
        
        $token = isset($_GET['csrf_token']) ? $_GET['csrf_token'] : $_POST['csrf_token'];
        
        // Utiliser la classe Security pour vérifier le token
        return $this->security->verifyCsrfToken($token);
    }
    
    /**
     * Envoie une réponse JSON
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Affiche la page d'aide pour le suivi des prospects
     */
    public function help() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Afficher la page d'aide
        $this->render('followup/help');
    }
    
    /**
     * Affiche le tableau de bord de suivi avec des statistiques
     */
    public function dashboard() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
            return;
        }
        
        // Récupérer l'ID de l'utilisateur
        $userId = $_SESSION['user_id'];
        
        // Récupérer les statistiques de base
        $totalProspects = $this->followupModel->countByUserId($userId);
        $monthlyInteractions = $this->followupModel->countInteractionsByMonth($userId);
        
        // Calculer le taux de conversion (prospects devenus clients)
        $clientCount = $this->followupModel->countByUserIdAndStatus($userId, 'client');
        $conversionRate = $totalProspects > 0 ? round(($clientCount / $totalProspects) * 100, 1) : 0;
        
        // Compter les prospects à relancer
        $toFollowUp = $this->followupModel->countByUserIdAndStatus($userId, 'à relancer');
        
        // Récupérer la distribution par statut
        $statusDistribution = $this->followupModel->getStatusDistribution($userId);
        
        // Récupérer l'activité récente (7 derniers jours)
        $recentActivity = $this->followupModel->getRecentActivity($userId, 7);
        
        // Récupérer les prospects qui nécessitent un suivi
        $prospectsToFollowUp = $this->followupModel->getProspectsToFollowUp($userId);
        
        // Récupérer les interactions planifiées pour aujourd'hui
        $scheduledFollowUps = $this->followupModel->getScheduledFollowUps($userId);
        
        // Générer des recommandations en fonction des données
        $recommendations = $this->generateRecommendations($userId, $statusDistribution, $recentActivity);
        
        // Afficher la vue
        $this->render('followup/dashboard', [
            'totalProspects' => $totalProspects,
            'monthlyInteractions' => $monthlyInteractions,
            'conversionRate' => $conversionRate,
            'toFollowUp' => $toFollowUp,
            'statusDistribution' => $statusDistribution,
            'recentActivity' => $recentActivity,
            'recommendations' => $recommendations,
            'prospectsToFollowUp' => $prospectsToFollowUp,
            'scheduledFollowUps' => $scheduledFollowUps
        ]);
    }
    
    /**
     * Génère des recommandations en fonction des données de suivi
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $statusDistribution Distribution des statuts
     * @param array $recentActivity Activité récente
     * @return array Recommandations
     */
    private function generateRecommendations($userId, $statusDistribution, $recentActivity) {
        $recommendations = [];
        
        // Vérifier si l'utilisateur a des prospects à relancer
        if (isset($statusDistribution['à relancer']) && $statusDistribution['à relancer']['count'] > 0) {
            $recommendations[] = [
                'icon' => 'fas fa-bell',
                'title' => 'Prospects à relancer',
                'description' => 'Vous avez ' . $statusDistribution['à relancer']['count'] . ' prospects à relancer. Pensez à les contacter prochainement.'
            ];
        }
        
        // Vérifier si l'utilisateur a peu d'activité récente
        $recentActivityCount = array_sum(array_column($recentActivity, 'count'));
        if ($recentActivityCount < 5) {
            $recommendations[] = [
                'icon' => 'fas fa-chart-line',
                'title' => 'Augmentez votre activité',
                'description' => 'Votre activité de prospection est faible ces derniers jours. Essayez de contacter au moins 3 prospects par jour.'
            ];
        }
        
        // Vérifier le taux de conversion
        $conversionRate = 0;
        $totalProspects = array_sum(array_column($statusDistribution, 'count'));
        if (isset($statusDistribution['client']) && $totalProspects > 0) {
            $conversionRate = ($statusDistribution['client']['count'] / $totalProspects) * 100;
        }
        
        if ($conversionRate < 10 && $totalProspects > 10) {
            $recommendations[] = [
                'icon' => 'fas fa-percentage',
                'title' => 'Améliorez votre taux de conversion',
                'description' => 'Votre taux de conversion est de ' . round($conversionRate, 1) . '%. Essayez d\'améliorer votre approche ou de cibler des prospects plus qualifiés.'
            ];
        }
        
        // Vérifier si l'utilisateur a beaucoup de prospects non contactés
        if (isset($statusDistribution['non contacté']) && $statusDistribution['non contacté']['count'] > 10) {
            $recommendations[] = [
                'icon' => 'fas fa-user-plus',
                'title' => 'Prospects non contactés',
                'description' => 'Vous avez ' . $statusDistribution['non contacté']['count'] . ' prospects non contactés. Commencez à les contacter pour augmenter vos chances de conversion.'
            ];
        }
        
        return $recommendations;
    }
}
