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

$empId = $jsonData->empId;
$visitDate = $jsonData->visitDate;
$visitList = $jsonData->visitList;

$delSql = "DELETE FROM `DistanceTravel` where `Emp_Id`='$empId' and `Visit_Date`='$visitDate'";
mysqli_query($conn,$delSql);

$totalDistance=0;
$successArr = array();
$errorArr = array();
for($i=0;$i<count($visitList);$i++){
	$visitObj = $visitList[$i];
	$activityId = $visitObj->activityId;
	$loopEmpId = $visitObj->empId;
	$dateTime = $visitObj->dateTime;
	$latlong = $visitObj->latlong;
	$event = $visitObj->event;

	$geoLocation = str_replace("/", ",", $latlong);
	$latitude= explode(",", $geoLocation)[0] ;
	$longitude= explode(",", $geoLocation)[1];

	if($i == 0){
		$distanceSql = "INSERT into `DistanceTravel` (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`) values ($activityId, '$loopEmpId', '$visitDate', '$dateTime', '$latitude', '$longitude', '$latitude', '$longitude', 0, '$event')";
		mysqli_query($conn,$distanceSql);
	}
	else{
		$distSql = "SELECT lat_lng_distance($origin_lat,$origin_long,$latitude,$longitude) as latLngDist";
		$distQuery = mysqli_query($conn, $distSql);
		$distRow = mysqli_fetch_assoc($distQuery);
		$distance = $distRow["latLngDist"];

		$distanceSql = "INSERT into `DistanceTravel` (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`) values ($activityId, '$loopEmpId', '$visitDate', '$dateTime', '$origin_lat', '$origin_long', '$latitude', '$longitude', $distance, '$event')";
		mysqli_query($conn,$distanceSql);

		$totalDistance += $distance;
	}


	$origin_lat=$latitude;
	$origin_long=$longitude;
}


$output = array(
	'date' => $visitDate, 
	'successArr' => $successArr, 
	'errorArr' => $errorArr,
	'totalDistance' => round($totalDistance,2)
);
echo json_encode($output);
?>
