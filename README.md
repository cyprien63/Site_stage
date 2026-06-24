# DocKey

## Description

DocKey est une application web permettant de créer, ouvrir et modifier des documents texte en ligne. Chaque document généré reçoit un code unique (10 caractères hexadécimaux) pour y accéder.

## Fonctionnalités

### 1. Création de document
- Page : `page/Creer_doc.php`
- Génère automatiquement un code unique (10 caractères)
- Insère le document en base de données
- Redirige automatiquement vers l'éditeur

### 2. Accès au document
- Page : `page/Ouvrir_doc.php`
- Permet de saisir un code unique pour accéder à un document
- Valide le code en base de données
- Redirige vers l'éditeur si le code est valide
- Affiche "Code incorrect." si le code n'existe pas

### 3. Éditeur de texte
- Page : `page/editeur.php`
- Récupère le contenu sauvegardé lors du chargement
- Permet de modifier le texte du document
- Sauvegarde automatiquement au clic sur "Enregistrer"
- Affiche un message de confirmation ou d'erreur

### 4. Gestion de la base de données
- Base : `DocKey`
- Table : `doc` (id, compte, contents, created_at, updated_at)
- Fonctions PHP centralisées dans `source/connection.php`

## Structure du projet

### Pages PHP
- `index.php` : page d'accueil avec boutons "Créer Doc" et "Ouvrir Doc"
- `page/Creer_doc.php` : génère un code et crée un nouveau document
- `page/Ouvrir_doc.php` : formulaire pour accéder à un document via code
- `page/editeur.php` : éditeur de texte avec sauvegarde
- `page/page_connection.html` : page de connexion (statique)
- `page/page_créer_compte.html` : page d'inscription (statique)
- `page/page_liste.html` : page de liste des documents (statique)

### Backend
- `source/connection.php` : 
  - `getConnection()` : établit la connexion MySQL
  - `getDocContents(id)` : récupère le contenu d'un document
  - `updateDocContents(id, contents)` : met à jour le contenu d'un document

### Styles
- `style/style.css` : CSS partagé pour toutes les pages

### Assets
- `source/LOGO.png` : logo de l'application

## utilisateur

1. **Créer un document** :
   - Utilisateur → `index.php` → clic "Créer Doc"
   - `Creer_doc.php` génère un code unique
   - Redirection automatique vers `editeur.php?id=CODE`

2. **Ouvrir un document existant** :
   - Utilisateur → `index.php` → clic "Ouvrir Doc"
   - `Ouvrir_doc.php` : l'utilisateur saisit le code
   - Vérification en base de données
   - Redirection vers `editeur.php?id=CODE` si valide

3. **Modifier et sauvegarder** :
   - Utilisateur écrit dans l'éditeur
   - Clic "Enregistrer" → `editeur.php` (POST)
   - Mise à jour en base de données
   - Message de confirmation


### Structure table `doc`

```sql
CREATE TABLE `doc` (
  `id` varchar(10) NOT NULL,
  `compte` varchar(255) DEFAULT 'no',
  `contents` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

