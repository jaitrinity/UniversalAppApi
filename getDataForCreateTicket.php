<?php
include("dbConfiguration.php");
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

$sql = "SELECT `MenuId`, `Cat` FROM `Menu` where `Tenent_Id`=$tenentId";
$query=mysqli_query($conn,$sql);
$menuList = array();
while($row = mysqli_fetch_assoc($query)){
	$menuJson = array(
		'menuId' => $row["MenuId"],
		'menuName' => $row["Cat"]
	);
	array_push($menuList, $menuJson);
}

$sql = "SELECT `EmpId`, `Name` FROM `Employees` where `Active`=1 and `Tenent_Id`=$tenentId";
$query=mysqli_query($conn,$sql);
$empList = array();
while($row = mysqli_fetch_assoc($query)){
	$empJson = array(
		'empId' => $row["EmpId"],
		'empName' => $row["Name"]
	);
	array_push($empList, $empJson);
}

$output = array(
	'menuList' => $menuList, 
	'empList' => $empList, 
	'verifierEmpList' => $empList, 
	'approverEmpList' => $empList
);
echo json_encode($output);
?>