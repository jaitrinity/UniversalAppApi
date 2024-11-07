<?php
include("dbConfig.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$color1 = $jsonData->color1;
$color2 = $jsonData->color2;
$isAllChecklistColorSame = $jsonData->isAllChecklistColorSame;

$code = 0;
$message = "";
$sql = "UPDATE `Accounts` set `Color1`='$color1', `Color2`='$color2' where `Company_Name` = 'UniversalApp_216'";
if(mysqli_query($conn,$sql)){
	$code = 200;
	$message = "Successfully update";
	if($isAllChecklistColorSame == 1){
		require 'ChangeAllChecklistColorClass.php';
		$classObj = new ChangeAllChecklistColorClass();
		$classObj->changeChecklistColor($color1,$color2);
	}
}
else{
	$code = 0;
	$message = "Something wrong";
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);
?>