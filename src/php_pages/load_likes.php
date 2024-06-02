<?php
require_once 'connection.php';

// Check if the button has been pressed
if (isset($_POST['recipe_id'])) {
    $id = $_POST['recipe_id'];
    $sql = "UPDATE recipes SET likes = likes + 1 WHERE id = :id";
    $stmt = $db_conn->prepare($sql);
    $stmt->execute(["id" => $id]);

    // Return the new like count
    $sql = "SELECT likes FROM recipes WHERE id = :id";
    $stmt = $db_conn->prepare($sql);
    $stmt->execute(["id" => $id]);
    $likes = $stmt->fetch(PDO::FETCH_ASSOC)['likes'];

    echo json_encode(['likes' => $likes]);
}
?>