<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$insertType = $_REQUEST["insertType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

if($insertType == "location"){
	$state = $jsonData->state;
	$city = $jsonData->city;
	$area = $jsonData->area;
	$locationName = $jsonData->locationName;
	$geoCoordinate = $jsonData->geoCoordinate;


	$insertLocation = "INSERT INTO `Location`(`State`, `City`, `Area`, `Name`, `GeoCoordinates`, `Tenent_Id`) VALUES ('$state', '$city', '$area', '$locationName', '$geoCoordinate', $tenentId)";

	$code = 0;
	$message = "";
	try {
		if(mysqli_query($conn,$insertLocation)){
			//$last_id = $conn->insert_id;
			$code = 200;
			$message = "Successfully inserted";
			//echo "New record created successfully. Last inserted ID is: " . $last_id;
		}
		else{
			$code = 0;
			$message = "Something wrong";
			//echo "New record created successfully. Last inserted ID is: " . $last_id;
		}
	} catch (Exception $e) {
		$code = 500;
		$message = $e->getMessage();
	}
		
	$output = array('code' => $code, 'message' => $message);
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

	$sql1 = "SELECT * from `Employees` where `Mobile` = '$mobile' and `Tenent_Id`=$tenentId ";
	$query1 = mysqli_query($conn,$sql1);

	$isExist1 = false;
	if(mysqli_num_rows($query1) != 0){
		$isExist1 = true;
	}

	// $sql2 = "SELECT * from `Employees` where `Secondary_Mobile` = '$secondaryMobile' and `Tenent_Id`=$tenentId ";
	// $query2 = mysqli_query($conn,$sql2);
	// $isExist2 = false;
	// if(mysqli_num_rows($query2) != 0){
	// 	$isExist2 = true;
	// }

	$sql3 = "SELECT * from `Employees` where `EmpId` = '$employeeId' and `Tenent_Id`=$tenentId ";
	$query3 = mysqli_query($conn,$sql3);
	$isExist3 = false;
	if(mysqli_num_rows($query3) != 0){
		$isExist3 = true;
	}

	$code = 0;
	$message = "";
	if($isExist1){
		$code = 422;
		$message = "already exist employee on ".$mobile." mobile number";
	}
	else if($isExist2){
		$code = 422;
		$message = "already exist employee on ".$secondaryMobile." secondary mobile number";
	}
	else if($isExist3){
		$code = 422;
		$message = "already exist employee on ".$employeeId." employee id";
	}
	else{
		// $passTxt = 'tr'.$employeeId;
		$passTxt = '1234';
		$password = base64_encode($passTxt);

		$insertEmployee = "INSERT INTO `Employees`(`EmpId`, `Name`,`Password`,`Mobile`,`Secondary_Mobile`,`RoleId`,`Area`,`City`,`State`,`RMId`,`FieldUser`,`Tenent_Id`) VALUES ('$employeeId','$employeeName','$password','$mobile','$secondaryMobile',$roleId,'$area','$city','$state','$rmId',$fieldUser,$tenentId)";

		if(mysqli_query($conn,$insertEmployee)){
			$code = 200;
			$message = "Successfully inserted";	
		}
		else{
			$code = 0;
			$message = "Something wrong";
		}
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);

}
else if($insertType == "assign"){
	$empId = $jsonData->empId;
	$menuId = $jsonData->menuId;
	$locationId = $jsonData->locationId;
	$startDate = $jsonData->startDate;
	$endDate = $jsonData->endDate;
	
	$insertAssign = "INSERT INTO `Assign`(`EmpId`, `MenuId`,`LocationId`,`StartDate`,`EndDate`,`Active`) VALUES ('$empId',$menuId,'$locationId','$startDate','$endDate',1)";

	if(mysqli_query($conn,$insertAssign)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);

}
else if($insertType == "mapping"){
	$empId = $jsonData->empId;
	$menuId = $jsonData->menuId;
	$locationId = $jsonData->locationId;
	$verifier = $jsonData->verifier;
	$approver = $jsonData->approver;
	
	$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`,`LocationId`,`Verifier`,`Approver`,`Active`) VALUES ('$empId',$menuId,'$locationId','$verifier','$approver',1)";

	if(mysqli_query($conn,$insertMapping)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
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
	$active = $jsonData->active;

	if($inputTypeId == 18 || $inputTypeId == 19){
		require 'base64ToAny.php';
		$t=time();
		$base64 = new Base64ToAny();

		$optionValue = $base64->base64_to_jpeg($optionValue,$t.'_'.$inputTypeId);
	}
	
	$sql = "INSERT INTO `Checkpoints`(`Description`, `Value`,`TypeId`,`Mandatory`,`Editable`,`Language`,`Correct`,`Size`,`Score`,`Dependent`,`Logic`,`Active`,`Tenent_Id`) VALUES (?,?,$inputTypeId,$isMandatory,$isEditable,$languageId,'$correct','$size','$score','$dependent','$logic',1,$tenentId)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $description, $optionValue);

	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);

}
else if($insertType == "checkpoint_old"){
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
	$active = $jsonData->active;

	if($inputTypeId == 18 || $inputTypeId == 19){
		require 'base64ToAny.php';
		$t=time();
		$base64 = new Base64ToAny();

		$optionValue = $base64->base64_to_jpeg($optionValue,$t.'_'.$inputTypeId);
	}
	
	$insertCheckpoint = "INSERT INTO `Checkpoints`(`Description`, `Value`,`TypeId`,`Mandatory`,`Editable`,`Language`,`Correct`,`Size`,`Score`,`Dependent`,`Logic`,`Active`,`Tenent_Id`) VALUES ('$description','$optionValue',$inputTypeId,$isMandatory,$isEditable,$languageId,'$correct','$size','$score','$dependent','$logic',1,$tenentId)";

	$code = 0;
	$message = "";
	if(mysqli_query($conn,$insertCheckpoint)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);

}
else if($insertType == "inputType"){
	$typeName = $jsonData->typeName;
	$insertInputType = "INSERT INTO `Type`(`Type`) VALUES ('$typeName')";
	if(mysqli_query($conn,$insertInputType)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($insertType == "checklist"){
	$category = $jsonData->category;
	// $catBgFontColor = $jsonData->catBgFontColor;
	$subcategory = $jsonData->subcategory;
	// $subCatBgFontColor = $jsonData->subCatBgFontColor;
	$caption = $jsonData->caption;
	// $capBgFontColor = $jsonData->capBgFontColor;
	$checkpointId = $jsonData->checkpointId;
	$verifierChkId = $jsonData->verifierChkId;
	$approverChkId = $jsonData->approverChkId;
	$geoFence = $jsonData->geoFence;
	$categoryIcon = $jsonData->categoryIcon;
	$subcategoryIcon = $jsonData->subcategoryIcon;
	$captionIcon = $jsonData->captionIcon;
	$verifierRoleId = $jsonData->verifierRoleId;
	$approvalRoleId = $jsonData->approvalRoleId;

	require 'base64ToAny.php';
	$t=time();
	$base64 = new Base64ToAny();

	if($categoryIcon != ""){
		$categoryIcon = $base64->base64_to_jpeg($categoryIcon,$t.'_CategoryIcon');
	}
	if($subcategory != "" && $subcategoryIcon != ""){
		$subcategoryIcon = $base64->base64_to_jpeg($subcategoryIcon,$t.'_SubcategoryIcon');
	}
	if($caption != "" && $captionIcon != ""){
		$captionIcon = $base64->base64_to_jpeg($captionIcon,$t.'_CaptionIcon');
	}


	require 'AppDefaultColorClass.php';
	$classObj = new AppDefaultColorClass();
	$appColors = $classObj->getAppDefaultColor();
	$colors = $appColors;

	$colorList = explode(":", $colors);
	$catBgFontColor = $colorList[0];
	$subCatBgFontColor = $colorList[1];
	$capBgFontColor = $colorList[2];

	$insertChecklist = "INSERT INTO `Menu`(`Cat`,`Sub`,`Caption`,`CheckpointId`,`Verifier`,`Approver`,`GeoFence`,`CategoryIcon`, `SubCategoryIcon`, `CaptionIcon`, `CatBgFontColor`, `SubCatBgFontColor`, `CapBgFontColor`, `Verifier_RoleId`, `Approver_RoleId`,`Tenent_Id`) VALUES 
	('$category', '$subcategory', '$caption', '$checkpointId', '$verifierChkId', '$approverChkId', '$geoFence', '$categoryIcon', '$subcategoryIcon', '$captionIcon', '$catBgFontColor', '$subCatBgFontColor', '$capBgFontColor', '$verifierRoleId', '$approvalRoleId',$tenentId)";

	// echo $insertChecklist;

	$code=0;
	$message="";
	if(mysqli_query($conn,$insertChecklist)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($insertType == "role"){
	$roleName = $jsonData->roleName;
	$menuId = $jsonData->menuId;

	$insertRole = "INSERT INTO `Role`(`Role`,`MenuId`, `Tenent_Id`) VALUES ('$roleName', '$menuId', $tenentId)";

	$code=0;
	$message="";
	if(mysqli_query($conn,$insertRole)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($insertType == "employeeLocationMapping"){
	$locId = $jsonData->locId;
	$employee = $jsonData->employee;
	$explodeEmp =  explode(",", $employee);

	$delMapping = "DELETE FROM `EmployeeLocationMapping` WHERE `LocationId`=$locId  AND `Tenent_Id`=$tenentId";
	mysqli_query($conn,$delMapping);

	$insertDataList = array();
	for($i=0;$i<count($explodeEmp);$i++){
		$data = "($locId, '$explodeEmp[$i]', $tenentId)";
		array_push($insertDataList, $data);
	}
	$code=0;
	$message="";
	if(count($insertDataList) != 0){
		$insertTable = "INSERT INTO `EmployeeLocationMapping`(`LocationId`, `Emp_Id`, `Tenent_Id`)";
		$insertData = implode(",", $insertDataList);

		$insertEmpLocMapping = $insertTable." values ".$insertData;
		if(mysqli_query($conn,$insertEmpLocMapping)){
			$code = 200;
			$message = "Mapping successfully done";
		}
		else{
			$code = 0;
			$message = "Something wrong";
		}
	}
	else{
		$code = 200;
		$message = "Mapping removed";
	}
		
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}	
else if($insertType == "nLevelFlow"){
	$menuId = $jsonData->menuId;
	$flowList = $jsonData->flowList;


	$valueList = [];

	for($i=0;$i<count($flowList);$i++){
		$flowObj = $flowList[$i];
		$roleId = $flowObj->roleId;
		$checkpointId = $flowObj->checkpointId;
		$status = "";
		$afterStatus = "";
		if($i==0){
			$status = "Created";
			$afterStatus = "TR1";
		}
		else{
			$nextI = $i+1;
			$status = "TR".$i;
			$afterStatus = "TR".$nextI;
		}

		$value = "($menuId, $roleId, '$status', '$afterStatus', '$checkpointId')";
		array_push($valueList, $value);

	}

	$data = implode(",", $valueList);

	$sql = "INSERT INTO `FlowCheckpointMaster`(`MenuId`, `RoleId`, `Status`, `AfterStatus`, `FlowCheckpointId`) VALUES $data";
	$code=0;
	$message="";
	if(mysqli_query($conn,$sql)){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}


?>