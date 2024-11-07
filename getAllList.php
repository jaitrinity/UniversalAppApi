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
	$output = array('assignList' => $assignArr);
	echo json_encode($output);

}
else if($selectType == "activity"){
	$filterSql = "";
	if($loginEmpRoleId == "4"){
		
	}
	else{
		// Self and RM
		$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
	}

	$underEmpList = array();
	$sql = "SELECT `EmpId` FROM `Employees` WHERE 1=1 $filterSql and `Tenent_Id`='$tenentId' and `Active`=1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($underEmpList, $row["EmpId"]);
	}

	$empIds = implode("','", $underEmpList);

	$sql = "SELECT a.`ActivityId`, `a`.`EmpId`, `e`.`Name`  as empName, `a`.`MenuId`, `m`.`Cat`, `a`.`LocationId`, `l`.`Name` as locName, `a`.`Event`, `a`.`MobileDateTime` FROM `Activity` a left join `Employees` e on `a`.`EmpId` = `e`.`EmpId` left join `Menu` m on `a`.`MenuId` = `m`.`MenuId` left join `Location` l on `a`.`LocationId` = `l`.`LocationId` where `a`.`EmpId` in ('$empIds') order by a.`ActivityId` desc";
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
			'activityId' => $row["ActivityId"],
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
	$output = array('activityList' => $activityArr);
	echo json_encode($output);

}
else if($selectType == "employee"){
	$filterSql = "";
	if($loginEmpRoleId == 4){

	}
	else{
		$filterSql .= "and (e.RMId = '$loginEmpId' or e.EmpId = '$loginEmpId')";
	}
	// $sql = "SELECT * FROM `Employees` where 1=1 $filterSql and `Tenent_Id`=$tenentId and `Active`=1 ";
	$sql = "SELECT e.*, e1.Name as RMName FROM Employees e left join Employees e1 on e.RMId=e1.EmpId where 1=1 $filterSql and e.Tenent_Id=$tenentId";
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
		$rmName = $row["RMName"];
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
			'rmName' => $rmName,
			'fieldUser' => $fieldUser,
			'active' => $active,
		);
		array_push($empArr,$json);
	}
	$output = array('employeeList' => $empArr);
	echo json_encode($output);
}
else if($selectType == "device"){
	$filterSql = "";
	if($loginEmpRoleId == 4){

	}
	else{
		$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
	}
	$underEmpList = array();
	$sql = "SELECT `EmpId` FROM `Employees` WHERE 1=1 $filterSql and `Tenent_Id`=$tenentId and `Active`=1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($underEmpList, $row["EmpId"]);
	}

	$empIds = implode("','", $underEmpList);

	$sql = "SELECT * FROM `Devices` where `EmpId` in ('$empIds') order by `DeviceId` desc";
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
	$output = array('deviceList' => $deviceArr);
	echo json_encode($output);
}
else if($selectType == "location"){
	$sql = "SELECT * FROM `Location` where `Tenent_Id`=$tenentId order by `LocationId` desc ";
	$query=mysqli_query($conn,$sql);

	$locationArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$locId = $row["LocationId"];
		$locName = $row["Name"];
		$geoCoordinate = $row["GeoCoordinates"];
		$geoCoordinate = str_replace("/", ",", $geoCoordinate);
		$geoExplode = explode(",", $geoCoordinate);
		$isActive = $row["Is_Active"];
		$status = $isActive == 1 ? "Active" : "Deactive";

		$sql2 = "SELECT emp.EmpId as empId, emp.Name as empName from EmployeeLocationMapping empLoc join Employees emp on empLoc.Emp_Id = emp.EmpId where empLoc.LocationId=$locId";
		$query2=mysqli_query($conn,$sql2);
		$empArr = array();
		while($row2 = mysqli_fetch_assoc($query2)){
			array_push($empArr,$row2);
		}
		
		$json = array(
			'locId' => $locId,
			'locName' => $locName,
			'geoCoordinate' => $geoCoordinate,
			'latitude' => $geoExplode[0],
			'longitude' => $geoExplode[1],
			'isActive' => $isActive,
			'status' => $status,
			'empList' => $empArr

		);
		array_push($locationArr,$json);
	}

	$output = array('locationList' => $locationArr);
	echo json_encode($output);
}
else if($selectType == "mapping"){
	// $sql = "SELECT `m`.`MappingId`, `m`.`EmpId`, `e`.`Name`  as empName, `m`.`MenuId`, `mu`.`Cat`, `m`.`LocationId`, `m`.`Verifier`, `m`.`Approver`, `m`.`Active` FROM `Mapping` m left join `Employees` e on `m`.`EmpId` = `e`.`EmpId` left join `Menu` mu on `m`.`MenuId` = `mu`.`MenuId` order by `m`.`MappingId` desc";

	$filterSql = "";
	if($loginEmpRoleId == 4){

	}
	else{
		$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
	}
	$underEmpList = array();
	$sql = "SELECT `EmpId` FROM `Employees` WHERE 1=1 $filterSql and `Tenent_Id`=$tenentId and `Active`=1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($underEmpList, $row["EmpId"]);
	}

	$empIds = implode("','", $underEmpList);

	$sql = "SELECT `m`.`MappingId`, `m`.`EmpId`, `e`.`Name` AS `EmpName`, `m`.`MenuId`, `mu`.`Cat`, `m`.`LocationId`, l.Name as LocName, `m`.`Verifier`, `m`.`Approver`, e1.Name AS `VerifierName`, e2.Name AS `ApproverName`, `m`.`Active` FROM `Mapping` AS `m` LEFT JOIN `Employees` AS `e` ON `m`.`EmpId` = `e`.`EmpId` LEFT JOIN `Employees` AS `e1` ON `m`.`Verifier` = `e1`.`EmpId` LEFT JOIN `Employees` AS `e2` ON `m`.`Verifier` = `e2`.`EmpId` left join Location l on m.LocationId=l.LocationId LEFT JOIN `Menu` AS `mu` ON `m`.`MenuId` = `mu`.`MenuId` where m.EmpId in ('$empIds') ORDER BY `m`.`MappingId`  DESC";

	$query=mysqli_query($conn,$sql);

	$mappingArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$mappingId = $row["MappingId"];
		$empId = $row["EmpId"];
		$empName = $row["EmpName"];
		$menuId = $row["MenuId"];
		$menuName = $row["Cat"];
		$locId = $row["LocationId"];
		$locName = $row["LocName"];
		$verifier = $row["Verifier"];
		$verifierName = $row["VerifierName"];
		$approver = $row["Approver"];
		$approverName = $row["ApproverName"];
		$active = $row["Active"];
		
		$json = array(
			'mappingId' => $mappingId,
			'empId' => $empId,
			'empName' => $empName,
			'MenuId' => $menuId,
			'menuName' => $menuName,
			'locId' => $locId,
			'locName' => $locName,
			'verifier' => $verifier,
			'verifierName' => $verifierName,
			'approver' => $approver,
			'approverName' => $approverName,
			'active' => $active

		);
		array_push($mappingArr,$json);
	}
	$output = array('mappingList' => $mappingArr);
	echo json_encode($output);
}
else if($selectType == "checkpoint"){
	$sql = "SELECT * FROM `Checkpoints` where `Tenent_Id`=$tenentId order by `CheckpointId` desc ";
	$query=mysqli_query($conn,$sql);
	//echo $sql;
	$checkpointArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$checkpointId = $row["CheckpointId"];
		$description = $row["Description"];
		$value = $row["Value"];
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
	$output = array('inputTypeList' => $inputTypeArr);
	echo json_encode($output);
}
else if($selectType == "checklist"){
	// require 'AppDefaultColorClass.php';
	// $classObj = new AppDefaultColorClass();
	// $appColors = $classObj->getAppDefaultColor();

	$checklistSql = "SELECT m.*, r.Role as VerifierRole, r1.Role as ApproverRole FROM Menu m left join Role r  on m.Verifier_RoleId=r.RoleId left join Role r1 on m.Approver_RoleId=r1.RoleId order by m.MenuId desc";
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
		$expIcons = explode(",", $icons);
		$colors = $checklistRow["Colors"];
		// if($colors == ""){
		// 	$colors = $appColors;
		// }
		$colorsExp = explode(":", $colors);
		$catBgFontColor = $colorsExp[0];
		$subCatBgFontColor = $colorsExp[1];
		$capBgFontColor = $colorsExp[2];

		$active = $checklistRow["Active"];

		$json = array(
			'menuId' => $menuId,
			'category' => $category,
			'catBgFontColor' => $catBgFontColor,
			'categoryIcon' => $expIcons[0],
			'subcategory' => $subcategory,
			'subCategoryIcon' => $expIcons[1] == null ? "" : $expIcons[1],
			'subCatBgFontColor' => $subCatBgFontColor,
			'caption' => $caption,
			'captionIcon' => $expIcons[2] == null ? "" : $expIcons[2],
			'capBgFontColor' => $capBgFontColor,
			'checkpoint' => $checkpoint,
			'verifier' => $verifier,
			'verifierRoleId' => $checklistRow["Verifier_RoleId"],
			'verifierRole' => $checklistRow["VerifierRole"],
			'approver' => $approver,
			'approverRoleId' => $checklistRow["Approver_RoleId"],
			'approverRole' => $checklistRow["ApproverRole"],
			'geoFence' => $geoFence,
			// 'icons' => $icons,
			'active' => $active
		);
		array_push($checklistArr,$json);
	}
	$output = array('checklist' => $checklistArr);
	echo json_encode($output);
}

