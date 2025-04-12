<?php
/**
 * Classe pour l'exécution des tests
 */
class TestRunner {
    private $testDirectory;
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    private $skippedTests = 0;
    
    /**
     * Constructeur
     * 
     * @param string|null $testDirectory Répertoire contenant les tests à exécuter
     */
    public function __construct($testDirectory = null) {
        $this->testDirectory = $testDirectory ?: __DIR__;
    }
    
    /**
     * Exécuter tous les tests
     */
    public function runAllTests() {
        $this->results = [];
        $this->totalTests = 0;
        $this->passedTests = 0;
        $this->failedTests = 0;
        $this->skippedTests = 0;
        
        $testFiles = $this->findTestFiles();
        
        foreach ($testFiles as $testFile) {
            $this->runTestFile($testFile);
        }
        
        return $this->results;
    }
    
    /**
     * Exécuter un fichier de test spécifique
     */
    public function runTestFile($testFile) {
        require_once $testFile;
        
        $className = $this->getClassNameFromFile($testFile);
        
        if (!class_exists($className)) {
            $this->results[$testFile] = [
                'status' => 'error',
                'message' => "La classe {$className} n'existe pas dans le fichier {$testFile}"
            ];
            return;
        }
        
        $testClass = new $className();
        $methods = get_class_methods($testClass);
        
        $fileResults = [
            'class' => $className,
            'tests' => []
        ];
        
        // Exécuter la méthode setUp si elle existe
        if (in_array('setUp', $methods)) {
            $testClass->setUp();
        }
        
        foreach ($methods as $method) {
            // Ne pas exécuter les méthodes qui ne commencent pas par "test"
            if (strpos($method, 'test') !== 0) {
                continue;
            }
            
            $this->totalTests++;
            
            try {
                // Exécuter la méthode de test
                $testClass->$method();
                
                $this->passedTests++;
                $fileResults['tests'][$method] = [
                    'status' => 'passed',
                    'message' => 'Test réussi'
                ];
            } catch (SkippedTestException $e) {
                $this->skippedTests++;
                $fileResults['tests'][$method] = [
                    'status' => 'skipped',
                    'message' => $e->getMessage()
                ];
            } catch (AssertionFailedException $e) {
                $this->failedTests++;
                $fileResults['tests'][$method] = [
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            } catch (Exception $e) {
                $this->failedTests++;
                $fileResults['tests'][$method] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
        }
        
        // Exécuter la méthode tearDown si elle existe
        if (in_array('tearDown', $methods)) {
            $testClass->tearDown();
        }
        
        $this->results[$testFile] = $fileResults;
    }
    
    /**
     * Trouver tous les fichiers de test
     */
    private function findTestFiles() {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->testDirectory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getFilename(), 'Test.php') !== false) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Obtenir le nom de la classe à partir du nom du fichier
     */
    private function getClassNameFromFile($file) {
        $fileName = basename($file, '.php');
        return $fileName;
    }
    
    /**
     * Obtenir les résultats des tests
     * 
     * @return array Résultats des tests
     */
    public function getResults() {
        return $this->results;
    }
    
    /**
     * Obtenir un résumé des résultats des tests
     */
    public function getSummary() {
        return [
            'total' => $this->totalTests,
            'passed' => $this->passedTests,
            'failed' => $this->failedTests,
            'skipped' => $this->skippedTests,
            'success_rate' => $this->totalTests > 0 ? round($this->passedTests / $this->totalTests * 100, 2) : 0
        ];
    }
    
    /**
     * Afficher les résultats des tests
     */
    public function displayResults() {
        $summary = $this->getSummary();
        
        echo "Résultats des tests :\n";
        echo "-------------------\n";
        echo "Total : {$summary['total']}\n";
        echo "Réussis : {$summary['passed']}\n";
        echo "Échoués : {$summary['failed']}\n";
        echo "Ignorés : {$summary['skipped']}\n";
        echo "Taux de réussite : {$summary['success_rate']}%\n\n";
        
        foreach ($this->results as $file => $fileResults) {
            echo "Fichier : " . basename($file) . "\n";
            echo "Classe : {$fileResults['class']}\n";
            
            foreach ($fileResults['tests'] as $method => $testResult) {
                $status = $testResult['status'];
                $statusStr = '';
                
                switch ($status) {
                    case 'passed':
                        $statusStr = "\033[32mRéussi\033[0m";
                        break;
                    case 'failed':
                        $statusStr = "\033[31mÉchoué\033[0m";
                        break;
                    case 'skipped':
                        $statusStr = "\033[33mIgnoré\033[0m";
                        break;
                    case 'error':
                        $statusStr = "\033[31mErreur\033[0m";
                        break;
                }
                
                echo "  {$method} : {$statusStr}\n";
                
                if ($status !== 'passed') {
                    echo "    Message : {$testResult['message']}\n";
                    
                    if (isset($testResult['file']) && isset($testResult['line'])) {
                        echo "    Fichier : {$testResult['file']} (ligne {$testResult['line']})\n";
                    }
                }
            }
            
            echo "\n";
        }
    }
}
