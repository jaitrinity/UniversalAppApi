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
$empId = $jsonData->empId;
$menuId = $jsonData->menuId;
$verifierEmpId = $jsonData->verifierEmpId;
$approverEmpId = $jsonData->approverEmpId;
$fromDate = $jsonData->fromDate;
$toDate = $jsonData->toDate;

$t=date("YmdHis");
// $tktNumber = "TKT-".$t.'-'.$fromDate.'-'.$toDate;
$tktNumber = "TKT-$empId-$t";

$sql = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `LocationId`, `Verifier`, `Approver`, `Start`, `End`, `TktNumber`) VALUES ('$empId',$menuId,1,'$verifierEmpId','$approverEmpId','$fromDate','$toDate', '$tktNumber')";
if(mysqli_query($conn,$sql)){
	// $sql = "UPDATE `FlowCheckpointMaster` set `FlowEmpId`='$verifierEmpId' where `MenuId`=$menuId and `Status`='Created'";
	// if(mysqli_query($conn,$sql)){
	// 	$code = 200;
	// 	$message = 'Success1';
	// }

	// $sql = "UPDATE `FlowCheckpointMaster` set `FlowEmpId`='$approverEmpId' where `MenuId`=$menuId and `Status`='Verified'";
	// if(mysqli_query($conn,$sql)){
	// 	$code = 200;
	// 	$message = 'Success2';
	// }
	$code = 200;
	$message = 'Success';
}
else{
	$code = 404;
	$message = 'Something wrong';
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);


?>