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
$sql="SELECT * FROM `Activity` where `EmpId`='$empId' and date(`MobileDateTime`)='$visitDate' and `Event`='Start'";
$query=mysqli_query($conn,$sql);
$rowCount=mysqli_num_rows($query);
$lastGpsOnTime="";
$lastGpsOnLatlong="";
$gpsOffTime="";
$gpsOffLatlong="";
$dataList= array();
$srNo=0;
if($rowCount != 0){
	$startSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and `Event` in ('Start') and date(`MobileDateTime`) = '$visitDate' ORDER by `ActivityId` ASC LIMIT 0,1";
	$startQuery = mysqli_query($conn,$startSql);
	$startRow = mysqli_fetch_assoc($startQuery);
	$startActId = $startRow["ActivityId"];
	$startDatetime = $startRow["MobileDateTime"];
	$startLatlong = $startRow["GeoLocation"];
	$startEvent = $startRow["Event"];
	$startGpsStatus = $startRow["GpsStatus"];
	$startJson = array(
		'srNo'=>$srNo,
		'dateTime'=>$startDatetime,
		'latlong'=>$startLatlong,
		'event'=>$startEvent,
		'gpsStatus'=>$startGpsStatus
	);
	array_push($dataList, $startJson);

	$stopSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and `Event` in ('Stop') and date(`MobileDateTime`) = '$visitDate' ORDER by `ActivityId` DESC LIMIT 0,1";
	$stopQuery = mysqli_query($conn,$stopSql);
	$stopRowCount=mysqli_num_rows($stopQuery);
	if($stopRowCount !=0){
		$stopRow = mysqli_fetch_assoc($stopQuery);
		$stopActId = $stopRow["ActivityId"];

		$periodicSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and `Event` in ('periodicData') and date(`MobileDateTime`) = '$visitDate' and `ActivityId`>=$stopActId ORDER by `ActivityId` ASC";
		// echo $periodicSql;
		$periodicQueryTemp = mysqli_query($conn,$periodicSql);
		$periodicRowCount=mysqli_num_rows($periodicQueryTemp);
		if($periodicRowCount == 0){
			$periodicSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and `Event` in ('periodicData') and date(`MobileDateTime`) = '$visitDate' and `ActivityId`>=$startActId and `ActivityId`<=$stopActId ORDER by `ActivityId` ASC";
			$periodicQuery = mysqli_query($conn,$periodicSql);
			while($periodicRow = mysqli_fetch_assoc($periodicQuery)){
				$srNo++;
				$periodicDatetime = $periodicRow["MobileDateTime"];
				$periodicLatlong = $periodicRow["GeoLocation"];
				$periodicEvent = $periodicRow["Event"];
				$periodicGpsStatus = $periodicRow["GpsStatus"];
				// if($periodicGpsStatus == "ON"){
				// 	$lastGpsOnTime=$periodicDatetime;
				// 	$lastGpsOnLatlong=$periodicLatlong;
				// }
				// else if($periodicGpsStatus == "OFF"){
				// 	$gpsOffTime=$lastGpsOnTime;
				// 	$gpsOffLatlong=$lastGpsOnLatlong;
				// }
				// $latlong = str_replace(",", "/", $periodicLatlong);
				// $latlongList = explode("/", $latlong);
				// $lat = $latlongList[0];
				// $long = $latlongList[1];
				// if($lat == "" || $long == ""){

				// }
				// else{
					$periodicJson = array(
						'srNo'=>$srNo,
						'dateTime'=>$periodicDatetime,
						'latlong'=>$periodicLatlong,
						'event'=>$periodicEvent,
						'gpsStatus'=>$periodicGpsStatus
					);
					array_push($dataList, $periodicJson);
				// }
			}
			$stopDatetime = $stopRow["MobileDateTime"];
			$stopLatlong = $stopRow["GeoLocation"];
			$stopEvent = $stopRow["Event"];
			$stopGpsStatus = $stopRow["GpsStatus"];
			$srNo++;
			$stopJson = array(
				'srNo'=>$srNo,
				'dateTime'=>$stopDatetime,
				'latlong'=>$stopLatlong,
				'event'=>$stopEvent,
				'gpsStatus'=>$startGpsStatus
			);
			array_push($dataList, $stopJson);
		}
		else{
			$periodicSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and `Event` in ('periodicData') and date(`MobileDateTime`) = '$visitDate' and `ActivityId`>=$startActId ORDER by `ActivityId` ASC";
			$periodicQuery = mysqli_query($conn,$periodicSql);
			while($periodicRow = mysqli_fetch_assoc($periodicQuery)){
				$srNo++;
				$periodicDatetime = $periodicRow["MobileDateTime"];
				$periodicLatlong = $periodicRow["GeoLocation"];
				$periodicEvent = $periodicRow["Event"];
				$periodicGpsStatus = $periodicRow["GpsStatus"];
				// if($periodicGpsStatus == "ON"){
				// 	$lastGpsOnTime=$periodicDatetime;
				// 	$lastGpsOnLatlong=$periodicLatlong;
				// }
				// else if($periodicGpsStatus == "OFF"){
				// 	$gpsOffTime=$lastGpsOnTime;
				// 	$gpsOffLatlong=$lastGpsOnLatlong;
				// }
				// $latlong = str_replace(",", "/", $periodicLatlong);
				// $latlongList = explode("/", $latlong);
				// $lat = $latlongList[0];
				// $long = $latlongList[1];
				// if($lat == "" || $long == ""){

				// }
				// else{
					$periodicJson = array(
						'srNo'=>$srNo,
						'dateTime'=>$periodicDatetime,
						'latlong'=>$periodicLatlong,
						'event'=>$periodicEvent,
						'gpsStatus'=>$periodicGpsStatus
					);
					array_push($dataList, $periodicJson);
				// }
			}
			// $stopDatetime = $stopRow["MobileDateTime"];
			// $stopLatlong = $stopRow["GeoLocation"];
			// $stopJson = array(
			// 	'dateTime'=>$stopDatetime,
			// 	'latlong'=>$stopLatlong,
			// 	'event'=>'Stop'
			// );
			// array_push($dataList, $stopJson);
		}
			
			
	}
	else{
		$periodicSql = "SELECT * FROM `Activity` where `EmpId` = '$empId' and `Event` in ('periodicData') and date(`MobileDateTime`) = '$visitDate' and `ActivityId`>=$startActId ORDER by `ActivityId` ASC";
		$periodicQuery = mysqli_query($conn,$periodicSql);
		while($periodicRow = mysqli_fetch_assoc($periodicQuery)){
			$srNo++;
			$periodicDatetime = $periodicRow["MobileDateTime"];
			$periodicLatlong = $periodicRow["GeoLocation"];
			$periodicEvent = $periodicRow["Event"];
			$periodicGpsStatus = $periodicRow["GpsStatus"];
			$periodicJson = array(
				'srNo'=>$srNo,
				'dateTime'=>$periodicDatetime,
				'latlong'=>$periodicLatlong,
				'event'=>$periodicEvent,
				'gpsStatus'=>$periodicGpsStatus
			);
			array_push($dataList, $periodicJson);
		}
	}
}
// $output = array(
// 	'gpsOffTime' => $gpsOffTime, 
// 	'gpsOffLatlong'=>$gpsOffLatlong,
// 	'dataList'=>$dataList
// );
// echo json_encode($output);
echo json_encode($dataList);
?>