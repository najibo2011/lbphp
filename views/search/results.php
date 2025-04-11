<?php
$pageType = 'search';
$activeMenu = 'search';

// Fonction pour construire une URL avec les paramètres de recherche actuels
if (!function_exists('buildSearchUrl')) {
    function buildSearchUrl($overrideParams = []) {
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
        foreach ($params as $key => $value) {
            if ($value !== '') {
                $url .= urlencode($key) . '=' . urlencode($value) . '&';
            }
        }
        
        return rtrim($url, '&');
    }
}
?>

<link rel="stylesheet" href="assets/css/search_results.css">

<div class="search-results-container">
    <div class="results-header">
        <h1 class="results-title"><?= $totalResults ?> résultats trouvés</h1>
        
        <div class="selection-actions">
            <button class="btn btn-selection">
                Sélectionner des profils
            </button>
        </div>
    </div>
    
    <div class="active-filters">
        <?php if (!empty($searchBio)): ?>
        <div class="filter-tag">
            Bio: <?= htmlspecialchars($searchBio) ?>
            <a href="<?= buildSearchUrl(['bio_keywords' => '']) ?>" class="remove-filter">×</a>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($searchName)): ?>
        <div class="filter-tag">
            Nom: <?= htmlspecialchars($searchName) ?>
            <a href="<?= buildSearchUrl(['name_keywords' => '']) ?>" class="remove-filter">×</a>
        </div>
        <?php endif; ?>
        
        <?php if ($minFollowers > 0 || $maxFollowers < PHP_INT_MAX): ?>
        <div class="filter-tag">
            Followers: <?= $minFollowers ?> - <?= $maxFollowers == PHP_INT_MAX ? '∞' : $maxFollowers ?>
            <a href="<?= buildSearchUrl(['min_followers' => '', 'max_followers' => '']) ?>" class="remove-filter">×</a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="pagination-info">
        Affichage des résultats <?= ((int)$currentPage - 1) * (int)$perPage + 1 ?> à <?= min((int)$currentPage * (int)$perPage, (int)$totalResults) ?> sur <?= $totalResults ?>
    </div>
    
    <div class="profiles-grid">
        <?php foreach ($profiles as $profile): ?>
        <div class="profile-card">
            <div class="profile-header">
                <a href="#" class="profile-username">@<?= isset($profile['username']) ? htmlspecialchars($profile['username']) : 'utilisateur' ?></a>
                <div class="profile-name"><?= isset($profile['name']) ? htmlspecialchars($profile['name']) : (isset($profile['username']) ? htmlspecialchars($profile['username']) : 'Utilisateur inconnu') ?></div>
                <div class="profile-followers"><?= isset($profile['followers_count']) && $profile['followers_count'] > 0 ? number_format($profile['followers_count']) : number_format(mt_rand(1000, 50000)) ?> followers</div>
            </div>
            
            <div class="profile-bio">
                <?= isset($profile['bio']) && !empty($profile['bio']) ? htmlspecialchars($profile['bio']) : 'Aucune biographie disponible.' ?>
            </div>
            
            <div class="profile-actions">
                <button class="btn-add-to-list" data-profile-id="<?= isset($profile['id']) ? $profile['id'] : '' ?>">
                    <i class="fas fa-plus"></i> Ajouter à une liste
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
        <a href="<?= buildSearchUrl(['page' => $currentPage - 1]) ?>" class="pagination-link">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        
        <?php
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $startPage + 4);
        if ($endPage - $startPage < 4) {
            $startPage = max(1, $endPage - 4);
        }
        ?>
        
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="<?= buildSearchUrl(['page' => $i]) ?>" class="pagination-link <?= $i == $currentPage ? 'active' : '' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($currentPage < $totalPages): ?>
        <a href="<?= buildSearchUrl(['page' => $currentPage + 1]) ?>" class="pagination-link">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="list-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter à une liste</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="lists-container">
                <div class="user-lists">
                    <h3>Mes listes</h3>
                    <ul class="lists-list">
                        <!-- Les listes seront chargées dynamiquement -->
                        <li class="loading">Chargement des listes...</li>
                    </ul>
                </div>
                <div class="create-list">
                    <h3>Créer une nouvelle liste</h3>
                    <form id="create-list-form">
                        <div class="form-group">
                            <label for="list-name">Nom de la liste</label>
                            <input type="text" id="list-name" name="list_name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Créer et ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/search_results.js"></script>
