-- ============================================================================
-- VALRES2 - Base de données de réservation de salles M2L
-- Script de création et d'initialisation
-- ============================================================================

-- Suppression de la base si elle existe
DROP DATABASE IF EXISTS valres2;

-- Création de la base de données
CREATE DATABASE valres2 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE valres2;

-- ============================================================================
-- Table des utilisateurs
-- ============================================================================
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('administrateur', 'secretariat', 'responsable', 'utilisateur') NOT NULL,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_actif (actif)
) ENGINE=InnoDB;

-- ============================================================================
-- Table des salles
-- ============================================================================
CREATE TABLE salles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    capacite INT NOT NULL DEFAULT 0,
    equipements TEXT,
    active BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nom (nom),
    INDEX idx_active (active),
    INDEX idx_capacite (capacite)
) ENGINE=InnoDB;

-- ============================================================================
-- Table des réservations
-- ============================================================================
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    salle_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    objet VARCHAR(255) NOT NULL,
    description TEXT,
    etat ENUM('provisoire', 'confirme', 'annule') DEFAULT 'provisoire',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (salle_id) REFERENCES salles(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    
    INDEX idx_salle_date (salle_id, date_debut, date_fin),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_etat (etat),
    INDEX idx_date_debut (date_debut),
    INDEX idx_date_fin (date_fin)
) ENGINE=InnoDB;

-- ============================================================================
-- Table de logs (optionnelle pour traçabilité)
-- ============================================================================
CREATE TABLE logs_activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    action VARCHAR(100) NOT NULL,
    table_concernee VARCHAR(50),
    enregistrement_id INT,
    details JSON,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    
    INDEX idx_utilisateur_date (utilisateur_id, date_action),
    INDEX idx_action (action),
    INDEX idx_table (table_concernee)
) ENGINE=InnoDB;

-- ============================================================================
-- Insertion des données de test
-- ============================================================================

-- Utilisateurs de test
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Système', 'admin@m2l.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrateur'),
('Dupont', 'Marie', 'secretariat@m2l.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretariat'),
('Martin', 'Pierre', 'responsable@m2l.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable'),
('Legrand', 'Sophie', 'utilisateur@m2l.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur'),
('Bernard', 'Jean', 'jean.bernard@m2l.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable'),
('Moreau', 'Claire', 'claire.moreau@m2l.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur');

-- Salles de test
INSERT INTO salles (nom, description, capacite, equipements) VALUES
('Salle de Conférence A', 'Grande salle de conférence avec équipement audiovisuel complet', 50, 'Vidéoprojecteur, Écran, Système audio, Wifi, Climatisation'),
('Salle de Réunion B', 'Salle de réunion pour petites équipes', 12, 'Tableau blanc, Wifi, Climatisation'),
('Salle de Formation C', 'Salle équipée pour les formations avec ordinateurs', 20, 'Ordinateurs, Vidéoprojecteur, Wifi, Tableau interactif'),
('Amphithéâtre D', 'Grand amphithéâtre pour événements', 100, 'Sonorisation, Éclairage scénique, Vidéoprojecteur, Wifi'),
('Salle de Réunion E', 'Petite salle pour réunions confidentielles', 8, 'Tableau blanc, Wifi, Isolation phonique'),
('Salle Polyvalente F', 'Salle modulable selon les besoins', 30, 'Mobilier modulable, Vidéoprojecteur, Wifi, Climatisation');

-- Réservations de test
INSERT INTO reservations (salle_id, utilisateur_id, date_debut, date_fin, objet, description, etat) VALUES
(1, 3, '2024-11-15 09:00:00', '2024-11-15 12:00:00', 'Réunion équipe marketing', 'Présentation des nouveaux projets', 'confirme'),
(2, 5, '2024-11-15 14:00:00', '2024-11-15 16:00:00', 'Formation utilisateurs', 'Formation sur le nouveau logiciel', 'provisoire'),
(3, 3, '2024-11-16 10:00:00', '2024-11-16 17:00:00', 'Formation développement', 'Atelier développement web', 'confirme'),
(1, 5, '2024-11-17 08:30:00', '2024-11-17 11:30:00', 'Conférence annuelle', 'Présentation des résultats annuels', 'provisoire'),
(4, 3, '2024-11-18 13:00:00', '2024-11-18 18:00:00', 'Événement public', 'Conférence ouverte au public', 'confirme'),
(2, 5, '2024-11-19 09:00:00', '2024-11-19 11:00:00', 'Réunion direction', 'Point mensuel direction', 'provisoire');