else if($selectType == "headerMenu"){
	$headerMenuSql = "SELECT * FROM `Header_Menu` where `Is_Active` = 1 order by `Display_Order` ";
	$headerMenuQuery=mysqli_query($conn,$headerMenuSql);
	$headerMenuArr = array();
	while($headerMenuRow = mysqli_fetch_assoc($headerMenuQuery)){
		$id = $headerMenuRow["PortalMenuId"];
		$menuName = $headerMenuRow["Name"];
		$routerLink = $headerMenuRow["Router_Link"];

		$json = array(
			'menuId' => $id,
			'menuName' => $menuName,
			'routerLink' => $routerLink
		);
		array_push($headerMenuArr,$json);
	}
	$output = array('headerMenuList' => $headerMenuArr);
	echo json_encode($output);
}
else if($selectType == "portalMenu"){
	$filterSql = "";
	if($loginEmpRoleId == "4"){

	}
	else{
		$roleSql = "SELECT `PortalMenuId` FROM `Role` where `RoleId`='$loginEmpRoleId' and `Tenent_Id`=$tenentId";
		$roleQuery=mysqli_query($conn,$roleSql);
		$roleRow = mysqli_fetch_assoc($roleQuery);
		$portalMenuId = $roleRow["PortalMenuId"];
		if($portalMenuId != null && $portalMenuId != "")
		$filterSql .= "and `PortalMenuId` in ($portalMenuId)";

	}
	$headerMenuSql = "SELECT * FROM `Header_Menu` where 1=1 $filterSql and `Is_Active` = 1 order by `Display_Order` ";
	$headerMenuQuery=mysqli_query($conn,$headerMenuSql);
	$portalMenuArr = array();
	while($headerMenuRow = mysqli_fetch_assoc($headerMenuQuery)){
		$id = $headerMenuRow["PortalMenuId"];
		$menuName = $headerMenuRow["Name"];
		$routerLink = $headerMenuRow["Router_Link"];

		$json = array(
			'portalMenuId' => $id,
			'menuName' => $menuName,
			'routerLink' => $routerLink
		);
		array_push($portalMenuArr,$json);
	}
	$output = array('portalMenuList' => $portalMenuArr);
	echo json_encode($output);
}
else if($selectType == "role"){
	$roleSql = "SELECT * FROM `Role` where `Tenent_Id`=$tenentId order by `RoleId` desc ";
	$roleQuery=mysqli_query($conn,$roleSql);
	$roleArr = array();
	while($roleRow = mysqli_fetch_assoc($roleQuery)){
		$roleId = $roleRow["RoleId"];
		$roleName = $roleRow["Role"];
		$menuId = $roleRow["MenuId"];

		$checklistSql = "SELECT * FROM Menu where MenuId in ($menuId) ORDER BY FIELD(MenuId,$menuId)";
		$checklistQuery=mysqli_query($conn,$checklistSql);
		$checklistArr = array();
		while($checklistRow = mysqli_fetch_assoc($checklistQuery)){
			$cat = $checklistRow["Cat"];
			$sub = $checklistRow["Sub"];
			$cap = $checklistRow["Caption"];
			$menuName = $cat;
			if($sub !='')
				$menuName .= ' -- '.$sub;
			if($cap != "")
				$menuName .= ' -- '.$cap;

			array_push($checklistArr,$menuName);
		}

		$json = array(
			'roleId' => $roleId,
			'roleName' => $roleName,
			'menuId' => $menuId,
			'menuList' => $checklistArr
		);
		array_push($roleArr,$json);
	}
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
	$output = array('captionList' => $capArr);
	echo json_encode($output);
}
else if($selectType == "myEmployee"){
	$filterSql = "";
	if($loginEmpRoleId == 4){

	}
	else{
		$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
	}
	$sql = "SELECT `EmpId` as `empId`, `Name` as `empName` FROM `Employees` where 1=1 $filterSql and `Tenent_Id` = $tenentId and `Active` = 1";
	$query=mysqli_query($conn,$sql);
	$empList=array();
	while($row = mysqli_fetch_assoc($query)){
		$empId = $row["empId"];

		$dateList = array();
		$dateSql = "SELECT distinct date(`MobileDateTime`) as `DataDate` FROM `Activity` where `EmpId`='$empId' ";
		$dateQuery=mysqli_query($conn,$dateSql);
		while($dateRow = mysqli_fetch_assoc($dateQuery)){
			array_push($dateList,$dateRow["DataDate"]);
		}

		$row["dateList"] = $dateList;
		array_push($empList,$row);
	}
	echo json_encode($empList);
}
else if($selectType == "attendance"){
	$filterSql = "";
	if($loginEmpRoleId == "4"){
		
	}
	else{
		// Self and RM
		
		$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
	}

	$underEmpList = array();
	$sql = "SELECT `EmpId` FROM `Employees` WHERE 1=1 $filterSql and `Tenent_Id`='$tenentId' and `Active`=1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($underEmpList, $row["EmpId"]);
	}

	$empIds = implode("','", $underEmpList);

	$attendanceList = array();
	$filterSql = "";
	$filterStartDate = $jsonData->filterStartDate;
	$filterEndDate = $jsonData->filterEndDate;

	if($filterStartDate != ""){
		$filterSql .= " and a.AttendanceDate >= '$filterStartDate' ";
	}
	if($filterEndDate != ""){
		$filterSql .= " and a.AttendanceDate <= '$filterEndDate' ";
	}

	// $attSql = "SELECT * FROM `Attendance` where `EmpId` in ('".$empIds."') ".$filterSql." ORDER by `AttendanceDate` desc";
	$attSql = "SELECT a.*, round(sum(d.Distance_KM),2) AS Distance FROM Attendance AS a LEFT JOIN DistanceTravel AS d ON a.EmpId=d.Emp_Id AND a.AttendanceDate=d.Visit_Date where a.EmpId in ('$empIds') $filterSql GROUP BY a.EmpId, a.AttendanceDate ORDER by a.AttendanceDate desc";
	// echo $attSql;
	$attQuery=mysqli_query($conn,$attSql);
	$srNo=0;
	while($attRow = mysqli_fetch_assoc($attQuery)){
		$inDateTime = $attRow["InDateTime"] == null ? '' : $attRow["InDateTime"];
		$outDateTime = $attRow["OutDateTime"] == null ? '' : $attRow["OutDateTime"];
		$workingHours = $attRow["WorkingHours"] == null ? '' : $attRow["WorkingHours"];
		$inLatlong = $attRow["InLatlong"] == null ? '' : $attRow["InLatlong"];
		$outLatlong = $attRow["OutLatlong"] == null ? '' : $attRow["OutLatlong"];
		$srNo++;
		$attJson = array(
			'srNo' => $srNo,
			'empId' => $attRow["EmpId"], 
			'name' => $attRow["Name"], 
			'attendanceDate' => $attRow["AttendanceDate"], 
			'inDateTime' => $inDateTime, 
			'outDateTime' => $outDateTime, 
			'workingHours' => $workingHours, 
			'inLatlong' => $inLatlong, 
			'outLatlong' => $outLatlong,
			'distance' => $attRow["Distance"]
		);
		array_push($attendanceList, $attJson);
	}
	$output = array('attendanceList' => $attendanceList);
	echo json_encode($output);
}
else if($selectType == "ticket"){
	$filterSql = "";
	if($loginEmpRoleId == "4"){
		
	}
	else{
		// Self and RM
		
		$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
	}

	$underEmpList = array();
	$sql = "SELECT `EmpId` FROM `Employees` WHERE 1=1 $filterSql and `Tenent_Id`='$tenentId' and `Active`=1";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($underEmpList, $row["EmpId"]);
	}

	$empIds = implode("','", $underEmpList);

	$sql = "SELECT m.MappingId as mappingId, e.Name as name, e1.Name as verifierName, e2.Name as approverName, m.Start as startDate, m.End as endDate, m.ActivityId, h.VerifierActivityId, h.ApproverActivityId FROM Mapping m join Employees e on m.EmpId=e.EmpId join Employees e1 on m.Verifier=e1.EmpId join Employees e2 on m.Approver=e2.EmpId left join TransactionHDR h on m.ActivityId=h.ActivityId where 1=1 and m.EmpId in ('$empIds') and TktNumber is not null";
	$query=mysqli_query($conn,$sql);
	$ticketList = array();
	while($row = mysqli_fetch_assoc($query)){
		$actId = $row["ActivityId"];
		$veriActId = $row["VerifierActivityId"];
		$appActId = $row["ApproverActivityId"];

		$status = $actId == 0 ? "Pending" : "Done";
		$veriStatus = $veriActId == null ? "Pending" : "Done";
		$appStatus = $appActId == null ? "Pending" : "Done";
		$row["status"] = $status;
		$row["verifyStatus"] = $veriStatus;
		$row["approveStatus"] = $appStatus;
		unset($row["ActivityId"]);
		unset($row["VerifierActivityId"]);
		unset($row["ApproverActivityId"]);
		array_push($ticketList, $row);
	}
	$output = array('ticketList' => $ticketList);
	echo json_encode($output);
}
else if($selectType == "EmpLocMapping"){
	$a = "";
	if($loginEmpRoleId != 4){
		$a = "and empLoc.Emp_Id = '$loginEmpId' ";
	}
	$sql = "SELECT empLoc.Id, loc.State, loc.City, loc.Area, loc.Name as locName, loc.GeoCoordinates, empLoc.Emp_Id, emp.Name as empName FROM EmployeeLocationMapping empLoc join Location loc on empLoc.LocationId = loc.LocationId left join Employees emp on empLoc.Emp_Id = emp.EmpId where 1=1 ".$a." and empLoc.Tenent_Id = $tenentId";
	$query=mysqli_query($conn,$sql);
	$empLocMappingArr = array();
	while($row = mysqli_fetch_assoc($query)){
		
		$json = array(
			'id' => $row["Id"],
			'locName' => $row["locName"],
			'empId' => $row["Emp_Id"],
			'empName' => $row["empName"]
		);
		array_push($empLocMappingArr,$json);
	}
	$output = array('empLocMappingList' => $empLocMappingArr);
	echo json_encode($output);
}
else{
	$output = array('message' => 'Invalid selectType');
	echo json_encode($output);
}



?>