<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - LeadsBuilder PHP</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/search_results.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Header / Navigation -->
        <header class="app-header">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="LeadsBuilder PHP" class="logo-image">
                    <span class="logo-text">LeadsBuilder</span>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li class="<?= $activeMenu === 'search' ? 'active' : '' ?>">
                        <a href="index.php"><i class="fas fa-search"></i> Recherche</a>
                    </li>
                    <li class="<?= $activeMenu === 'lists' ? 'active' : '' ?>">
                        <a href="lists.php"><i class="fas fa-list"></i> Listes</a>
                    </li>
                    <li class="<?= $activeMenu === 'follow' ? 'active' : '' ?>">
                        <a href="follow.php"><i class="fas fa-user-plus"></i> Suivi</a>
                    </li>
                    <li class="<?= $activeMenu === 'crm' ? 'active' : '' ?>">
                        <a href="crm.php"><i class="fas fa-chart-pie"></i> CRM</a>
                    </li>
                </ul>
            </nav>
            <div class="user-menu">
                <a href="profile.php" class="user-profile">
                    <span class="user-email">exemple@gmail.com</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="app-content">
            <div class="search-results-container">
                <div class="search-results-header">
                    <h2><?= $totalResults ?> résultats trouvés</h2>
                    <div class="search-actions">
                        <button class="btn btn-outline" id="select-all-btn">
                            <i class="fas fa-check-square"></i> Tout sélectionner
                        </button>
                        <button class="btn btn-outline" id="select-profiles-btn">
                            <i class="fas fa-tasks"></i> Sélectionner
                        </button>
                    </div>
                </div>
                
                <!-- Informations sur les résultats -->
                <div class="results-info">
                    Page <?= $currentPage ?> sur <?= $totalPages ?>, affichage de 
                    <?= ($currentPage - 1) * $perPage + 1 ?>-<?= min($currentPage * $perPage, $totalResults) ?> 
                    sur <?= $totalResults ?> résultats
                </div>
                
                <!-- Filtres actifs -->
                <div class="active-filters">
                    <?php if (!empty($searchName)): ?>
                        <div class="filter-tag">
                            <span class="filter-label">Nom:</span>
                            <span class="filter-value"><?= htmlspecialchars($searchName) ?></span>
                            <a href="<?= $controller->buildUrl(['name_keywords' => '']) ?>" class="filter-remove" data-filter-type="name_keywords">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($searchBio)): ?>
                        <div class="filter-tag">
                            <span class="filter-label">Bio:</span>
                            <span class="filter-value"><?= htmlspecialchars($searchBio) ?></span>
                            <a href="<?= $controller->buildUrl(['bio_keywords' => '']) ?>" class="filter-remove" data-filter-type="bio_keywords">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($minFollowers > 0): ?>
                        <div class="filter-tag">
                            <span class="filter-label">Min. followers:</span>
                            <span class="filter-value"><?= number_format($minFollowers, 0, ',', ' ') ?></span>
                            <a href="<?= $controller->buildUrl(['min_followers' => '']) ?>" class="filter-remove" data-filter-type="min_followers">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($maxFollowers < PHP_INT_MAX): ?>
                        <div class="filter-tag">
                            <span class="filter-label">Max. followers:</span>
                            <span class="filter-value"><?= number_format($maxFollowers, 0, ',', ' ') ?></span>
                            <a href="<?= $controller->buildUrl(['max_followers' => '']) ?>" class="filter-remove" data-filter-type="max_followers">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($searchName) || !empty($searchBio) || $minFollowers > 0 || $maxFollowers < PHP_INT_MAX): ?>
                        <a href="index.php?controller=search&action=search" class="clear-all-filters">Effacer tous les filtres</a>
                    <?php endif; ?>
                </div>
                
                <!-- Grille de profils -->
                <div class="profiles-grid">
                    <?php if (empty($profiles)): ?>
                        <div class="empty-state">
                            <i class="fas fa-search fa-3x"></i>
                            <p>Aucun profil ne correspond à vos critères de recherche.</p>
                            <a href="index.php" class="btn btn-primary">Modifier la recherche</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($profiles as $profile): ?>
                            <div class="profile-card" data-profile-id="<?= $profile['id'] ?>">
                                <div class="profile-selection">
                                    <input type="checkbox" class="profile-checkbox" id="profile-<?= $profile['id'] ?>">
                                    <label for="profile-<?= $profile['id'] ?>"></label>
                                </div>
                                <div class="profile-header">
                                    <div class="profile-avatar">
                                        <?php if (!empty($profile['avatar_url'])): ?>
                                            <img src="<?= htmlspecialchars($profile['avatar_url']) ?>" alt="<?= htmlspecialchars($profile['username']) ?>">
                                        <?php else: ?>
                                            <div class="avatar-placeholder"><?= strtoupper(substr($profile['username'], 0, 1)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="profile-name">
                                        <h3><?= htmlspecialchars($profile['username']) ?></h3>
                                        <span class="profile-location">
                                            <?= !empty($profile['location']) ? htmlspecialchars($profile['location']) : 'Lieu non spécifié' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="profile-body">
                                    <div class="profile-bio">
                                        <?= !empty($profile['bio']) ? htmlspecialchars($profile['bio']) : 'Aucune biographie disponible.' ?>
                                    </div>
                                    <div class="profile-stats">
                                        <div class="stat-item">
                                            <span class="stat-value"><?= number_format($profile['followers'] ?? 0, 0, ',', ' ') ?></span>
                                            <span class="stat-label">followers</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value"><?= number_format($profile['following'] ?? 0, 0, ',', ' ') ?></span>
                                            <span class="stat-label">following</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value"><?= number_format($profile['posts'] ?? 0, 0, ',', ' ') ?></span>
                                            <span class="stat-label">posts</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-footer">
                                    <a href="profile.php?id=<?= $profile['id'] ?>" class="btn btn-outline">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <button class="btn btn-outline add-to-list-single" data-profile-id="<?= $profile['id'] ?>">
                                        <i class="fas fa-plus"></i> Ajouter à une liste
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= $controller->buildUrl(['page' => $currentPage - 1]) ?>" class="pagination-item prev">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    <?php else: ?>
                        <span class="pagination-item prev disabled">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </span>
                    <?php endif; ?>
                    
                    <?php
                    // Afficher un nombre limité de pages
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $startPage + 4);
                    
                    // Ajuster le début si on est proche de la fin
                    if ($endPage - $startPage < 4) {
                        $startPage = max(1, $endPage - 4);
                    }
                    
                    // Première page
                    if ($startPage > 1) {
                        echo '<a href="' . $controller->buildUrl(['page' => 1]) . '" class="pagination-item">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="pagination-ellipsis">...</span>';
                        }
                    }
                    
                    // Pages numérotées
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $activeClass = $i === $currentPage ? 'active' : '';
                        echo '<a href="' . $controller->buildUrl(['page' => $i]) . '" class="pagination-item ' . $activeClass . '">' . $i . '</a>';
                    }
                    
                    // Dernière page
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="pagination-ellipsis">...</span>';
                        }
                        echo '<a href="' . $controller->buildUrl(['page' => $totalPages]) . '" class="pagination-item">' . $totalPages . '</a>';
                    }
                    ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= $controller->buildUrl(['page' => $currentPage + 1]) ?>" class="pagination-item next">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-item next disabled">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Actions en masse -->
                <div class="bulk-actions">
                    <div class="selected-count">
                        <span class="selected-count">0</span> profils sélectionnés
                    </div>
                    <div class="bulk-buttons">
                        <button class="btn btn-primary" id="add-selected-to-list">
                            <i class="fas fa-plus"></i> Ajouter à une liste
                        </button>
                        <button class="btn btn-outline" id="deselect-all-btn">
                            <i class="fas fa-times-circle"></i> Désélectionner tout
                        </button>
                        <button class="btn btn-outline" id="cancel-selection">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter à une liste -->
    <div id="add-to-list-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter à une liste</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-to-list-form">
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="create-list-toggle">
                            <span class="checkmark"></span>
                            Créer une nouvelle liste
                        </label>
                    </div>
                    
                    <div id="existing-list-section">
                        <div class="form-group">
                            <label for="list-select">Sélectionnez une liste</label>
                            <select id="list-select" class="form-control" required>
                                <option value="">Chargement des listes...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="new-list-section" style="display: none;">
                        <div class="form-group">
                            <label for="new-list-name">Nom de la liste</label>
                            <input type="text" id="new-list-name" class="form-control" placeholder="Entrez un nom pour la liste">
                        </div>
                        <div class="form-group">
                            <label for="new-list-description">Description (optionnelle)</label>
                            <textarea id="new-list-description" class="form-control" placeholder="Décrivez cette liste"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" id="new-list-public">
                                <span class="checkmark"></span>
                                Liste publique
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-notes">Notes (optionnelles)</label>
                        <textarea id="profile-notes" class="form-control" placeholder="Ajoutez des notes sur ces profils"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                <button type="submit" form="add-to-list-form" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>

    <script src="assets/js/common.js"></script>
    <script src="assets/js/search_results.js"></script>
    <script src="assets/js/list_management.js"></script>
</body>
</html>
