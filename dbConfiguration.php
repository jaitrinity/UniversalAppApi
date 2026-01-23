<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
$DB_HOST = getenv('DB_HOST');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('DB_PASS');
$DB_NAME = getenv('DB_NAME');
$conn = mysqli_connect($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
// $conn = new mysqli(
//     getenv('DB_HOST'),
//     getenv('DB_USER'),
//     getenv('DB_PASS'),
//     getenv('DB_NAME'),
//     3306
// );
mysqli_set_charset($conn, 'utf8');
?>