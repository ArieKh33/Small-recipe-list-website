<?php

require_once 'connection.php';

if (isset($_POST["recipe_id"])) {
    $id = $_POST["recipe_id"];
    $sql = "DELETE FROM recipes WHERE id = :id";
    $stmt = $db_conn->prepare($sql);
    $stmt->execute(["id" => $id]);
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

?>