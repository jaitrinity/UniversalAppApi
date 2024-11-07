<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRole = $jsonData->loginEmpRole;
$transactionId = $jsonData->transactionId;
$menuId = $jsonData->menuId;
$locationId = $jsonData->locationId;
$status = $jsonData->status;
$validatedDataList = $jsonData->validatedDataList;


$activitySql = "INSERT INTO `Activity`(`EmpId`, `MenuId`, `LocationId`, `Event`,`MobileDateTime`, `ServerDateTime`) VALUES 
('$loginEmpId', $menuId, '$locationId', 'Submit', current_timestamp, current_timestamp ) ";
$activityQuery = mysqli_query($conn,$activitySql);
$lastActivityId = 0;
if($activityQuery){
	$lastActivityId = $conn->insert_id;

	$actionSql = "SELECT *  FROM `FlowActivityMaster` WHERE find_in_set('$loginEmpId', `EmpId`) <> 0 and `MenuId`='$menuId' and `ActivityId` = $transactionId and `FlowActivityId` is null order by `Id` LIMIT 0,1";
	$actionQuery=mysqli_query($conn,$actionSql);
	$actionRowCount=mysqli_num_rows($actionQuery);
	if($actionRowCount !=0){
		$actionRow = mysqli_fetch_assoc($actionQuery);
		$id = $actionRow["Id"];
		$afterStatus = $actionRow["AfterStatus"];

		$updateHDR_Sql = "UPDATE `TransactionHDR` set `Status` = '$afterStatus' WHERE `ActivityId` = $transactionId ";
		$updateHDRQuery = mysqli_query($conn,$updateHDR_Sql);

		$updateAct_Sql = "UPDATE `FlowActivityMaster` set `FlowActivityId`=$lastActivityId, `FlowEmpId`='$loginEmpId', `FlowSubmitDate`=current_timestamp where `Id`=$id ";
		$updateActQuery = mysqli_query($conn,$updateAct_Sql);
		
	}

	$insertDET_Sql = "INSERT INTO `TransactionDTL`(`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ";
	for($i=0;$i<count($validatedDataList);$i++){
		$checkpointId = $validatedDataList[$i]->checkpointId;
		$checkpointValue = $validatedDataList[$i]->checkpointValue;
		
		$dependChpId = $validatedDataList[$i]->dependChpId;
		$typeId = $validatedDataList[$i]->typeId;
		$size = $validatedDataList[$i]->size;
		if($typeId == "7" && $size == "1"){
			// date
			$newDate = date("d/m/Y", strtotime($checkpointValue));
			$checkpointValue = $newDate;
		}
		else if($typeId == "7" && $size == "0"){
			// time
			$newTime = date("g:i A", strtotime($checkpointValue));
			$checkpointValue = $newTime;
		}

		$detSql = $insertDET_Sql."($lastActivityId, '$checkpointId', '$checkpointValue', $dependChpId)";
		// echo $detSql;
		$detQuery = mysqli_query($conn,$detSql);
	}

}

if($lastActivityId !=0 ){
	$output = array('status' => 200, 'message' => 'SUCCESSFUL');
	echo json_encode($output);
}	
else{
	$output = array('status' => 0, 'message' => 'Something wrong');
	echo json_encode($output);
}

?>