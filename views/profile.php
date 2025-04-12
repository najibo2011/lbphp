<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - LeadsBuilder</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/layouts/header.php'; ?>
    
    <div class="profile-container">
        <h1>Mon Profil</h1>
        
        <div class="profile-sections">
            <div class="profile-section">
                <h2>Informations personnelles</h2>
                
                <?php if (isset($updateSuccess)): ?>
                <div class="alert alert-success">
                    <?= $updateSuccess ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($updateErrors) && !empty($updateErrors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($updateErrors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="update_profile.php" method="post" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Biographie</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Mettre à jour mon profil</button>
                    </div>
                </form>
            </div>
            
            <div class="profile-section">
                <h2>Changer de mot de passe</h2>
                
                <?php if (isset($passwordSuccess)): ?>
                <div class="alert alert-success">
                    <?= $passwordSuccess ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($passwordErrors) && !empty($passwordErrors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($passwordErrors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="update_password.php" method="post" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <small class="form-text">Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Changer mon mot de passe</button>
                    </div>
                </form>
            </div>
            
            <div class="profile-section">
                <h2>Abonnement</h2>
                
                <div class="subscription-info">
                    <p><strong>Plan actuel :</strong> <?= $subscription['plan_name'] ?? 'Gratuit' ?></p>
                    
                    <?php if (isset($subscription['next_billing_date'])): ?>
                    <p><strong>Prochaine facturation :</strong> <?= date('d/m/Y', strtotime($subscription['next_billing_date'])) ?></p>
                    <?php endif; ?>
                    
                    <div class="subscription-actions">
                        <?php if (isset($subscription['plan_id']) && $subscription['plan_id'] !== 'free'): ?>
                        <a href="manage_subscription.php" class="btn-secondary">Gérer mon abonnement</a>
                        <?php else: ?>
                        <a href="plans.php" class="btn-primary">Voir les plans disponibles</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="profile-section">
                <h2>Mes données personnelles (RGPD)</h2>
                
                <div class="gdpr-options">
                    <div class="gdpr-option">
                        <h3>Exporter mes données</h3>
                        <p>Téléchargez une copie de vos données personnelles au format JSON.</p>
                        <button id="export-data" class="btn-secondary">Exporter mes données</button>
                    </div>
                    
                    <div class="gdpr-option">
                        <h3>Supprimer mon compte</h3>
                        <p>Cette action supprimera définitivement votre compte et toutes vos données.</p>
                        <button id="delete-account" class="btn-danger">Supprimer mon compte</button>
                    </div>
                    
                    <div class="gdpr-option">
                        <h3>Exercer mes droits RGPD</h3>
                        <p>Faites une demande pour exercer vos droits (rectification, limitation, etc.)</p>
                        <a href="gdpr_request.php" class="btn-secondary">Faire une demande</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation pour la suppression du compte -->
    <div id="delete-account-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirmer la suppression</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible et toutes vos données seront définitivement supprimées.</p>
                
                <form action="delete_account.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label for="confirm_email">Pour confirmer, veuillez saisir votre adresse email</label>
                        <input type="email" id="confirm_email" name="confirm_email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="confirm_deletion" id="confirm_deletion" required>
                            <span class="checkmark"></span>
                            Je comprends que cette action est irréversible et que toutes mes données seront définitivement supprimées.
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-delete">Annuler</button>
                        <button type="submit" class="btn-danger">Supprimer définitivement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'views/layouts/footer.php'; ?>
    
    <script src="assets/js/export.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion de l'exportation des données
            document.getElementById('export-data').addEventListener('click', function() {
                exportUserDataGDPR();
            });
            
            // Gestion de la suppression du compte
            const deleteModal = document.getElementById('delete-account-modal');
            const deleteBtn = document.getElementById('delete-account');
            const closeBtn = deleteModal.querySelector('.close');
            const cancelBtn = deleteModal.querySelector('.cancel-delete');
            
            deleteBtn.addEventListener('click', function() {
                deleteModal.style.display = 'block';
            });
            
            closeBtn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });
        });
    </script>
    
    <style>
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-container h1 {
            font-size: 28px;
            color: #111827;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .profile-section {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
        }
        
        .profile-section h2 {
            font-size: 20px;
            color: #111827;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        .profile-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #111827;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .form-actions {
            margin-top: 25px;
        }
        
        .btn-primary,
        .btn-secondary,
        .btn-danger {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
            text-decoration: none;
            display: inline-block;
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
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 6px;
        }
        
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .subscription-info {
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 6px;
        }
        
        .subscription-info p {
            margin: 0 0 10px;
        }
        
        .subscription-actions {
            margin-top: 20px;
        }
        
        .gdpr-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .gdpr-option {
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 6px;
        }
        
        .gdpr-option h3 {
            font-size: 16px;
            color: #111827;
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .gdpr-option p {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 15px;
        }
        
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
            font-size: 14px;
            color: #4b5563;
        }
        
        .checkbox-container input {
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: #111827;
        }
        
        .close {
            color: #6b7280;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #111827;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .gdpr-options {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 20% auto;
                width: 95%;
            }
        }
    </style>
</body>
</html>
