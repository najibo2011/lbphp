<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Tableau de bord de suivi</h1>
        <a href="followup.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour au suivi
        </a>
    </div>
    
    <div class="followup-tabs">
        <?php 
        $tabs = [
            ['label' => 'Tous les prospects', 'href' => 'followup.php', 'icon' => 'fas fa-users'],
            ['label' => 'Tableau de bord', 'href' => 'followup.php?action=dashboard', 'icon' => 'fas fa-chart-pie'],
            ['label' => 'Aide', 'href' => 'followup.php?action=help', 'icon' => 'fas fa-question-circle'],
        ];
        
        $currentUrl = $_SERVER['REQUEST_URI'];
        foreach ($tabs as $tab): 
            $isActive = strpos($currentUrl, $tab['href']) !== false;
            if ($tab['href'] === 'followup.php' && $currentUrl !== 'followup.php') {
                $isActive = false;
            }
        ?>
        <div class="tab <?= $isActive ? 'active' : '' ?>">
            <a href="<?= $tab['href'] ?>"><i class="<?= $tab['icon'] ?>"></i> <?= $tab['label'] ?></a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="dashboard-stats">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-content">
                <h3>Total des prospects</h3>
                <p class="stats-number"><?= $totalProspects ?></p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-comment"></i>
            </div>
            <div class="stats-content">
                <h3>Interactions ce mois</h3>
                <p class="stats-number"><?= $monthlyInteractions ?></p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stats-content">
                <h3>Taux de conversion</h3>
                <p class="stats-number"><?= $conversionRate ?>%</p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
                <h3>À relancer</h3>
                <p class="stats-number"><?= $toFollowUp ?></p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-alerts">
        <h2><i class="fas fa-bell"></i> Alertes et rappels</h2>
        
        <div class="alerts-container">
            <div class="alert-section">
                <h3>Prospects à relancer</h3>
                <?php if (empty($prospectsToFollowUp)): ?>
                    <p class="empty-alert">Aucun prospect à relancer pour le moment.</p>
                <?php else: ?>
                    <div class="alert-list">
                        <?php foreach ($prospectsToFollowUp as $prospect): ?>
                            <div class="alert-item">
                                <div class="alert-icon">
                                    <i class="fas fa-user-clock"></i>
                                </div>
                                <div class="alert-content">
                                    <h4><?= htmlspecialchars($prospect['username']) ?></h4>
                                    <p class="alert-meta">
                                        <span class="alert-list-name"><?= htmlspecialchars($prospect['list_name']) ?></span>
                                        <span class="alert-status"><?= htmlspecialchars($prospect['status']) ?></span>
                                    </p>
                                    <p class="alert-description"><?= htmlspecialchars(substr($prospect['description'], 0, 100)) . (strlen($prospect['description']) > 100 ? '...' : '') ?></p>
                                    <?php if ($prospect['last_interaction']): ?>
                                        <p class="alert-last-interaction">Dernière interaction: <?= date('d/m/Y', strtotime($prospect['last_interaction'])) ?></p>
                                    <?php else: ?>
                                        <p class="alert-last-interaction">Aucune interaction</p>
                                    <?php endif; ?>
                                </div>
                                <div class="alert-actions">
                                    <a href="followup.php?action=addInteraction&followup_id=<?= $prospect['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn-add-interaction">
                                        <i class="fas fa-plus-circle"></i> Ajouter une interaction
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="alert-section">
                <h3>Interactions planifiées aujourd'hui</h3>
                <?php if (empty($scheduledFollowUps)): ?>
                    <p class="empty-alert">Aucune interaction planifiée pour aujourd'hui.</p>
                <?php else: ?>
                    <div class="alert-list">
                        <?php foreach ($scheduledFollowUps as $followUp): ?>
                            <div class="alert-item">
                                <div class="alert-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="alert-content">
                                    <h4><?= htmlspecialchars($followUp['username']) ?></h4>
                                    <p class="alert-meta">
                                        <span class="alert-list-name"><?= htmlspecialchars($followUp['list_name']) ?></span>
                                        <span class="alert-type"><?= htmlspecialchars($followUp['type']) ?></span>
                                    </p>
                                    <p class="alert-notes"><?= htmlspecialchars($followUp['notes']) ?></p>
                                </div>
                                <div class="alert-actions">
                                    <a href="followup.php?action=markInteractionComplete&interaction_id=<?= $followUp['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn-complete-interaction">
                                        <i class="fas fa-check"></i> Marquer comme terminée
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-charts">
        <div class="chart-container">
            <h2>Répartition par statut</h2>
            <div class="status-chart">
                <canvas id="statusChart" width="400" height="300"></canvas>
                <div class="status-legend">
                    <?php foreach ($statusDistribution as $status => $count): ?>
                    <div class="status-legend-item">
                        <div class="status-color" data-status="<?= $status ?>"></div>
                        <div class="status-label"><?= $status ?></div>
                        <div class="status-count"><?= $count['count'] ?> (<?= $count['percentage'] ?>%)</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <h2>Activité récente</h2>
            <div class="activity-chart">
                <canvas id="activityChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="dashboard-recommendations">
        <h2>Recommandations</h2>
        <div class="recommendations-container">
            <?php if ($recommendations): ?>
                <?php foreach ($recommendations as $recommendation): ?>
                <div class="recommendation-card">
                    <div class="recommendation-icon">
                        <i class="<?= $recommendation['icon'] ?>"></i>
                    </div>
                    <div class="recommendation-content">
                        <h3><?= $recommendation['title'] ?></h3>
                        <p><?= $recommendation['description'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-recommendations">Aucune recommandation pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour le graphique de statuts
    const statusLabels = <?= json_encode(array_keys($statusDistribution)) ?>;
    const statusData = statusLabels.map(status => <?= json_encode($statusDistribution) ?>[status].count);
    const statusColors = [
        '#4F46E5', // bleu
        '#10B981', // vert
        '#F59E0B', // orange
        '#EF4444', // rouge
        '#8B5CF6', // violet
        '#EC4899'  // rose
    ];
    
    // Graphique de répartition par statut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors.slice(0, statusLabels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Données pour le graphique d'activité
    const activityLabels = <?= json_encode(array_keys($recentActivity)) ?>;
    const activityData = activityLabels.map(date => <?= json_encode($recentActivity) ?>[date].count);
    
    // Graphique d'activité récente
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: activityLabels,
            datasets: [{
                label: 'Interactions',
                data: activityData,
                backgroundColor: '#4F46E5',
                borderColor: '#4338CA',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Associer les couleurs aux légendes
    document.querySelectorAll('.status-color').forEach((el, index) => {
        el.style.backgroundColor = statusColors[index % statusColors.length];
    });
});
</script>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background-color: #f3f4f6;
    color: #374151;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-back:hover {
    background-color: #e5e7eb;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stats-card {
    display: flex;
    align-items: center;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background-color: #f3f4f6;
    border-radius: 8px;
    margin-right: 15px;
    color: #4f46e5;
    font-size: 1.5rem;
}

