<?php
/**
 * Page pour exécuter les tests unitaires
 * Accessible uniquement aux administrateurs
 */
require_once 'tests/TestRunner.php';
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

// Initialiser le runner de tests
$testRunner = new TestRunner(__DIR__ . '/tests');

// Vérifier si un fichier de test spécifique est demandé
$testFile = isset($_GET['file']) ? $_GET['file'] : null;

// Exécuter les tests
if ($testFile) {
    $testRunner->runTestFile($testFile);
} else {
    $testRunner->runAllTests();
}

// Récupérer les résultats et le résumé
$results = $testRunner->getResults();
$summary = $testRunner->getSummary();

// Définir le titre de la page
$title = 'Exécution des tests - LeadsBuilder';
$currentPage = 'admin';

// Inclure l'en-tête
include 'views/layouts/header.php';
?>

<div class="test-runner-container">
    <h1>Exécution des tests unitaires</h1>
    
    <div class="test-summary">
        <h2>Résumé</h2>
        
        <div class="summary-grid">
            <div class="summary-item">
                <h3>Total</h3>
                <p class="summary-value"><?= $summary['total'] ?></p>
            </div>
            
            <div class="summary-item">
                <h3>Réussis</h3>
                <p class="summary-value success"><?= $summary['passed'] ?></p>
            </div>
            
            <div class="summary-item">
                <h3>Échoués</h3>
                <p class="summary-value failed"><?= $summary['failed'] ?></p>
            </div>
            
            <div class="summary-item">
                <h3>Ignorés</h3>
                <p class="summary-value skipped"><?= $summary['skipped'] ?></p>
            </div>
            
            <div class="summary-item">
                <h3>Taux de réussite</h3>
                <p class="summary-value <?= $summary['success_rate'] >= 90 ? 'success' : ($summary['success_rate'] >= 70 ? 'warning' : 'failed') ?>">
                    <?= $summary['success_rate'] ?>%
                </p>
            </div>
        </div>
    </div>
    
    <div class="test-results">
        <h2>Résultats détaillés</h2>
        
        <?php if (empty($results)): ?>
        <p class="no-results">Aucun test n'a été exécuté.</p>
        <?php else: ?>
            <?php foreach ($results as $file => $fileResults): ?>
            <div class="test-file">
                <h3><?= basename($file) ?></h3>
                <p class="class-name"><?= $fileResults['class'] ?></p>
                
                <div class="test-methods">
                    <?php foreach ($fileResults['tests'] as $method => $testResult): ?>
                    <div class="test-method <?= $testResult['status'] ?>">
                        <div class="method-header">
                            <h4><?= $method ?></h4>
                            <span class="status-badge <?= $testResult['status'] ?>">
                                <?= $testResult['status'] === 'passed' ? 'Réussi' : ($testResult['status'] === 'failed' ? 'Échoué' : 'Ignoré') ?>
                            </span>
                        </div>
                        
                        <?php if ($testResult['status'] !== 'passed'): ?>
                        <div class="method-details">
                            <p class="message"><?= $testResult['message'] ?></p>
                            
                            <?php if (isset($testResult['file']) && isset($testResult['line'])): ?>
                            <p class="location">
                                <strong>Fichier :</strong> <?= $testResult['file'] ?> (ligne <?= $testResult['line'] ?>)
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="test-actions">
        <a href="test_runner.php" class="btn btn-primary">Exécuter tous les tests</a>
        <a href="health.php" class="btn btn-secondary">Retour au tableau de bord</a>
    </div>
</div>

<style>
.test-runner-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.test-runner-container h1 {
    font-size: 28px;
    margin-bottom: 30px;
    color: #111827;
    text-align: center;
}

.test-summary,
.test-results,
.test-actions {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 20px;
    margin-bottom: 30px;
}

.test-summary h2,
.test-results h2 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #111827;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
}

.summary-item {
    background-color: #f9fafb;
    padding: 15px;
    border-radius: 6px;
    text-align: center;
}

.summary-item h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #4b5563;
}

.summary-value {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
}

.summary-value.success {
    color: #10b981;
}

.summary-value.warning {
    color: #f59e0b;
}

.summary-value.failed {
    color: #ef4444;
}

.summary-value.skipped {
    color: #6b7280;
}

.no-results {
    text-align: center;
    color: #6b7280;
    font-style: italic;
    padding: 20px;
}

.test-file {
    margin-bottom: 30px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
}

.test-file h3 {
    font-size: 18px;
    padding: 15px;
    margin: 0;
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    color: #111827;
}

.class-name {
    padding: 0 15px 15px;
    margin: 0;
    background-color: #f9fafb;
    color: #6b7280;
    font-style: italic;
    font-size: 14px;
}

.test-methods {
    padding: 0;
}

.test-method {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.test-method:last-child {
    border-bottom: none;
}

.test-method.passed {
    background-color: #f0fdf4;
}

.test-method.failed {
    background-color: #fef2f2;
}

.test-method.skipped {
    background-color: #f9fafb;
}

.method-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.method-header h4 {
    margin: 0;
    font-size: 16px;
    color: #111827;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.passed {
    background-color: #d1fae5;
    color: #065f46;
}

.status-badge.failed {
    background-color: #fee2e2;
    color: #b91c1c;
}

.status-badge.skipped {
    background-color: #f3f4f6;
    color: #4b5563;
}

.method-details {
    margin-top: 10px;
    padding: 10px;
    background-color: rgba(0, 0, 0, 0.02);
    border-radius: 4px;
}

.message {
    margin: 0 0 10px;
    font-size: 14px;
    color: #4b5563;
}

.location {
    margin: 0;
    font-size: 12px;
    color: #6b7280;
}

.test-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-primary {
    background-color: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background-color: #4338ca;
}

.btn-secondary {
    background-color: #f3f4f6;
    color: #4b5563;
}

.btn-secondary:hover {
    background-color: #e5e7eb;
}

@media (max-width: 768px) {
    .summary-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .method-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .status-badge {
        margin-top: 5px;
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>
