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

if($selectType == "location"){
	$stateList = array();
	$sql = "SELECT `State` as state, `City` as city, `Area` as area FROM `StateCityAreaMaster`";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($stateList, $row);
	}

	$sql = "SELECT e.EmpId as empId, e.Name as empName from Employees e where 1=1 and e.Tenent_Id=$tenentId";
	$query=mysqli_query($conn,$sql);
	$empArr = array();
	while($row = mysqli_fetch_assoc($query)){
		array_push($empArr,$row);
	}

	$output = array('stateList' => $stateList, 'employeeList' => $empArr);
}
else if($selectType == "employee"){
	$stateList = array();
	$sql = "SELECT `State` as state, `City` as city, `Area` as area FROM `StateCityAreaMaster`";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($stateList, $row);
	}

	$roleList = array();
	$sql = "SELECT `RoleId` as roleId, `Role` as roleName FROM `Role` where `Tenent_Id`=$tenentId";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($roleList, $row);
	}

	$rmList = array();
	$sql = "SELECT `EmpId` as empId, `Name` as empName FROM `Employees` where `Tenent_Id`=$tenentId";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($rmList, $row);
	}
	$output = array('stateList' => $stateList, 'roleList' => $roleList, 'rmList' => $rmList);
}
else if($selectType == "checkpoint"){
	$inputTypeSql = "SELECT `TypeId`,`Type` FROM `Type` ";
	$inputTypeQuery=mysqli_query($conn,$inputTypeSql);
	$inputTypeArr = array();
	while($inputTypeRow = mysqli_fetch_assoc($inputTypeQuery)){
		$typeId = $inputTypeRow["TypeId"];
		$typeName = $inputTypeRow["Type"];
		$json3 = array(
			'typeId' => $typeId,
			'name' => $typeName,
		);
		array_push($inputTypeArr,$json3);
	}
	$langSql = "SELECT `LanguageId`,`Name` FROM `Language` ";
	$langQuery=mysqli_query($conn,$langSql);
	$langArr = array();
	while($inputTypeRow = mysqli_fetch_assoc($langQuery)){
		$langId = $inputTypeRow["LanguageId"];
		$langName = $inputTypeRow["Name"];
		$json4 = array(
			'languageId' => $langId,
			'name' => $langName,
		);
		array_push($langArr,$json4);
	}
	$checkpointSql = "SELECT `CheckpointId`,`Description` FROM `Checkpoints` where `Tenent_Id`=$tenentId order by `CheckpointId` desc ";
	$checkpointQuery=mysqli_query($conn,$checkpointSql);
	$checkpointArr = array();
	while($checkpointRow = mysqli_fetch_assoc($checkpointQuery)){
		$checkpointId = $checkpointRow["CheckpointId"];
		$checkpointName = $checkpointRow["Description"];
		$json7 = array(
			'checkpointId' => $checkpointId,
			'name' => $checkpointName,
		);
		array_push($checkpointArr,$json7);
	}
	$output = array( 'inputTypeList' => $inputTypeArr, 'languageList' => $langArr, 'checkpointList' => $checkpointArr);
}
else if($selectType == "role"){
	$sql = "SELECT `MenuId`,`Cat`,`Sub`,`Caption` FROM `Menu` where `Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$menuArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$menuId = $row["MenuId"];
		$cat = $row["Cat"];
		$sub = $row["Sub"];
		$caption = $row["Caption"];
		$menuName = $cat;
		if($sub != null && $sub != '')
			$menuName .= " - ".$sub;
		if($caption != null && $caption != '')
			$menuName .= " - ".$caption;

		$json = array(
			'menuId' => $menuId,
			'menuName' => $menuName
		);
		array_push($menuArr,$json);
	}
	$output = array('menuList' => $menuArr);
}
else if($selectType == "checklist"){
	$sql = "SELECT `RoleId`,`Role` FROM `Role` where `Role` != 'Admin' and `Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$roleArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$roleId = $row["RoleId"];
		$roleName = $row["Role"];
		$json = array(
			'roleId' => $roleId,
			'roleName' => $roleName,
		);
		array_push($roleArr,$json);
	}

	$sql = "SELECT `LocationId`,`Name` FROM `Location` where `Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$locationArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$locationId = $row["LocationId"];
		$locationName = $row["Name"];
		$json = array(
			'locationId' => $locationId,
			'locationName' => $locationName,
		);
		array_push($locationArr,$json);
	}

	$sql = "SELECT `CheckpointId`,`Description` FROM `Checkpoints` where `Tenent_Id` = $tenentId order by `CheckpointId` desc ";
	$query=mysqli_query($conn,$sql);
	$checkpointArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$checkpointId = $row["CheckpointId"];
		$checkpointName = $row["Description"];
		$json = array(
			'checkpointId' => $checkpointId,
			'checkpointName' => $checkpointName,
		);
		array_push($checkpointArr,$json);
	}
	$output = array(
		'verfierList' => $roleArr, 
		'approverList' => $roleArr, 
		'checkpointList' => $checkpointArr,
		'locationList' => $locationArr
	);
}
else if($selectType == "nLevelFlow"){
	$sql = "SELECT `MenuId`,`Cat`,`Sub`,`Caption` FROM `Menu` where `Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$menuArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$menuId = $row["MenuId"];
		$cat = $row["Cat"];
		$sub = $row["Sub"];
		$caption = $row["Caption"];
		$menuName = $cat;
		if($sub != null && $sub != '')
			$menuName .= " - ".$sub;
		if($caption != null && $caption != '')
			$menuName .= " - ".$caption;

		$json = array(
			'menuId' => $menuId,
			'menuName' => $menuName
		);
		array_push($menuArr,$json);
	}

	$sql = "SELECT `RoleId`,`Role` FROM `Role` where `Role` != 'Admin' and `Tenent_Id` = $tenentId ";
	$query=mysqli_query($conn,$sql);
	$roleArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$roleId = $row["RoleId"];
		$roleName = $row["Role"];
		$json = array(
			'roleId' => $roleId,
			'roleName' => $roleName,
		);
		array_push($roleArr,$json);
	}

	$sql = "SELECT `CheckpointId`,`Description` FROM `Checkpoints` where `Tenent_Id` = $tenentId order by `CheckpointId` desc ";
	$query=mysqli_query($conn,$sql);
	$checkpointArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$checkpointId = $row["CheckpointId"];
		$checkpointName = $row["Description"];
		$json = array(
			'checkpointId' => $checkpointId,
			'checkpointName' => $checkpointName,
		);
		array_push($checkpointArr,$json);
	}

	$output = array(
		'menuList' => $menuArr, 
		'roleList' => $roleArr, 
		'checkpointList' => $checkpointArr
	);
}
else{
	$output = array('message' => 'Invalid selectType');
}

echo json_encode($output);


?>