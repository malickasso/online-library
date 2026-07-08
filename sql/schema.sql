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
('L''Aventure ambiguë', 'Cheikh Hamidou Kane', 'Un roman sénégalais qui explore le conflit entre les valeurs traditionnelles africaines et l''éducation occidentale.', 'Julliard', 5),
('Une si longue lettre', 'Mariama Bâ', 'Un roman épistolaire abordant la condition de la femme et les réalités sociales au Sénégal.', 'Nouvelles Éditions Africaines', 6),
('Le Soleil des indépendances', 'Ahmadou Kourouma', 'Une critique des désillusions qui ont suivi les indépendances africaines.', 'Éditions du Seuil', 4),
('Les Bouts de bois de Dieu', 'Ousmane Sembène', 'Une œuvre majeure retraçant la grève des cheminots du Dakar-Niger.', 'Le Livre Contemporain', 5),
('Le Pauvre Christ de Bomba', 'Mongo Beti', 'Une satire de la colonisation et de l''évangélisation en Afrique centrale.', 'Robert Laffont', 3),
('L''Enfant noir', 'Camara Laye', 'Un récit autobiographique décrivant l''enfance de l''auteur en Guinée.', 'Plon', 4),
('Mission terminée', 'Mongo Beti', 'Un roman humoristique mettant en lumière les contradictions entre tradition et modernité.', 'Buchet-Chastel', 3),
('Allah n''est pas obligé', 'Ahmadou Kourouma', 'L''histoire poignante d''un enfant soldat dans les guerres civiles d''Afrique de l''Ouest.', 'Éditions du Seuil', 5),
('Le Monde s''effondre', 'Chinua Achebe', 'Un classique de la littérature africaine racontant les bouleversements causés par la colonisation au Nigeria.', 'Heinemann', 7),
('Americanah', 'Chimamanda Ngozi Adichie', 'Un roman contemporain sur l''identité, l''immigration et les relations raciales.', 'Alfred A. Knopf', 6);

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