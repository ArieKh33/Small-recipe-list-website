<?php

// The information needed to log into phpmyadmin
$servername = 'localhost';
$username = 'bit_academy';
$password = 'school2000';
$database = 'foodblog';

// 
try {
    $db_conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
	$db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
