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
$menuId = $jsonData->menuId;
$tenentId = $jsonData->tenentId;

$sql = "SELECT a.ActivityId, a.EmpId, e.Name as `Employee Name`, a.MobileDateTime as `Submit Datetime` FROM Activity a join TransactionHDR h on a.ActivityId = h.ActivityId join Employees e on a.EmpId = e.EmpId WHERE a.Event = 'Submit' and a.MenuId = $menuId ORDER BY a.MobileDateTime DESC";

$result = mysqli_query($conn,$sql);
$row=mysqli_fetch_assoc($result);
$columnName = array();
foreach ($row as $key => $value) {
	array_push($columnName, $key);
}

$wrappedList = array();
mysqli_data_seek($result, 0);
while($row=mysqli_fetch_assoc($result)){
	$json = new StdClass;
	foreach ($columnName as $key => $value) {
		$json -> $value = $row[$value]; 
	}
	array_push($wrappedList, $json);
}
if(count($wrappedList) > 0){
	// array_push($columnName, "View");
	$output = array(
		'columnName' => $columnName, 
		'wrappedList' => $wrappedList, 
		'responseCode' => 100000,
		'responseDesc' => 'Successfull'
	);	
}
else{
	// $columnName = ["Activity Id", "EmpId", "Employee Name", "Submit Datetime", "View"];
	$columnName = ["Activity Id", "EmpId", "Employee Name", "Submit Datetime"];
	$output = array(
		'columnName' => $columnName, 
		'wrappedList' => $wrappedList, 
		'responseCode' => 400,
		'responseDesc' => 'No record found'
	);
}
echo json_encode($output);

?>