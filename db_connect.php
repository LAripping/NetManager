<?php
$host = "127.0.0.1";
$username = "web";
$password = "web";
$db = "captures";

global $conn;
$conn= new mysqli($host, $username, $password, $db);
if ($conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
}
?>
