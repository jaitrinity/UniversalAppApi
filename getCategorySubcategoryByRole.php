<?php
include("dbConfiguration.php");
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpRole = $jsonData->loginEmpRole;
$categoryName = $jsonData->categoryName;

$distSubCat = [];
$sql = "SELECT `MenuId`,`Sub`,`Caption` FROM `Menu` WHERE `Cat` = '$categoryName' ORDER BY `MenuId` ASC ";
$query=mysqli_query($conn,$sql);

$output = array();
$wrappedList = [];
while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	$subName = $row["Sub"];
	$captionName = $row["Caption"];
	$level = 0;
	if($subName != ''){
		$level = 2;
	}
	if($captionName != ''){
		$level = 3;
	}
	
	if(!in_array($subName, $distSubCat)){
		$json = new StdClass;
		$json -> paramCode = $menuId;
		$json -> paramDesc = $subName;
		array_push($wrappedList,$json);
	
		array_push($distSubCat,$subName);
	}
}
$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000', 'count' => $level);
echo json_encode($output);
?>