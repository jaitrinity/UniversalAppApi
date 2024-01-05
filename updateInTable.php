<?php
include("dbConfiguration.php");
$updateType = $_REQUEST["updateType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
if($updateType == "device"){
	$deviceId = $jsonData->deviceId;
	$action = $jsonData->action;
	
	$updateDevice = "update `Devices` set `Active` = $action, `Update` = current_timestamp where `DeviceId` = $deviceId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateDevice)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "mapping"){
	$mappingId = $jsonData->mappingId;
	$action = $jsonData->action;
	
	$updateMapping = "update `Mapping` set `Active` = $action where `MappingId` = $mappingId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "assign"){
	$assignId = $jsonData->assignId;
	$action = $jsonData->action;
	
	$updateAssign = "update `Assign` set `Active` = $action where `AssignId` = $assignId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateAssign)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "employee"){
	$id = $jsonData->id;
	$action = $jsonData->action;
	
	$updateEmployee = "update `Employees` set `Active` = $action where `Id` = $id ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateEmployee)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "editEmployee"){
	$id = $jsonData->id;
	$employeeId = $jsonData->employeeId;
	$employeeName = $jsonData->employeeName;
	$roleId = $jsonData->roleId;
	$rmId = $jsonData->rmId;
	$mobile = $jsonData->mobile;
	$secondaryMobile = $jsonData->secondaryMobile;
	$area = $jsonData->area;
	$city = $jsonData->city;
	$state = $jsonData->state;
	$fieldUser = $jsonData->fieldUser;

	$sql1 = "select * from `Employees` where `Id` = $id and `Mobile` = '$mobile' and `Secondary_Mobile` = '$secondaryMobile'  ";
	$query1 = mysqli_query($conn,$sql1);
	$isSame = false;
	if(mysqli_num_rows($query1) != 0){
		$isSame = true;
	}

	if(!$isSame){
		$sql2 = "select * from `Employees` where `Mobile` = '$mobile' ";
		$query2 = mysqli_query($conn,$sql2);
		$isExist2 = false;
		if(mysqli_num_rows($query2) != 0){
			$isExist2 = true;
		}

		$sql3 = "select * from `Employees` where `Secondary_Mobile` = '$secondaryMobile' ";
		$query3 = mysqli_query($conn,$sql3);
		$isExist3 = false;
		if(mysqli_num_rows($query3) != 0){
			$isExist3 = true;
		}
	}

	

	$output = new StdClass;
	if($isSame){
		$updateEditEmployee = "update `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `Secondary_Mobile`='$secondaryMobile', `RoleId`=$roleId, `Area`='$area', `City`='$city', `State`='$state', `RMId`='$rmId', `FieldUser`=$fieldUser, `Update`=current_timestamp where `Id` = $id ";
	
		if(mysqli_query($conn,$updateEditEmployee)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully update";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	// else if($isExist1){
	// 	$output -> responseCode = "422";
	// 	$output -> responseDesc = "already exist employee on ".$secondaryMobile." secondary mobile number";
	// }
	else if($isExist2){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$mobile." mobile number";
	}
	else if($isExist3){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$secondaryMobile." secondary mobile number";
	}
	else{
		$updateEditEmployee = "update `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `Secondary_Mobile`='$secondaryMobile', `RoleId`=$roleId, `Area`='$area', `City`='$city', `State`='$state', `RMId`='$rmId', `FieldUser`=$fieldUser, `Update`=current_timestamp where `Id` = $id ";
	
		if(mysqli_query($conn,$updateEditEmployee)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully update";
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	
	
	echo json_encode($output);
}
else if($updateType == "roleDelete"){
	$roleId = $jsonData->roleId;
	
	$deleteRole = "delete from `Role` where `RoleId` = $roleId ";
	$output = new StdClass;
	if(mysqli_query($conn,$deleteRole)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully Deleted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "roleUpdate"){
	$roleId = $jsonData->roleId;
	$menuId = $jsonData->menuId;
	
	$updateRole = "update `Role` set `MenuId` = '$menuId' where `RoleId` = $roleId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateRole)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}

else if($updateType == "updateMapping"){
	$mappingId = $jsonData->mappingId;
	$locationId = $jsonData->locationId;
	$verifierId = $jsonData->verifierId;
	$approverId = $jsonData->approverId;
	
	$updateMapping = "update `Mapping` set `LocationId` = '$locationId',`Verifier` = '$verifierId', `Approver` = '$approverId' where `MappingId` = $mappingId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($updateType == "updateLocation"){
	$locationId = $jsonData->locationId;
	$locationName = $jsonData->locationName;
	$geoCoordinate = $jsonData->geoCoordinate;
	
	$updateMapping = "update `Location` set `Name` = '$locationName', `GeoCoordinates` = '$geoCoordinate' where `LocationId` = $locationId ";
	$output = new StdClass;
	if(mysqli_query($conn,$updateMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully update";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}

?>