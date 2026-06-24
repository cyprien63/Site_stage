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

function getDocContents(string $id): string {
    $conn = getConnection();
    $safeId = $conn->real_escape_string($id);
    $sql = "SELECT contents FROM doc WHERE id='$safeId'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['contents'] ?? '';
    }

    $conn->close();
    return '';
}

function updateDocContents(string $id, string $contents): string {
    $conn = getConnection();

    $safeId = $conn->real_escape_string($id);
    $safeContents = $conn->real_escape_string($contents);
    $sql = "UPDATE doc 
            SET contents='$safeContents', updated_at=NOW() 
            WHERE id='$safeId'";

    if ($conn->query($sql) === true) {
        $conn->close();
        return '';
    }

    $error = $conn->error;
    $conn->close();
    return $error;
}
