<?php
session_start();
require_once __DIR__ . '/../source/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $pass = $_POST['mot_de_passe'] ?? '';
    $passConfirme = $_POST['confirmer_mot_de_passe'] ?? '';
    $erreur = '';

    if ($pass !== $passConfirme) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        $conn = getConnection();
        $existe = $conn->query("SELECT nom FROM utilisateur WHERE nom = '$nom'");

        if ($existe->num_rows > 0) {
            $erreur = 'Ce nom existe déjà.';
        } else {
            $ok = $conn->query("INSERT INTO utilisateur (nom, mot_de_passe) VALUES ('$nom', '$pass')");
            if ($ok) {
                $conn->close();
                header("Location: /page/page_liste.php");
                exit;
            } else {
                $erreur = 'Erreur : ' . $conn->error;
            }
        }
        $conn->close();
    }

    header('Location: /page/page_créer_compte.php?err=' . urlencode($erreur));
    exit;
}

$erreur = $_GET['err'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DocKey</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

    <header class="top-bar">
        <h1>DocKey<?php if (isset($_SESSION['id']) && $_SESSION['id'] !== ''): ?><span title="<?php echo htmlspecialchars($_SESSION['Nom'] ?? ''); ?>">*</span><?php endif; ?></h1>
    </header>

    <div class="conteneur">
        <form action="" id="connection-form" method="post">

            <div class="boite-connection">
                <p>Création de compte</p>
                <?php if (!empty($erreur)): ?>
                    <p style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($erreur); ?></p>
                <?php endif; ?>
                <input type="text" name="nom" placeholder="Entrez votre Nom..." maxlength="50" id="nom" class="input-connection" required>
                <input type="password" name="mot_de_passe" placeholder="Entrez votre Mot de passe..." maxlength="100" id="mot_de_passe" class="input-connection" required>
                <input type="password" name="confirmer_mot_de_passe" placeholder="Confirmez votre Mot de passe..." maxlength="100" id="confirmer_mot_de_passe" class="input-connection" required>
            </div>

            <button class="btn" id="connection-button" type="submit">Créer compte</button>
        </form>

    </div>


    
</body>
</html>