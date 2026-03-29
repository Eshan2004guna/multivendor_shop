<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "multivendor_shop";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>