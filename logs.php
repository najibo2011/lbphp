<?php
/**
 * Page pour visualiser les logs de l'application
 * Accessible uniquement aux administrateurs
 */
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

// Définir le répertoire des logs
$logDir = __DIR__ . '/logs/';

// Récupérer le type de log demandé
$logType = isset($_GET['type']) ? $_GET['type'] : 'error';

// Valider le type de log
$validLogTypes = ['error', 'access', 'performance', 'consent', 'gdpr_requests'];
if (!in_array($logType, $validLogTypes)) {
    $logType = 'error';
}

// Définir le fichier de log
$logFile = $logDir . $logType . '.log';

// Récupérer le nombre de lignes à afficher
$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 100;
$lines = max(10, min(1000, $lines)); // Entre 10 et 1000 lignes

// Récupérer les logs
$logs = [];
if (file_exists($logFile)) {
    // Lire les dernières lignes du fichier
    $file = new SplFileObject($logFile, 'r');
    $file->seek(PHP_INT_MAX); // Aller à la fin du fichier
    $totalLines = $file->key(); // Obtenir le nombre total de lignes
    
    // Calculer la position de départ
    $start = max(0, $totalLines - $lines);
    
    // Lire les lignes
    $file->seek($start);
    while (!$file->eof()) {
        $line = $file->fgets();
        if (trim($line) !== '') {
            $logs[] = json_decode($line, true);
        }
    }
    
    // Inverser l'ordre pour avoir les plus récents en premier
    $logs = array_reverse($logs);
}

// Définir le titre de la page
$title = 'Logs - LeadsBuilder';
$currentPage = 'admin';

// Inclure l'en-tête
include 'views/layouts/header.php';
?>

<div class="logs-container">
    <h1>Logs de l'application</h1>
    
    <div class="logs-controls">
        <div class="log-type-selector">
            <label for="log-type">Type de log :</label>
            <select id="log-type" onchange="changeLogType(this.value)">
                <option value="error" <?= $logType === 'error' ? 'selected' : '' ?>>Erreurs</option>
                <option value="access" <?= $logType === 'access' ? 'selected' : '' ?>>Accès</option>
                <option value="performance" <?= $logType === 'performance' ? 'selected' : '' ?>>Performance</option>
                <option value="consent" <?= $logType === 'consent' ? 'selected' : '' ?>>Consentements</option>
                <option value="gdpr_requests" <?= $logType === 'gdpr_requests' ? 'selected' : '' ?>>Demandes RGPD</option>
            </select>
        </div>
        
        <div class="log-lines-selector">
            <label for="log-lines">Nombre de lignes :</label>
            <select id="log-lines" onchange="changeLines(this.value)">
                <option value="50" <?= $lines === 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $lines === 100 ? 'selected' : '' ?>>100</option>
                <option value="250" <?= $lines === 250 ? 'selected' : '' ?>>250</option>
                <option value="500" <?= $lines === 500 ? 'selected' : '' ?>>500</option>
                <option value="1000" <?= $lines === 1000 ? 'selected' : '' ?>>1000</option>
            </select>
        </div>
        
        <div class="log-actions">
            <button id="refresh-logs" class="btn-refresh">
                <i class="fas fa-sync-alt"></i> Rafraîchir
            </button>
            <a href="health.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <div class="logs-table-container">
        <?php if (empty($logs)): ?>
        <p class="no-logs">Aucun log disponible pour le moment.</p>
        <?php else: ?>
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <?php if ($logType === 'error'): ?>
                    <th>Niveau</th>
                    <th>Message</th>
                    <th>Contexte</th>
                    <?php elseif ($logType === 'access'): ?>
                    <th>IP</th>
                    <th>Utilisateur</th>
                    <th>URL</th>
                    <th>Méthode</th>
                    <?php elseif ($logType === 'performance'): ?>
                    <th>Action</th>
                    <th>Durée (ms)</th>
                    <th>Contexte</th>
                    <?php elseif ($logType === 'consent'): ?>
                    <th>IP</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <?php elseif ($logType === 'gdpr_requests'): ?>
                    <th>IP</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Type de demande</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $log['timestamp'] ?? '-' ?></td>
                    <?php if ($logType === 'error'): ?>
                    <td class="severity <?= $log['severity'] ?? 'error' ?>"><?= $log['severity'] ?? 'error' ?></td>
                    <td><?= $log['message'] ?? '-' ?></td>
                    <td>
                        <?php if (isset($log['context']) && !empty($log['context'])): ?>
                        <pre><?= json_encode($log['context'], JSON_PRETTY_PRINT) ?></pre>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <?php elseif ($logType === 'access'): ?>
                    <td><?= $log['ip'] ?? '-' ?></td>
                    <td><?= $log['user_id'] ?? '-' ?></td>
                    <td><?= $log['url'] ?? '-' ?></td>
                    <td><?= $log['method'] ?? '-' ?></td>
                    <?php elseif ($logType === 'performance'): ?>
                    <td><?= $log['action'] ?? '-' ?></td>
                    <td><?= $log['duration'] ?? '-' ?></td>
                    <td>
                        <?php if (isset($log['context']) && !empty($log['context'])): ?>
                        <pre><?= json_encode($log['context'], JSON_PRETTY_PRINT) ?></pre>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <?php elseif ($logType === 'consent'): ?>
                    <td><?= $log['ip'] ?? '-' ?></td>
                    <td><?= $log['user_id'] ?? '-' ?></td>
                    <td><?= $log['action'] ?? '-' ?></td>
                    <?php elseif ($logType === 'gdpr_requests'): ?>
                    <td><?= $log['ip'] ?? '-' ?></td>
                    <td><?= $log['name'] ?? '-' ?></td>
                    <td><?= $log['email'] ?? '-' ?></td>
                    <td><?= $log['request_type'] ?? '-' ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function changeLogType(type) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('type', type);
    window.location.href = currentUrl.toString();
}

