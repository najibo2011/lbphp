<?php
// Suppression de l'inclusion du header car il est déjà inclus par le système de rendu
// $currentPage = 'followup';
// include_once __DIR__ . '/../layouts/header.php';

// Simuler des données pour la démonstration
$prospects = [
    [
        'compte' => '@emf.training',
        'description' => 'Excuse My French Training | Coaching & Programmation 🇫🇷',
        'liste' => 'coaching',
        'statut' => 'non contacté'
    ],
    [
        'compte' => '@latriumcoachingbordeaux',
        'description' => 'L\'Atrium Coaching Sportif',
        'liste' => 'coaching',
        'statut' => 'non contacté'
    ],
    [
        'compte' => '@coachingcib',
        'description' => 'Fred Marcérou - Coaching Club',
        'liste' => 'coaching',
        'statut' => 'pas intéressé'
    ],
    [
        'compte' => '@yanngondrand',
        'description' => 'Intuitv Coaching',
        'liste' => 'coaching',
        'statut' => 'non contacté'
    ],
    [
        'compte' => '@ibtissam.idrissi.fit',
        'description' => 'Betty Coaching',
        'liste' => 'coaching',
        'statut' => 'non contacté'
    ]
];

// Générer des dates pour les colonnes (7 jours à partir d'aujourd'hui)
$dates = [];
$today = time();
for ($i = 0; $i < 7; $i++) {
    $date = $today + ($i * 86400); // 86400 secondes = 1 jour
    $dates[] = date('d/m', $date);
}

// Pagination
$totalProspects = 365;
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 5;
$totalPages = ceil($totalProspects / $itemsPerPage);
$startItem = ($currentPage - 1) * $itemsPerPage + 1;
$endItem = min($startItem + $itemsPerPage - 1, $totalProspects);
?>

<div class="followup-container">
    <div class="followup-header">
        <h1>Suivi des prospects</h1>
        <button class="btn-refresh">
            <i class="fas fa-sync-alt"></i> Actualiser
        </button>
    </div>
    
    <div class="followup-subheader">
        <p>Affichage <?= $startItem ?> à <?= $endItem ?> sur <?= $totalProspects ?> prospects</p>
    </div>
    
    <div class="followup-table-container">
        <table class="followup-table">
            <thead>
                <tr>
                    <th class="account-col">COMPTE INSTAGRAM</th>
                    <th class="list-col">LISTE</th>
                    <th class="status-col">STATUT</th>
                    <?php foreach ($dates as $date): ?>
                    <th class="date-col"><?= $date ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prospects as $prospect): ?>
                <tr>
                    <td class="account-cell">
                        <a href="#" class="account-link"><?= $prospect['compte'] ?></a>
                        <p class="account-description"><?= $prospect['description'] ?></p>
                    </td>
                    <td class="list-cell"><?= $prospect['liste'] ?></td>
                    <td class="status-cell">
                        <span class="status-badge <?= str_replace(' ', '-', $prospect['statut']) ?>">
                            <?= $prospect['statut'] ?>
                        </span>
                    </td>
                    <?php 
                    // Générer des cellules pour chaque date
                    foreach ($dates as $date): 
                        // Simuler aléatoirement des actions
                        $hasAction = rand(0, 10) < 2; // 20% de chance d'avoir une action
                        $actionType = '';
                        if ($hasAction) {
                            $actions = ['1er message', 'Pas intéressé'];
                            $actionType = $actions[array_rand($actions)];
                        }
                    ?>
                    <td class="date-cell <?= $hasAction ? 'has-action' : '' ?>">
                        <?php if ($hasAction): ?>
                            <span class="action-badge <?= str_replace(' ', '-', strtolower($actionType)) ?>"><?= $actionType ?></span>
                        <?php else: ?>
                            <i class="far fa-circle"></i>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
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

<?php 
// Suppression de l'inclusion du footer car il est déjà inclus par le système de rendu
// include_once __DIR__ . '/../layouts/footer.php'; 
?>
