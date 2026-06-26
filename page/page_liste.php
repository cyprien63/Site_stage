<?php

    session_start();


    if (!isset($_SESSION['id'])) {
        header('Location: /page/page_connection.php');
        exit;
    }

    require_once __DIR__ . '/../source/connection.php';
    $conn = getConnection();

    $id_connecte = $_SESSION['id'];

    if (isset($_GET['delete']) && $_GET['delete'] !== '') {
        $deleteId = $conn->real_escape_string($_GET['delete']);
        $conn->query("DELETE FROM doc WHERE id = '$deleteId' AND id_compte = '$id_connecte'");
        header("Location: /page/page_liste.php");
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: /page/page_connection.php');
        exit;
    }

    $list = $conn->query("SELECT d.id, d.id_compte, p.mode as mon_perm, u.nom as proprietaire_nom FROM doc d LEFT JOIN doc_permissions p ON p.doc_id = d.id AND p.user_id = '$id_connecte' LEFT JOIN utilisateur u ON u.id = d.id_compte WHERE d.id_compte = '$id_connecte' OR p.user_id = '$id_connecte'");
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DocKey - Mes documents</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

    <header class="top-bar">
        <h1>DocKey<?php if (isset($_SESSION["id"]) && $_SESSION["id"] !== ""): ?><span title="<?php echo htmlspecialchars($_SESSION["Nom"] ?? ""); ?>">*</span><?php endif; ?></h1>
        <a href="?action=logout" class="btn_deconnextion">Déconnexion</a>
    </header>
    
    <div class="liste">
        <p>Mes documents :</p>
        <?php if ($list && $list->num_rows > 0): ?>
            <ul>
                <?php while ($doc = $list->fetch_assoc()): 
                    $est_proprietaire = ((int)$doc['id_compte'] === (int)$id_connecte);
                ?>
                    <li>
                        <a class="doc-link" href="/page/editeur.php?id=<?php echo urlencode($doc['id']); ?>"><?php echo htmlspecialchars($doc['id']); ?></a>
                        <?php if (!$est_proprietaire): ?>
                            <span style="font-size:12px;color:#888;flex:1;text-align:center;">
                                Partagé par <?php echo htmlspecialchars($doc['proprietaire_nom'] ?? 'Inconnu'); ?>
                                (<?php echo $doc['mon_perm'] === 'edit' ? 'Édition' : 'Lecture'; ?>)
                            </span>
                        <?php endif; ?>
                        <?php if ($est_proprietaire): ?>
                            <a class="perm-link" href="/page/perm_doc.php?id=<?php echo urlencode($doc['id']); ?>" title="Permissions">+</a>
                            <a class="delete-link" href="?delete=<?php echo urlencode($doc['id']); ?>" title="Supprimer">✕</a>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Aucun doc.</p>
        <?php endif; ?>
    </div>

    <div class="conteneur-bouton">
        <a href="/page/Creer_doc.php" class="btn" id ="conteneur-bouton1">Créer un doc</a>

        <a href="/page/Ouvrir_doc.php" class="btn" id ="conteneur-bouton2">ouvrir doc</a>
    </div>

    

</body>
</html>