.stats-content h3 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.stats-number {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
    color: #1f2937;
}

.dashboard-alerts {
    margin-bottom: 30px;
}

.dashboard-alerts h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.2rem;
    color: #1f2937;
}

.alerts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.alert-section {
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.alert-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1rem;
    color: #1f2937;
}

.alert-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.alert-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.alert-item:last-child {
    border-bottom: none;
}

.alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: #e0f2fe;
    border-radius: 8px;
    margin-right: 15px;
    color: #0ea5e9;
    font-size: 1.2rem;
}

.alert-content {
    flex-grow: 1;
}

.alert-content h4 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    color: #1f2937;
}

.alert-meta {
    margin: 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.alert-description {
    margin: 0;
    font-size: 0.9rem;
    color: #4b5563;
}

.alert-last-interaction {
    margin: 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.alert-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-left: 15px;
}

.btn-add-interaction {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background-color: #4f46e5;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-add-interaction:hover {
    background-color: #4338ca;
}

.btn-complete-interaction {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background-color: #10b981;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-complete-interaction:hover {
    background-color: #059669;
}

.dashboard-charts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-container {
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-container h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.2rem;
    color: #1f2937;
}

.status-chart {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.status-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 15px;
    gap: 10px;
}

.status-legend-item {
    display: flex;
    align-items: center;
    margin-right: 15px;
}

.status-color {
    width: 15px;
    height: 15px;
    border-radius: 3px;
    margin-right: 5px;
}

.activity-chart {
    height: 300px;
}

canvas {
    max-width: 100%;
}

.dashboard-recommendations {
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.dashboard-recommendations h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.2rem;
    color: #1f2937;
}

.recommendations-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.recommendation-card {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    background-color: #f9fafb;
    border-radius: 8px;
}

.recommendation-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: #e0f2fe;
    border-radius: 8px;
    margin-right: 15px;
    color: #0ea5e9;
    font-size: 1.2rem;
}

.recommendation-content h3 {
    margin: 0 0 5px 0;
    font-size: 1rem;
    color: #1f2937;
}

.recommendation-content p {
    margin: 0;
    font-size: 0.9rem;
    color: #4b5563;
}

.no-recommendations {
    color: #6b7280;
    font-style: italic;
}

.followup-tabs {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
}

.tab {
    margin-right: 20px;
}

.tab a {
    color: #374151;
    text-decoration: none;
}

.tab a:hover {
    color: #1f2937;
}

.tab.active a {
    color: #1f2937;
    font-weight: 600;
}

.tab i {
    margin-right: 5px;
}

@media (max-width: 768px) {
    .dashboard-charts {
        grid-template-columns: 1fr;
    }
    
    .activity-chart {
        overflow-x: auto;
        padding-bottom: 10px;
    }
}
</style>
