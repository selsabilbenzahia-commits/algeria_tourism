<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "algeria_tourism";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}
mysqli_query($conn, "SET session sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

// لضمان دعم اللغة العربية بشكل صحيح 100%
mysqli_set_charset($conn, "utf8mb4");
?>