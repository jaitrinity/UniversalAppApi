<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");

mysqli_set_charset($conn,'utf8');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	include("connect.php");

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		exit();
	}

	mysqli_set_charset($conn,'utf8');

	$req = file_get_contents('php://input');
	$jsonData = json_decode($req);

	$response = new StdClass;
	$wrappedListArray = array();
	$response->wrappedList = $wrappedListArray;
	$response->responseCode = "0";
	$response->responseMsg = "Failure";
	$flag = 0;

	$empId = $jsonData->empId;
	$empRole = $jsonData->empRole;
	$tenentId = $jsonData->tenentId;
	$transId = $jsonData->transId;
	$approvalRemark = $jsonData->approvalRemark;
	$approvalStatus = $jsonData->approvalStatus;
	$approvalType = $jsonData->approvalType;

	if($approvalType == 'P'){
		$saveApprovalSql = "Update Transaction_Hdr set Primary_Approval_Id = '".$empId."', Primary_Approval_Remark = '".$approvalRemark."', Primary_Approval_DateTime = NOW(),Primary_Approval_Status = '".$approvalStatus."'
		where Trans_id = '".$transId."'";
	}
	else{
		$saveApprovalSql = "Update Transaction_Hdr set Secondary_Approval_Id = '".$empId."',Secondary_Approval_Remark = '".$approvalRemark."', Secondary_Approval_DateTime = NOW(),Secondary_Approval_Status = '".$approvalStatus."',Ticket_Status = '".$approvalStatus."' where Trans_id = '".$transId."'";
	}
	if(mysqli_query($conn,$saveApprovalSql)){
		$response->responseCode = "100000";
		$response->responseMsg = "Success";
		$flag = 1;
	}
	echo $response->responseCode;
	header('Content-type:application/json');
	echo json_encode($response);

}

?>