<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}

$requestJson = file_get_contents('php://input');
$jsonData=json_decode($requestJson);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$graphType = $jsonData->graphType;
$tenentId = $jsonData->tenentId;
if($graphType == 1){
	$sql = "SELECT s.MenuId, m.Cat, m.Sub, m.Caption FROM Sampling s join Menu m on s.MenuId = m.MenuId GROUP by s.MenuId";
	$query = mysqli_query($conn,$sql);
	$response = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$menuId = $row["MenuId"];
		$menuName = "";
		$caption = $row["Caption"];
		$sub = $row["Sub"];
		$cat = $row["Cat"];

		if($caption != null && $caption != '') $menuName = $caption;
		else if($sub != null && $sub != '') $menuName = $sub;
		else if($cat != null && $cat != '') $menuName = $cat;

		$pincodeDataArr = array();
		$pincodeLabelArr = array();
		$pincodeColorArr = array();
		$divisonDataArr = array();
		$divisonLabelArr = array();
		$divisonColorArr = array();
		$stateDataArr = array();
		$stateLabelArr = array();
		$stateColorArr = array();
		$localityDataArr = array();
		$localityLabelArr = array();
		$localityColorArr = array();
		$countryDataArr = array();
		$countryLabelArr = array();
		$countryColorArr = array();
		$checkpointDataArr = array();
		$checkpointLabelArr = array();
		$checkpointColorArr = array();

		$sql1 = "SELECT * FROM `Sampling` where `MenuId` = $menuId";
		$query1 = mysqli_query($conn,$sql1);
		while ($row1 = mysqli_fetch_assoc($query1)) {
			$sampling = $row1["Sampling"];
			$sampList = explode(":", $sampling);
			$samplingType = $row1["SamplingType"];
			$targetDone = $row1["TargetDone"];
			$targetDone = intval($targetDone);
			if($samplingType == 1){
				$geo = $sampList[0];
				$value = $sampList[1];
				$target = $sampList[2];
				$color = getColorHexCode();
				if($geo == 0){
					array_push($pincodeDataArr, $targetDone);
					array_push($pincodeLabelArr, $value.' - '.$targetDone.'/'.$target);
					array_push($pincodeColorArr, $color);
				}
				else if($geo == 1){
					array_push($divisonDataArr, $targetDone);
					array_push($divisonLabelArr, $value.' - '.$targetDone.'/'.$target);
					array_push($divisonColorArr, $color);
				}
				else if($geo == 2){
					array_push($stateDataArr, $targetDone);
					array_push($stateLabelArr, $value.' - '.$targetDone.'/'.$target);
					array_push($stateColorArr, $color);
				}
				else if($geo == 3){
					array_push($localityDataArr, $targetDone);
					array_push($localityLabelArr, $value.' - '.$targetDone.'/'.$target);
					array_push($localityColorArr, $color);
				}
				else if($geo == 4){
					array_push($countryDataArr, $targetDone);
					array_push($countryLabelArr, $value.' - '.$targetDone.'/'.$target);
					array_push($countryColorArr, $color);
				}
			}
			else{
				$geo = $sampList[0];
				$value = $sampList[1];
				$target = $sampList[2];
				$color = getColorHexCode();
				
				array_push($checkpointDataArr, $targetDone);
				array_push($checkpointLabelArr, $value.' - '.$targetDone.'/'.$target);
				array_push($checkpointColorArr, $color);
			}
		}

		$pincodeSampling = array(
			'divId' => 'pincode_'.$menuId, 
			'series' => $pincodeDataArr, 'labels' => $pincodeLabelArr, 'colors' => $pincodeColorArr
		);
		$divisonSampling = array(
			'divId' => 'divison_'.$menuId, 
			'series' => $divisonDataArr, 'labels' => $divisonLabelArr, 'colors' => $divisonColorArr
		);
		$stateSampling = array(
			'divId' => 'state_'.$menuId, 
			'series' => $stateDataArr, 'labels' => $stateLabelArr, 'colors' => $stateColorArr
		);
		$localitySampling = array(
			'divId' => 'locality_'.$menuId, 
			'series' => $localityDataArr, 'labels' => $localityLabelArr, 'colors' => $localityColorArr
		);
		$countrySampling = array(
			'divId' => 'country_'.$menuId, 
			'series' => $countryDataArr, 'labels' => $countryLabelArr, 'colors' => $countryColorArr
		);
		$checkpointSampling = array(
			'divId' => 'checkpoint_'.$menuId, 
			'series' => $checkpointDataArr, 'labels' => $checkpointLabelArr, 
			'colors' => $checkpointColorArr
		);

		$resultJson = array(
			'menuId' => $menuId,
			'menuName' => $menuName,
			'isPincodeGraph' => count($pincodeDataArr) == 0 ? false : true,
			'pincodeSampling' => $pincodeSampling, 
			'isDivisonGraph' => count($divisonDataArr) == 0 ? false : true,
			'divisonSampling' => $divisonSampling,
			'isStateGraph' => count($stateDataArr) == 0 ? false : true,
			'stateSampling' => $stateSampling,
			'isLocalityGraph' => count($localityDataArr) == 0 ? false : true,
			'localitySampling' => $localitySampling,
			'isCountryGraph' => count($countryDataArr) == 0 ? false : true,
			'countrySampling' => $countrySampling,
			'isCheckpointGraph' => count($checkpointDataArr) == 0 ? false : true,
			'checkpointSampling' => $checkpointSampling
		);
		array_push($response, $resultJson);
	}
	echo json_encode($response);
}
else if($graphType == 2){
	$filterSql = "";
	if($loginEmpRoleId != 4){
		$empList = [];
		$empSql = "SELECT * FROM `Employees` WHERE (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId') and `Tenent_Id` = $tenentId and `Active` = 1";
		$empQuery=mysqli_query($conn,$empSql);
		if(mysqli_num_rows($empQuery) !=0){
			while($row11 = mysqli_fetch_assoc($empQuery)){
				array_push($empList,$row11["EmpId"]);
			}
		}
		array_push($empList,$loginEmpId);

		$impEmp = implode("','", $empList);

		$filterSql .= "and a.EmpId in ('$impEmp') ";
	}
	$sql = "SELECT m.MenuId, m.Cat, m.Sub, m.Caption, count(a.ActivityId) as DoneCount FROM Menu m left join Activity a on m.MenuId = a.MenuId and a.Event = 'Submit' where 1=1 and m.Tenent_Id=$tenentId $filterSql GROUP by m.MenuId";
	$query = mysqli_query($conn,$sql);
	$dataArr = array();
	$labelArr = array();
	$colorArr = array();
	$countArr = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$menuId = $row["MenuId"];
		$menuName = "";
		$caption = $row["Caption"];
		$sub = $row["Sub"];
		$cat = $row["Cat"];
		$doneCount = $row["DoneCount"];
		$doneCount = intval($doneCount);
		$color = getColorHexCode();

		if($caption != null && $caption != '') $menuName = $caption;
		else if($sub != null && $sub != '') $menuName = $sub;
		else if($cat != null && $cat != '') $menuName = $cat;

		if($doneCount != 0){
			array_push($dataArr, $doneCount);
			array_push($labelArr, $menuName);
			array_push($colorArr, $color);

			$json = array('menuName' => $menuName, 'doneCount' => $doneCount, 'color' => $color);
			array_push($countArr, $json);
		}
	}
	$response = array('data' => $dataArr, 'label' => $labelArr, 'color' => $colorArr, 'count' => $countArr);

	echo json_encode($response);
}
else if($graphType == 3){
	$response = array();
	$sql = "SELECT e.EmpId, e.Name, r.MenuId FROM Employees e join Role r on e.RoleId = r.RoleId where e.Active = 1";
	$query = mysqli_query($conn,$sql);
	while ($row = mysqli_fetch_assoc($query)) {
		$empId = $row["EmpId"];
		$name = $row["Name"];
		$menuId = $row["MenuId"];
		// echo $empId."--";
		$dataArr = array();
		$labelArr = array();
		$colorArr = array();
		$menuIdArr = explode(",", $menuId);
		for($i=0;$i<count($menuIdArr);$i++){
			$loopMenuId = $menuIdArr[$i];
			$menuSql = "SELECT `MenuId`, `Cat`, `Sub`, `Caption` FROM `Menu` where `MenuId` = $loopMenuId";
			$menuQuery = mysqli_query($conn,$menuSql);
			$menuRow = mysqli_fetch_assoc($menuQuery);
			$menuName = $menuRow["Cat"];

			$actSql = "SELECT *  FROM `Activity` WHERE `MenuId` = $loopMenuId and `EmpId` = '$empId' and `Event` = 'Submit'";
			$actQuery = mysqli_query($conn,$actSql);
			$rowCount = mysqli_num_rows($actQuery);
			if($rowCount !=0){
				$color = getRGBAcode();
				array_push($colorArr, $color);
				array_push($dataArr, $rowCount);
				array_push($labelArr, $menuName); 
			}
				
		}

		$resultJson = array('empId' => $empId, 
			'dataArr' => $dataArr, 
			'labelArr' => $labelArr, 
			'colorArr' => $colorArr
		);
		array_push($response, $resultJson);
	}
	echo json_encode($response);
	
}
?>
<?php 
// function getRGBAcode(){
// 	$red = rand(0, 255);
// 	$green = rand(0, 255);
// 	$blue = rand(0, 255);
// 	return "rgba($red, $green, $blue, 0.2)";
// }
function getColorHexCode(){
	$characters = "0123456789ABCDEF";
	$charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 6; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return "#".$randomString;
}
?>