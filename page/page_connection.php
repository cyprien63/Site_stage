<?php
session_start();

// Simple login (no security) - works with existing `utilisateur` table
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $pass = $_POST['mot_de_passe'] ?? '';

    $erreur = '';

    if ($nom === '' || $pass === '') {
        $erreur = 'Nom et mot de passe requis.';
    } else {
        require_once __DIR__ . '/../source/connection.php';
        $conn = getConnection();
        // Simple query, no escaping (per user request)
        $res = $conn->query("SELECT id, mot_de_passe FROM utilisateur WHERE nom = '$nom'");

        if ($res && $row = $res->fetch_assoc()) {
            if ($row['mot_de_passe'] === $pass) {
                $_SESSION['Nom'] = $nom;
                $_SESSION['id'] = $row['id'];
                $conn->close();
                header('Location: /page/page_liste.php');
                exit;
            } else {
                $erreur = 'Mot de passe incorrect.';
            }
        } else {
            $erreur = 'Compte introuvable.';
        }

        $conn->close();
    }
} else {
    $erreur = '';
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DocKey - Connexion</title>
    <link rel="stylesheet" href="/style/style.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

    <header class="top-bar">
        <h1>DocKey</h1>
    </header>

    <div class="conteneur">
        <form action="" id="connection-form" method="post">

            <div class="boite-connection">
                <p>Connectez vous</p>
                <?php if (!empty($erreur)): ?>
                    <p style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($erreur); ?></p>
                <?php endif; ?>
                <input type="text" name="nom" placeholder="Entrez votre Nom..." maxlength="50" id="Nom" class="input-connection" required>
                <input type="password" name="mot_de_passe" placeholder="Entrez votre Mot de passe..." maxlength="100" id="MDP" class="input-connection" required>
            </div>

            <button class="btn" id="connection-button" type="submit">Se connecter</button>
        </form>

    </div>

    <div class="boutton-connection">
        <a href="/page/page_créer_compte.php" class="btn">Créer compte</a>
    </div>
    
</body>
</html>