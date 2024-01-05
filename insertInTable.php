<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
$insertType = $_REQUEST["insertType"];
//echo $insertType;
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
if($insertType == "location"){
	$locationName = $jsonData->locationName;
	//$latitude = $jsonData->latitude;
	//$longitude = $jsonData->longitude;
	//$geoCoordinate = $latitude.",".$longitude;
	$geoCoordinate = $jsonData->geoCoordinate;


	$insertLocation = "INSERT INTO `Location`(`Name`, `GeoCoordinates`) VALUES ('$locationName','$geoCoordinate')";

	$output = new StdClass;
	if(mysqli_query($conn,$insertLocation)){
		//$last_id = $conn->insert_id;
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
		//echo "New record created successfully. Last inserted ID is: " . $last_id;
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
		//echo "New record created successfully. Last inserted ID is: " . $last_id;
	}
	echo json_encode($output);
}
else if($insertType == "employee"){
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

	$sql1 = "select * from `Employees` where `Mobile` = '$mobile' ";
	$query1 = mysqli_query($conn,$sql1);

	$isExist1 = false;
	if(mysqli_num_rows($query1) != 0){
		$isExist1 = true;
	}

	$sql2 = "select * from `Employees` where `Secondary_Mobile` = '$secondaryMobile' ";
	$query2 = mysqli_query($conn,$sql2);
	$isExist2 = false;
	if(mysqli_num_rows($query2) != 0){
		$isExist2 = true;
	}

	$sql3 = "select * from `Employees` where `EmpId` = '$employeeId' ";
	$query3 = mysqli_query($conn,$sql3);
	$isExist3 = false;
	if(mysqli_num_rows($query3) != 0){
		$isExist3 = true;
	}

	$output = new StdClass;
	if($isExist1){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$mobile." mobile number";
	}
	else if($isExist2){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$secondaryMobile." secondary mobile number";
	}
	else if($isExist3){
		$output -> responseCode = "422";
		$output -> responseDesc = "already exist employee on ".$employeeId." employee id";
	}
	else{
		$insertEmployee = "INSERT INTO `Employees`(`EmpId`, `Name`,`Password`,`Mobile`,`Secondary_Mobile`,`RoleId`,`Area`,`City`,`State`,`RMId`,`FieldUser`,`Registered`,`Update`,`Active`) VALUES ('$employeeId','$employeeName',concat('tr','$employeeId'),'$mobile','$secondaryMobile',$roleId,'$area','$city','$state','$rmId',$fieldUser,current_timestamp,current_timestamp,1)";

		if(mysqli_query($conn,$insertEmployee)){
			$output -> responseCode = "100000";
			$output -> responseDesc = "Successfully inserted";	
		}
		else{
			$output -> responseCode = "0";
			$output -> responseDesc = "Something wrong";
		}
	}
	echo json_encode($output);

}
else if($insertType == "assign"){
	$empId = $jsonData->empId;
	$menuId = $jsonData->menuId;
	$locationId = $jsonData->locationId;
	$startDate = $jsonData->startDate;
	$endDate = $jsonData->endDate;
	
	$insertAssign = "INSERT INTO `Assign`(`EmpId`, `MenuId`,`LocationId`,`StartDate`,`EndDate`,`Active`) VALUES ('$empId',$menuId,'$locationId','$startDate','$endDate',1)";


	$output = new StdClass;
	if(mysqli_query($conn,$insertAssign)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);

}
else if($insertType == "mapping"){
	$empId = $jsonData->empId;
	$menuId = $jsonData->menuId;
	$locationId = $jsonData->locationId;
	$verifier = $jsonData->verifier;
	$approver = $jsonData->approver;
	
	$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`,`LocationId`,`Verifier`,`Approver`,`Active`) VALUES ('$empId',$menuId,'$locationId','$verifier','$approver',1)";

	$output = new StdClass;
	if(mysqli_query($conn,$insertMapping)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);

}
else if($insertType == "checkpoint"){
	$description = $jsonData->description;
	$optionValue = $jsonData->optionValue;
	$isMandatory = $jsonData->isMandatory;
	$isEditable = $jsonData->isEditable;
	$inputTypeId = $jsonData->inputTypeId;
	$languageId = $jsonData->languageId;
	$correct = $jsonData->correct;
	$size = $jsonData->size;
	$score = $jsonData->score;
	$dependent = $jsonData->dependent;
	$logic = $jsonData->logic;
	
	$insertCheckpoint = "INSERT INTO `Checkpoints`(`Description`, `Value`,`TypeId`,`Mandatory`,`Editable`,`Language`,`Correct`,`Size`,`Score`,`Dependent`,`Logic`,`Active`) VALUES ('$description','$optionValue',$inputTypeId,$isMandatory,$isEditable,$languageId,'$correct','$size','$score','$dependent','$logic',1)";

	$output = new StdClass;
	if(mysqli_query($conn,$insertCheckpoint)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);

}
else if($insertType == "inputType"){
	$typeName = $jsonData->typeName;
	$insertInputType = "INSERT INTO `Type`(`Type`) VALUES ('$typeName')";
	$output = new StdClass;
	if(mysqli_query($conn,$insertInputType)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($insertType == "checklist"){
	$category = $jsonData->category;
	$subcategory = $jsonData->subcategory;
	$caption = $jsonData->caption;
	$checkpointId = $jsonData->checkpointId;
	$verifierId = $jsonData->verifierId;
	$approverId = $jsonData->approverId;
	$geoFence = $jsonData->geoFence;
	$icons = $jsonData->icons;

	$insertChecklist = "INSERT INTO `Menu`(`Cat`,`Sub`,`Caption`,`CheckpointId`,`Verifier`,`Approver`,`GeoFence`,`Icons`,`Active`) VALUES ('$category', '$subcategory','$caption','$checkpointId','$verifierId','$approverId','$geoFence','$icons',1)";

	$output = new StdClass;
	if(mysqli_query($conn,$insertChecklist)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}
else if($insertType == "role"){
	$roleName = $jsonData->roleName;
	$menuId = $jsonData->menuId;

	$insertRole = "INSERT INTO `Role`(`Role`,`MenuId`) VALUES ('$roleName', '$menuId')";

	$output = new StdClass;
	if(mysqli_query($conn,$insertRole)){
		$output -> responseCode = "100000";
		$output -> responseDesc = "Successfully inserted";
	}
	else{
		$output -> responseCode = "0";
		$output -> responseDesc = "Something wrong";
	}
	echo json_encode($output);
}


?>