<?php
/**
 * Classe de base pour les tests
 */
class TestCase {
    /**
     * Méthode appelée avant chaque test
     */
    public function setUp() {
        // À surcharger dans les classes enfants
    }
    
    /**
     * Méthode appelée après chaque test
     */
    public function tearDown() {
        // À surcharger dans les classes enfants
    }
    
    /**
     * Vérifier que deux valeurs sont égales
     */
    protected function assertEquals($expected, $actual, $message = null) {
        if ($expected !== $actual) {
            $message = $message ?: "Attendu: " . $this->formatValue($expected) . ", Obtenu: " . $this->formatValue($actual);
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une valeur est vraie
     */
    protected function assertTrue($condition, $message = null) {
        if ($condition !== true) {
            $message = $message ?: "La condition n'est pas vraie";
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une valeur est fausse
     */
    protected function assertFalse($condition, $message = null) {
        if ($condition !== false) {
            $message = $message ?: "La condition n'est pas fausse";
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une valeur est null
     */
    protected function assertNull($value, $message = null) {
        if ($value !== null) {
            $message = $message ?: "La valeur n'est pas null";
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une valeur n'est pas null
     */
    protected function assertNotNull($value, $message = null) {
        if ($value === null) {
            $message = $message ?: "La valeur est null";
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'un tableau contient une valeur
     */
    protected function assertContains($needle, $haystack, $message = null) {
        if (!in_array($needle, $haystack)) {
            $message = $message ?: "Le tableau ne contient pas la valeur " . $this->formatValue($needle);
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une chaîne contient une sous-chaîne
     */
    protected function assertStringContains($needle, $haystack, $message = null) {
        if (strpos($haystack, $needle) === false) {
            $message = $message ?: "La chaîne ne contient pas la sous-chaîne " . $this->formatValue($needle);
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'un tableau a une certaine taille
     */
    protected function assertCount($expectedCount, $haystack, $message = null) {
        $count = count($haystack);
        if ($count !== $expectedCount) {
            $message = $message ?: "Taille attendue: {$expectedCount}, Taille obtenue: {$count}";
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une valeur est du type attendu
     */
    protected function assertInstanceOf($expected, $actual, $message = null) {
        if (!($actual instanceof $expected)) {
            $message = $message ?: "L'objet n'est pas une instance de {$expected}";
            throw new AssertionFailedException($message);
        }
    }
    
    /**
     * Vérifier qu'une exception est levée
     */
    protected function assertException($exceptionClass, $callback, $message = null) {
        try {
            $callback();
            $message = $message ?: "Aucune exception n'a été levée";
            throw new AssertionFailedException($message);
        } catch (Exception $e) {
            if (!($e instanceof $exceptionClass)) {
                $message = $message ?: "Exception attendue: {$exceptionClass}, Exception obtenue: " . get_class($e);
                throw new AssertionFailedException($message);
            }
        }
    }
    
    /**
     * Ignorer un test
     */
    protected function skip($message = "Test ignoré") {
        throw new SkippedTestException($message);
    }
    
    /**
     * Formater une valeur pour l'affichage
     */
    private function formatValue($value) {
        if (is_null($value)) {
            return 'null';
        } else if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } else if (is_array($value)) {
            return 'Array(' . count($value) . ')';
        } else if (is_object($value)) {
            return 'Object(' . get_class($value) . ')';
        } else if (is_string($value)) {
            return '"' . $value . '"';
        } else {
            return (string) $value;
        }
    }
}

/**
 * Exception levée lorsqu'une assertion échoue
 */
class AssertionFailedException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Exception levée lorsqu'un test est ignoré
 */
class SkippedTestException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
