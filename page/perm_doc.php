<?php
session_start();
require_once __DIR__ . '/../source/connection.php';

if (!isset($_SESSION['id'])) {
    header('Location: /page/page_connection.php');
    exit;
}

$id_doc = $_GET['id'] ?? '';

if ($id_doc === '') {
    header('Location: /page/page_liste.php');
    exit;
}

$conn = getConnection();
$sid = $conn->real_escape_string($id_doc);
$res = $conn->query("SELECT id_compte FROM doc WHERE id = '$sid'");
$doc = $res->fetch_assoc();
$conn->close();

$proprietaire = (int)$doc['id_compte'];
if (!$doc || ($proprietaire !== 0 && $proprietaire !== (int)$_SESSION['id'])) {
    header('Location: /page/page_liste.php');
    exit;
}

$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'changer_mode') {
        $mode = $_POST['mode'] ?? 'public';
        definirPermModeDoc($id_doc, $mode);

        if ($mode === 'restreint' && $doc['id_compte'] == 0) {
            $conn = getConnection();
            $sid2 = $conn->real_escape_string($id_doc);
            $uid2 = (int)$_SESSION['id'];
            $conn->query("UPDATE doc SET id_compte = $uid2 WHERE id = '$sid2'");
            $conn->close();
            $doc['id_compte'] = $uid2;
        }

        $message = 'Mode mis à jour.';
    }

    if ($action === 'ajouter') {
        $nom = trim($_POST['nom'] ?? '');
        $perm = $_POST['perm'] ?? 'edit';

        if ($nom !== '') {
            $id_user = obtenirIdParNom($nom);

            if ($id_user !== null) {
                definirPermUtilisateur($id_doc, $id_user, $perm);
                $message = $nom . ' ajouté.';
            } else {
                $erreur = 'Utilisateur introuvable.';
            }
        }
    }

    if ($action === 'supprimer') {
        $id_user = (int)($_POST['user_id'] ?? 0);
        supprimerPermUtilisateur($id_doc, $id_user);
        $message = 'Utilisateur retiré.';
    }
}

$mode_doc = obtenirPermModeDoc($id_doc);
$utilisateurs = obtenirPermUtilisateurs($id_doc);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DocKey - Permissions</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

<header class="top-bar">
    <h1>DocKey</h1>
    <a href="/page/page_liste.php" class="btn_deconnextion">Retour</a>
</header>

<div class="boite" style="width:50%;margin:30px auto;padding:20px;font-size:16px;">

    <h2 style="margin-top:0;">Permissions</h2>
    <p>Code : <?php echo htmlspecialchars($id_doc); ?></p>

    <?php if ($message !== ''): ?>
        <p style="color:green;font-weight:bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($erreur !== ''): ?>
        <p style="color:red;font-weight:bold;"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="action" value="changer_mode">
        <p>Mode d'accès :</p>
        <p>
            <label>
                <input type="radio" name="mode" value="public" <?php echo $mode_doc === 'public' ? 'checked' : ''; ?>>
                <strong>Tout le monde</strong> — n'importe qui peut ouvrir et modifier
            </label>
        </p>
        <p>
            <label>
                <input type="radio" name="mode" value="restreint" <?php echo $mode_doc === 'restreint' ? 'checked' : ''; ?>>
                <strong>Restreint</strong> — seuls les utilisateurs ajoutés peuvent ouvrir
            </label>
        </p>
        <button type="submit" class="btn" style="font-size:14px;padding:6px 16px;">Appliquer</button>
    </form>

    <?php if ($mode_doc === 'restreint'): ?>

        <hr>
        <h3>Utilisateurs autorisés</h3>

        <?php if (count($utilisateurs) > 0): ?>

            <table style="width:100%;border-collapse:collapse;">
                <?php foreach ($utilisateurs as $u): ?>
                    <tr style="border:1px solid #ccc;">
                        <td style="padding:6px 10px;font-weight:bold;"><?php echo htmlspecialchars($u['nom']); ?></td>
                        <td style="padding:6px 10px;color:#555;">
                            <?php echo $u['mode'] === 'edit' ? 'Modification' : 'Lecture seule'; ?>
                        </td>
                        <td style="padding:6px 10px;">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['user_id']; ?>">
                                <button type="submit" style="background:none;border:none;cursor:pointer;color:red;font-size:18px;" title="Retirer">✕</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

        <?php else: ?>
            <p style="color:#888;">Aucun utilisateur ajouté.</p>
        <?php endif; ?>

        <hr>
        <h4>Ajouter un utilisateur</h4>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter">
            <p>
                <input type="text" name="nom" placeholder="Nom d'utilisateur" style="padding:6px;font-size:14px;border:2px solid #000;border-radius:4px;" required>
                <select name="perm" style="padding:6px;font-size:14px;border:2px solid #000;border-radius:4px;">
                    <option value="edit">Modification</option>
                    <option value="readonly">Lecture seule</option>
                </select>
                <button type="submit" class="btn" style="font-size:12px;padding:6px 14px;">Ajouter</button>
            </p>
        </form>


    <?php endif; ?>

</div>

</body>
</html>
