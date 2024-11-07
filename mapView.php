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

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;
$empId = $jsonData->empId;
$visitDate = $jsonData->visitDate;

$filterSql = "";
$empList = [];
if($loginEmpRoleId == '4'){
	$empSql = "SELECT * FROM `Employees` WHERE `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($row11 = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$row11["EmpId"]);
		}
	}

}
else{
	$empSql = "SELECT * FROM `Employees` WHERE `RMId`='$loginEmpId' and `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($row11 = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$row11["EmpId"]);
		}
	}
	
	array_push($empList,$loginEmpId);
}

$allEmpId = implode("','", $empList);

if($empId != ""){
	$filterSql .= "and a.EmpId='$empId' ";
}
else{
	$filterSql .= "and a.EmpId in ('$allEmpId') ";
}
if($visitDate != ""){
	$filterSql .= "and date(a.MobileDateTime)='$visitDate' ";
}
else{
	$filterSql .= "and date(a.MobileDateTime)=curDate() ";
}

$sql="SELECT a.ActivityId, e.EmpId, e.Name, a.MobileDateTime, a.GeoLocation, a.Event, a.GpsStatus FROM Activity a join Employees e on a.EmpId = e.EmpId where 1=1 $filterSql and a.Event in ('Start','periodicData','Submit','Stop')";
$query=mysqli_query($conn,$sql);
$srNo=0;
$dataList= array();
$lastDatetime = "";
while($row = mysqli_fetch_assoc($query)){
	$srNo++;
	$activityId = $row["ActivityId"];
	$periodicDatetime = $row["MobileDateTime"];
	$periodicLatlong = $row["GeoLocation"];
	$periodicLatlong = str_replace("/", ",", $periodicLatlong);
	$periodicEvent = $row["Event"];
	$periodicGpsStatus = $row["GpsStatus"];
	$timeSpend = "";

	if($lastDatetime != ""){

		$timeSql = "SELECT CONCAT(FLOOR(t.Working_Mint/60),'H:',MOD(t.Working_Mint,60),'M') as Working_Hours from (SELECT TIMESTAMPDIFF(MINUTE,'$lastDatetime','$periodicDatetime') Working_Mint from Dual) t";
		$timeQuery=mysqli_query($conn,$timeSql);
		$timeRow = mysqli_fetch_assoc($timeQuery);
		// echo $timeSql.'--';
		$timeSpend = $timeRow["Working_Hours"];
	}

	$dataJson = array(
		'srNo'=>$srNo,
		'activityId'=>$activityId,
		'empId' => $row["EmpId"],
		'empName' => $row["Name"],
		'dateTime'=>$periodicDatetime,
		'latlong'=>$periodicLatlong,
		'event'=>$periodicEvent,
		'timeSpend' => $timeSpend,
		'gpsStatus'=>$periodicGpsStatus
	);
	array_push($dataList, $dataJson);

	$lastDatetime = $periodicDatetime;
}
echo json_encode($dataList);
?>