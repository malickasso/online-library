-- ============================================================
-- sql/schema.sql
-- Base de données : bibliotheque_online
-- ============================================================

CREATE DATABASE IF NOT EXISTS bibliotheque_online
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bibliotheque_online;

-- ----------------------------
-- Table : livres
-- ----------------------------
DROP TABLE IF EXISTS liste_lecture;
DROP TABLE IF EXISTS livres;
DROP TABLE IF EXISTS lecteurs;

CREATE TABLE livres (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    titre              VARCHAR(100) NOT NULL,
    auteur             VARCHAR(100) NOT NULL,
    description        TEXT,
    maison_edition     VARCHAR(100),
    nombre_exemplaire  INT NOT NULL DEFAULT 0,
    image              VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ----------------------------
-- Table : lecteurs
-- ----------------------------
CREATE TABLE lecteurs (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nom      VARCHAR(100) NOT NULL,
    prenom   VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('lecteur', 'admin') NOT NULL DEFAULT 'lecteur'
) ENGINE=InnoDB;

-- ----------------------------
-- Table : liste_lecture
-- ----------------------------
CREATE TABLE liste_lecture (
    id_livre      INT NOT NULL,
    id_lecteur    INT NOT NULL,
    date_emprunt  DATE NOT NULL,
    date_retour   DATE NULL,
    PRIMARY KEY (id_livre, id_lecteur),
    CONSTRAINT fk_liste_livre
        FOREIGN KEY (id_livre) REFERENCES livres(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_liste_lecteur
        FOREIGN KEY (id_lecteur) REFERENCES lecteurs(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Données de démonstration
-- ----------------------------
INSERT INTO livres (titre, auteur, description, maison_edition, nombre_exemplaire) VALUES
('Ecological Systems', 'Dr. Aris Thorne', 'Une étude approfondie des écosystèmes et de leur équilibre naturel.', 'GreenPress', 5),
('The Quiet Mind', 'S. J. Miller', 'Un guide sur la pleine conscience et la sérénité intérieure.', 'Mindful Editions', 3),
('Sustainable Design', 'Liam Chen', 'Les principes du design durable appliqués à l’architecture moderne.', 'EcoBuild', 4),
('Zero Waste Living', 'Anita Ray', 'Adopter un mode de vie sans déchet, étape par étape.', 'Archive Press', 6),
('Digital Archiving', 'Archive Press', 'Les meilleures pratiques pour numériser et conserver des documents.', 'Archive Press', 2),
('The Modern Scholar', 'Eduard Weiss', 'Un panorama des méthodes de recherche académique contemporaines.', 'Scholar House', 3);

-- ----------------------------
-- Créer un compte administrateur
-- ----------------------------
-- 1. Inscrivez-vous normalement sur /public/register.php avec l'email de votre choix.
-- 2. Puis exécutez la requête suivante (en remplaçant l'email) pour le promouvoir admin :
--
-- UPDATE lecteurs SET role = 'admin' WHERE email = 'admin@readly.com';

-- ----------------------------
-- Migration (si votre base existait déjà avant l'ajout de la colonne image)
-- ----------------------------
-- ALTER TABLE livres ADD COLUMN image VARCHAR(255) DEFAULT NULL;