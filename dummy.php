<?php 
include("dbConfiguration.php");
$json = file_get_contents('php://input');
// $jsonData = json_decode($json,true);
// $empId = $jsonData["emp_id"];

// echo $_SERVER['SERVER_PROTOCOL'];

$jsonData = json_decode($json);
$empId = $jsonData->emp_id;
// if($empId == 1){
// 	header("HTTP/1.1 200");
// }
// else{
// 	header("HTTP/1.1 202");
// }

$output = new StdClass;
$output -> empId = $empId;
echo json_encode($output);
?>


