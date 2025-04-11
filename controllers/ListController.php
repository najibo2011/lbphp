<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/ListModel.php';
require_once __DIR__ . '/../models/ProfileModel.php';
require_once __DIR__ . '/../includes/Database.php';

/**
 * Contrôleur pour la gestion des listes
 */
class ListController extends Controller {
    private $listModel;
    private $profileModel;
    
    public function __construct() {
        parent::__construct();
        $this->listModel = new ListModel();
        $this->profileModel = new ProfileModel();
    }
    
    /**
     * Afficher la page des listes
     */
    public function index() {
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les listes de l'utilisateur
        $lists = $this->listModel->getByUserId($userId);
        
        // Données à passer à la vue
        $data = [
            'title' => 'Mes Listes',
            'lists' => $lists,
            'currentPage' => 'lists'
        ];
        
        // Afficher la vue
        $this->render('lists/index', $data);
    }
    
    /**
     * Afficher une liste spécifique
     */
    public function view($listId = null) {
        // Récupérer l'ID de la liste depuis l'URL si non fourni
        if (!$listId) {
            $listId = $_GET['id'] ?? 0;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Vérifier si la liste existe
        $list = $this->listModel->getById($listId);
        if (!$list) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Liste non trouvée'
            ];
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Vérifier si l'utilisateur a accès à cette liste
        if ($list['user_id'] != $userId && !$list['is_public']) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Vous n\'avez pas accès à cette liste'
            ];
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Récupérer les profils de la liste
        $profiles = $this->listModel->getProfiles($listId);
        
