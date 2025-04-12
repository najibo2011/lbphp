<?php
/**
 * Classe pour la gestion de la conformité RGPD
 */
class Gdpr {
    private $cookieConsent;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Initialiser la session si elle n'est pas déjà démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si le consentement aux cookies existe
        $this->cookieConsent = $_SESSION['cookie_consent'] ?? null;
    }
    
    /**
     * Vérifier si l'utilisateur a donné son consentement aux cookies
     */
    public function hasCookieConsent() {
        return $this->cookieConsent !== null;
    }
    
    /**
     * Obtenir le statut du consentement aux cookies
     */
    public function getCookieConsent() {
        return $this->cookieConsent;
    }
    
    /**
     * Définir le consentement aux cookies
     */
    public function setCookieConsent($consent) {
        $_SESSION['cookie_consent'] = $consent;
        $this->cookieConsent = $consent;
        
        // Stocker le consentement dans un cookie pour une durée de 6 mois
        setcookie('cookie_consent', $consent ? '1' : '0', time() + (6 * 30 * 24 * 60 * 60), '/', '', false, true);
        
        // Journaliser le consentement
        $this->logConsentAction($consent ? 'accept' : 'reject');
    }
    
    /**
     * Générer la bannière de consentement aux cookies
     */
    public function renderCookieBanner() {
        if ($this->hasCookieConsent()) {
            return '';
        }
        
        $html = '
        <div id="cookie-banner" class="cookie-banner">
            <div class="cookie-content">
                <h3>Utilisation des cookies</h3>
                <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site. En continuant à naviguer sur ce site, vous acceptez notre utilisation des cookies.</p>
                <div class="cookie-buttons">
                    <button id="cookie-accept" class="btn-cookie btn-accept">Accepter tous les cookies</button>
                    <button id="cookie-reject" class="btn-cookie btn-reject">Rejeter les cookies non essentiels</button>
                    <a href="privacy.php" class="cookie-more">En savoir plus</a>
                </div>
            </div>
        </div>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const cookieBanner = document.getElementById("cookie-banner");
                const acceptButton = document.getElementById("cookie-accept");
                const rejectButton = document.getElementById("cookie-reject");
                
                // Fonction pour définir le consentement
                function setCookieConsent(consent) {
                    fetch("set_cookie_consent.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: "consent=" + (consent ? "1" : "0")
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            cookieBanner.style.display = "none";
                            
                            // Recharger la page si nécessaire
                            if (data.reload) {
                                window.location.reload();
                            }
                        }
                    });
                }
                
                // Écouteurs d\'événements
                acceptButton.addEventListener("click", function() {
                    setCookieConsent(true);
                });
                
                rejectButton.addEventListener("click", function() {
                    setCookieConsent(false);
                });
            });
        </script>
        
        <style>
            .cookie-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: #fff;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            }
            
            .cookie-content {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .cookie-content h3 {
                font-size: 18px;
                margin-top: 0;
                margin-bottom: 10px;
                color: #111827;
            }
            
            .cookie-content p {
                font-size: 14px;
                margin-bottom: 20px;
                color: #4b5563;
                line-height: 1.5;
            }
            
            .cookie-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }
            
            .btn-cookie {
                padding: 10px 20px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                border: none;
                transition: background-color 0.2s ease;
            }
            
            .btn-accept {
                background-color: #4f46e5;
                color: white;
            }
            
            .btn-accept:hover {
                background-color: #4338ca;
            }
            
            .btn-reject {
                background-color: #f3f4f6;
                color: #4b5563;
            }
            
            .btn-reject:hover {
                background-color: #e5e7eb;
            }
            
            .cookie-more {
                font-size: 14px;
                color: #4f46e5;
                text-decoration: none;
                margin-left: 10px;
            }
            
            .cookie-more:hover {
                text-decoration: underline;
            }
            
            @media (max-width: 768px) {
                .cookie-buttons {
                    flex-direction: column;
                    align-items: stretch;
                }
                
                .cookie-more {
                    margin-left: 0;
                    margin-top: 10px;
                    text-align: center;
                }
            }
        </style>
        ';
        
        return $html;
    }
    
    /**
     * Générer le formulaire de demande d'exercice des droits RGPD
     */
    public function renderGdprRequestForm() {
        $html = '
        <div class="gdpr-request-container">
            <h1>Exercer vos droits RGPD</h1>
            <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez de plusieurs droits concernant vos données personnelles. Veuillez remplir ce formulaire pour exercer l\'un de ces droits.</p>
            
            <form action="process_gdpr_request.php" method="post" class="gdpr-form">
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="request_type">Type de demande</label>
                    <select id="request_type" name="request_type" class="form-control" required>
                        <option value="">Sélectionnez un type de demande</option>
                        <option value="access">Accès à mes données</option>
                        <option value="rectification">Rectification de mes données</option>
                        <option value="deletion">Suppression de mes données (droit à l\'oubli)</option>
                        <option value="restriction">Restriction du traitement</option>
                        <option value="portability">Portabilité des données</option>
                        <option value="objection">Opposition au traitement</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message">Détails de votre demande</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="consent" id="consent" required>
                        <span class="checkmark"></span>
                        Je confirme que les informations fournies sont exactes et que je suis la personne concernée par cette demande.
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Soumettre ma demande</button>
                </div>
            </form>
            
            <div class="gdpr-info">
                <h2>Informations importantes</h2>
                <ul>
                    <li>Nous traiterons votre demande dans un délai d\'un mois à compter de sa réception.</li>
                    <li>Ce délai peut être prolongé de deux mois supplémentaires si nécessaire, compte tenu de la complexité et du nombre de demandes.</li>
                    <li>Nous pouvons vous demander des informations supplémentaires pour confirmer votre identité.</li>
                    <li>L\'exercice de vos droits est gratuit, sauf en cas de demandes manifestement infondées ou excessives.</li>
                </ul>
            </div>
        </div>
        
        <style>
            .gdpr-request-container {
                max-width: 800px;
                margin: 40px auto;
                padding: 0 20px;
            }
            
            .gdpr-request-container h1 {
                font-size: 28px;
                color: #111827;
                margin-bottom: 20px;
            }
            
            .gdpr-request-container p {
                font-size: 16px;
                color: #4b5563;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            
            .gdpr-form {
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                padding: 30px;
                margin-bottom: 30px;
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
            
            .gdpr-info {
                background-color: #f9fafb;
                border-radius: 10px;
                padding: 20px;
            }
            
            .gdpr-info h2 {
                font-size: 18px;
                color: #111827;
                margin-top: 0;
                margin-bottom: 15px;
            }
            
            .gdpr-info ul {
                padding-left: 20px;
                margin: 0;
            }
            
            .gdpr-info li {
                font-size: 14px;
                color: #4b5563;
                margin-bottom: 10px;
                line-height: 1.5;
            }
            
            .gdpr-info li:last-child {
                margin-bottom: 0;
            }
        </style>
        ';
        
        return $html;
    }
    
    /**
     * Traiter une demande d'exercice des droits RGPD
     */
    public function processGdprRequest($data) {
        // Valider les données
        if (empty($data['name']) || empty($data['email']) || empty($data['request_type']) || empty($data['message']) || empty($data['consent'])) {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs du formulaire.'
            ];
        }
        
        // Vérifier que l'email est valide
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Veuillez fournir une adresse email valide.'
            ];
        }
        
        // Enregistrer la demande dans la base de données
        $db = new Database();
        $result = $db->query(
            "INSERT INTO gdpr_requests (name, email, request_type, message, created_at) VALUES (?, ?, ?, ?, NOW())",
            [$data['name'], $data['email'], $data['request_type'], $data['message']]
        );
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement de votre demande. Veuillez réessayer.'
            ];
        }
        
        // Envoyer un email de confirmation
        $this->sendGdprRequestConfirmation($data);
        
        // Journaliser la demande
        $this->logGdprRequest($data);
        
        return [
            'success' => true,
            'message' => 'Votre demande a été enregistrée avec succès. Nous vous contacterons dans les plus brefs délais.'
        ];
    }
    
    /**
     * Envoyer un email de confirmation de demande RGPD
     */
    private function sendGdprRequestConfirmation($data) {
        // Sujet de l'email
        $subject = 'Confirmation de votre demande RGPD - LeadsBuilder';
        
        // Corps de l'email
        $message = "
        <html>
        <head>
            <title>Confirmation de votre demande RGPD</title>
        </head>
        <body>
            <h2>Confirmation de votre demande RGPD</h2>
            <p>Cher(e) {$data['name']},</p>
            <p>Nous confirmons la réception de votre demande concernant vos droits en vertu du RGPD.</p>
            <p><strong>Type de demande :</strong> {$this->getRequestTypeName($data['request_type'])}</p>
            <p><strong>Détails :</strong> {$data['message']}</p>
            <p>Nous traiterons votre demande dans un délai d'un mois à compter de sa réception. Nous vous contacterons si nous avons besoin d'informations supplémentaires.</p>
            <p>Cordialement,<br>L'équipe LeadsBuilder</p>
        </body>
        </html>
        ";
        
        // En-têtes de l'email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: LeadsBuilder <noreply@leadsbuilder.com>',
            'Reply-To: privacy@leadsbuilder.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Envoyer l'email
        mail($data['email'], $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Obtenir le nom du type de demande RGPD
     */
    private function getRequestTypeName($requestType) {
        $types = [
            'access' => 'Accès à mes données',
            'rectification' => 'Rectification de mes données',
            'deletion' => 'Suppression de mes données (droit à l\'oubli)',
            'restriction' => 'Restriction du traitement',
            'portability' => 'Portabilité des données',
            'objection' => 'Opposition au traitement'
        ];
        
        return $types[$requestType] ?? $requestType;
    }
    
    /**
     * Journaliser une action de consentement
     */
    private function logConsentAction($action) {
        $logFile = __DIR__ . '/../logs/consent.log';
        $logDir = dirname($logFile);
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Préparer les données du log
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'user_id' => $_SESSION['user_id'] ?? 'Guest',
            'action' => $action
        ];
        
        // Écrire dans le fichier de log
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
    
    /**
     * Journaliser une demande RGPD
     */
    private function logGdprRequest($data) {
        $logFile = __DIR__ . '/../logs/gdpr_requests.log';
        $logDir = dirname($logFile);
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Préparer les données du log
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'name' => $data['name'],
            'email' => $data['email'],
            'request_type' => $data['request_type']
        ];
        
        // Écrire dans le fichier de log
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
    
    /**
     * Exporter les données d'un utilisateur (droit d'accès)
     */
    public function exportUserData($userId) {
        // Récupérer les données de l'utilisateur
        $db = new Database();
        $user = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
        
        if (!$user) {
            return null;
        }
        
        // Supprimer les données sensibles
        unset($user['password']);
        unset($user['reset_token']);
        unset($user['reset_token_expiry']);
        
        // Récupérer les listes de l'utilisateur
        $lists = $db->query("SELECT * FROM lists WHERE user_id = ?", [$userId])->fetchAll();
        
        // Récupérer les profils dans les listes
        $listProfiles = [];
        foreach ($lists as $list) {
            $profiles = $db->query(
                "SELECT lp.*, p.* FROM list_profiles lp
                JOIN profiles p ON lp.profile_id = p.id
                WHERE lp.list_id = ?",
                [$list['id']]
            )->fetchAll();
            
            $listProfiles[$list['id']] = $profiles;
        }
        
        // Récupérer l'historique des recherches
        $searchHistory = $db->query(
            "SELECT * FROM search_history WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        )->fetchAll();
        
        // Récupérer les abonnements
        $subscriptions = $db->query(
            "SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        )->fetchAll();
        
        // Assembler toutes les données
        $userData = [
            'user' => $user,
            'lists' => $lists,
            'list_profiles' => $listProfiles,
            'search_history' => $searchHistory,
            'subscriptions' => $subscriptions
        ];
        
        return $userData;
    }
    
    /**
     * Supprimer les données d'un utilisateur (droit à l'oubli)
     */
    public function deleteUserData($userId) {
        $db = new Database();
        
        // Commencer une transaction
        $db->beginTransaction();
        
        try {
            // Supprimer les profils des listes
            $lists = $db->query("SELECT id FROM lists WHERE user_id = ?", [$userId])->fetchAll();
            foreach ($lists as $list) {
                $db->query("DELETE FROM list_profiles WHERE list_id = ?", [$list['id']]);
            }
            
            // Supprimer les listes
            $db->query("DELETE FROM lists WHERE user_id = ?", [$userId]);
            
            // Supprimer l'historique des recherches
            $db->query("DELETE FROM search_history WHERE user_id = ?", [$userId]);
            
            // Anonymiser les abonnements (pour des raisons fiscales)
            $db->query(
                "UPDATE subscriptions SET user_data_anonymized = 1 WHERE user_id = ?",
                [$userId]
            );
            
            // Anonymiser l'utilisateur
            $db->query(
                "UPDATE users SET
                name = 'Utilisateur supprimé',
                email = CONCAT('deleted_', id, '@example.com'),
                password = NULL,
                stripe_customer_id = NULL,
                data_deleted = 1,
                deleted_at = NOW()
                WHERE id = ?",
                [$userId]
            );
            
            // Valider la transaction
            $db->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $db->rollBack();
            
            // Journaliser l'erreur
            error_log('Erreur lors de la suppression des données utilisateur : ' . $e->getMessage());
            
            return false;
        }
    }
}
