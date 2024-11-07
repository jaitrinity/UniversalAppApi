<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

$stateList = array();
$sql = "SELECT `State` as state, `City` as city, `Area` as area FROM `StateCityAreaMaster`";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	array_push($stateList, $row);
}

$roleList = array();
$sql = "SELECT `RoleId` as roleId, `Role` as roleName FROM `Role` where `Tenent_Id`=$tenentId";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	array_push($roleList, $row);
}

$rmList = array();
$sql = "SELECT `EmpId` as empId, `Name` as empName FROM `Employees` where `Tenent_Id`=$tenentId";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	array_push($rmList, $row);
}
$output = array('stateList' => $stateList, 'roleList' => $roleList, 'rmList' => $rmList);
echo json_encode($output);
?>