<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$selectType = $_REQUEST["selectType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;
if($selectType == "assign"){
	$sql = "SELECT `Assign`.`AssignId`, `Employees`.`Name`  as empName, `Assign`.`MenuId`,`Menu`.`Cat`,`Assign`.`LocationId`,`Assign`.`StartDate`,`Assign`.`EndDate`, `Assign`.`Active` FROM `Assign` left join `Employees` on `Assign`.`EmpId` = `Employees`.`EmpId` left join `Menu` on `Assign`.`MenuId` = `Menu`.`MenuId` ";
	$query=mysqli_query($conn,$sql);

	$assignArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$assignId = $row["AssignId"];
		$empName = $row["empName"];
		$menuId = $row["MenuId"];
		$menuName = $row["Cat"];
		$locId = $row["LocationId"];
		$startDate = $row["StartDate"];
		$endDate = $row["EndDate"];
		$active = $row["Active"];
		
		$json = array(
			'assignId' => $assignId,
			'empId' => $empId,
			'empName' => $empName,
			'menuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'active' => $active,
		);
		array_push($assignArr,$json);
	}
	$output = array();
	$output = array('assignList' => $assignArr);
	echo json_encode($output);

}
else if($selectType == "activity"){
	$sql = "SELECT `Activity`.`EmpId`, `Employees`.`Name`  as empName, `Activity`.`MenuId`,`Menu`.`Cat`,`Activity`.`LocationId`,`Location`.`Name` as locName, `Activity`.`Event`,`Activity`.`MobileDateTime` FROM `Activity` left join `Employees` on `Activity`.`EmpId` = `Employees`.`EmpId` left join `Menu` on `Activity`.`MenuId` = `Menu`.`MenuId` left join `Location` on `Activity`.`LocationId` = `Location`.`LocationId` where `Activity`.`EmpId` = '$loginEmpId'";
	$query=mysqli_query($conn,$sql);

	$activityArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$empId = $row["EmpId"];
		$empName = $row["empName"];
		$menuId = $row["MenuId"];
		$menuName = $row["Cat"];
		$locId = $row["LocationId"];
		$locName = $row["locName"];
		$event = $row["Event"];
		$dateTime = $row["MobileDateTime"];
		
		$json = array(
			'empId' => $empId,
			'empName' => $empName,
			'menuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'locName' => $locName,
			'event' => $event,
			'dateTime' => $dateTime,
		);
		array_push($activityArr,$json);
	}
	$output = array();
	$output = array('activityList' => $activityArr);
	echo json_encode($output);

}
else if($selectType == "employee"){
	$sql = "SELECT * FROM `Employees` ";
	$query=mysqli_query($conn,$sql);

	$empArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$id = $row["Id"];
		$empId = $row["EmpId"];
		$empName = $row["Name"];
		$password = $row["Password"];
		$mobile = $row["Mobile"];
		$secMobile = $row["Secondary_Mobile"];
		$roleId = $row["RoleId"];
		$area = $row["Area"];
		$city = $row["City"];
		$state = $row["State"];
		$rmId = $row["RMId"];
		$fieldUser = $row["FieldUser"];
		$active = $row["Active"];

		$json = array(
			'id' => $id,
			'empId' => $empId,
			'empName' => $empName,
			// 'password' => $password,
			'mobile' => $mobile,
			'secMobile' => $secMobile,
			'roleId' => $roleId,
			'area' => $area,
			'city' => $city,
			'state' => $state,
			'rmId' => $rmId,
			'fieldUser' => $fieldUser,
			'active' => $active,
		);
		array_push($empArr,$json);
	}
	$output = array();
	$output = array('employeeList' => $empArr);
	echo json_encode($output);
}
else if($selectType == "device"){
	$sql = "SELECT * FROM `Devices` where `EmpId`='$loginEmpId' ";
	$query=mysqli_query($conn,$sql);

	$deviceArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$deviceId = $row["DeviceId"];
		$empId = $row["EmpId"];
		$mobile = $row["Mobile"];
		$token = $row["Token"];
		$name = $row["Name"];
		$make = $row["Make"];
		$model = $row["Model"];
		$os = $row["OS"];
		$appVer = $row["AppVer"];
		$active = $row["Active"];
		$registeredOn = $row["Registered"];
		
		$json = array(
			'deviceId' => $deviceId,
			'empId' => $empId,
			'mobile' => $mobile,
			'token' => $token,
			'name' => $name,
			'make' => $make,
			'model' => $model,
			'os' => $os,
			'appVer' => $appVer,
			'active' => $active,
			'registeredOn' => explode(" ", $registeredOn)[0],
		);
		array_push($deviceArr,$json);
	}
	$output = array();
	$output = array('deviceList' => $deviceArr);
	echo json_encode($output);
}
else if($selectType == "location"){
	$sql = "SELECT * FROM `Location` ";
	$query=mysqli_query($conn,$sql);

	$locationArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$locId = $row["LocationId"];
		$locName = $row["Name"];
		$geoCoordinate = $row["GeoCoordinates"];
		$geoExplode = explode(",", $geoCoordinate);
		
		$json = array(
			'locId' => $locId,
			'locName' => $locName,
			'geoCoordinate' => $geoCoordinate,
			'latitude' => $geoExplode[0],
			'longitude' => $geoExplode[1],

		);
		array_push($locationArr,$json);
	}
	$output = array();
	$output = array('locationList' => $locationArr);
	echo json_encode($output);
}
else if($selectType == "mapping"){
	$sql = "SELECT `Mapping`.`MappingId`, `Mapping`.`EmpId`, `Employees`.`Name`  as empName, `Mapping`.`MenuId`, `Menu`.`Cat`, `Mapping`.`LocationId`, `Mapping`.`Verifier`, `Mapping`.`Approver`, `Mapping`.`Active` FROM `Mapping` left join `Employees` on `Mapping`.`EmpId` = `Employees`.`EmpId` left join `Menu` on `Mapping`.`MenuId` = `Menu`.`MenuId`";
	$query=mysqli_query($conn,$sql);

	$mappingArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$mappingId = $row["MappingId"];
		$empId = $row["EmpId"];
		$empName = $row["empName"];
		$menuId = $row["MenuId"];
		$menuName = $row["Cat"];
		$locId = $row["LocationId"];
		$verifier = $row["Verifier"];
		$approver = $row["Approver"];
		$active = $row["Active"];
		
		$json = array(
			'mappingId' => $mappingId,
			'empId' => $empId,
			'empName' => $empName,
			'MenuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'verifier' => $verifier,
			'approver' => $approver,
			'active' => $active,

		);
		array_push($mappingArr,$json);
	}
	$output = array();
	$output = array('mappingList' => $mappingArr);
	echo json_encode($output);
}
else if($selectType == "checkpoint"){
	$sql = "SELECT * FROM `Checkpoints` order by `CheckpointId` desc ";
	$query=mysqli_query($conn,$sql);
	//echo $sql;
	$checkpointArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$checkpointId = $row["CheckpointId"];
		$description = $row["Description"];
		$valuess = $row["Value"];
		$typeId = $row["TypeId"];
		$mandatory = $row["Mandatory"];
		$editable = $row["Editable"];
		$correct = $row["Correct"];
		$size = $row["Size"];
		$score = $row["Score"];
		$language = $row["Language"];
		$active = $row["Active"];
		$dependent = $row["Dependent"];
		$logic = $row["Logic"];

		//echo $description;
		
		$json = array(
			'checkpointId' => $checkpointId,
			'description' => $description,
			'value' => $value,
			//'description' => '',
			//'value' => '',
			'typeId' => $typeId,
			'mandatory' => $mandatory,
			'editable' => $editable,
			'correct' => $correct,
			'size' => $size,
			'score' => $score,
			'language' => $language,
			'active' => $active,
			'dependent' => $dependent,
			'logic' => $logic

		);
		array_push($checkpointArr,$json);
	}
	$output = array();
	$output = array('checkpointList' => $checkpointArr);
	echo json_encode($output);
}

