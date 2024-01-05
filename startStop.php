<?php
include("dbConfiguration.php");

$sql="SELECT DISTINCT date(`MobileDateTime`) as `AttDate` FROM `Activity` WHERE `Event` in ('Start') ORDER by `ActivityId`";
// $sql="SELECT DISTINCT date(`MobileDateTime`) as `AttDate` FROM `Activity` WHERE date(`MobileDateTime`)='2023-04-17' and `Event` in ('Start') ORDER by `ActivityId`";
$result=mysqli_query($conn,$sql);
$outputList = array();
while($row = mysqli_fetch_assoc($result)){
	$attDate = $row["AttDate"];

	$sql1 = "SELECT distinct `EmpId` FROM `Activity` where `Event`='Start' and date(`MobileDateTime`) = '$attDate' ";
	// echo $sql1;
	$result1=mysqli_query($conn,$sql1);
	$rowCount=mysqli_num_rows($result1);
	if($rowCount != 0){
		while($row1 = mysqli_fetch_assoc($result1)){
			$empId = $row1["EmpId"];
			$currentSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and date(`MobileDateTime`) = '$attDate' and `Event` in ('Start','Stop') ORDER by `ActivityId` DESC LIMIT 0,1";

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
}
echo json_encode($outputList);

// file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/AttLog_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($outputList)."\n", FILE_APPEND);
?>
