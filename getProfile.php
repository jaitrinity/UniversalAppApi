<?php 
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];

$startStopSql = "SELECT a.Event, date(a.MobileDateTime) as AttDate FROM Activity a join Employees e on a.EmpId = e.EmpId and e.Active = 1 where a.EmpId = '$empId' and a.Event in ('Start','Stop') ORDER by a.ActivityId DESC LIMIT 0,1";
// echo $startStopSql;
$startStopQuery = mysqli_query($conn, $startStopSql);
$startStopRowcount=mysqli_num_rows($startStopQuery);
$attStatus = "Stop";
if($startStopRowcount != 0){
	$startStopRow = mysqli_fetch_assoc($startStopQuery);
	$attStatus = $startStopRow['Event'];
	$attDate = $startStopRow['AttDate'];
	if($attStatus == 'Start'){
		$firstStartSql = "SELECT date_format(`MobileDateTime`,'%H:%i') as AttTime  FROM `Activity` WHERE date(`MobileDateTime`)='$attDate' and `EmpId`='$empId' and `Event`='Start' ORDER by ActivityId  LIMIT 0,1";
		$firstStartQuery = mysqli_query($conn, $firstStartSql);
		$firstStartRowcount=mysqli_num_rows($firstStartQuery);
		if($firstStartRowcount !=0){
			$firstStartRow = mysqli_fetch_assoc($firstStartQuery);
			$attendanceTime = $firstStartRow["AttTime"];
		}
		else{
			$attendanceTime="";
		}
	}
	else{
		$attendanceTime="";
	}
}

$sql = "SELECT e.*,r.Role as EmpRole,e1.Name as RMName
FROM Employees e 
left join Role r on (e.RoleId = r.RoleId)
left join Employees e1 on (e.RMId = e1.EmpId)
WHERE e.EmpId= '$empId'";
$query=mysqli_query($conn,$sql);
$empId = "";
$roleId = "";
$fieldUser = "";
$isActive = "";
$msgStatus = "";
$owner = "";
$empMobile = "";
$empEmailId = "";
$empRole = "";
$area = "";
$city = "";
$state = "";
$rmName = "";
$profileUrl = "";
$geofenceLatlong = "0/0";
$geofenceDistance = 0;
$isGeofence = 0;

while($row = mysqli_fetch_assoc($query)){
	$empId = $row["EmpId"];
	$roleId = $row["RoleId"];
	$fieldUser = $row["FieldUser"];
	$isActive = $row["Active"];
	
	// if($row["Name"] != null && $row["Name"] != ""){
		$owner  = $row["Name"];
	// }
	// if($row["Mobile"] != null && $row["Mobile"] != ""){
		$empMobile = $row["Mobile"];
	// }
	// if($row["EmailId"] != null && $row["EmailId"] != ""){
		$empEmailId = $row["EmailId"];
	// }
	// if($row["EmpRole"] != null && $row["EmpRole"] != ""){
		$empRole = $row["EmpRole"];	
	// }
	// if($row["Area"] != null && $row["Area"] != ""){
		$area = $row["Area"];
	// }
	// if($row["City"] != null && $row["City"] != ""){
		$city = $row["City"];
	// }
	// if($row["State"] != null && $row["State"] != ""){
		$state = $row["State"];
	// }
	// if($row["RMName"] != null && $row["RMName"] != ""){
		$rmName = $row["RMName"];
	// }
	// if($row["ProfileURL"] != null && $row["ProfileURL"] != ""){
		$profileUrl = $row["ProfileURL"];
	// }	
	$geofenceLatlong = $row["GeofenceLatlong"];
	$geofenceDistance = $row["GeofenceDistance"];
	$isGeofence = $row["IsGeofence"];
}
$output = new StdClass;
$empConf = "SELECT * FROM `EmpProfileConfigration`";
$empConfQuery = mysqli_query($conn, $empConf);
$empConfRow = mysqli_fetch_assoc($empConfQuery);

$output -> status = 'Success';
$output -> code = 200;
$output -> empId = $empId.','.$empConfRow["EmpId"];
$output -> roleId = $roleId.','.$empConfRow["RoleId"];
$output -> empRole = $empRole.','.$empConfRow["RoleName"];
$output -> empName = $owner.','.$empConfRow["Name"];
$output -> empMobile = $empMobile.','.$empConfRow["Mobile"];
$output -> empEmailId = $empEmailId.','.$empConfRow["EmailId"];
$output -> area = $area.','.$empConfRow["Area"];
$output -> state = $state.','.$empConfRow["State"];
$output -> city = $city.','.$empConfRow["City"];
$output -> rmName = $rmName.','.$empConfRow["RmName"];
$output -> fieldUser = $fieldUser.','.$empConfRow["FieldUser"];
$output -> profileUrl = $profileUrl.','.$empConfRow["ProfileURL"];
$output -> isActive = $isActive;
$output -> attendanceStatus = $attStatus;
$output -> attendanceTime = $attendanceTime;
$output -> geofenceLatlong = $geofenceLatlong;
$output -> geofenceDistance = $geofenceDistance;
$output -> isGeofence = $isGeofence;
$output -> did = 15;
echo json_encode($output);


?>