else if($selectType == "inputType"){
	$inputTypeSql = "SELECT `TypeId`,`Type` FROM `Type` ";
	$inputTypeQuery=mysqli_query($conn,$inputTypeSql);
	$inputTypeArr = array();
	while($inputTypeRow = mysqli_fetch_assoc($inputTypeQuery)){
		$typeId = $inputTypeRow["TypeId"];
		$typeName = $inputTypeRow["Type"];
		$json = array(
			'typeId' => $typeId,
			'typeName' => $typeName,
		);
		array_push($inputTypeArr,$json);
	}
	$output = array();
	$output = array('inputTypeList' => $inputTypeArr);
	echo json_encode($output);
}
else if($selectType == "checklist"){
	$checklistSql = "SELECT * FROM `Menu` where `Active` = 1 ";
	$checklistQuery=mysqli_query($conn,$checklistSql);
	$checklistArr = array();
	while($checklistRow = mysqli_fetch_assoc($checklistQuery)){
		$menuId = $checklistRow["MenuId"];
		$category = $checklistRow["Cat"];
		$subcategory = $checklistRow["Sub"];
		$caption = $checklistRow["Caption"];
		$checkpoint = $checklistRow["CheckpointId"];
		$verifier = $checklistRow["Verifier"];
		$approver = $checklistRow["Approver"];
		$geoFence = $checklistRow["GeoFence"];
		$icons = $checklistRow["Icons"];
		$active = $checklistRow["Active"];

		$json = array(
			'menuId' => $menuId,
			'category' => $category,
			'subcategory' => $subcategory,
			'caption' => $caption,
			'checkpoint' => $checkpoint,
			'verifier' => $verifier,
			'approver' => $approver,
			'geoFence' => $geoFence,
			'icons' => $icons,
			'active' => $active
		);
		array_push($checklistArr,$json);
	}
	$output = array();
	$output = array('checklist' => $checklistArr);
	echo json_encode($output);
}

