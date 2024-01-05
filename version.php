<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");


$empId=$_REQUEST['empId'];
$appVer=$_REQUEST['appVer'];
$appType=$_REQUEST['type'];

$logout = "0";

$deviceSql = "Select * from Devices where EmpId = '$empId'";
$deviceQuery = mysqli_query($conn,$deviceSql);

if(mysqli_num_rows($deviceQuery) > 0){
	
	$deviceRow = mysqli_fetch_assoc($deviceQuery);
	$deviceAppVer = $deviceRow['AppVer'];
	
	if($deviceRow['OS'] == $appType && $deviceRow['AppVer'] < $appVer){
		$logout = "0";
	}
	
}


$versionSql = "Select * from Version";
$versionQuery= mysqli_query($conn, $versionSql);
$rowcount=mysqli_num_rows($versionQuery);

$ar = new StdClass;

if($rowcount > 0){
	$ver = mysqli_fetch_assoc($versionQuery);

	$android = explode(";",$ver['Android']);
	$ios = explode(";",$ver['Ios']);

	$confSql = "Select * from configuration";
	$confQuery = mysqli_query($conn, $confSql);
	$conf = mysqli_fetch_assoc($confQuery);
	$fontSize = $conf["FontSize"];
	$fontSize = explode(":", $fontSize);

	$ar->andVer=$android[0];
	$ar->andForce=$android[1];
	$ar->iosVer=$ios[0];
	$ar->iosForce=$ios[1];
	$ar->logout = $logout;
	$ar->fakeGps = $ver["FakeGPS"];
	$ar->whitelistApp = explode(",", $ver["WA"]);
	$ar->blacklistApp = explode(",", $ver["BA"]);
	$ar->geofenceLatlong = $conf['GeofenceLatlong'];
	$ar->geofenceDistance = $conf['Geofence'];
	$ar->helpdesk = $conf['Helpdesk'];
	$ar->menuFontSize = $fontSize[0];
	$ar->checkpointFontSize = $fontSize[1];
		
}

echo json_encode($ar);

?>