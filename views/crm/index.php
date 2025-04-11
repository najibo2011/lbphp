<?php
// Suppression de l'inclusion du header car il est déjà inclus par le système de rendu
// $currentPage = 'crm';
// include_once __DIR__ . '/../layouts/header.php';

// Simuler des données pour la démonstration
$lists = [
    [
        'name' => 'test list',
        'total_contacts' => 5,
        'contacted' => 1,
        'stats' => [
            '1er message' => ['count' => 1, 'percent' => 33.33, 'color' => '#4f46e5'],
            '1er follow-up' => ['count' => 1, 'percent' => 33.33, 'color' => '#4f46e5'],
            '2ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '3ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '4ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#ef4444'],
            '5ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#f97316'],
            'RDV' => ['count' => 1, 'percent' => 33.33, 'color' => '#10b981'],
            'Pas intéressé' => ['count' => 0, 'percent' => 0, 'color' => '#ef4444']
        ]
    ],
    [
        'name' => 'test 2',
        'total_contacts' => 4,
        'contacted' => 0,
        'stats' => [
            '1er message' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '1er follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '2ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '3ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '4ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#ef4444'],
            '5ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#f97316'],
            'RDV' => ['count' => 0, 'percent' => 0, 'color' => '#10b981'],
            'Pas intéressé' => ['count' => 0, 'percent' => 0, 'color' => '#ef4444']
        ]
    ],
    [
        'name' => 'test 3',
        'total_contacts' => 12,
        'contacted' => 0,
        'stats' => [
            '1er message' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '1er follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '2ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '3ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#4f46e5'],
            '4ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#ef4444'],
            '5ème follow-up' => ['count' => 0, 'percent' => 0, 'color' => '#f97316'],
            'RDV' => ['count' => 0, 'percent' => 0, 'color' => '#10b981'],
            'Pas intéressé' => ['count' => 0, 'percent' => 0, 'color' => '#ef4444']
        ]
    ]
];

// Calculer le total des messages pour la première liste
$totalMessages = 0;
foreach ($lists[0]['stats'] as $key => $stat) {
    if ($key !== 'Pas intéressé') {
        $totalMessages += $stat['count'];
    }
}
?>

<div class="crm-container">
    <div class="crm-header">
        <h1>Tableau de bord CRM</h1>
        <button class="btn-refresh">
            <i class="fas fa-sync-alt"></i> Actualiser
        </button>
    </div>
    
    <div class="crm-grid">
        <?php foreach ($lists as $list): ?>
        <div class="crm-card">
            <div class="crm-card-header">
                <h2 class="list-name"><?= $list['name'] ?></h2>
                <span class="contact-count"><?= $list['contacted'] ?> / <?= $list['total_contacts'] ?> contactés</span>
            </div>
            
            <div class="crm-card-body">
                <?php foreach ($list['stats'] as $stage => $stat): ?>
                <div class="progress-item">
                    <div class="progress-label">
                        <span class="stage-name"><?= $stage ?></span>
                        <span class="stage-count"><?= $stat['count'] ?> (<?= number_format($stat['percent'], 1) ?>%)</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $stat['percent'] ?>%; background-color: <?= $stat['color'] ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($list === $lists[0]): ?>
            <div class="crm-card-footer">
                <span class="total-messages">Total des messages : <?= $totalMessages ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
// Suppression de l'inclusion du footer car il est déjà inclus par le système de rendu
// include_once __DIR__ . '/../layouts/footer.php'; 
?>