-- ============================================================================
-- Contraintes et vérifications supplémentaires
-- ============================================================================

-- Contrainte pour vérifier que la date de fin est après la date de début
ALTER TABLE reservations 
ADD CONSTRAINT chk_dates_coherentes 
CHECK (date_fin > date_debut);

-- Contrainte pour vérifier la capacité positive des salles
ALTER TABLE salles 
ADD CONSTRAINT chk_capacite_positive 
CHECK (capacite > 0);

-- ============================================================================
-- Vues utiles pour les requêtes fréquentes
-- ============================================================================

-- Vue des réservations avec détails complets
CREATE VIEW v_reservations_completes AS
SELECT 
    r.id,
    r.date_debut,
    r.date_fin,
    r.objet,
    r.description,
    r.etat,
    r.date_creation,
    s.nom AS salle_nom,
    s.capacite AS salle_capacite,
    s.equipements AS salle_equipements,
    u.nom AS utilisateur_nom,
    u.prenom AS utilisateur_prenom,
    u.email AS utilisateur_email,
    u.role AS utilisateur_role
FROM reservations r
JOIN salles s ON r.salle_id = s.id
JOIN utilisateurs u ON r.utilisateur_id = u.id;

-- Vue des salles avec nombre de réservations
CREATE VIEW v_salles_statistiques AS
SELECT 
    s.id,
    s.nom,
    s.description,
    s.capacite,
    s.equipements,
    s.active,
    COUNT(r.id) AS nb_reservations,
    COUNT(CASE WHEN r.etat = 'confirme' THEN 1 END) AS nb_reservations_confirmees,
    COUNT(CASE WHEN r.etat = 'provisoire' THEN 1 END) AS nb_reservations_provisoires
FROM salles s
LEFT JOIN reservations r ON s.id = r.salle_id
WHERE s.active = TRUE
GROUP BY s.id, s.nom, s.description, s.capacite, s.equipements, s.active;

-- ============================================================================
-- Fonctions de nettoyage et maintenance
-- ============================================================================

-- Procédure pour nettoyer les anciennes réservations annulées
DELIMITER //
CREATE PROCEDURE CleanOldCancelledReservations()
BEGIN
    DELETE FROM reservations 
    WHERE etat = 'annule' 
    AND date_creation < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //
DELIMITER ;

-- Procédure pour archiver les réservations anciennes
DELIMITER //
CREATE PROCEDURE ArchiveOldReservations()
BEGIN
    -- Cette procédure pourrait déplacer les anciennes réservations vers une table d'archive
    -- Pour l'instant, on se contente d'un commentaire
    SELECT 'Procédure d\'archivage à implémenter selon les besoins' AS message;
END //
DELIMITER ;

-- ============================================================================
-- Index pour optimiser les performances
-- ============================================================================

-- Index composite pour la recherche de disponibilité des salles
CREATE INDEX idx_reservations_disponibilite 
ON reservations (salle_id, date_debut, date_fin, etat);

-- Index pour les recherches par utilisateur et date
CREATE INDEX idx_reservations_user_date 
ON reservations (utilisateur_id, date_debut DESC);

-- ============================================================================
-- Commentaires et documentation
-- ============================================================================

-- Ajout de commentaires sur les tables principales
ALTER TABLE utilisateurs COMMENT = 'Table des utilisateurs du système avec gestion des rôles';
ALTER TABLE salles COMMENT = 'Table des salles disponibles à la réservation';
ALTER TABLE reservations COMMENT = 'Table des réservations avec gestion des états';
ALTER TABLE logs_activites COMMENT = 'Table de logs pour la traçabilité des actions';

-- ============================================================================
-- Fin du script d'initialisation
-- ============================================================================

-- Affichage d'un message de confirmation
SELECT 'Base de données VALRES2 créée et initialisée avec succès!' AS message;
SELECT 'Comptes de test créés avec mot de passe par défaut : secret' AS info;
SELECT 'N\'oubliez pas de changer les mots de passe en production!' AS securite;