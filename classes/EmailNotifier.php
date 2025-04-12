<?php
/**
 * Classe pour l'envoi de notifications par email
 */
class EmailNotifier {
    private $mailer;
    private $fromEmail;
    private $fromName;
    
    /**
     * Constructeur
     * 
     * @param string $fromEmail Email de l'expéditeur
     * @param string $fromName Nom de l'expéditeur
     */
    public function __construct($fromEmail = 'notifications@leadsbuilder.com', $fromName = 'LeadsBuilder') {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        
        // Initialiser PHPMailer
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'] ?? 'smtp.example.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'] ?? 'user@example.com';
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'] ?? 'password';
        $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'] ?? 587;
        $this->mailer->setFrom($this->fromEmail, $this->fromName);
        $this->mailer->isHTML(true);
    }
    
    /**
     * Envoie une notification de rappel pour les prospects à relancer
     * 
     * @param string $userEmail Email de l'utilisateur
     * @param string $userName Nom de l'utilisateur
     * @param array $prospectsToFollowUp Liste des prospects à relancer
     * @return bool Succès de l'envoi
     */
    public function sendFollowUpReminder($userEmail, $userName, $prospectsToFollowUp) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            $this->mailer->Subject = 'Rappel : Prospects à relancer - LeadsBuilder';
            
            // Construire le corps de l'email
            $body = $this->buildFollowUpReminderTemplate($userName, $prospectsToFollowUp);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Construit le template HTML pour les rappels de relance
     * 
     * @param string $userName Nom de l'utilisateur
     * @param array $prospectsToFollowUp Liste des prospects à relancer
     * @return string Corps de l'email en HTML
     */
    private function buildFollowUpReminderTemplate($userName, $prospectsToFollowUp) {
        $count = count($prospectsToFollowUp);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Rappel de relance</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #4f46e5;
                    color: #fff;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    padding: 20px;
                    background-color: #f9fafb;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    font-size: 12px;
                    color: #6b7280;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #e5e7eb;
                }
                th {
                    background-color: #f3f4f6;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #4f46e5;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>LeadsBuilder</h1>
                </div>
                <div class="content">
                    <h2>Bonjour ' . htmlspecialchars($userName) . ',</h2>
                    <p>Vous avez <strong>' . $count . ' prospect' . ($count > 1 ? 's' : '') . ' à relancer</strong> dans votre suivi.</p>';
        
        if ($count > 0) {
            $html .= '
                    <table>
                        <thead>
                            <tr>
                                <th>Nom d\'utilisateur</th>
                                <th>Liste</th>
                                <th>Dernière interaction</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($prospectsToFollowUp as $prospect) {
                $html .= '
                            <tr>
                                <td>' . htmlspecialchars($prospect['username']) . '</td>
                                <td>' . htmlspecialchars($prospect['list_name']) . '</td>
                                <td>' . htmlspecialchars($prospect['last_interaction']) . '</td>
                            </tr>';
            }
            
            $html .= '
                        </tbody>
                    </table>';
        }
        
        $html .= '
                    <p>Pensez à contacter ces prospects pour augmenter vos chances de conversion.</p>
                    <a href="https://leadsbuilder.com/followup.php" class="btn">Accéder à mon suivi</a>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' LeadsBuilder. Tous droits réservés.</p>
                    <p>Si vous ne souhaitez plus recevoir ces notifications, vous pouvez les désactiver dans les paramètres de votre compte.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}
