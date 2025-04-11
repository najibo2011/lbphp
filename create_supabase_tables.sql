-- Création de la table profiles si elle n'existe pas déjà
CREATE TABLE IF NOT EXISTS profiles (
    id SERIAL PRIMARY KEY,
    instagram_url VARCHAR(255),
    username VARCHAR(100) NOT NULL,
    full_name VARCHAR(100),
    bio TEXT,
    website VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    country VARCHAR(100),
    city VARCHAR(100),
    language VARCHAR(50),
    followers INTEGER DEFAULT 0,
    following INTEGER DEFAULT 0,
    avatar VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Création de la table user_searches pour enregistrer les recherches
CREATE TABLE IF NOT EXISTS user_searches (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    search_params JSONB,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Création d'index pour améliorer les performances de recherche
CREATE INDEX IF NOT EXISTS idx_profiles_username ON profiles USING gin (username gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_profiles_bio ON profiles USING gin (bio gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_profiles_followers ON profiles (followers);

-- Activation de l'extension pg_trgm pour la recherche de texte
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Création de la fonction de recherche
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
        query := query || ' AND (username ILIKE ''%' || search_name || '%'' OR full_name ILIKE ''%' || search_name || '%'')';
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
