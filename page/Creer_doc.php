<?php
session_start();
require_once __DIR__ . '/../source/connection.php';

$conn = getConnection();
$code_unique = bin2hex(random_bytes(5));
$id_connecte = isset($_SESSION['id']) && $_SESSION['id'] !== '' ? (int) $_SESSION['id'] : 0;

$sql = "INSERT INTO doc (
            id,
            id_compte, 
            contents, 
            created_at, 
            updated_at
        ) VALUES (
            '$code_unique',
            $id_connecte, 
            '', 
            NOW(), 
            NOW()
        )";

if ($conn->query($sql) === true) {
    $conn->close();
    header("Location: /page/editeur.php?id=" . urlencode($code_unique));
    exit;
} else {
    $error = $conn->error;
    $conn->close();
}
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
        <h1>DocKey<?php if (isset($_SESSION["id"]) && $_SESSION["id"] !== ""): ?><span title="<?php echo htmlspecialchars($_SESSION["Nom"] ?? ""); ?>">*</span><?php endif; ?></h1>
    </header>
    <div class="conteneur">
        <div class="boite">
            <p>Chargement...</p>
        </div>
    </div>

</body>
</html>