<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

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
$checkpointSql = "SELECT `CheckpointId`,`Description` FROM `Checkpoints` where `Tenent_Id`=$tenentId ";
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
$output = array( 'inputTypeList' => $inputTypeArr, 'languageList' => $langArr, 'checkpointList' => $checkpointArr);
echo json_encode($output);
?>