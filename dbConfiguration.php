<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
$conn = mysqli_connect("localhost","UniversalApp","YQrdnH6hX3","UniversalApp");
mysqli_set_charset($conn, 'utf8');
?>