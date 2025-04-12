<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'LeadsBuilder' ?> - LeadsBuilder PHP</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    
    <?php if (isset($currentPage) && $currentPage === 'lists'): ?>
    <link rel="stylesheet" href="assets/css/lists.css">
    <link rel="stylesheet" href="assets/css/list_view.css">
    <?php endif; ?>
    
    <?php if (isset($currentPage) && $currentPage === 'followup'): ?>
    <style>
    /* Styles pour la page de suivi des prospects */
    .followup-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .followup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .followup-header h1 {
        font-size: 24px;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .btn-refresh {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-refresh:hover {
        background-color: #f9fafb;
        border-color: #d1d5db;
    }

    .btn-refresh i {
        font-size: 12px;
    }

    .followup-subheader {
        margin-bottom: 20px;
    }

    .followup-subheader p {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    .followup-table-container {
        overflow-x: auto;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .followup-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .followup-table th {
        background-color: #f9fafb;
        color: #6b7280;
        font-weight: 500;
        text-align: left;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .followup-table td {
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }

    .followup-table tr:last-child td {
        border-bottom: none;
    }

    .followup-table tr:hover {
        background-color: #f9fafb;
    }

    .account-col {
        min-width: 250px;
    }

    .list-col, .status-col {
        min-width: 120px;
    }

    .date-col {
        min-width: 80px;
        text-align: center;
    }

    .account-cell {
        padding-right: 24px;
    }

    .account-link {
        color: #4f46e5;
        font-weight: 500;
        text-decoration: none;
        display: block;
        margin-bottom: 4px;
    }

    .account-link:hover {
        text-decoration: underline;
    }

    .account-description {
        color: #6b7280;
        margin: 0;
        font-size: 13px;
        line-height: 1.4;
    }

    .list-cell {
        color: #4b5563;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-badge.non-contacté {
        background-color: #f3f4f6;
        color: #6b7280;
    }

    .status-badge.pas-intéressé {
        background-color: #fee2e2;
        color: #ef4444;
    }

    .status-badge.contacté {
        background-color: #e0f2fe;
        color: #0ea5e9;
    }

    .status-badge.rendez-vous {
        background-color: #d1fae5;
        color: #10b981;
    }

    .date-cell {
        text-align: center;
        color: #d1d5db;
    }

    .date-cell.has-action {
        color: #111827;
    }

    .action-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* Sélecteurs pour les classes avec des chiffres au début */
    .action-badge[class*="1er-message"] {
        background-color: #e0f2fe;
        color: #0ea5e9;
    }

    .action-badge[class*="pas-intéressé"] {
        background-color: #fee2e2;
        color: #ef4444;
    }

    .followup-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
    }

    .pagination-prev,
    .pagination-next {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4f46e5;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }

    .pagination-prev:hover,
    .pagination-next:hover {
        background-color: #f5f5ff;
    }

    .pagination-prev.disabled,
    .pagination-next.disabled {
        color: #d1d5db;
        pointer-events: none;
    }

    .pagination-numbers {
        display: flex;
        gap: 4px;
    }

    .pagination-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        color: #4b5563;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .pagination-number:hover {
        background-color: #f5f5ff;
        color: #4f46e5;
    }

    .pagination-number.active {
        background-color: #4f46e5;
        color: #fff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .followup-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .btn-refresh {
            width: 100%;
            justify-content: center;
        }
        
        .followup-pagination {
            flex-direction: column;
            gap: 16px;
        }
        
        .pagination-numbers {
            order: -1;
        }
    }
    </style>
    <?php endif; ?>
    
    <?php if (isset($currentPage) && $currentPage === 'crm'): ?>
    <style>
    /* Styles pour le tableau de bord CRM */

    .crm-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .crm-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .crm-header h1 {
        font-size: 24px;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .btn-refresh {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-refresh:hover {
        background-color: #f9fafb;
        border-color: #d1d5db;
    }

    .btn-refresh i {
        font-size: 12px;
    }

    .crm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
    }

    .crm-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .crm-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }

    .list-name {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }

    .contact-count {
        font-size: 14px;
        color: #6b7280;
    }

    .crm-card-body {
        padding: 16px 20px;
    }

    .progress-item {
        margin-bottom: 16px;
    }

    .progress-item:last-child {
        margin-bottom: 0;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .stage-name {
        font-size: 14px;
        color: #4b5563;
    }

    .stage-count {
        font-size: 14px;
        color: #6b7280;
    }

    .progress-bar-container {
        height: 8px;
        background-color: #f3f4f6;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .crm-card-footer {
        padding: 12px 20px;
        border-top: 1px solid #f3f4f6;
        background-color: #f9fafb;
    }

    .total-messages {
        font-size: 14px;
        color: #4b5563;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .crm-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .btn-refresh {
            width: 100%;
            justify-content: center;
        }
        
        .crm-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php endif; ?>
    
    <?php if (isset($currentPage) && $currentPage === 'search'): ?>
    <style>
    /* Styles pour la page de recherche */

    .search-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .search-header h1 {
        font-size: 24px;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .search-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 20px;
    }

    .search-input {
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
    }

    .search-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 2px #4f46e5;
    }

    .search-button {
        background-color: #4f46e5;
        color: #fff;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .search-button:hover {
        background-color: #3b3f54;
    }

    .search-results {
        margin-top: 20px;
    }

    .search-result {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid #e5e7eb;
        margin-bottom: 16px;
    }

    .search-result-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }

    .result-name {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }

    .result-description {
        font-size: 14px;
        color: #6b7280;
    }

    .search-result-body {
        padding: 16px 20px;
    }

    .result-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .result-label {
        font-size: 14px;
        color: #4b5563;
    }

    .result-value {
        font-size: 14px;
        color: #6b7280;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .search-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .search-form {
            flex-direction: column;
            gap: 12px;
        }
        
        .search-input {
            width: 100%;
        }
        
        .search-button {
            width: 100%;
        }
    }
    </style>
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (isset($extraStyles)): ?>
        <?php foreach ($extraStyles as $style): ?>
            <link rel="stylesheet" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="app-container">
        <!-- Header / Navigation -->
        <?php include __DIR__ . '/header.php'; ?>

        <!-- Main Content -->
        <main class="app-content">
            <!-- Messages flash -->
            <?php if (method_exists($controller, 'getFlash')): ?>
                <?php $flashMessages = $controller->getFlash(); ?>
                <?php if (!empty($flashMessages)): ?>
                    <div class="flash-messages">
                        <?php foreach ($flashMessages as $type => $message): ?>
                            <div class="alert alert-<?= $type ?>">
                                <?= $message ?>
                                <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php include $viewPath; ?>
        </main>
        
        <!-- Footer -->
        <footer class="app-footer">
            <div class="footer-content">
                <p>&copy; <?= date('Y') ?> LeadsBuilder - Tous droits réservés</p>
            </div>
        </footer>
    </div>

    <!-- Scripts communs -->
    <script src="assets/js/common.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/export.js"></script>
    
    <?php if (isset($currentPage) && $currentPage === 'followup'): ?>
    <script src="assets/js/followup.js"></script>
    <?php endif; ?>
    
    <?php if (isset($currentPage) && $currentPage === 'crm'): ?>
    <script src="assets/js/crm.js"></script>
    <?php endif; ?>
    
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
