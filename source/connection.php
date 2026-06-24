<?php
function getConnection(): mysqli {
    $servername = "localhost";
    $username = "SITE";
    $password = "";
    $dbname = "DocKey";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function updateDocContents(string $id, string $contents): string {
    $conn = getConnection();

    $sql = "UPDATE doc 
            SET contents='$contents', updated_at=NOW() 
            WHERE id='$id'";

    if ($conn->query($sql) === true) {
        $conn->close();
        return '';
    }

    $error = $conn->error;
    $conn->close();
    return $error;
}
