<?php
require_once __DIR__ . '/../includes/Controller.php';
require_once __DIR__ . '/../models/ProfileModel.php';
require_once __DIR__ . '/../models/ProfileSearchModel.php';

/**
 * Contrôleur pour la recherche de profils
 */
class SearchController extends Controller {
    private $profileModel;
    private $profileSearchModel;
    
    public function __construct() {
        parent::__construct();
        $this->profileModel = new ProfileModel();
        $this->profileSearchModel = new ProfileSearchModel();
    }
    
    /**
     * Afficher la page de recherche
     */
    public function index() {
        // Nombre de recherches effectuées
        $searchCount = isset($_SESSION['search_count']) ? $_SESSION['search_count'] : 0;
        $maxSearches = 30; // Limite de recherches
        
        // Données à passer à la vue
        $data = [
            'searchCount' => $searchCount,
            'maxSearches' => $maxSearches,
            'title' => 'Recherche de profils',
            'activeMenu' => 'search'
        ];
        
        // Afficher la vue
        $this->render('search/index', $data);
    }
    
    /**
     * Traiter la recherche
     */
    public function search() {
        // Récupérer les paramètres de recherche (POST ou GET)
        $nameKeywords = isset($_POST['name_keywords']) ? $_POST['name_keywords'] : (isset($_GET['name_keywords']) ? $_GET['name_keywords'] : '');
        $bioKeywords = isset($_POST['bio_keywords']) ? $_POST['bio_keywords'] : (isset($_GET['bio_keywords']) ? $_GET['bio_keywords'] : '');
        $minFollowers = isset($_POST['min_followers']) && !empty($_POST['min_followers']) ? (int)$_POST['min_followers'] : (isset($_GET['min_followers']) && !empty($_GET['min_followers']) ? (int)$_GET['min_followers'] : 0);
        $maxFollowers = isset($_POST['max_followers']) && !empty($_POST['max_followers']) && $_POST['max_followers'] !== '∞' 
                        ? (int)$_POST['max_followers'] : (isset($_GET['max_followers']) && !empty($_GET['max_followers']) && $_GET['max_followers'] !== '∞' 
                        ? (int)$_GET['max_followers'] : PHP_INT_MAX);
        
        // Paramètres de pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 12; // Nombre de profils par page
        $offset = ($page - 1) * $perPage;
        
        // Vérifier si c'est une requête AJAX
        if ($this->isAjax()) {
            // Effectuer la recherche avec le nouveau modèle
            $results = $this->profileSearchModel->searchProfiles(
                $nameKeywords,
                $bioKeywords,
                $minFollowers,
                $maxFollowers,
                $perPage,
                $offset
            );
            
            // Compter le nombre total de résultats pour la pagination
            $totalResults = $this->profileSearchModel->countSearchResults(
                $nameKeywords,
                $bioKeywords,
                $minFollowers,
                $maxFollowers
            );
            
            // Calculer le nombre total de pages
            $totalPages = ceil($totalResults / $perPage);
            
            // Enregistrer la recherche si l'utilisateur est connecté
            if (isset($_SESSION['user_id'])) {
                $searchParams = [
                    'name' => $nameKeywords,
                    'bio' => $bioKeywords,
                    'min_followers' => $minFollowers,
                    'max_followers' => $maxFollowers
                ];
                
                $this->profileSearchModel->saveSearch($_SESSION['user_id'], $searchParams);
            }
            
            // Incrémenter le compteur de recherches
            if (!isset($_SESSION['search_count'])) {
                $_SESSION['search_count'] = 0;
            }
            $_SESSION['search_count']++;
            
            // Retourner les résultats en JSON
            $this->json([
                'success' => true,
                'results' => $results,
                'count' => $totalResults,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'searchCount' => $_SESSION['search_count'],
                'maxSearches' => 30
            ]);
        } else {
            // Effectuer la recherche pour l'affichage de la page de résultats
            $profiles = $this->profileSearchModel->searchProfiles(
                $nameKeywords,
                $bioKeywords,
                $minFollowers,
                $maxFollowers,
                $perPage,
                $offset
            );
            
            // Compter le nombre total de résultats pour la pagination
            $totalResults = $this->profileSearchModel->countSearchResults(
                $nameKeywords,
                $bioKeywords,
                $minFollowers,
                $maxFollowers
            );
            
            // Calculer le nombre total de pages
            $totalPages = ceil($totalResults / $perPage);
            
            // Incrémenter le compteur de recherches
            if (!isset($_SESSION['search_count'])) {
                $_SESSION['search_count'] = 0;
            }
            $_SESSION['search_count']++;
            
            // Données à passer à la vue
            $data = [
                'profiles' => $profiles,
                'searchName' => $nameKeywords,
                'searchBio' => $bioKeywords,
                'minFollowers' => $minFollowers,
                'maxFollowers' => $maxFollowers,
                'searchCount' => $_SESSION['search_count'],
                'maxSearches' => 30,
                'title' => 'Résultats de recherche',
                'activeMenu' => 'search',
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalResults' => $totalResults,
                'perPage' => $perPage
            ];
            
            // Afficher la vue des résultats
            $this->render('search/results', $data);
        }
    }
    
    /**
     * Réinitialiser les recherches
     */
    public function resetSearches() {
        $_SESSION['search_count'] = 0;
        $this->redirect($this->config['app_url']);
    }
    
    /**
     * Construire une URL avec les paramètres de recherche actuels
     * 
     * @param array $overrideParams Paramètres à remplacer ou ajouter
     * @return string URL construite
     */
    public function buildUrl($overrideParams = []) {
        // Récupérer les paramètres actuels
        $params = [
            'controller' => 'search',
            'action' => 'search',
            'name_keywords' => isset($_GET['name_keywords']) ? $_GET['name_keywords'] : '',
            'bio_keywords' => isset($_GET['bio_keywords']) ? $_GET['bio_keywords'] : '',
            'min_followers' => isset($_GET['min_followers']) ? $_GET['min_followers'] : '',
            'max_followers' => isset($_GET['max_followers']) ? $_GET['max_followers'] : '',
            'page' => isset($_GET['page']) ? $_GET['page'] : 1
        ];
        
        // Remplacer les paramètres spécifiés
        foreach ($overrideParams as $key => $value) {
            $params[$key] = $value;
        }
        
        // Construire l'URL
        $url = 'index.php?';
        $queryParams = [];
        
        foreach ($params as $key => $value) {
            if ($value !== '') {
                $queryParams[] = $key . '=' . urlencode($value);
            }
        }
        
        return $url . implode('&', $queryParams);
    }
}
