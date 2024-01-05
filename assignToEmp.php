<?php 
include("dbConfiguration.php");
$sql = "SELECT `EmpId`,`Name` FROM `Employees` WHERE `Active`= '1' ";
$query=mysqli_query($conn,$sql);
$empArr = array();
while($row = mysqli_fetch_assoc($query)){
	$empId = $row["EmpId"];
	$empName = $row["Name"];
	$json = array(
		'paramCode' => $empId,
		'paramDesc' => $empName,
	);
	array_push($empArr,$json);
}

$menuSql = "SELECT `MenuId`,`Cat`,`Sub`,`Caption` FROM `Menu` WHERE `Active`= '1' ";
$menuQuery=mysqli_query($conn,$menuSql);
$menuArr = array();
while($menuRow = mysqli_fetch_assoc($menuQuery)){
	$menuId = $menuRow["MenuId"];
	$cat = $menuRow["Cat"];
	$sub = $menuRow["Sub"];
	$caption = $menuRow["Caption"];

	// $json1 = array(
	// 	'paramCode' => $menuId,
	// 	'paramDesc' => $cat.",".$sub.",".$caption,
	// );
	// array_push($menuArr,$json1);

	if($caption != null && $caption != ""){
		$json1 = array(
			'paramCode' => $menuId,
			'paramDesc' => $caption,
		);
		array_push($menuArr,$json1);
	}
	else if($sub != null && $sub != ""){
		$json1 = array(
			'paramCode' => $menuId,
			'paramDesc' => $sub,
		);
		array_push($menuArr,$json1);

	}
	else if($cat != null && $cat != ""){
		$json1 = array(
			'paramCode' => $menuId,
			'paramDesc' => $cat,
		);
		array_push($menuArr,$json1);

	}

	
}

$locationSql = "SELECT `LocationId`,`Name` FROM `Location` ";
$locationQuery=mysqli_query($conn,$locationSql);
$locationArr = array();
while($locationRow = mysqli_fetch_assoc($locationQuery)){
	$locationId = $locationRow["LocationId"];
	$locationName = $locationRow["Name"];
	$json2 = array(
		'paramCode' => $locationId,
		'paramDesc' => $locationName,
	);
	array_push($locationArr,$json2);
}
$inputTypeSql = "SELECT `TypeId`,`Type` FROM `Type` ";
$inputTypeQuery=mysqli_query($conn,$inputTypeSql);
$inputTypeArr = array();
while($inputTypeRow = mysqli_fetch_assoc($inputTypeQuery)){
	$typeId = $inputTypeRow["TypeId"];
	$typeName = $inputTypeRow["Type"];
	$json3 = array(
		'paramCode' => $typeId,
		'paramDesc' => $typeName,
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
		'paramCode' => $langId,
		'paramDesc' => $langName,
	);
	array_push($langArr,$json4);
}

$roleSql = "SELECT `RoleId`,`Role` FROM `Role` ";
$roleQuery=mysqli_query($conn,$roleSql);
$roleArr = array();
while($roleRow = mysqli_fetch_assoc($roleQuery)){
	$roleId = $roleRow["RoleId"];
	$roleName = $roleRow["Role"];
	$json5 = array(
		'paramCode' => $roleId,
		'paramDesc' => $roleName,
	);
	array_push($roleArr,$json5);
}

$rmSql = "SELECT `EmpId`,`Name` FROM `Employees` where `Active` = 1 ";
$rmQuery=mysqli_query($conn,$rmSql);
$rmArr = array();
while($rmRow = mysqli_fetch_assoc($rmQuery)){
	$rmId = $rmRow["EmpId"];
	$rmName = $rmRow["Name"];
	$json6 = array(
		'paramCode' => $rmId,
		'paramDesc' => $rmName,
	);
	array_push($rmArr,$json6);
}

$checkpointSql = "SELECT `CheckpointId`,`Description` FROM `Checkpoints` ";
$checkpointQuery=mysqli_query($conn,$checkpointSql);
$checkpointArr = array();
while($checkpointRow = mysqli_fetch_assoc($checkpointQuery)){
	$checkpointId = $checkpointRow["CheckpointId"];
	$checkpointName = $checkpointRow["Description"];
	$json7 = array(
		'paramCode' => $checkpointId,
		'paramDesc' => $checkpointName,
	);
	array_push($checkpointArr,$json7);
}

$output = array();
$output = array('empList' => $empArr,'menuList' => $menuArr,'locationList' => $locationArr, 'inputTypeList' => $inputTypeArr, 'languageList' => $langArr, 
'roleList' => $roleArr, 'rmIdList' => $rmArr, 'checkpointList' => $checkpointArr);
echo json_encode($output);

?>