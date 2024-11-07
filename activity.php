<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");

$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/activity_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($json)."\n", FILE_APPEND);
$jsonData=json_decode($json);

$mapId = $jsonData->mappingId;
$empId = $jsonData->empId;
if($empId == null || $empId == '')
	$empId = $jsonData->Emp_id;
$meId = $jsonData->menuId;
if($meId == null || $meId == '')
	$meId = $jsonData->M_Id;
$lId = $jsonData->locationId;
$event = $jsonData->event;
$geolocation = $jsonData->geolocation;
$distance = $jsonData->distance;
$dId = $jsonData->did;
$mobiledatetime = $jsonData->mobiledatetime;
$gpsStatus = $jsonData->gpsStatus;
if($meId == "" )
{
	$meId = 0;
}

// Prevent if latlong not found -- Start --
$latlong = $geolocation;
$latlong = str_replace(",", "/", $latlong);
$latlongList = explode("/", $latlong);
$lat = $latlongList[0];
$long = $latlongList[1];
if($event != 'periodicData' && ($lat == "" || $long == "")){
	$output = array('status' => "Lat-long not found plz try again");
	echo json_encode($output);
	return;
}
// Prevent if latlong not found -- Stop --

// if ((strpos($mobiledatetime, 'AM') !== false) || (strpos($mobiledatetime, 'PM')) || (strpos($mobiledatetime, 'am') !== false) || (strpos($mobiledatetime, 'pm')))   {
// 	$date = date_create_from_format("Y-m-d h:i:s A","$mobiledatetime");
// 	$date1 = date_format($date,"Y-m-d H:i:s");
// }
// else{
// 	$date1 = $mobiledatetime;
// }

$date = date_create($mobiledatetime);
$date1 = date_format($date,"Y-m-d H:i:s");


$insertActivity = "INSERT INTO `Activity`(`DId`,`MappingId`,`EmpId`,`MenuId`,`LocationId`,`Event`,`GeoLocation`,`GpsStatus`,`Distance`,`MobileDateTime`,`ServerDateTime`) VALUES ('$dId','$mapId','$empId',$meId,'$lId','$event','$geolocation','$gpsStatus','$distance','$date1',current_timestamp)";
//echo $insertActivity;
$output = new StdClass;
if(mysqli_query($conn,$insertActivity)){
	$output -> status = "success";
	$last_id = $conn->insert_id;
	$output -> Activity_Id = $last_id;
	if($event == "Start"){
		$attDate = date_format($date,"Y-m-d");
		$firstStartSql = "SELECT date_format(`MobileDateTime`,'%H:%i') as AttTime  FROM `Activity` WHERE date(`MobileDateTime`)='$attDate' and `EmpId`='$empId' and `Event`='Start' ORDER by ActivityId  LIMIT 0,1";
		$firstStartQuery = mysqli_query($conn, $firstStartSql);
		$firstStartRowcount=mysqli_num_rows($firstStartQuery);
		if($firstStartRowcount !=0){
			$firstStartRow = mysqli_fetch_assoc($firstStartQuery);
			$output -> attendanceTime = $firstStartRow["AttTime"];
		}
		else{
			$output -> attendanceTime = date_format($date,"H:i");
		}
	}
	else{
		$output -> attendanceTime = "";
	}
	
	//echo "New record created successfully. Last inserted ID is: " . $last_id;
}
else{
	$output -> status = "something went wrong";
}
echo json_encode($output);
?>