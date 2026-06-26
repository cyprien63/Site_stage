# DocKey

Application web de création, partage et édition de documents texte en ligne. Chaque document est identifié par un code unique de 10 caractères hexadécimaux.

## Technologies

- **PHP 8+** (procédural, sessions)
- **MySQL / MariaDB** (base de données `DocKey`)
- **CSS** (style unique partagé `style/style.css`)
- **Serveur** : Apache / Nginx avec PHP

## Structure de la base de données

### Table `doc`

Stocke les documents. Chaque document a un code unique (clé primaire), un propriétaire optionnel, un contenu texte, et des timestamps.

```sql
CREATE TABLE `doc` (
  `id` varchar(10) NOT NULL,
  `id_compte` int DEFAULT 0,
  `contents` text,
  `perm_mode` varchar(10) DEFAULT 'public',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

| Colonne       | Type         | Description                                           |
|---------------|-------------|-------------------------------------------------------|
| `id`          | varchar(10)  | Code unique de 10 caractères hexadécimaux (clé primaire) |
| `id_compte`   | int          | ID du propriétaire (0 = anonyme)                      |
| `contents`    | text         | Contenu texte du document                             |
| `perm_mode`   | varchar(10)  | Mode d'accès : `public` ou `restreint`                |
| `created_at`  | datetime     | Date de création (auto)                               |
| `updated_at`  | datetime     | Date de dernière modification (auto)                  |

### Table `utilisateur`

Stocke les comptes utilisateurs.

```sql
CREATE TABLE `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `mot_de_passe` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

| Colonne        | Type         | Description                          |
|----------------|-------------|--------------------------------------|
| `id`           | int         | ID unique (auto-incrément)           |
| `nom`          | varchar(50) | Nom d'utilisateur (unique)           |
| `mot_de_passe` | varchar(100)| Mot de passe en clair                |

### Table `doc_permissions`

Gère les permissions individuelles pour les documents en mode restreint.

```sql
CREATE TABLE `doc_permissions` (
  `doc_id` varchar(10) NOT NULL,
  `user_id` int NOT NULL,
  `mode` varchar(10) NOT NULL DEFAULT 'edit',
  PRIMARY KEY (`doc_id`, `user_id`),
  FOREIGN KEY (`doc_id`) REFERENCES `doc`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `utilisateur`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;
```

| Colonne   | Type         | Description                                    |
|-----------|-------------|------------------------------------------------|
| `doc_id`  | varchar(10) | Code du document (clé étrangère → doc)         |
| `user_id` | int         | ID de l'utilisateur (clé étrangère → utilisateur) |
| `mode`    | varchar(10) | Niveau : `edit` ou `readonly`                  |

## Arborescence du projet

```
Site_stage/
├── index.php                  # Accueil
├── README.md
├── page/
│   ├── Creer_doc.php          # Création d'un document
│   ├── editeur.php            # Éditeur de texte
│   ├── Ouvrir_doc.php         # Accès par code
│   ├── page_connection.php    # Connexion utilisateur
│   ├── page_créer_compte.php  # Création de compte
│   ├── page_liste.php         # Liste des documents (dashboard)
│   └── perm_doc.php           # Gestion des permissions
├── source/
│   ├── connection.php         # Backend : connexion DB + fonctions
│   └── LOGO.png               # Logo
└── style/
    └── style.css              # CSS global
```

## Fonctionnement détaillé

### 1. Accueil (`index.php`)

Page d'entrée publique. Affiche le logo et trois boutons :
- **Créer Doc** → `Creer_doc.php`
- **Ouvrir Doc** → `Ouvrir_doc.php`
- **Se connecter** → `page_connection.php`

Si l'utilisateur est connecté, un `*` apparaît à côté du titre avec son nom en infobulle.

---

### 2. Création d'un document (`page/Creer_doc.php`)

1. Génère un code unique : `bin2hex(random_bytes(5))` → 10 caractères hexadécimaux
2. Récupère l'ID du propriétaire depuis la session (0 si anonyme)
3. Insère dans `doc` : `id`, `id_compte`, `contents=''`, `created_at=NOW()`, `updated_at=NOW()`
4. Redirige vers `editeur.php?id=CODE`

```sql
INSERT INTO doc (id, id_compte, contents, created_at, updated_at)
VALUES ('$code', $id_connecte, '', NOW(), NOW());
```

---

### 3. Accès par code (`page/Ouvrir_doc.php`)

1. Formulaire GET avec champ `id` (10 caractères max)
2. Vérifie l'existence du document :
   ```sql
   SELECT id FROM doc WHERE id = '$safeId';
   ```
3. Si trouvé → redirige vers `editeur.php?id=CODE`
4. Sinon → affiche "Code incorrect."

---

### 4. Éditeur de texte (`page/editeur.php`)

**Contrôle d'accès :**
- Vérifie `peutVoirDoc($code, $user_id)` → si refusé, redirige vers `index.php`
- Vérifie `peutModifierDoc($code, $user_id)` → si refusé, mode lecture seule

**GET** : Charge le contenu via `getDocContents()`
```sql
SELECT contents FROM doc WHERE id = '$safeId';
```

**POST** : Sauvegarde via `updateDocContents()`
```sql
UPDATE doc SET contents='$contents', updated_at=NOW() WHERE id='$id';
```

Affiche le code du document en superposition fixe en bas à droite.

---

### 5. Connexion (`page/page_connection.php`)

1. Si déjà connecté → redirige vers `page_liste.php`
2. Vérifie les identifiants :
   ```sql
   SELECT id, mot_de_passe FROM utilisateur WHERE nom = '$nom';
   ```
3. Compare le mot de passe en clair (`===`)
4. Si OK : `$_SESSION['Nom'] = $nom`, `$_SESSION['id'] = $row['id']` → `page_liste.php`
5. Si échec : redirige avec message d'erreur

---

### 6. Création de compte (`page/page_créer_compte.php`)

1. Vérifie que les deux mots de passe correspondent
2. Vérifie l'unicité du nom :
   ```sql
   SELECT nom FROM utilisateur WHERE nom = '$nom';
   ```
3. Insère le nouvel utilisateur :
   ```sql
   INSERT INTO utilisateur (nom, mot_de_passe) VALUES ('$nom', '$pass');
   ```
4. Redirige vers `page_liste.php` (⚠️ ne connecte pas automatiquement)

---

### 7. Dashboard / Liste des documents (`page/page_liste.php`)

**Authentification requise** (redirige vers connexion sinon).

**Suppression** : `?delete=CODE` → supprime seulement si propriétaire :
```sql
DELETE FROM doc WHERE id = '$id' AND id_compte = '$id_connecte';
```

**Liste des documents accessibles** :
```sql
SELECT d.id, d.id_compte, p.mode as mon_perm, u.nom as proprietaire_nom
FROM doc d
LEFT JOIN doc_permissions p ON p.doc_id = d.id AND p.user_id = '$id_connecte'
LEFT JOIN utilisateur u ON u.id = d.id_compte
WHERE d.id_compte = '$id_connecte' OR p.user_id = '$id_connecte';
```

Affiche pour chaque document :
- **Propriétaire** : lien vers l'éditeur, bouton permissions (+), bouton supprimer (✕)
- **Partagé** : lien vers l'éditeur, nom du propriétaire, niveau (Édition/Lecture)

Boutons : "Créer un doc", "ouvrir doc", "Déconnexion"

---

### 8. Gestion des permissions (`page/perm_doc.php`)

**Authentification requise** + vérification que l'utilisateur est propriétaire du document (ou que `id_compte = 0`).

**Actions POST :**

- **`changer_mode`** : Bascule entre `public` et `restreint`
  ```sql
  UPDATE doc SET perm_mode = '$mode' WHERE id = '$id';
  ```
  Si passage en restreint et `id_compte = 0`, définit l'utilisateur courant comme propriétaire :
  ```sql
  UPDATE doc SET id_compte = $uid WHERE id = '$id';
  ```

- **`ajouter`** : Ajoute un utilisateur par nom avec niveau `edit` ou `readonly`
  ```sql
  SELECT id FROM utilisateur WHERE nom = '$nom';
  INSERT INTO doc_permissions (doc_id, user_id, mode)
  VALUES ('$id', $uid, '$mode')
  ON DUPLICATE KEY UPDATE mode = '$mode';
  ```

- **`supprimer`** : Retire un utilisateur
  ```sql
  DELETE FROM doc_permissions WHERE doc_id = '$id' AND user_id = $uid;
  ```

---

## Système de permissions

### Modes de document

| Mode | Accès |
|------|-------|
| `public` | Tout le monde peut voir et modifier (même sans compte) |
| `restreint` | Seuls le propriétaire et les utilisateurs autorisés peuvent accéder |

### Niveaux de permission utilisateur

| Niveau | Description |
|--------|-------------|
| `edit` | L'utilisateur peut voir et modifier le document |
| `readonly` | L'utilisateur peut voir le document mais pas le modifier |

### Règles

1. **Document public** → tout le monde peut voir et modifier (pas de vérification)
2. **Document restreint** :
   - Utilisateur non connecté → accès refusé
   - Propriétaire du document → accès total
   - Utilisateur avec permission `edit` → peut voir et modifier
   - Utilisateur avec permission `readonly` → peut voir seulement
   - Utilisateur sans permission → accès refusé

---

## Backend (`source/connection.php`)

### Connexion

| Fonction | Description |
|----------|-------------|
| `getConnection(): mysqli` | Connexion à MySQL (host: `localhost`, user: `SITE`, db: `DocKey`) |

### Documents

| Fonction | Description |
|----------|-------------|
| `getDocContents(id): string` | Récupère le contenu texte d'un document |
| `updateDocContents(id, contents): string` | Sauvegarde le contenu, retourne vide si OK ou l'erreur MySQL |

### Permissions

| Fonction | Description |
|----------|-------------|
| `obtenirPermModeDoc(id_doc): string` | Retourne `public` ou `restreint` |
| `definirPermModeDoc(id_doc, mode)` | Définit le mode d'accès du document |
| `obtenirPermUtilisateurs(id_doc): array` | Liste des utilisateurs autorisés (nom, mode) |
| `definirPermUtilisateur(id_doc, id_user, mode)` | Ajoute ou met à jour une permission |
| `supprimerPermUtilisateur(id_doc, id_user)` | Supprime une permission |
| `obtenirIdParNom(nom): int|null` | Trouve l'ID d'un utilisateur par son nom |
| `peutVoirDoc(id_doc, id_user): bool` | Vérifie si l'utilisateur peut voir le document |
| `peutModifierDoc(id_doc, id_user): bool` | Vérifie si l'utilisateur peut modifier le document |

### Session

| Fonction | Description |
|----------|-------------|
| `SESSION(nom_utilisateur)` | Définit la session à partir d'un nom (legacy, non utilisée) |
| `deconnexion()` | Détruit la session et redirige vers l'accueil (legacy) |

---

## Parcours utilisateur

```
                    ┌─────────────────┐
                    │   index.php     │
                    │  (page d'accueil)│
                    └────────┬────────┘
         ┌──────────────────┼──────────────────┐
         ▼                  ▼                  ▼
  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
  │ Creer_doc.php │  │ Ouvrir_doc   │  │ page_connect │
  │ (auto exec)   │  │ .php         │  │ -ion.php     │
  └───────┬───────┘  └──────┬───────┘  └──────┬───────┘
          │                 │ (code valide)   │ (login OK)
          ▼                 ▼                  ▼
  ┌─────────────────────────────────────────────────┐
  │              editeur.php?id=CODE                │
  │  (chargement / édition / sauvegarde)            │
  └─────────────────────────────────────────────────┘
                          │
                          ▼
  ┌─────────────────────────────────────────────────┐
  │  page_liste.php  (dashboard, liste des docs)   │
  │  perm_doc.php    (gestion des permissions)     │
  └─────────────────────────────────────────────────┘
```

