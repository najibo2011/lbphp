-- Fonction de recherche avancée pour les profils
CREATE OR REPLACE FUNCTION search_profiles(
    search_name TEXT DEFAULT NULL,
    search_bio TEXT DEFAULT NULL,
    min_followers INT DEFAULT NULL,
    max_followers INT DEFAULT NULL,
    limit_val INT DEFAULT 20
)
RETURNS SETOF profiles AS $$
BEGIN
    RETURN QUERY
    SELECT *
    FROM profiles
    WHERE 
        (search_name IS NULL OR name ILIKE '%' || search_name || '%')
        AND (search_bio IS NULL OR bio ILIKE '%' || search_bio || '%')
        AND (min_followers IS NULL OR followers >= min_followers)
        AND (max_followers IS NULL OR followers <= max_followers)
    ORDER BY followers DESC
    LIMIT limit_val;
END;
$$ LANGUAGE plpgsql;
