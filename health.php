<?php
/**
 * Page d'état de santé de l'application
 * Accessible uniquement aux administrateurs
 */
require_once 'includes/Monitoring.php';
require_once 'includes/Security.php';
require_once 'models/UserModel.php';
require_once 'includes/Database.php';

// Initialiser la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et est administrateur
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $userModel = new UserModel($db);
    $user = $userModel->getById($_SESSION['user_id']);
    $isAdmin = $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

// Si l'utilisateur n'est pas administrateur, rediriger vers la page d'accueil
if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

// Initialiser la classe de monitoring
$monitoring = new Monitoring();

// Vérifier si la requête est une requête AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Si c'est une requête AJAX, renvoyer uniquement les données de santé
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($monitoring->healthCheck());
    exit;
}

// Récupérer les métriques d'utilisation
$metrics = $monitoring->collectMetrics();

// Récupérer l'état de santé
$healthStatus = $monitoring->healthCheck();

// Définir le titre de la page
$title = 'État de santé - LeadsBuilder';
$currentPage = 'admin';

// Inclure l'en-tête
include 'views/layouts/header.php';
?>

<div class="health-dashboard">
    <h1>État de santé de l'application</h1>
    
    <div class="health-status-container">
        <div class="health-status <?= $healthStatus['status'] ?>">
            <h2>État global : <?= ucfirst($healthStatus['status']) ?></h2>
            <p>Dernière vérification : <?= $healthStatus['timestamp'] ?></p>
        </div>
        
        <div class="health-checks">
            <h2>Vérifications détaillées</h2>
            
            <?php foreach ($healthStatus['checks'] as $checkName => $check): ?>
            <div class="health-check <?= $check['status'] ?>">
                <h3><?= ucfirst($checkName) ?></h3>
                <p><?= $check['message'] ?></p>
                
                <?php if (isset($check['free']) && isset($check['total'])): ?>
                <div class="progress-bar">
                    <div class="progress" style="width: <?= (($check['total'] - $check['free']) / $check['total']) * 100 ?>%"></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="metrics-container">
        <h2>Métriques d'utilisation</h2>
        
        <div class="metrics-grid">
            <div class="metric">
                <h3>Utilisateurs actifs</h3>
                <p class="metric-value"><?= $metrics['active_users'] ?></p>
            </div>
            
            <div class="metric">
                <h3>Recherches</h3>
                <p class="metric-value"><?= $metrics['searches'] ?></p>
            </div>
            
            <div class="metric">
                <h3>Listes créées</h3>
                <p class="metric-value"><?= $metrics['lists_created'] ?></p>
            </div>
            
            <div class="metric">
                <h3>Profils ajoutés</h3>
                <p class="metric-value"><?= $metrics['profiles_added'] ?></p>
            </div>
            
            <div class="metric">
                <h3>Temps de réponse moyen</h3>
                <p class="metric-value"><?= round($metrics['avg_response_time'], 2) ?> ms</p>
            </div>
            
            <div class="metric">
                <h3>Erreurs</h3>
                <p class="metric-value <?= $metrics['errors'] > 0 ? 'error' : '' ?>"><?= $metrics['errors'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="actions-container">
        <h2>Actions</h2>
        
        <div class="actions-grid">
            <a href="logs.php" class="action-button">
                <i class="fas fa-file-alt"></i>
                <span>Consulter les logs</span>
            </a>
            
            <a href="test_runner.php" class="action-button">
                <i class="fas fa-vial"></i>
                <span>Exécuter les tests</span>
            </a>
            
            <a href="#" id="refresh-status" class="action-button">
                <i class="fas fa-sync-alt"></i>
                <span>Rafraîchir l'état</span>
            </a>
            
            <a href="clear_cache.php" class="action-button">
                <i class="fas fa-broom"></i>
                <span>Vider le cache</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour rafraîchir l'état de santé
    function refreshHealthStatus() {
        fetch('health.php', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Mettre à jour l'état global
            const healthStatusElement = document.querySelector('.health-status');
            healthStatusElement.className = 'health-status ' + data.status;
            healthStatusElement.querySelector('h2').textContent = 'État global : ' + data.status.charAt(0).toUpperCase() + data.status.slice(1);
            healthStatusElement.querySelector('p').textContent = 'Dernière vérification : ' + data.timestamp;
            
            // Mettre à jour les vérifications détaillées
            const healthChecksElement = document.querySelector('.health-checks');
            let checksHtml = '<h2>Vérifications détaillées</h2>';
            
            for (const checkName in data.checks) {
                const check = data.checks[checkName];
                
                checksHtml += `
                <div class="health-check ${check.status}">
                    <h3>${checkName.charAt(0).toUpperCase() + checkName.slice(1)}</h3>
                    <p>${check.message}</p>
                `;
                
                if (check.free !== undefined && check.total !== undefined) {
                    const percentage = ((check.total - check.free) / check.total) * 100;
                    checksHtml += `
                    <div class="progress-bar">
                        <div class="progress" style="width: ${percentage}%"></div>
                    </div>
                    `;
                }
                
                checksHtml += '</div>';
            }
            
            healthChecksElement.innerHTML = checksHtml;
        })
        .catch(error => {
            console.error('Erreur lors du rafraîchissement de l\'état de santé :', error);
        });
    }
    
    // Ajouter un écouteur d'événement pour le bouton de rafraîchissement
    document.getElementById('refresh-status').addEventListener('click', function(e) {
        e.preventDefault();
        refreshHealthStatus();
    });
});
</script>

