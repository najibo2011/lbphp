-- Base de données LeadsBuilder PHP

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS leadsbuilder DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE leadsbuilder;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expiry DATETIME DEFAULT NULL,
    search_count INT DEFAULT 0,
    search_limit INT DEFAULT 30,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des profils
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    bio TEXT DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    followers INT DEFAULT 0,
    following INT DEFAULT 0,
    posts INT DEFAULT 0,
    website VARCHAR(255) DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL,
    source VARCHAR(50) DEFAULT NULL,
    source_id VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (source, source_id)
) ENGINE=InnoDB;

-- Table des listes
CREATE TABLE IF NOT EXISTS lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des profils dans les listes
CREATE TABLE IF NOT EXISTS list_profiles (
    list_id INT NOT NULL,
    profile_id INT NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (list_id, profile_id),
    FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des suivis
CREATE TABLE IF NOT EXISTS follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profile_id INT NOT NULL,
    status ENUM('pending', 'following', 'unfollowed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, profile_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des contacts CRM
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profile_id INT NOT NULL,
    status ENUM('lead', 'prospect', 'customer', 'lost') DEFAULT 'lead',
    notes TEXT DEFAULT NULL,
    last_contact DATETIME DEFAULT NULL,
    next_contact DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, profile_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des activités CRM
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    type ENUM('email', 'call', 'meeting', 'note', 'other') NOT NULL,
    description TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insertion de données d'exemple
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('Utilisateur Test', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'); -- password: password

-- Insertion de profils d'exemple
INSERT INTO profiles (name, username, bio, followers, following, posts, location, source) VALUES
('Jean Dupont', 'jeandupont', 'Entrepreneur et consultant en marketing digital', 5420, 1250, 342, 'Paris, France', 'instagram'),
('Marie Martin', 'mariemartin', 'Designer UX/UI | Créatrice de contenu', 12500, 980, 567, 'Lyon, France', 'instagram'),
('Pierre Durand', 'pierredurand', 'Développeur web full-stack | Passionné de nouvelles technologies', 3200, 450, 123, 'Bordeaux, France', 'instagram'),
('Sophie Leroy', 'sophieleroy', 'Coach en développement personnel | Auteure', 25600, 1800, 789, 'Marseille, France', 'instagram'),
('Thomas Bernard', 'thomasbernard', 'Photographe professionnel | Voyageur', 18900, 2100, 456, 'Nantes, France', 'instagram'),
('Julie Petit', 'juliepetit', 'Influenceuse mode et lifestyle', 45000, 1200, 890, 'Paris, France', 'instagram'),
('Nicolas Moreau', 'nicolasmoreau', 'Expert en référencement SEO | Formateur', 7800, 650, 234, 'Toulouse, France', 'instagram'),
('Emma Dubois', 'emmadubois', 'Nutritionniste | Créatrice de recettes healthy', 32000, 1500, 678, 'Lille, France', 'instagram'),
('Lucas Roux', 'lucasroux', 'Entrepreneur dans la tech | Investisseur', 9600, 890, 345, 'Strasbourg, France', 'instagram'),
('Camille Fournier', 'camillefournier', 'Artiste peintre | Professeur d\'art', 15700, 980, 567, 'Montpellier, France', 'instagram');

-- Insertion de listes d'exemple
INSERT INTO lists (user_id, name, description, is_public) VALUES
(2, 'Influenceurs Mode', 'Liste des influenceurs mode à contacter', FALSE),
(2, 'Experts Tech', 'Experts en technologie pour collaboration', FALSE),
(2, 'Créateurs de contenu', 'Créateurs de contenu pour partenariats', TRUE);

-- Insertion de profils dans les listes
INSERT INTO list_profiles (list_id, profile_id, notes) VALUES
(1, 6, 'À contacter pour la nouvelle collection'),
(1, 8, 'Partenariat potentiel pour produits healthy'),
(2, 3, 'Expert en développement web'),
(2, 7, 'Consultant SEO potentiel'),
(3, 2, 'Designer talentueuse'),
(3, 5, 'Excellentes photos de produits'),
(3, 10, 'Style artistique unique');

-- Insertion de suivis d'exemple
INSERT INTO follows (user_id, profile_id, status) VALUES
(2, 1, 'following'),
(2, 3, 'following'),
(2, 5, 'following'),
(2, 7, 'pending'),
(2, 9, 'pending');

-- Insertion de contacts CRM d'exemple
INSERT INTO contacts (user_id, profile_id, status, notes, last_contact, next_contact) VALUES
(2, 2, 'lead', 'Intéressée par une collaboration', '2025-03-15 14:30:00', '2025-04-15 10:00:00'),
(2, 4, 'prospect', 'A demandé plus d\'informations sur nos services', '2025-03-20 11:15:00', '2025-04-10 14:00:00'),
(2, 6, 'customer', 'Partenariat en cours pour la campagne printemps', '2025-04-01 09:45:00', '2025-04-20 15:30:00'),
(2, 8, 'lead', 'À contacter pour présentation de produits', NULL, '2025-04-12 11:00:00'),
(2, 10, 'prospect', 'Intéressée par nos services de design', '2025-03-25 16:20:00', '2025-04-18 13:45:00');

-- Insertion d'activités CRM d'exemple
INSERT INTO activities (contact_id, type, description) VALUES
(1, 'email', 'Email de présentation envoyé'),
(1, 'note', 'A visité notre site web plusieurs fois'),
(2, 'call', 'Appel de 15 minutes pour discuter des besoins'),
(2, 'email', 'Envoi de la documentation demandée'),
(3, 'meeting', 'Réunion de lancement de la campagne'),
(3, 'email', 'Envoi du contrat de partenariat'),
(5, 'email', 'Email de suivi après demande d\'information'),
(5, 'note', 'A mentionné un budget limité pour le projet');
