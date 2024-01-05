<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

$empList = [];
if($loginEmpRoleId == '4'){
	$empSql = "SELECT * FROM `Employees` WHERE `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($row11 = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$row11["EmpId"]);
		}
	}

}
else{
	array_push($empList,$loginEmpId);
}

$loginEmpId = implode("','", $empList);

$wrappedList = [];

$unionSql = "select DISTINCT t.`ActivityId` from (
select `ActivityId` from `Activity` where `EmpId` in ('$loginEmpId') and `Event` = 'Submit') t";


$sql = "SELECT distinct `h`.`ActivityId`, `h`.`ServerDateTime`, m.`Cat`, m.`Sub`, m.`Caption`, `a`.`EmpId` as empId, `e`.`Name` as empName, a.GeoLocation as latLong FROM `TransactionHDR` h 
join `Activity` a on `h`.`ActivityId` = `a`.`ActivityId`
join `Menu` m on `a`.`MenuId` = `m`.`MenuId`
join `Employees` e on `a`.`EmpId` = `e`.`EmpId` 
where `h`.`ActivityId` in ($unionSql) order by `h`.`ActivityId` desc";
$query = mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$json = array(
		'activityId' => $row["ActivityId"], 
		'submitDatetime' => $row["ServerDateTime"],
		'menuName' => $row["Cat"],
		'empId' => $row["empId"],
		'empName' => $row["empName"],
		'latLong' => $row["latLong"]
	);
	array_push($wrappedList,$json);
}
$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000');
echo json_encode($output);

?>