else if($selectType == "headerMenu"){
	$headerMenuSql = "SELECT * FROM `Header_Menu` where `Is_Active` = 1 order by `Display_Order` ";
	$headerMenuQuery=mysqli_query($conn,$headerMenuSql);
	$headerMenuArr = array();
	while($headerMenuRow = mysqli_fetch_assoc($headerMenuQuery)){
		$id = $headerMenuRow["Id"];
		$menuName = $headerMenuRow["Name"];
		$routerLink = $headerMenuRow["Router_Link"];

		$json = array(
			'menuId' => $id,
			'menuName' => $menuName,
			'routerLink' => $routerLink
		);
		array_push($headerMenuArr,$json);
	}
	$output = array();
	$output = array('headerMenuList' => $headerMenuArr);
	echo json_encode($output);
}
else if($selectType == "role"){
	$roleSql = "SELECT * FROM `Role` ";
	$roleQuery=mysqli_query($conn,$roleSql);
	$roleArr = array();
	while($roleRow = mysqli_fetch_assoc($roleQuery)){
		$roleId = $roleRow["RoleId"];
		$roleName = $roleRow["Role"];
		$menuId = $roleRow["MenuId"];

		$json = array(
			'roleId' => $roleId,
			'roleName' => $roleName,
			'menuId' => $menuId
		);
		array_push($roleArr,$json);
	}
	$output = array();
	$output = array('roleList' => $roleArr);
	echo json_encode($output);
}

else if($selectType == "caption"){
	$loginEmpRole = $jsonData->loginEmpRole;
	$categoryName = $jsonData->categoryName;
	$subCategoryName = $jsonData->subCategoryName;

	$capSql = "SELECT * FROM `Menu` where `Cat` = '$categoryName' and `Sub` = '$subCategoryName' and `Active` = '1' ";
	$capQuery=mysqli_query($conn,$capSql);
	$capArr = array();
	while($capRow = mysqli_fetch_assoc($capQuery)){
		$menuId = $capRow["MenuId"];
		$caption = $capRow["Caption"];

		$json = array(
			'paramCode' => $menuId,
			'paramDesc' => $caption
		);
		array_push($capArr,$json);
	}
	$output = array();
	$output = array('captionList' => $capArr);
	echo json_encode($output);
}
else if($selectType == "myEmployee"){
	$sql = "SELECT `EmpId` as `empId`, `Name` as `empName` FROM `Employees` where `RMId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
	$query=mysqli_query($conn,$sql);
	$empList=array();
	while($row = mysqli_fetch_assoc($query)){
		array_push($empList,$row);
	}
	echo json_encode($empList);
}



?>