<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$updateType = $_REQUEST["updateType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

if($updateType == "transactionMapping"){
	$transactionId = $jsonData->transactionId;
	$verifierEmpId = $jsonData->verifierEmpId;
	$approverEmpId = $jsonData->approverEmpId;

	$updateMapping = "update `Mapping` set `Verifier` = '$verifierEmpId', `Approver` = '$approverEmpId' where `ActivityId` = $transactionId ";
	
	if(mysqli_query($conn,$updateMapping)){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "employee"){
	$id = $jsonData->id;
	$action = $jsonData->action;
	
	$updateEmployee = "UPDATE `Employees` set `Active` = $action where `Id` = $id ";
	if(mysqli_query($conn,$updateEmployee)){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "editEmployee"){
	$id = $jsonData->id;
	$employeeId = $jsonData->empId;
	$employeeName = $jsonData->empName;
	$roleId = $jsonData->roleId;
	$rmId = $jsonData->rmId;
	$mobile = $jsonData->mobile;
	$secondaryMobile = $jsonData->secondaryMobile;
	$area = $jsonData->area;
	$city = $jsonData->city;
	$state = $jsonData->state;
	$fieldUser = $jsonData->fieldUser;

	$sql1 = "SELECT * from `Employees` where `Id` = $id and `Mobile` = '$mobile'";
	$query1 = mysqli_query($conn,$sql1);
	$isSame = false;
	if(mysqli_num_rows($query1) != 0){
		$isSame = true;
	}

	if(!$isSame){
		$sql2 = "SELECT * from `Employees` where `Mobile` = '$mobile' ";
		$query2 = mysqli_query($conn,$sql2);
		$isExist2 = false;
		if(mysqli_num_rows($query2) != 0){
			$isExist2 = true;
		}

		// $sql3 = "SELECT * from `Employees` where `Secondary_Mobile` = '$secondaryMobile' ";
		// $query3 = mysqli_query($conn,$sql3);
		// $isExist3 = false;
		// if(mysqli_num_rows($query3) != 0){
		// 	$isExist3 = true;
		// }
	}

	if($isSame){
		$updateEditEmployee = "UPDATE `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `Secondary_Mobile`='$secondaryMobile', `RoleId`=$roleId, `Area`='$area', `City`='$city', `State`='$state', `RMId`='$rmId', `FieldUser`=$fieldUser, `Update`=current_timestamp where `Id` = $id ";
	
		if(mysqli_query($conn,$updateEditEmployee)){
			$code = 200;
			$message = "Successfully update";
		}
		else{
			$code = 0;
			$message = "Something wrong";
		}
	}
	// else if($isExist1){
	// 	$output -> responseCode = "422";
	// 	$output -> responseDesc = "already exist employee on ".$secondaryMobile." secondary mobile number";
	// }
	else if($isExist2){
		$code = 422;
		$message = "already exist employee on ".$mobile." mobile number";
	}
	// else if($isExist3){
	// 	$code = 422;
	// 	$message = "already exist employee on ".$secondaryMobile." secondary mobile number";
	// }
	else{
		$updateEditEmployee = "UPDATE `Employees` set `Name`='$employeeName', `Mobile`='$mobile', `Secondary_Mobile`='$secondaryMobile', `RoleId`=$roleId, `Area`='$area', `City`='$city', `State`='$state', `RMId`='$rmId', `FieldUser`=$fieldUser, `Update`=current_timestamp where `Id` = $id ";
	
		if(mysqli_query($conn,$updateEditEmployee)){
			$code = 200;
			$message = "Successfully update";
		}
		else{
			$code = 0;
			$message = "Something wrong";
		}
	}
	
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "updateRole"){
	$roleId = $jsonData->roleId;
	$role = $jsonData->role;
	$menuId = $jsonData->menuId;
	
	$updateRole = "UPDATE `Role` set `Role`='$role', `MenuId`='$menuId' where `RoleId` = $roleId ";
	if(mysqli_query($conn,$updateRole)){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "updateLocation"){
	$locId = $jsonData->locId;
	$locName = $jsonData->locName;
	$geoCoordinate = $jsonData->geoCoordinate;
	
	$updateMapping = "UPDATE `Location` set `Name` = '$locName', `GeoCoordinates` = '$geoCoordinate' where `LocationId` = $locId ";
	if(mysqli_query($conn,$updateMapping)){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "locationStatus"){
	$locId = $jsonData->locId;
	$isActive = $jsonData->isActive;
	
	$sql = "UPDATE `Location` set `Is_Active`=$isActive where `LocationId`=$locId ";
	if(mysqli_query($conn,$sql)){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "checklist"){
	$menuId = $jsonData->menuId;
	$category = $jsonData->category;
	$catBgFontColor = $jsonData->catBgFontColor;
	$subcategory = $jsonData->subcategory;
	$subCatBgFontColor = $jsonData->subCatBgFontColor;
	$caption = $jsonData->caption;
	$capBgFontColor = $jsonData->capBgFontColor;
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

	$moreUpdate = "";

	if($categoryIcon != ""){
		$categoryIcon = $base64->base64_to_jpeg($categoryIcon,$t.'_CategoryIcon');
		$moreUpdate .= ", `CategoryIcon`='$categoryIcon'";
	}
	if($subcategoryIcon != ""){
		$subcategoryIcon = $base64->base64_to_jpeg($subcategoryIcon,$t.'_SubcategoryIcon');
		$moreUpdate .= ", `SubCategoryIcon`='$subcategoryIcon'";
	}
	if($captionIcon != ""){
		$captionIcon = $base64->base64_to_jpeg($captionIcon,$t.'_CaptionIcon');
		$moreUpdate .= ", `CaptionIcon`='$captionIcon'";
	}

	if($catBgFontColor !=""){
		$moreUpdate .= ", `CatBgFontColor`='$catBgFontColor'";
	}
	if($subCatBgFontColor != ""){
		$moreUpdate .= ", `SubCatBgFontColor`='$subCatBgFontColor'";
	}
	if($capBgFontColor != ""){
		$moreUpdate .= ", `CapBgFontColor`='$capBgFontColor'";
	}

	$sql = "UPDATE `Menu` set `Cat`='$category', `Sub`='$subcategory', `Caption`='$caption', `CheckpointId`='$checkpointId', `Verifier`='$verifierChkId', `Approver`='$approverChkId', `GeoFence`='$geoFence', `Verifier_RoleId`='$verifierRoleId', `Approver_RoleId`='$approvalRoleId' $moreUpdate where `MenuId`=$menuId";

	$code=0;
	$message="";
	if(mysqli_query($conn,$sql)){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
?>