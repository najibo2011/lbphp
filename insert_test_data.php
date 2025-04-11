<?php
require_once 'includes/Database.php';
require_once 'includes/SupabaseClient.php';

// Fonction pour générer des données aléatoires
function generateRandomProfiles($count = 20) {
    $profiles = [];
    $firstNames = ['Jean', 'Marie', 'Pierre', 'Sophie', 'Thomas', 'Émilie', 'Lucas', 'Camille', 'Antoine', 'Léa'];
    $lastNames = ['Dupont', 'Martin', 'Dubois', 'Moreau', 'Laurent', 'Simon', 'Michel', 'Leroy', 'Lefebvre', 'Garcia'];
    $industries = ['Marketing', 'Tech', 'Finance', 'Santé', 'Éducation', 'Immobilier', 'Mode', 'Alimentation', 'Voyage', 'Sport'];
    $positions = ['CEO', 'Directeur', 'Manager', 'Consultant', 'Freelance', 'Entrepreneur', 'Spécialiste', 'Analyste', 'Designer', 'Développeur'];
    
    for ($i = 0; $i < $count; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $industry = $industries[array_rand($industries)];
        $position = $positions[array_rand($positions)];
        
        $followers = rand(100, 50000);
        $following = rand(50, 500);
        
        $profiles[] = [
            'name' => $firstName . ' ' . $lastName,
            'bio' => $position . ' en ' . $industry . '. ' . rand(2, 10) . ' ans d\'expérience. Passionné par l\'innovation et le développement.',
            'followers' => $followers,
            'following' => $following,
            'avatar' => 'https://i.pravatar.cc/150?u=' . $i,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    return $profiles;
}

// Tester l'insertion de données
try {
    echo "<h1>Insertion de données de test dans Supabase</h1>";
    
    // Vérifier la connexion
    $db = Database::getInstance();
    
    if (!$db->isSupabase()) {
        die("Erreur : La connexion à Supabase n'est pas active. Vérifiez votre configuration.");
    }
    
    // Créer un client Supabase
    $supabase = new SupabaseClient();
    
    // Générer des profils aléatoires
    $profiles = generateRandomProfiles(30);
    
    // Insérer les profils
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($profiles as $profile) {
        try {
            $result = $supabase->post('/rest/v1/profiles', $profile);
            echo "<p>Profil inséré : {$profile['name']}</p>";
            $successCount++;
        } catch (Exception $e) {
            echo "<p>Erreur lors de l'insertion du profil {$profile['name']} : {$e->getMessage()}</p>";
            $errorCount++;
        }
    }
    
    echo "<h2>Résumé</h2>";
    echo "<p>{$successCount} profils insérés avec succès.</p>";
    echo "<p>{$errorCount} erreurs rencontrées.</p>";
    
} catch (Exception $e) {
    echo "<h2>Erreur</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
