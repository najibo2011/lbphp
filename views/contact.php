<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - LeadsBuilder</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'views/layouts/header.php'; ?>
    
    <div class="contact-container">
        <h1>Contactez-nous</h1>
        
        <div class="contact-content">
            <div class="contact-info">
                <h2>Nos coordonnées</h2>
                
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h3>Adresse</h3>
                        <p>LeadsBuilder SAS<br>123 Avenue de la République<br>75011 Paris, France</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Email</h3>
                        <p><a href="mailto:contact@leadsbuilder.com">contact@leadsbuilder.com</a></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h3>Téléphone</h3>
                        <p>+33 (0)1 23 45 67 89</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Horaires</h3>
                        <p>Du lundi au vendredi<br>9h00 - 18h00</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Envoyez-nous un message</h2>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="process_contact.php" method="post" class="contact-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <select id="subject" name="subject" class="form-control" required>
                            <option value="">Sélectionnez un sujet</option>
                            <option value="general" <?= isset($formData['subject']) && $formData['subject'] === 'general' ? 'selected' : '' ?>>Demande générale</option>
                            <option value="support" <?= isset($formData['subject']) && $formData['subject'] === 'support' ? 'selected' : '' ?>>Support technique</option>
                            <option value="billing" <?= isset($formData['subject']) && $formData['subject'] === 'billing' ? 'selected' : '' ?>>Facturation</option>
                            <option value="partnership" <?= isset($formData['subject']) && $formData['subject'] === 'partnership' ? 'selected' : '' ?>>Partenariat</option>
                            <option value="gdpr" <?= isset($formData['subject']) && $formData['subject'] === 'gdpr' ? 'selected' : '' ?>>Demande RGPD</option>
                            <option value="other" <?= isset($formData['subject']) && $formData['subject'] === 'other' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required><?= htmlspecialchars($formData['message'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="privacy_consent" id="privacy_consent" <?= isset($formData['privacy_consent']) ? 'checked' : '' ?> required>
                            <span class="checkmark"></span>
                            J'accepte que mes données soient traitées conformément à la <a href="privacy.php" target="_blank">politique de confidentialité</a>.
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Envoyer le message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'views/layouts/footer.php'; ?>
    
    <style>
        .contact-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .contact-container h1 {
            font-size: 32px;
            color: #111827;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
        }
        
        .contact-info,
        .contact-form-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }
        
        .contact-info h2,
        .contact-form-container h2 {
            font-size: 24px;
            color: #111827;
            margin-top: 0;
            margin-bottom: 25px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 25px;
        }
        
        .info-item i {
            font-size: 24px;
            color: #4f46e5;
            margin-right: 15px;
            margin-top: 3px;
        }
        
        .info-item h3 {
            font-size: 18px;
            color: #111827;
            margin: 0 0 5px;
        }
        
        .info-item p {
            margin: 0;
            color: #4b5563;
            line-height: 1.5;
        }
        
        .info-item a {
            color: #4f46e5;
            text-decoration: none;
        }
        
        .info-item a:hover {
            text-decoration: underline;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #f3f4f6;
            border-radius: 50%;
            color: #4b5563;
            transition: all 0.2s;
        }
        
        .social-link:hover {
            background-color: #4f46e5;
            color: white;
        }
        
        .social-link i {
            font-size: 18px;
        }
        
        .contact-form {
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
        
        textarea.form-control {
            resize: vertical;
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
        
        .checkbox-container a {
            color: #4f46e5;
            text-decoration: none;
        }
        
        .checkbox-container a:hover {
            text-decoration: underline;
        }
        
        .form-actions {
            margin-top: 25px;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
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
        
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
            }
            
            .contact-container h1 {
                font-size: 28px;
            }
            
            .contact-info h2,
            .contact-form-container h2 {
                font-size: 22px;
            }
        }
    </style>
</body>
</html>
