<?php
include("dbConfiguration.php");
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;
$password = $jsonData->password;

$sql = "SELECT * FROM `Employees` WHERE `Mobile` = BINARY('$mobile') and `Password` = BINARY('$password') and `Active` = 1 ";
$query=mysqli_query($conn,$sql);
$empArr = array();
if(mysqli_num_rows($query) != 0){
	while($row = mysqli_fetch_assoc($query)){
		$empId = $row["EmpId"];
		$empName = $row["Name"];
		$empRoleId = $row["RoleId"];
		
		$json = array(
			'empId' => $empId,
			'empName' => $empName,
			'empRoleId' => $empRoleId,
			'tenentId' => $row["Tenent_Id"]
		);
		array_push($empArr,$json);
	}
	$output = array('responseCode' => '100000','responseDesc' => 'SUCCESSFUL','wrappedList' => $empArr);
	echo json_encode($output);
}
else{
	$output = array('responseCode' => '102001','responseDesc' => 'No Record Found','wrappedList' => $empArr);
	echo json_encode($output);
}



?>