# LeadsBuilder PHP

Une plateforme complète de génération et gestion de leads développée en PHP pur sans framework.

## Fonctionnalités

- Création et gestion de formulaires de capture de leads
- Création de landing pages optimisées pour la conversion
- Gestion complète des contacts et segmentation
- Automatisation des emails de suivi
- Analyse et rapports détaillés
- API pour intégration avec d'autres outils
- Interface utilisateur moderne et intuitive
- Système de notifications en temps réel
- **Recherche avancée de profils** avec Supabase

## Installation

1. Clonez ce dépôt dans votre serveur web
2. Importez la base de données depuis `database/leadsbuilder.sql`
3. Configurez les paramètres de connexion dans `config/database.php`
4. Accédez à l'application via votre navigateur

## Configuration requise

- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Extension PDO PHP
- Extension MySQLi
- Extension GD pour le traitement d'images
- Extension Curl pour les API externes

## Intégration Supabase

LeadsBuilder prend désormais en charge l'intégration avec Supabase pour la recherche avancée de profils.

### Configuration de Supabase

1. Créez un compte sur [Supabase](https://supabase.com)
2. Créez un nouveau projet et notez l'URL et les clés API
3. Mettez à jour le fichier `config/database.php` avec vos informations Supabase :
   ```php
   define('SUPABASE_URL', 'https://votre-projet.supabase.co');
   define('SUPABASE_KEY', 'votre-clé-service');
   define('SUPABASE_ANON_KEY', 'votre-clé-anonyme');
   ```
4. Créez les tables nécessaires dans Supabase :
   - `profiles` : pour stocker les profils des utilisateurs
   - `user_searches` : pour enregistrer les recherches effectuées

### Création de la fonction de recherche

Exécutez le script `create_supabase_function.php` pour créer la fonction RPC `search_profiles` dans votre base de données Supabase, ou exécutez manuellement le SQL suivant dans l'éditeur SQL de Supabase :

```sql
CREATE OR REPLACE FUNCTION search_profiles(
    search_name TEXT DEFAULT NULL,
    search_bio TEXT DEFAULT NULL,
    min_followers INT DEFAULT NULL,
    max_followers INT DEFAULT NULL,
    limit_val INT DEFAULT 20
) RETURNS SETOF profiles AS $$
DECLARE
    query TEXT := 'SELECT * FROM profiles WHERE 1=1';
BEGIN
    IF search_name IS NOT NULL THEN
        query := query || ' AND name ILIKE ''%' || search_name || '%''';
    END IF;
    
    IF search_bio IS NOT NULL THEN
        query := query || ' AND bio ILIKE ''%' || search_bio || '%''';
    END IF;
    
    IF min_followers IS NOT NULL THEN
        query := query || ' AND followers >= ' || min_followers;
    END IF;
    
    IF max_followers IS NOT NULL THEN
        query := query || ' AND followers <= ' || max_followers;
    END IF;
    
    query := query || ' ORDER BY followers DESC LIMIT ' || limit_val;
    
    RETURN QUERY EXECUTE query;
END;
$$ LANGUAGE plpgsql;
```

### Test de l'intégration

Pour tester l'intégration Supabase, vous pouvez utiliser les scripts suivants :

1. `test_supabase_api.php` : Vérifie la connexion à l'API Supabase et teste les opérations de base
2. `insert_test_data.php` : Insère des données de test dans la base de données Supabase
3. `test_search.php` : Interface de test pour la recherche avancée de profils

### Interface de recherche

L'interface de recherche de profils comprend :
- Recherche par nom
- Recherche dans la bio
- Filtres de followers (min/max)
- Compteur de recherches (limité à 30 recherches par session)