        // Charger la vue
        include __DIR__ . '/../views/lists/view.php';
    }
    
    /**
     * Créer une nouvelle liste
     */
    public function create() {
        // Vérifier si la requête est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Simuler un utilisateur connecté pour la démo
            $userId = 2; // Utilisateur de test
            
            // Récupérer les données du formulaire
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $isPublic = isset($_POST['is_public']) ? 1 : 0;
            
            // Validation simple
            if (empty($name)) {
                $this->redirect($this->config['app_url'] . '/lists.php?error=name_required');
                return;
            }
            
            // Créer la liste
            $listData = [
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'is_public' => $isPublic
            ];
            
            try {
                $listId = $this->listModel->create($listData);
                
                // Rediriger vers la page des listes
                $this->redirect($this->config['app_url'] . '/lists.php?success=created');
            } catch (Exception $e) {
                // En cas d'erreur, afficher un message d'erreur
                $this->setFlash('error', 'Erreur lors de la création de la liste: ' . $e->getMessage());
                $this->redirect($this->config['app_url'] . '/create_list.php');
            }
            return;
        }
        
        // Données à passer à la vue
        $data = [
            'title' => 'Créer une liste',
            'currentPage' => 'lists',
            'dbInstance' => Database::getInstance()
        ];
        
        // Afficher la vue
        $this->render('lists/create', $data);
    }
    
    /**
     * Ajouter un profil à une liste
     */
    public function addProfile() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $listId = $_POST['list_id'] ?? 0;
        $profileId = $_POST['profile_id'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        // Validation
        if (!$listId || !$profileId) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres invalides'
            ]);
            return;
        }
        
        // Vérifier si la liste appartient à l'utilisateur
        $list = $this->listModel->getById($listId);
        if (!$list || $list['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Liste non trouvée ou accès non autorisé'
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
        
        // Vérifier si le profil est déjà dans la liste
        if ($this->listModel->profileInList($listId, $profileId)) {
            $this->json([
                'success' => false,
                'message' => 'Ce profil est déjà dans la liste'
            ]);
            return;
        }
        
        // Ajouter le profil à la liste
        $result = $this->listModel->addProfile($listId, $profileId, $notes);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Profil ajouté à la liste avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du profil à la liste'
            ]);
        }
    }
    
    /**
     * Supprimer un profil d'une liste
     */
    public function removeProfile() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $listId = $_POST['list_id'] ?? 0;
        $profileId = $_POST['profile_id'] ?? 0;
        
        // Validation
        if (!$listId || !$profileId) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres invalides'
            ]);
            return;
        }
        
        // Vérifier si la liste appartient à l'utilisateur
        $list = $this->listModel->getById($listId);
        if (!$list || $list['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Liste non trouvée ou accès non autorisé'
            ]);
            return;
        }
        
        // Supprimer le profil de la liste
        $result = $this->listModel->removeProfile($listId, $profileId);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Profil supprimé de la liste avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du profil de la liste'
            ]);
        }
    }
    
    /**
     * Récupérer les listes de l'utilisateur (AJAX)
     */
    public function getUserLists() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les listes de l'utilisateur
        $lists = $this->listModel->getByUserId($userId);
        
        $this->json([
            'success' => true,
            'lists' => $lists
        ]);
    }
    
    /**
     * Créer une liste via AJAX
     */
    public function createAjax() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données du formulaire
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 0;
        
        // Validation simple
        if (empty($name)) {
            $this->json([
                'success' => false,
                'message' => 'Le nom de la liste est obligatoire'
            ]);
            return;
        }
        
        // Créer la liste
        $listData = [
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'is_public' => $isPublic
        ];
        
        $listId = $this->listModel->create($listData);
        
        if ($listId) {
            $this->json([
                'success' => true,
                'message' => 'Liste créée avec succès',
                'list_id' => $listId
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la liste'
            ]);
        }
    }
    
    /**
     * Ajouter plusieurs profils à une liste
     */
    public function addProfiles() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $listId = $_POST['list_id'] ?? 0;
        $profileIds = $_POST['profile_ids'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Convertir la chaîne d'IDs en tableau
        if (!empty($profileIds)) {
            $profileIds = explode(',', $profileIds);
        } else {
            $profileIds = [];
        }
        
        // Validation
        if (!$listId || empty($profileIds)) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres invalides'
            ]);
            return;
        }
        
        // Vérifier si la liste appartient à l'utilisateur
        $list = $this->listModel->getById($listId);
        if (!$list || $list['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Liste non trouvée ou accès non autorisé'
            ]);
            return;
        }
        
        // Ajouter chaque profil à la liste
        $added = 0;
        $alreadyInList = 0;
        
        foreach ($profileIds as $profileId) {
            // Vérifier si le profil existe
            $profile = $this->profileModel->getById($profileId);
            if (!$profile) {
                continue; // Passer au suivant
            }
            
            // Vérifier si le profil est déjà dans la liste
            if ($this->listModel->profileInList($listId, $profileId)) {
                $alreadyInList++;
                continue; // Passer au suivant
            }
            
            // Ajouter le profil à la liste
            $result = $this->listModel->addProfile($listId, $profileId, $notes);
            if ($result) {
                $added++;
            }
        }
        
        // Construire le message de réponse
        $message = '';
        if ($added > 0) {
            $message .= $added . ' profil(s) ajouté(s) à la liste avec succès. ';
        }
        if ($alreadyInList > 0) {
            $message .= $alreadyInList . ' profil(s) déjà présent(s) dans la liste.';
        }
        
        $this->json([
            'success' => $added > 0,
            'message' => $message,
            'added' => $added,
            'alreadyInList' => $alreadyInList
        ]);
    }
    
    /**
     * Construire une URL avec les paramètres actuels
     */
    public function buildUrl($params = []) {
        $url = $_SERVER['PHP_SELF'] . '?';
        $queryParams = $_GET;
        
        // Fusionner les paramètres existants avec les nouveaux
        foreach ($params as $key => $value) {
            if ($value === '') {
                unset($queryParams[$key]);
            } else {
                $queryParams[$key] = $value;
            }
        }
        
        // Construire la chaîne de requête
        $queryString = http_build_query($queryParams);
        
        return $url . $queryString;
    }
    
    /**
     * Mettre à jour les notes d'un profil dans une liste
     */
    public function updateProfileNotes() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $listId = $_POST['list_id'] ?? 0;
        $profileId = $_POST['profile_id'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        // Validation
        if (!$listId || !$profileId) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres invalides'
            ]);
            return;
        }
        
        // Vérifier si la liste appartient à l'utilisateur
        $list = $this->listModel->getById($listId);
        if (!$list || $list['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Liste non trouvée ou accès non autorisé'
            ]);
            return;
        }
        
        // Vérifier si le profil est dans la liste
        if (!$this->listModel->profileInList($listId, $profileId)) {
            $this->json([
                'success' => false,
                'message' => 'Ce profil n\'est pas dans la liste'
            ]);
            return;
        }
        
        // Mettre à jour les notes
        $result = $this->listModel->updateProfileNotes($listId, $profileId, $notes);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Notes mises à jour avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des notes'
            ]);
        }
    }
    
    /**
     * Supprimer un profil d'une liste via AJAX
     */
    public function removeProfileAjax() {
        // Vérifier si la requête est AJAX
        if (!$this->isAjax()) {
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Récupérer les données
        $listId = $_POST['list_id'] ?? 0;
        $profileId = $_POST['profile_id'] ?? 0;
        
        // Validation
        if (!$listId || !$profileId) {
            $this->json([
                'success' => false,
                'message' => 'Paramètres invalides'
            ]);
            return;
        }
        
        // Vérifier si la liste appartient à l'utilisateur
        $list = $this->listModel->getById($listId);
        if (!$list || $list['user_id'] != $userId) {
            $this->json([
                'success' => false,
                'message' => 'Liste non trouvée ou accès non autorisé'
            ]);
            return;
        }
        
        // Vérifier si le profil est dans la liste
        if (!$this->listModel->profileInList($listId, $profileId)) {
            $this->json([
                'success' => false,
                'message' => 'Ce profil n\'est pas dans la liste'
            ]);
            return;
        }
        
        // Supprimer le profil de la liste
        $result = $this->listModel->removeProfile($listId, $profileId);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Profil retiré de la liste avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du profil'
            ]);
        }
    }
    
    /**
     * Supprimer une liste
     */
    public function delete($listId = null) {
        // Récupérer l'ID de la liste depuis les paramètres POST si non fourni
        if (!$listId) {
            $listId = $_POST['list_id'] ?? 0;
        }
        
        // Simuler un utilisateur connecté pour la démo
        $userId = 2; // Utilisateur de test
        
        // Vérifier si la liste existe
        $list = $this->listModel->getById($listId);
        if (!$list) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Liste non trouvée'
            ];
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Vérifier si l'utilisateur est le propriétaire de la liste
        if ($list['user_id'] != $userId) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Vous n\'avez pas l\'autorisation de supprimer cette liste'
            ];
            $this->redirect($this->config['app_url'] . '/lists.php');
            return;
        }
        
        // Supprimer la liste
        $result = $this->listModel->delete($listId);
        
        if ($result) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Liste supprimée avec succès'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Erreur lors de la suppression de la liste'
            ];
        }
        
        $this->redirect($this->config['app_url'] . '/lists.php');
    }
}
