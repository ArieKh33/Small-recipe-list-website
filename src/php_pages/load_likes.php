<?php
require_once 'connection.php';

// Check if the button has been pressed
if (isset($_POST['post_id'])) {
    $id = $_POST['post_id'];
    $sql = "UPDATE posts SET likes = likes + 1 WHERE id = :id";
    $stmt = $db_conn->prepare($sql);
    $stmt->execute(["id" => $id]);

    // Return the new like count
    $sql = "SELECT likes FROM posts WHERE id = :id";
    $stmt = $db_conn->prepare($sql);
    $stmt->execute(["id" => $id]);
    $likes = $stmt->fetch(PDO::FETCH_ASSOC)['likes'];

    echo json_encode(['likes' => $likes]);
}
?>