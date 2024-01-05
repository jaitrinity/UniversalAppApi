<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

$menuIdArr = [];
if($loginEmpRoleId != '4'){
	$sql = "SELECT DISTINCT `MenuId` FROM `Mapping` WHERE `EmpId` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1 ";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$menuId = $row["MenuId"];
		if(!in_array($menuId, $menuIdArr)){
			array_push($menuIdArr,$menuId);
		}
	}

	$sql1 = "SELECT DISTINCT `MenuId` FROM `Mapping` WHERE `Verifier` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1 ";
	$query1=mysqli_query($conn,$sql1);
	while($row1 = mysqli_fetch_assoc($query1)){
		$menuId1 = $row1["MenuId"];
		if(!in_array($menuId1, $menuIdArr)){
			array_push($menuIdArr,$menuId1);
		}
	}

	$sql2 = "SELECT DISTINCT `MenuId` FROM `Mapping` WHERE `Approver` = '$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1 ";
	$query2=mysqli_query($conn,$sql2);
	while($row2 = mysqli_fetch_assoc($query2)){
		$menuId2 = $row2["MenuId"];
		if(!in_array($menuId2, $menuIdArr)){
			array_push($menuIdArr,$menuId2);
		}
	}

	$sql4 = "SELECT DISTINCT `MenuId` from `Role` where `RoleId` = $loginEmpRoleId and `Tenent_Id` = $tenentId ";
	$query4=mysqli_query($conn,$sql4);
	while($row4 = mysqli_fetch_assoc($query4)){
		$menuId4 = $row4["MenuId"];
		$expMid = explode(",", $menuId4);
		for($ii = 0;$ii<count($expMid); $ii++){
			if(!in_array($expMid[$ii], $menuIdArr)){
				array_push($menuIdArr,$expMid[$ii]);
			}
		}
		
	}
}
else{
	$sql = "SELECT `MenuId`,`Cat`,`Sub`,`Caption`,`CheckpointId`,`Icons` FROM `Menu` where `Tenent_Id` = $tenentId";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		array_push($menuIdArr,$row["MenuId"]);
	}
}

sort($menuIdArr);
$output = array();
$wrappedList = [];
if(count($menuIdArr) == 0){
	$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'No record found', 'responseCode' => '102001');
}
else {
	$menuList = [];
	$distMenuId = [];
	for($i = 0; $i < count($menuIdArr); $i++){
		$loopMenuId = $menuIdArr[$i];
		$sql3 = "SELECT `MenuId`,`Cat`,`Sub`,`Caption`,`CheckpointId`,`Icons` FROM `Menu` WHERE `MenuId` = $loopMenuId and `Tenent_Id`= $tenentId 
		ORDER BY `MenuId` ASC ";
		$query3=mysqli_query($conn,$sql3);
		while($row3 = mysqli_fetch_assoc($query3)){
			$menuId = $row3["MenuId"];
			$catName = $row3["Cat"];
			$subName = $row3["Sub"];
			$captionName = $row3["Caption"];
			$checkpointId = $row3["CheckpointId"];
			$icons = $row3["Icons"];
			
			if(!in_array($catName, $distMenuId)){
				$json = array('menuId' => $menuId, 
					'menuName' => $catName, 
					'routerLink' => "menu-submenu/".$menuId,
					'checkpointId' => $checkpointId,
					'icon' => explode(",", $icons)[0]
				);
				// $json = new StdClass;
				// $json -> menuId = $menuId;
				// $json -> menuName = $catName;
				// $json -> routerLink = "menu-submenu/".$menuId;
				// $json -> checkpointId = $checkpointId;
				// $json -> icon = explode(",", $icons)[0];
				array_push($menuList,$json);
				
				array_push($distMenuId,$catName);

			}
		}
		
		$output = array('wrappedList' => $menuList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000');
	}
}

echo json_encode($output);
?>