<?php 
include("dbConfiguration.php");
// $empId = 'tr014';
// $todayDate = '2023-12-01';
// $sql = "SELECT distinct `EmpId` FROM `Activity` where `EmpId`='$empId' and `Event` in ('Start') and date(`MobileDateTime`) = '$todayDate'";

$todayDate = date('Y-m-d');
$sql = "SELECT distinct `EmpId` FROM `Activity` where `Event`='Start' and date(`MobileDateTime`) = '$todayDate' ";
$result=mysqli_query($conn,$sql);
$rowCount=mysqli_num_rows($result);
$outputList = array();
if($rowCount != 0){
	while($row = mysqli_fetch_assoc($result)){
		$empId = $row["EmpId"];

		$currentSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and date(`MobileDateTime`) = '$todayDate' and `Event` in ('Start','Stop') ORDER by `ActivityId` DESC LIMIT 0,1";

		$currentQuery=mysqli_query($conn,$currentSql);
		while($currentRow = mysqli_fetch_assoc($currentQuery)){
			$dId = $currentRow["DId"];
			$mappingId = $currentRow["MappingId"];
			$empId = $currentRow["EmpId"];
			$menuId = $currentRow["MenuId"];
			$locationId = $currentRow["LocationId"];
			$event = $currentRow["Event"];
			if($event == "Start"){
				$event = 'Stop';
				$geolocation = $currentRow["GeoLocation"];
				$distance = $currentRow["Distance"];
				$mobileDateTime = $currentRow["MobileDateTime"];

				$updateStop = "INSERT into `Activity` (`DId`, `MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `Distance`, `MobileDateTime`) Values 
				($dId, '$mappingId', '$empId', $menuId, '$locationId', '$event', '$geolocation', '$distance', '$mobileDateTime') ";
				// echo $updateStop;
				if(mysqli_query($conn,$updateStop)){
					$outJson = array('code' => 200, 'data' => $currentRow);
					array_push($outputList, $outJson);
				}
				else{
					$outJson = array('code' => 0, 'data' => $currentRow);
					array_push($outputList, $outJson);
				}
			}
		}
	}
}
else{
	$outJson = array('code' => 404, 'data' => 'No record found');
	array_push($outputList, $outJson);
}
	
echo json_encode($outputList);

file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/AttLog_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($outputList)."\n", FILE_APPEND);

?>