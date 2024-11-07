<?php
include("dbConfiguration.php");

// $yesterdayDate = date('Y-m-d', strtotime('-1 day'));
$yesterdayDate = '2024-08-13';

$delSql = "DELETE FROM `DistanceTravel` where `Visit_Date` = '$yesterdayDate'";
mysqli_query($conn,$delSql);

$sql="SELECT a.ActivityId, e.EmpId, e.Name, date(a.MobileDateTime) as VisitDate, a.MobileDateTime, a.GeoLocation, a.Event FROM Activity a join Employees e on a.EmpId = e.EmpId where date(a.MobileDateTime)='$yesterdayDate' and a.Event in ('Start','periodicData','Submit','Stop') order by a.MobileDateTime";
$query=mysqli_query($conn,$sql);
$srNo=0;
$dataList= array();
while($row = mysqli_fetch_assoc($query)){
	$srNo++;
	$activityId = $row["ActivityId"];
	$empId = $row["EmpId"];
	$empName = $row["Name"];
	$visitDate = $row["VisitDate"];
	$mobileDateTime = $row["MobileDateTime"];
	$geoLocation = $row["GeoLocation"];
	$geoLocationLatlong = str_replace("/", ",", $geoLocation);
	$latitude= explode(",", $geoLocationLatlong)[0] ;
	$longitude= explode(",", $geoLocationLatlong)[1];
	$event = $row["Event"];

	$distance = 0;
	if($srNo == 1){
		$distanceSql = "INSERT into `DistanceTravel` (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`) values ($activityId, '$empId', '$visitDate', '$mobileDateTime', '$latitude', '$longitude', '$latitude', '$longitude', $distance, '$event')";
		mysqli_query($conn,$distanceSql);
	}
	else{
		$distSql = "SELECT lat_lng_distance($origin_lat,$origin_long,$latitude,$longitude) as latLngDist";
		$distQuery = mysqli_query($conn, $distSql);
		$distRow = mysqli_fetch_assoc($distQuery);
		$distance = $distRow["latLngDist"];

		$distanceSql = "INSERT into `DistanceTravel` (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`) values ($activityId, '$empId', '$visitDate', '$mobileDateTime', '$origin_lat', '$origin_long', '$latitude', '$longitude', $distance, '$event')";
		mysqli_query($conn,$distanceSql);

	}

	$dataJson = array(
		'srNo'=>$srNo,
		'activityId'=>$activityId,
		'empId' => $empId,
		'empName' => $empName,
		'dateTime'=>$mobileDateTime,
		'latlong'=>$geoLocation,
		'distance'=>$distance,
		'event'=>$event
	);
	array_push($dataList, $dataJson);

	$origin_lat=$latitude;
	$origin_long=$longitude;

}
echo json_encode($dataList);

?>