function changeLines(lines) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('lines', lines);
    window.location.href = currentUrl.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('refresh-logs').addEventListener('click', function() {
        window.location.reload();
    });
});
</script>

<style>
.logs-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.logs-container h1 {
    font-size: 28px;
    margin-bottom: 30px;
    color: #111827;
    text-align: center;
}

.logs-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 15px;
}

.log-type-selector,
.log-lines-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-type-selector label,
.log-lines-selector label {
    font-weight: 500;
    color: #4b5563;
}

.log-type-selector select,
.log-lines-selector select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: #f9fafb;
    font-size: 14px;
    color: #111827;
}

.log-actions {
    margin-left: auto;
    display: flex;
    gap: 10px;
}

.btn-refresh,
.btn-back {
    padding: 8px 15px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
}

.btn-refresh {
    background-color: #4f46e5;
    color: white;
    border: none;
}

.btn-refresh:hover {
    background-color: #4338ca;
}

.btn-back {
    background-color: #f3f4f6;
    color: #4b5563;
    border: 1px solid #d1d5db;
}

.btn-back:hover {
    background-color: #e5e7eb;
}

.logs-table-container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 20px;
    overflow-x: auto;
}

.no-logs {
    text-align: center;
    color: #6b7280;
    font-style: italic;
    padding: 20px;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th,
.logs-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.logs-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #111827;
}

.logs-table tr:hover {
    background-color: #f9fafb;
}

.logs-table pre {
    margin: 0;
    white-space: pre-wrap;
    font-size: 12px;
    background-color: #f3f4f6;
    padding: 8px;
    border-radius: 4px;
    max-height: 100px;
    overflow-y: auto;
}

.severity {
    text-transform: capitalize;
    font-weight: 500;
}

.severity.error {
    color: #ef4444;
}

.severity.warning {
    color: #f59e0b;
}

.severity.info {
    color: #3b82f6;
}

.severity.debug {
    color: #6b7280;
}

.severity.critical {
    color: #b91c1c;
    font-weight: 700;
}

@media (max-width: 768px) {
    .logs-controls {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .log-actions {
        margin-left: 0;
        width: 100%;
        justify-content: space-between;
    }
    
    .logs-table th,
    .logs-table td {
        padding: 8px;
        font-size: 12px;
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>
