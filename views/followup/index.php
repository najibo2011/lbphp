<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pagination
$currentPage = $pagination['currentPage'] ?? 1;
$totalPages = $pagination['totalPages'] ?? 1;
$startItem = $pagination['startItem'] ?? 1;
$endItem = $pagination['endItem'] ?? 0;
$totalProspects = $pagination['totalItems'] ?? 0;
?>

<div class="followup-container">
    <div class="followup-header">
        <h1>Suivi des prospects</h1>
        <div class="header-buttons">
            <button id="exportAllBtn" class="btn-export">
                <i class="fas fa-download"></i> Exporter tous les prospects
            </button>
            <a href="followup.php?action=dashboard" class="btn-dashboard">
                <i class="fas fa-chart-pie"></i> Tableau de bord
            </a>
            <button class="btn-refresh">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <a href="followup.php?action=help" class="btn-help">
                <i class="fas fa-question-circle"></i> Aide
            </a>
        </div>
    </div>
    
    <div class="followup-tabs">
        <?php 
        $tabs = [
            ['label' => 'Tous les prospects', 'href' => 'followup.php', 'icon' => 'fas fa-users'],
            ['label' => 'Tableau de bord', 'href' => 'followup.php?action=dashboard', 'icon' => 'fas fa-chart-pie'],
            ['label' => 'Aide', 'href' => 'followup.php?action=help', 'icon' => 'fas fa-question-circle'],
        ];
        
        foreach ($tabs as $tab): 
            $isActive = $tab['href'] === $_SERVER['REQUEST_URI'];
        ?>
        <div class="tab <?= $isActive ? 'active' : '' ?>">
            <a href="<?= $tab['href'] ?>"><i class="<?= $tab['icon'] ?>"></i> <?= $tab['label'] ?></a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (isset($flash['success'])): ?>
    <div class="alert alert-success">
        <?= $flash['success'] ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($flash['error'])): ?>
    <div class="alert alert-danger">
        <?= $flash['error'] ?>
    </div>
    <?php endif; ?>
    
    <div class="followup-subheader">
        <p>Affichage <?= $startItem ?> à <?= $endItem ?> sur <?= $totalProspects ?> prospects</p>
        
        <div class="filter-container">
            <div class="filter-group search-group">
                <label for="searchInput">Recherche rapide:</label>
                <div class="search-input-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher un nom d'utilisateur...">
                    <button id="searchButton" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="filter-group">
                <label for="statusFilter">Filtrer par statut:</label>
                <select id="statusFilter" class="filter-select">
                    <option value="">Tous les statuts</option>
                    <option value="non contacté">Non contacté</option>
                    <option value="contacté">Contacté</option>
                    <option value="intéressé">Intéressé</option>
                    <option value="pas intéressé">Pas intéressé</option>
                    <option value="client">Client</option>
                    <option value="à relancer">À relancer</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="listFilter">Filtrer par liste:</label>
                <select id="listFilter" class="filter-select">
                    <option value="">Toutes les listes</option>
                    <?php foreach ($lists as $list): ?>
                    <option value="<?= $list['id'] ?>"><?= htmlspecialchars($list['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sortBy">Trier par:</label>
                <select id="sortBy" class="filter-select">
                    <option value="last_interaction">Dernière interaction</option>
                    <option value="username">Nom d'utilisateur</option>
                    <option value="status">Statut</option>
                    <option value="created_at">Date d'ajout</option>
                </select>
                <select id="sortOrder" class="filter-select">
                    <option value="desc">Décroissant</option>
                    <option value="asc">Croissant</option>
                </select>
            </div>
            
            <button id="applyFilters" class="btn-apply-filters">
                <i class="fas fa-filter"></i> Appliquer
            </button>
        </div>
    </div>
    
    <div class="followup-table-container">
        <table class="followup-table">
            <thead>
                <tr>
                    <th class="account-col">COMPTE INSTAGRAM</th>
                    <th class="list-col">LISTE</th>
                    <th class="status-col">STATUT</th>
                    <th class="date-col">DERNIÈRE INTERACTION</th>
                    <th class="actions-col">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($profiles)): ?>
                <tr>
                    <td colspan="<?= count($dates) + 4 ?>" class="empty-table">
                        <p>Aucun prospect dans votre suivi pour le moment.</p>
                        <p>Ajoutez des profils depuis vos listes pour commencer à les suivre.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($profiles as $profile): ?>
                <tr data-followup-id="<?= $profile['id'] ?>">
                    <td class="account-cell">
                        <a href="#" class="account-link"><?= htmlspecialchars($profile['username']) ?></a>
                        <p class="account-description"><?= htmlspecialchars($profile['description']) ?></p>
                    </td>
                    <td class="list-cell"><?= htmlspecialchars($profile['list_name']) ?></td>
                    <td class="status-cell">
                        <span class="status-badge <?= str_replace(' ', '-', $profile['status']) ?>" data-status="<?= $profile['status'] ?>">
                            <?= htmlspecialchars($profile['status']) ?>
                        </span>
                    </td>
                    <?php 
                    // Afficher les cellules pour chaque date
                    foreach ($dates as $date): 
                        $dateValue = $date['value'];
                        $hasInteraction = isset($interactions[$profile['id']][$dateValue]);
                        $interactionClass = $hasInteraction ? 'has-action' : '';
                    ?>
                    <td class="date-cell <?= $interactionClass ?>" data-date="<?= $dateValue ?>">
                        <?php if ($hasInteraction): 
                            $dayInteractions = $interactions[$profile['id']][$dateValue];
                            $firstInteraction = $dayInteractions[0];
                            $interactionType = htmlspecialchars($firstInteraction['type']);
                            $interactionClass = str_replace(' ', '-', strtolower($interactionType));
                        ?>
                            <span class="action-badge <?= $interactionClass ?>" title="<?= htmlspecialchars($firstInteraction['notes']) ?>">
                                <?= $interactionType ?>
                                <?php if (count($dayInteractions) > 1): ?>
                                <span class="interaction-count"><?= count($dayInteractions) ?></span>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <i class="far fa-circle"></i>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="actions-col">
                        <div class="action-buttons">
                            <button class="btn-action btn-add-interaction" data-followup-id="<?= $profile['id'] ?>" data-username="<?= htmlspecialchars($profile['username']) ?>">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button class="btn-action btn-view-interactions" data-followup-id="<?= $profile['id'] ?>" data-username="<?= htmlspecialchars($profile['username']) ?>">
                                <i class="fas fa-history"></i>
                            </button>
                            <button class="btn-action btn-export-interactions" data-followup-id="<?= $profile['id'] ?>" data-username="<?= htmlspecialchars($profile['username']) ?>">
                                <i class="fas fa-file-export"></i>
                            </button>
                            <button class="btn-action btn-delete-followup" data-followup-id="<?= $profile['id'] ?>" data-username="<?= htmlspecialchars($profile['username']) ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="followup-pagination">
        <a href="?page=<?= max(1, $currentPage - 1) ?>" class="pagination-prev <?= $currentPage <= 1 ? 'disabled' : '' ?>">
            <i class="fas fa-chevron-left"></i> Précédent
        </a>
        
        <div class="pagination-numbers">
            <?php
            // Afficher les numéros de page
            $startPage = max(1, $currentPage - 2);
            $endPage = min($startPage + 4, $totalPages);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?page=<?= $i ?>" class="pagination-number <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        
        <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="pagination-next <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
            Suivant <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<!-- Modales -->
<!-- Modale pour modifier le statut -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier le statut</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="statusForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="followup_id" id="statusFollowupId">
                
                <div class="form-group">
                    <label for="status">Statut:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="non contacté">Non contacté</option>
                        <option value="contacté">Contacté</option>
                        <option value="intéressé">Intéressé</option>
                        <option value="pas intéressé">Pas intéressé</option>
                        <option value="client">Client</option>
                        <option value="à relancer">À relancer</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer</button>
                    <button type="button" class="btn-secondary close-modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modale pour ajouter une interaction -->
<div class="modal" id="interactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter une interaction</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="interactionForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="followup_id" id="interactionFollowupId">
                <input type="hidden" name="date" id="interactionDate">
                
                <div class="form-group">
                    <label for="type">Type d'interaction:</label>
                    <select name="type" id="type" class="form-control">
                        <option value="1er message">1er message</option>
                        <option value="Relance">Relance</option>
                        <option value="Appel">Appel</option>
                        <option value="Réunion">Réunion</option>
                        <option value="Email">Email</option>
                        <option value="Pas intéressé">Pas intéressé</option>
                        <option value="Intéressé">Intéressé</option>
                        <option value="Vente">Vente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Détails de l'interaction..."></textarea>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-container">
                        <input type="checkbox" id="scheduleInteraction" name="schedule_interaction">
                        <label for="scheduleInteraction">Planifier pour une date future</label>
                    </div>
                </div>
                
                <div class="form-group scheduled-date-group" style="display: none;">
                    <label for="scheduledDate">Date planifiée:</label>
                    <input type="date" name="scheduled_date" id="scheduledDate" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-container">
                        <input type="checkbox" id="updateStatus" name="update_status" value="1">
                        <label for="updateStatus">Mettre à jour le statut</label>
                    </div>
                </div>
                
                <div class="form-group status-update-group" style="display: none;">
                    <label for="interactionStatus">Nouveau statut:</label>
                    <select name="status" id="interactionStatus" class="form-control">
                        <option value="non contacté">Non contacté</option>
                        <option value="contacté">Contacté</option>
                        <option value="intéressé">Intéressé</option>
                        <option value="pas intéressé">Pas intéressé</option>
                        <option value="client">Client</option>
                        <option value="à relancer">À relancer</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer</button>
                    <button type="button" class="btn-secondary close-modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modale pour confirmer la suppression -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer ce prospect de votre suivi ?</p>
            <p>Cette action est irréversible.</p>
            
            <form id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="followup_id" id="deleteFollowupId">
                
                <div class="form-actions">
                    <button type="submit" class="btn-danger">Supprimer</button>
                    <button type="button" class="btn-secondary close-modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modale pour voir les interactions d'une journée -->
<div class="modal" id="viewInteractionsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Interactions du <span id="interactionDateDisplay"></span></h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="interactionsList"></div>
            
            <div class="form-actions">
                <button type="button" class="btn-primary" id="addInteractionBtn">Ajouter une interaction</button>
                <button type="button" class="btn-secondary close-modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="/assets/js/followup.js"></script>
<script src="/assets/js/followup-export.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Exporter tous les prospects
        document.getElementById('exportAllBtn').addEventListener('click', function() {
            fetch('followup.php?action=getAllFollowups&csrf_token=<?= $_SESSION['csrf_token'] ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        exportFollowupData(data.followups, 'prospects_suivis_<?= date('Y-m-d') ?>.csv');
                    } else {
                        showNotification(data.message || 'Erreur lors de l\'export', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('Erreur lors de l\'export', 'error');
                });
        });
        
        // Exporter les interactions d'un prospect
        document.querySelectorAll('.btn-export-interactions').forEach(button => {
            button.addEventListener('click', function() {
                const followupId = this.getAttribute('data-followup-id');
                const username = this.getAttribute('data-username');
                exportInteractions(followupId, username);
            });
        });
        
        // Recherche rapide
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const tableRows = document.querySelectorAll('.followup-table tbody tr');
        
        // Fonction de recherche
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                // Si le champ de recherche est vide, afficher toutes les lignes
                tableRows.forEach(row => {
                    row.style.display = '';
                });
                return;
            }
            
            // Filtrer les lignes du tableau
            tableRows.forEach(row => {
                const username = row.querySelector('td:first-child').textContent.toLowerCase();
                
                if (username.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Événement de clic sur le bouton de recherche
        searchButton.addEventListener('click', performSearch);
        
        // Événement de frappe dans le champ de recherche (recherche en temps réel)
        searchInput.addEventListener('keyup', function(event) {
            // Recherche immédiate
            performSearch();
            
            // Si la touche Entrée est pressée, empêcher le rechargement de la page
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
        
        // Appliquer les filtres
        document.getElementById('applyFilters').addEventListener('click', function() {
            const statusFilter = document.getElementById('statusFilter').value;
            const listFilter = document.getElementById('listFilter').value;
            const sortBy = document.getElementById('sortBy').value;
            const sortOrder = document.getElementById('sortOrder').value;
            
            let url = 'followup.php?page=1';
            
            if (statusFilter) {
                url += '&status=' + encodeURIComponent(statusFilter);
            }
            
            if (listFilter) {
                url += '&list_id=' + encodeURIComponent(listFilter);
            }
            
            if (sortBy) {
                url += '&sort_by=' + encodeURIComponent(sortBy);
            }
            
            if (sortOrder) {
                url += '&sort_order=' + encodeURIComponent(sortOrder);
            }
            
            window.location.href = url;
        });
        
        // Pré-remplir les filtres avec les valeurs de l'URL
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('status')) {
            document.getElementById('statusFilter').value = urlParams.get('status');
        }
        
        if (urlParams.has('list_id')) {
            document.getElementById('listFilter').value = urlParams.get('list_id');
        }
        
        if (urlParams.has('sort_by')) {
            document.getElementById('sortBy').value = urlParams.get('sort_by');
        }
        
        if (urlParams.has('sort_order')) {
            document.getElementById('sortOrder').value = urlParams.get('sort_order');
        }
    });
</script>
