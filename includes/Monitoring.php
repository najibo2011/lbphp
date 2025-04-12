<?php
/**
 * Classe pour le monitoring de l'application
 */
class Monitoring {
    private $logDir;
    private $errorLogFile;
    private $accessLogFile;
    private $performanceLogFile;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->logDir = __DIR__ . '/../logs/';
        $this->errorLogFile = $this->logDir . 'error.log';
        $this->accessLogFile = $this->logDir . 'access.log';
        $this->performanceLogFile = $this->logDir . 'performance.log';
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Journaliser une erreur
     */
    public function logError($message, $context = [], $severity = 'error') {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'user_id' => $_SESSION['user_id'] ?? 'Guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
        ];
        
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($this->errorLogFile, $logLine, FILE_APPEND);
        
        // Si c'est une erreur critique, envoyer une alerte
        if ($severity === 'critical') {
            $this->sendAlert('Erreur critique', $message, $context);
        }
    }
    
    /**
     * Journaliser un accès
     */
    public function logAccess() {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'user_id' => $_SESSION['user_id'] ?? 'Guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'Unknown'
        ];
        
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($this->accessLogFile, $logLine, FILE_APPEND);
    }
    
    /**
     * Journaliser les performances
     */
    public function logPerformance($action, $duration, $context = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'duration' => $duration,
            'context' => $context,
            'user_id' => $_SESSION['user_id'] ?? 'Guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'Unknown'
        ];
        
        $logLine = json_encode($logData) . PHP_EOL;
        file_put_contents($this->performanceLogFile, $logLine, FILE_APPEND);
        
        // Si la durée dépasse un seuil, envoyer une alerte
        if ($duration > 5000) { // 5 secondes
            $this->sendAlert('Performance dégradée', "L'action {$action} a pris {$duration}ms", $context);
        }
    }
    
    /**
     * Mesurer le temps d'exécution d'une fonction
     */
    public function measureExecutionTime($callback, $action, $context = []) {
        $startTime = microtime(true);
        
        $result = $callback();
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // en millisecondes
        
        $this->logPerformance($action, $duration, $context);
        
        return $result;
    }
    
    /**
     * Envoyer une alerte
     */
    private function sendAlert($title, $message, $context = []) {
        // Préparer le contenu de l'alerte
        $content = "Alerte : {$title}\n";
        $content .= "Message : {$message}\n";
        $content .= "Date : " . date('Y-m-d H:i:s') . "\n";
        $content .= "Contexte : " . json_encode($context) . "\n";
        
        // Envoyer l'alerte par email
        $to = 'alerts@leadsbuilder.com';
        $subject = "Alerte LeadsBuilder : {$title}";
        $headers = 'From: monitoring@leadsbuilder.com' . "\r\n";
        
        mail($to, $subject, $content, $headers);
        
        // Journaliser l'alerte
        $this->logError("Alerte envoyée : {$title} - {$message}", $context, 'alert');
    }
    
    /**
     * Collecter les métriques d'utilisation
     */
    public function collectMetrics() {
        $db = new Database();
        
        // Nombre d'utilisateurs actifs aujourd'hui
        $activeUsers = $db->query(
            "SELECT COUNT(DISTINCT user_id) as count FROM access_logs WHERE DATE(timestamp) = CURDATE()"
        )->fetch();
        
        // Nombre de recherches effectuées aujourd'hui
        $searches = $db->query(
            "SELECT COUNT(*) as count FROM search_history WHERE DATE(created_at) = CURDATE()"
        )->fetch();
        
        // Nombre de listes créées aujourd'hui
        $lists = $db->query(
            "SELECT COUNT(*) as count FROM lists WHERE DATE(created_at) = CURDATE()"
        )->fetch();
        
        // Nombre de profils ajoutés aux listes aujourd'hui
        $profiles = $db->query(
            "SELECT COUNT(*) as count FROM list_profiles WHERE DATE(created_at) = CURDATE()"
        )->fetch();
        
        // Temps de réponse moyen des requêtes aujourd'hui
        $avgResponseTime = $db->query(
            "SELECT AVG(duration) as avg FROM performance_logs WHERE DATE(timestamp) = CURDATE()"
        )->fetch();
        
        // Nombre d'erreurs aujourd'hui
        $errors = $db->query(
            "SELECT COUNT(*) as count FROM error_logs WHERE DATE(timestamp) = CURDATE()"
        )->fetch();
        
        // Assembler les métriques
        $metrics = [
            'timestamp' => date('Y-m-d H:i:s'),
            'active_users' => $activeUsers['count'] ?? 0,
            'searches' => $searches['count'] ?? 0,
            'lists_created' => $lists['count'] ?? 0,
            'profiles_added' => $profiles['count'] ?? 0,
            'avg_response_time' => $avgResponseTime['avg'] ?? 0,
            'errors' => $errors['count'] ?? 0
        ];
        
        // Enregistrer les métriques
        $db->query(
            "INSERT INTO metrics (timestamp, active_users, searches, lists_created, profiles_added, avg_response_time, errors)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?)",
            [
                $metrics['active_users'],
                $metrics['searches'],
                $metrics['lists_created'],
                $metrics['profiles_added'],
                $metrics['avg_response_time'],
                $metrics['errors']
            ]
        );
        
        return $metrics;
    }
    
    /**
     * Vérifier l'état de santé de l'application
     */
    public function healthCheck() {
        $status = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => []
        ];
        
        // Vérifier la connexion à la base de données
        try {
            $db = new Database();
            $db->query("SELECT 1");
            $status['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Connexion à la base de données réussie'
            ];
        } catch (Exception $e) {
            $status['status'] = 'error';
            $status['checks']['database'] = [
                'status' => 'error',
                'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
            ];
        }
        
        // Vérifier l'espace disque
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsage = ($diskTotal - $diskFree) / $diskTotal * 100;
        
        if ($diskUsage > 90) {
            $status['status'] = 'warning';
            $status['checks']['disk'] = [
                'status' => 'warning',
                'message' => 'Espace disque faible : ' . round($diskUsage, 2) . '% utilisé',
                'free' => $diskFree,
                'total' => $diskTotal
            ];
        } else {
            $status['checks']['disk'] = [
                'status' => 'ok',
                'message' => 'Espace disque suffisant : ' . round($diskUsage, 2) . '% utilisé',
                'free' => $diskFree,
                'total' => $diskTotal
            ];
        }
        
        // Vérifier la mémoire
        if (function_exists('memory_get_usage')) {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->getMemoryLimit();
            
            if ($memoryLimit > 0) {
                $memoryUsagePercent = $memoryUsage / $memoryLimit * 100;
                
                if ($memoryUsagePercent > 80) {
                    $status['status'] = 'warning';
                    $status['checks']['memory'] = [
                        'status' => 'warning',
                        'message' => 'Utilisation mémoire élevée : ' . round($memoryUsagePercent, 2) . '%',
                        'usage' => $memoryUsage,
                        'limit' => $memoryLimit
                    ];
                } else {
                    $status['checks']['memory'] = [
                        'status' => 'ok',
                        'message' => 'Utilisation mémoire normale : ' . round($memoryUsagePercent, 2) . '%',
                        'usage' => $memoryUsage,
                        'limit' => $memoryLimit
                    ];
                }
            }
        }
        
        // Vérifier les erreurs récentes
        $errorCount = 0;
        if (file_exists($this->errorLogFile)) {
            $errorLogs = file($this->errorLogFile);
            $recentErrors = array_slice($errorLogs, -10);
            
            foreach ($recentErrors as $error) {
                $errorData = json_decode($error, true);
                if ($errorData && strtotime($errorData['timestamp']) > strtotime('-1 hour')) {
                    $errorCount++;
                }
            }
            
            if ($errorCount > 5) {
                $status['status'] = 'error';
                $status['checks']['errors'] = [
                    'status' => 'error',
                    'message' => $errorCount . ' erreurs détectées dans la dernière heure',
                    'count' => $errorCount
                ];
            } else if ($errorCount > 0) {
                $status['checks']['errors'] = [
                    'status' => 'warning',
                    'message' => $errorCount . ' erreurs détectées dans la dernière heure',
                    'count' => $errorCount
                ];
            } else {
                $status['checks']['errors'] = [
                    'status' => 'ok',
                    'message' => 'Aucune erreur récente détectée',
                    'count' => 0
                ];
            }
        }
        
        return $status;
    }
    
    /**
     * Obtenir la limite de mémoire en octets
     */
    private function getMemoryLimit() {
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit === '-1') {
            return 0; // Illimité
        }
        
        $value = (int) $memoryLimit;
        $unit = strtolower(substr($memoryLimit, -1));
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}
