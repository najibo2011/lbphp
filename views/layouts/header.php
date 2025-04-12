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
    <?php if ($currentPage === 'followup'): ?>
    <link rel="stylesheet" href="assets/css/followup.css">
    <?php endif; ?>
    <?php if ($currentPage === 'crm'): ?>
    <link rel="stylesheet" href="assets/css/crm.css">
    <?php endif; ?>
    <?php if ($currentPage === 'search'): ?>
    <link rel="stylesheet" href="assets/css/search_form.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php 
    // Initialiser le modèle de suivi pour l'affichage des alertes
    require_once __DIR__ . '/../../includes/init_followup.php';
    ?>
</head>
<body>
    <div class="">
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
                    <li<?= $currentPage === 'followup' ? ' class="active"' : '' ?> class="has-submenu">
                        <a href="followup.php">
                            <i class="fas fa-user-plus"></i> Suivi
                            <?php 
                            // Vérifier s'il y a des prospects à relancer
                            if (isset($followupModel) && isset($_SESSION['user_id'])) {
                                $prospectsCount = $followupModel->countProspectsToFollowUp($_SESSION['user_id']);
                                if ($prospectsCount > 0):
                            ?>
                            <span class="notification-badge"><?= $prospectsCount ?></span>
                            <?php 
                                endif;
                            }
                            ?>
                        </a>
                        <ul class="submenu">
                            <li><a href="followup.php"><i class="fas fa-users"></i> Tous les prospects</a></li>
                            <li><a href="followup.php?action=dashboard"><i class="fas fa-chart-pie"></i> Tableau de bord</a></li>
                            <li><a href="followup.php?action=add"><i class="fas fa-plus"></i> Ajouter un prospect</a></li>
                            <li><a href="followup.php?action=help"><i class="fas fa-question-circle"></i> Aide</a></li>
                        </ul>
                    </li>
                    <li<?= $currentPage === 'crm' ? ' class="active"' : '' ?>><a href="crm.php"><i class="fas fa-chart-pie"></i> CRM</a></li>
                    <li<?= $currentPage === 'profile' ? ' class="active"' : '' ?>><a href="profile.php"><i class="fas fa-user-cog"></i> Profil</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <a href="#" class="user-profile" id="user-menu-toggle">
                    <span class="user-email"><?= isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'exemple@gmail.com' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="user-dropdown" id="user-dropdown">
                    <ul>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Mon profil</a></li>
                        <li><a href="update_profile.php"><i class="fas fa-edit"></i> Modifier le profil</a></li>
                        <li><a href="update_password.php"><i class="fas fa-key"></i> Changer le mot de passe</a></li>
                        <li><a href="subscription.php"><i class="fas fa-credit-card"></i> Abonnement</a></li>
                        <li class="divider"></li>
                        <li><a href="export_user_data.php"><i class="fas fa-download"></i> Exporter mes données</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <style>
            .user-menu {
                position: relative;
            }
            
            .user-profile {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 6px;
                color: #4b5563;
                text-decoration: none;
                transition: background-color 0.2s;
            }
            
            .user-profile:hover {
                background-color: #f3f4f6;
            }
            
            .user-email {
                font-size: 14px;
                font-weight: 500;
            }
            
            .notification-badge {
                background-color: #ff9800;
                color: #fff;
                padding: 2px 6px;
                border-radius: 50%;
                font-size: 12px;
                position: absolute;
                top: 10px;
                right: 10px;
            }
            
            /* Styles pour le menu déroulant utilisateur */
            .user-dropdown {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                min-width: 220px;
                z-index: 100;
                padding: 8px 0;
            }
            
            .user-dropdown.active {
                display: block;
            }
            
            .user-dropdown ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .user-dropdown li {
                display: block;
                margin: 0;
                padding: 0;
            }
            
            .user-dropdown li a {
                display: flex;
                align-items: center;
                padding: 10px 16px;
                color: #4b5563;
                text-decoration: none;
                font-size: 14px;
                transition: background-color 0.2s;
            }
            
            .user-dropdown li a:hover {
                background-color: #f3f4f6;
            }
            
            .user-dropdown li a i {
                margin-right: 8px;
                width: 16px;
                text-align: center;
            }
            
            .user-dropdown .divider {
                height: 1px;
                background-color: #e5e7eb;
                margin: 8px 0;
            }
            
            /* Styles pour le sous-menu */
            .has-submenu {
                position: relative;
            }
            
            .submenu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                padding: 8px 0;
                min-width: 200px;
                z-index: 100;
            }
            
            .has-submenu:hover .submenu {
                display: block;
            }
            
            .submenu li {
                display: block;
                margin: 0;
                padding: 0;
            }
            
            .submenu li a {
                display: flex;
                align-items: center;
                padding: 10px 16px;
                color: #4b5563;
                text-decoration: none;
                font-size: 14px;
                transition: background-color 0.2s;
            }
            
            .submenu li a:hover {
                background-color: #f3f4f6;
            }
            
            .submenu li a i {
                margin-right: 8px;
                width: 16px;
                text-align: center;
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gestion du menu utilisateur
                const userMenuToggle = document.getElementById('user-menu-toggle');
                const userDropdown = document.getElementById('user-dropdown');
                
                if (userMenuToggle && userDropdown) {
                    userMenuToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        userDropdown.classList.toggle('active');
                    });
                    
                    // Fermer le menu lorsqu'on clique ailleurs
                    document.addEventListener('click', function(e) {
                        if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                            userDropdown.classList.remove('active');
                        }
                    });
                }
            });
        </script>
    </main>
</div>
</body>
</html>
