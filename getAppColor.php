<?php 
include("dbConfig.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$selectType = $_REQUEST["selectType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

$sql = "SELECT `Color1` as `color1`, `Color2` as `color2` FROM `Accounts` where `Company_Name`='UniversalApp_216'";
$query=mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($query);

$output = array('appColor' => $row);
echo json_encode($output);

?>