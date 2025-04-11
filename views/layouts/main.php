<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'LeadsBuilder' ?> - LeadsBuilder PHP</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/search_results.css">
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
        <header class="app-header">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="LeadsBuilder" class="logo-image">
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li class="<?= isset($activeMenu) && $activeMenu === 'search' ? 'active' : '' ?>">
                        <a href="index.php"><i class="fas fa-search"></i> Recherche</a>
                    </li>
                    <li class="<?= isset($activeMenu) && $activeMenu === 'lists' ? 'active' : '' ?>">
                        <a href="lists.php"><i class="fas fa-list"></i> Listes</a>
                    </li>
                    <li class="<?= isset($activeMenu) && $activeMenu === 'follow' ? 'active' : '' ?>">
                        <a href="follow.php"><i class="fas fa-user-plus"></i> Suivi</a>
                    </li>
                    <li class="<?= isset($activeMenu) && $activeMenu === 'crm' ? 'active' : '' ?>">
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
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
