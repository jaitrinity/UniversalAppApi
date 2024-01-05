<?php 
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

$sql= "SELECT * from `Role` where `RoleId` = $roleId";
$result = mysqli_query($conn,$sql);
$samplingList = array();
if(mysqli_num_rows($result) > 0){
	$row = mysqli_fetch_Array($result);
	$menuId = $row['MenuId'];

	$sampSql = "SELECT * FROM `Sampling` where `MenuId` in ($menuId) and `RoleId` = $roleId";
	$sampResult = mysqli_query($conn,$sampSql);
	while($sampRow = mysqli_fetch_assoc($sampResult)){
		$samType = $sampRow["SamplingType"];
		$demographicSampling = "";
		$checkpointSampling = "";
		if($samType == 1){
			$demographicSampling = $sampRow["Sampling"];
		}
		else if($samType == 2){
			$checkpointSampling = $sampRow["Sampling"];
		}
		$json = array('menuId' => $sampRow["MenuId"], 
			'demographicSampling' => $demographicSampling, 
			'checkpointSampling' => $checkpointSampling, 
			'targetDone' => $sampRow["TargetDone"]);
		array_push($samplingList, $json);
	}
}

echo json_encode($samplingList);

?>