<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'LeadsBuilder PHP' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <?php if ($currentPage === 'lists'): ?>
    <link rel="stylesheet" href="assets/css/lists.css">
    <link rel="stylesheet" href="assets/css/list_view.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Header / Navigation -->
        <header class="app-header">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="LeadsBuilder PHP" class="logo-image">
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li<?= $currentPage === 'search' ? ' class="active"' : '' ?>><a href="index.php"><i class="fas fa-search"></i> Recherche</a></li>
                    <li<?= $currentPage === 'lists' ? ' class="active"' : '' ?>><a href="lists.php"><i class="fas fa-list"></i> Listes</a></li>
                    <li<?= $currentPage === 'follow' ? ' class="active"' : '' ?>><a href="follow.php"><i class="fas fa-user-plus"></i> Suivi</a></li>
                    <li<?= $currentPage === 'crm' ? ' class="active"' : '' ?>><a href="crm.php"><i class="fas fa-chart-pie"></i> CRM</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <a href="profile.php" class="user-profile">
                    <span class="user-email"><?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'exemple@gmail.com' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="app-content">
