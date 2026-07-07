<?php
/**
 * app/helpers.php
 * Fonctions utilitaires partagées par toutes les pages publiques.
 */

require_once __DIR__ . '/db.php';

/**
 * Échappe une chaîne pour un affichage sûr dans le HTML.
 */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Construit une URL absolue vers un asset (css/js/image) du dossier public/assets.
 */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Récupère les N derniers livres ajoutés (utilisé sur la page d'accueil).
 */
function getDernieresNouveautes(int $limite = 4): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, titre, auteur, maison_edition, image FROM livres ORDER BY id DESC LIMIT :limite');
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Recherche des livres par titre ou par auteur.
 * Si $q est vide, retourne l'ensemble des livres de la bibliothèque.
 *
 * @param string $critere 'titre' ou 'auteur'
 * @param string $q       Terme recherché
 */
function rechercherLivres(string $critere, string $q): array
{
    $pdo = getPDO();

    // Liste blanche des colonnes autorisées : on ne laisse jamais
    // une valeur venant de l'utilisateur être injectée telle quelle dans le SQL.
    $colonnesAutorisees = ['titre' => 'titre', 'auteur' => 'auteur'];
    $colonne = $colonnesAutorisees[$critere] ?? 'titre';

    if ($q === '') {
        $stmt = $pdo->query("SELECT id, titre, auteur, maison_edition, image FROM livres ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    $sql = "SELECT id, titre, auteur, maison_edition, image FROM livres WHERE {$colonne} LIKE :q ORDER BY {$colonne} ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Retourne 'titre' ou 'auteur' à partir d'une valeur brute (protège les pages
 * contre une valeur de critère inattendue dans l'URL).
 */
function critereValide(?string $critere): string
{
    return in_array($critere, ['titre', 'auteur'], true) ? $critere : 'titre';
}

/**
 * Récupère un livre par son id. Retourne null si aucun livre ne correspond.
 */
function getLivreParId(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM livres WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $livre = $stmt->fetch();
    return $livre ?: null;
}

/* ---------------------------------------------------------------
 * Authentification (lecteurs)
 * ------------------------------------------------------------- */

/**
 * Inscrit un nouveau lecteur. Retourne l'id créé, ou false si l'email existe déjà.
 */
function inscrireLecteur(string $nom, string $prenom, string $email, string $motDePasse): int|false
{
    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT id FROM lecteurs WHERE email = :email');
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    if ($stmt->fetch()) {
        return false; // email déjà utilisé
    }

    $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO lecteurs (nom, prenom, email, password, role) VALUES (:nom, :prenom, :email, :password, "lecteur")'
    );
    $stmt->execute([
        ':nom'      => $nom,
        ':prenom'   => $prenom,
        ':email'    => $email,
        ':password' => $hash,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Vérifie les identifiants et ouvre la session si valides.
 * Retourne le lecteur (sans le mot de passe) ou false si échec.
 */
function connecterLecteur(string $email, string $motDePasse): array|false
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM lecteurs WHERE email = :email');
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $lecteur = $stmt->fetch();

    if (!$lecteur || !password_verify($motDePasse, $lecteur['password'])) {
        return false;
    }

    $_SESSION['lecteur_id']     = $lecteur['id'];
    $_SESSION['lecteur_nom']    = $lecteur['nom'];
    $_SESSION['lecteur_prenom'] = $lecteur['prenom'];
    $_SESSION['lecteur_role']   = $lecteur['role'];

    unset($lecteur['password']);
    return $lecteur;
}

/**
 * Ferme la session du lecteur connecté.
 */
function deconnecterLecteur(): void
{
    unset(
        $_SESSION['lecteur_id'],
        $_SESSION['lecteur_nom'],
        $_SESSION['lecteur_prenom'],
        $_SESSION['lecteur_role']
    );
    session_regenerate_id(true);
}

/**
 * Un lecteur est-il connecté ?
 */
function estConnecte(): bool
{
    return isset($_SESSION['lecteur_id']);
}

/**
 * Le lecteur connecté est-il administrateur ?
 */
function estAdmin(): bool
{
    return estConnecte() && ($_SESSION['lecteur_role'] ?? '') === 'admin';
}

/**
 * Bloque l'accès à une page si aucun lecteur n'est connecté.
 * $retour permet de revenir sur la bonne page après connexion.
 */
function exigerConnexion(string $retour = ''): void
{
    if (!estConnecte()) {
        $suffixe = $retour !== '' ? '?redirect=' . urlencode($retour) : '';
        header('Location: ' . BASE_URL . '/login.php' . $suffixe);
        exit;
    }
}

/**
 * Bloque l'accès à une page si le lecteur connecté n'est pas administrateur.
 */
function exigerAdmin(): void
{
    if (!estAdmin()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/* ---------------------------------------------------------------
 * Liste de lecture (table liste_lecture, liée au lecteur connecté)
 * ------------------------------------------------------------- */

/**
 * Ajoute un livre à la liste de lecture d'un lecteur (aujourd'hui comme date d'emprunt).
 */
function ajouterALaListe(int $idLivre, int $idLecteur): void
{
    if (estDansLaListe($idLivre, $idLecteur)) {
        return;
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO liste_lecture (id_livre, id_lecteur, date_emprunt, date_retour) VALUES (:livre, :lecteur, CURDATE(), NULL)'
    );
    $stmt->execute([':livre' => $idLivre, ':lecteur' => $idLecteur]);
}

/**
 * Retire un livre de la liste de lecture d'un lecteur.
 */
function retirerDeLaListe(int $idLivre, int $idLecteur): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('DELETE FROM liste_lecture WHERE id_livre = :livre AND id_lecteur = :lecteur');
    $stmt->execute([':livre' => $idLivre, ':lecteur' => $idLecteur]);
}

/**
 * Un livre donné est-il déjà dans la liste de lecture d'un lecteur ?
 */
function estDansLaListe(int $idLivre, int $idLecteur): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT 1 FROM liste_lecture WHERE id_livre = :livre AND id_lecteur = :lecteur');
    $stmt->execute([':livre' => $idLivre, ':lecteur' => $idLecteur]);
    return (bool) $stmt->fetch();
}

/**
 * Récupère les livres de la liste de lecture d'un lecteur, avec la date d'emprunt.
 */
function getLivresDeLaListe(int $idLecteur): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'SELECT l.*, ll.date_emprunt, ll.date_retour
         FROM liste_lecture ll
         JOIN livres l ON l.id = ll.id_livre
         WHERE ll.id_lecteur = :lecteur
         ORDER BY l.titre ASC'
    );
    $stmt->execute([':lecteur' => $idLecteur]);

    return $stmt->fetchAll();
}

/* ---------------------------------------------------------------
 * CRUD livres (réservé à l'administration)
 * ------------------------------------------------------------- */

/**
 * Récupère tous les livres pour l'espace admin (tri par id décroissant).
 */
function getTousLesLivresAdmin(): array
{
    $pdo = getPDO();
    return $pdo->query('SELECT * FROM livres ORDER BY id DESC')->fetchAll();
}

/**
 * Construit l'URL publique d'une couverture de livre, ou null si le livre n'en a pas.
 * À utiliser dans les templates : couvertureUrl($livre['image'])
 */
function couvertureUrl(?string $image): ?string
{
    if ($image === null || $image === '') {
        return asset('images/default-book.jpg');
    }

    $imageNormalizee = ltrim(str_replace('\\', '/', $image), '/');
    $imageNormalizee = preg_replace('#^(?:public/)?assets/images/#i', '', $imageNormalizee) ?? $imageNormalizee;
    $imageNormalizee = preg_replace('#^images/#i', '', $imageNormalizee) ?? $imageNormalizee;

    if ($imageNormalizee === '') {
        return asset('images/default-book.jpg');
    }

    return asset('images/' . ltrim($imageNormalizee, '/'));
}

/**
 * Gère l'upload d'une image de couverture envoyée via $_FILES['image'].
 * Retourne le nom du fichier à enregistrer en base, ou null si aucun fichier valide n'a été envoyé.
 */
function uploadImageLivre(array $fichier): ?string
{
    if (!isset($fichier['error']) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($fichier['name'] ?? 'image', PATHINFO_EXTENSION));

    if (!in_array($extension, $extensionsAutorisees, true)) {
        return null;
    }

    if (($fichier['size'] ?? 0) > 5 * 1024 * 1024) {
        return null;
    }

    if (!isset($fichier['tmp_name']) || $fichier['tmp_name'] === '') {
        return null;
    }

    if (!file_exists($fichier['tmp_name'])) {
        return null;
    }

    if (!is_readable($fichier['tmp_name'])) {
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $nomFichier = 'livre-' . uniqid('', true) . '.' . $extension;
    $destination = UPLOAD_DIR . $nomFichier;

    $imageSource = @imagecreatefromstring(file_get_contents($fichier['tmp_name']));
    if ($imageSource === false) {
        return null;
    }

    $largeurSource = imagesx($imageSource);
    $hauteurSource = imagesy($imageSource);
    $largeurCible = 640;
    $hauteurCible = 900;

    $ratio = min($largeurCible / $largeurSource, $hauteurCible / $hauteurSource);
    $largeurNouvelle = max(1, (int) round($largeurSource * $ratio));
    $hauteurNouvelle = max(1, (int) round($hauteurSource * $ratio));

    $imageRedimensionnee = imagecreatetruecolor($largeurNouvelle, $hauteurNouvelle);
    imagealphablending($imageRedimensionnee, true);
    imagesavealpha($imageRedimensionnee, true);
    imagecopyresampled($imageRedimensionnee, $imageSource, 0, 0, 0, 0, $largeurNouvelle, $hauteurNouvelle, $largeurSource, $hauteurSource);

    if ($extension === 'jpg' || $extension === 'jpeg') {
        imagejpeg($imageRedimensionnee, $destination, 85);
    } elseif ($extension === 'png') {
        imagepng($imageRedimensionnee, $destination, 8);
    } else {
        imagewebp($imageRedimensionnee, $destination, 85);
    }

    imagedestroy($imageSource);
    imagedestroy($imageRedimensionnee);

    return $nomFichier;
}

/**
 * Ajoute un nouveau livre. Retourne l'id créé.
 */
function ajouterLivre(string $titre, string $auteur, string $description, string $maisonEdition, int $nombreExemplaire, ?string $image = null): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO livres (titre, auteur, description, maison_edition, nombre_exemplaire, image)
         VALUES (:titre, :auteur, :description, :maison, :nombre, :image)'
    );
    $stmt->execute([
        ':titre'       => $titre,
        ':auteur'      => $auteur,
        ':description' => $description,
        ':maison'      => $maisonEdition,
        ':nombre'      => $nombreExemplaire,
        ':image'       => $image,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Met à jour un livre existant.
 */
function modifierLivre(int $id, string $titre, string $auteur, string $description, string $maisonEdition, int $nombreExemplaire, ?string $image = null): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'UPDATE livres
         SET titre = :titre, auteur = :auteur, description = :description,
             maison_edition = :maison, nombre_exemplaire = :nombre, image = :image
         WHERE id = :id'
    );
    $stmt->execute([
        ':titre'       => $titre,
        ':auteur'      => $auteur,
        ':description' => $description,
        ':maison'      => $maisonEdition,
        ':nombre'      => $nombreExemplaire,
        ':image'       => $image,
        ':id'          => $id,
    ]);
}

/**
 * Supprime un livre (les entrées liste_lecture liées sont supprimées en cascade).
 */
function supprimerLivre(int $id): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('DELETE FROM livres WHERE id = :id');
    $stmt->execute([':id' => $id]);
}