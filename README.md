<div align="center">

# Bibliothèque en Ligne

Site web de bibliothèque numérique : recherche, fiches livres, liste de lecture personnelle
et back-office d'administration.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)
</div>

---

## Sommaire

- [Fonctionnalités](#-fonctionnalités)
- [Architecture du projet](#️-architecture-du-projet)
- [Base de données](#️-base-de-données)
- [Installation](#-installation-wamp--xampp--laragon)
- [Créer un compte administrateur](#-créer-un-compte-administrateur)
- [Sécurité](#-sécurité-mise-en-place)
- [Technologies](#-technologies-utilisées)
- [Pistes d'amélioration](#-pistes-damélioration-possibles)
- [Licence](#-licence)
- [Auteur](#️-auteur)

---

## Fonctionnalités

-  **Page d'accueil** avec présentation, recherche rapide et les 4 dernières nouveautés.
-  **Recherche** de livres par titre ou par auteur.
-  **Fiche détail** d'un livre (titre, auteur, description, maison d'édition, disponibilité).
-  **Authentification** : inscription, connexion, déconnexion (mots de passe hashés avec `password_hash`).
-  **Liste de lecture personnelle** : un lecteur connecté peut ajouter/retirer des livres, avec la date d'emprunt.
-  **Espace administrateur** protégé : ajouter, modifier et supprimer des livres de la bibliothèque.
-  Interface **responsive**, police **Montserrat**, design sobre (fond crème, accents vert clair).

---

## Architecture du projet

```
bibliotheque_online/
├── public/                     ← Pages accessibles depuis le navigateur
│   ├── index.php                Accueil + hero + recherche + nouveautés
│   ├── results.php               Résultats de recherche
│   ├── details.php               Détail d'un livre + ajout à la liste de lecture
│   ├── wishlist.php               Liste de lecture du lecteur connecté
│   ├── register.php              Inscription
│   ├── login.php                 Connexion
│   ├── logout.php                Déconnexion
│   ├── partials/
│   │   ├── header.php            En-tête commun (nav, menu selon connexion)
│   │   └── footer.php            Pied de page commun
│   ├── admin/                    Espace réservé aux administrateurs
│   │   ├── index.php              Liste des livres + suppression
│   │   ├── ajouter.php             Formulaire d'ajout
│   │   └── modifier.php            Formulaire de modification
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
├── app/
│   ├── config.php               Constantes (BDD, session, nom de l'app)
│   ├── db.php                   Connexion PDO (singleton)
│   └── helpers.php              Toutes les fonctions métier (auth, recherche, CRUD, liste de lecture)
└── sql/
    └── schema.sql                Structure de la BDD + données de démonstration
```

---

## Base de données

Trois tables principales :

| Table            | Rôle                                                              |
|-------------------|-------------------------------------------------------------------|
| `livres`          | Catalogue des livres (titre, auteur, description, éditeur, stock) |
| `lecteurs`        | Comptes utilisateurs (nom, prénom, email, mot de passe, rôle)      |
| `liste_lecture`   | Table de liaison lecteur ↔ livre (date d'emprunt / de retour)      |

Le rôle du lecteur (`role`) vaut `lecteur` par défaut ou `admin` pour accéder à l'espace d'administration.

<details>
<summary>Voir le schéma SQL complet</summary>

```sql
CREATE TABLE livres (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    titre              VARCHAR(100) NOT NULL,
    auteur             VARCHAR(100) NOT NULL,
    description        TEXT,
    maison_edition     VARCHAR(100),
    nombre_exemplaire  INT NOT NULL DEFAULT 0
);

CREATE TABLE lecteurs (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nom      VARCHAR(100) NOT NULL,
    prenom   VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('lecteur', 'admin') NOT NULL DEFAULT 'lecteur'
);

CREATE TABLE liste_lecture (
    id_livre      INT NOT NULL,
    id_lecteur    INT NOT NULL,
    date_emprunt  DATE NOT NULL,
    date_retour   DATE NULL,
    PRIMARY KEY (id_livre, id_lecteur),
    FOREIGN KEY (id_livre) REFERENCES livres(id) ON DELETE CASCADE,
    FOREIGN KEY (id_lecteur) REFERENCES lecteurs(id) ON DELETE CASCADE
);
```

Le fichier complet, avec les données de démonstration, se trouve dans [`sql/schema.sql`](sql/schema.sql).

</details>

---

## Installation (WAMP / XAMPP / Laragon)

1. **Cloner le dépôt** dans le dossier serveur (`www/` ou `htdocs/`) :
   ```bash
   git clone https://github.com/malickasso/bibliotheque_online.git
   ```

2. **Créer la base de données** : importer [`sql/schema.sql`](sql/schema.sql) dans phpMyAdmin (ou via la ligne de commande MySQL).
   Cela crée la base `bibliotheque_online`, les 3 tables, et ajoute 6 livres de démonstration.

3. **Configurer la connexion** dans `app/config.php` si besoin (utilisateur/mot de passe MySQL) :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'bibliotheque_online');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Lancer le site** :
   ```
   http://localhost/bibliotheque_online/public/index.php
   ```

---

## Créer un compte administrateur

1. Inscrivez-vous normalement via **`/public/register.php`**.
2. Dans phpMyAdmin (onglet SQL), exécutez :
   ```sql
   UPDATE lecteurs SET role = 'admin' WHERE email = 'votre@email.com';
   ```
3. Reconnectez-vous : le lien **"Administration"** apparaît dans le menu, donnant accès à
   `public/admin/index.php`.

---

##  Sécurité mise en place

- Mots de passe **hashés** avec `password_hash()` / vérifiés avec `password_verify()`.
- Toutes les requêtes SQL utilisent des **requêtes préparées PDO** (protection contre les injections SQL).
- Le nom de colonne de recherche (`titre`/`auteur`) passe par une **liste blanche** plutôt que d'être injecté directement.
- Toutes les sorties HTML passent par la fonction `h()` (échappement `htmlspecialchars`), protection contre le XSS.
- Pattern **Post/Redirect/Get** sur les suppressions et retraits pour éviter les doublons en cas de rafraîchissement.
- Pages protégées par `exigerConnexion()` (liste de lecture) et `exigerAdmin()` (espace admin).

---

## Technologies utilisées

- HTML5 / CSS3 (police **Montserrat**, design responsive)
- JavaScript (léger, `assets/js/app.js`)
- PHP 8+ (PDO, sessions, programmation procédurale organisée par fonctions)
- MySQL / MariaDB

---

##  Pistes d'amélioration possibles

- [ ] Empêcher l'emprunt d'un livre si `nombre_exemplaire = 0`.
- [ ] Page "Mon profil" pour modifier ses informations personnelles.
- [ ] Pagination des résultats de recherche et de la liste des livres en admin.
- [ ] Upload d'une image de couverture pour chaque livre.

---

## Auteur

Projet réalisé par **Abdou Malick Assouma** dans le cadre du projet D-CLIC.

[GitHub](https://github.com/malickasso) · [LinkedIn](https://www.linkedin.com/in/abdoumalick-assouma-63a878270)