<style>
.health-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.health-dashboard h1 {
    font-size: 28px;
    margin-bottom: 30px;
    color: #111827;
    text-align: center;
}

.health-status-container,
.metrics-container,
.actions-container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 20px;
    margin-bottom: 30px;
}

.health-status {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
}

.health-status.ok {
    background-color: #d1fae5;
    color: #065f46;
}

.health-status.warning {
    background-color: #fef3c7;
    color: #92400e;
}

.health-status.error {
    background-color: #fee2e2;
    color: #b91c1c;
}

.health-status h2 {
    font-size: 20px;
    margin-bottom: 10px;
}

.health-checks {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.health-checks h2 {
    grid-column: 1 / -1;
    font-size: 20px;
    margin-bottom: 15px;
    color: #111827;
}

.health-check {
    padding: 15px;
    border-radius: 6px;
}

.health-check.ok {
    background-color: #f0fdf4;
    border-left: 4px solid #22c55e;
}

.health-check.warning {
    background-color: #fffbeb;
    border-left: 4px solid #f59e0b;
}

.health-check.error {
    background-color: #fef2f2;
    border-left: 4px solid #ef4444;
}

.health-check h3 {
    font-size: 16px;
    margin-bottom: 8px;
    color: #111827;
}

.health-check p {
    font-size: 14px;
    margin-bottom: 10px;
    color: #4b5563;
}

.progress-bar {
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress {
    height: 100%;
    background-color: #3b82f6;
    border-radius: 4px;
}

.metrics-container h2,
.actions-container h2 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #111827;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.metric {
    background-color: #f9fafb;
    padding: 15px;
    border-radius: 6px;
    text-align: center;
}

.metric h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #4b5563;
}

.metric-value {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
}

.metric-value.error {
    color: #ef4444;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.action-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: #f9fafb;
    border-radius: 6px;
    text-decoration: none;
    color: #4b5563;
    transition: all 0.2s ease;
}

.action-button:hover {
    background-color: #f3f4f6;
    transform: translateY(-2px);
}

.action-button i {
    font-size: 24px;
    margin-bottom: 10px;
    color: #3b82f6;
}

.action-button span {
    font-size: 14px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .health-checks,
    .metrics-grid,
    .actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>
