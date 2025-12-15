<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "tvet_school_management_system";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
