<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$selectType = $_REQUEST["selectType"];
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;
// $fromDate = $jsonData->fromDate;
// $toDate = $jsonData->toDate;

$mtd = date('Y-m');

$filterSql = "";
if($loginEmpRoleId == 4){

}
else{
	$filterSql .= "and (`RMId` = '$loginEmpId' or `EmpId` = '$loginEmpId')";
}

$underEmpList = array();
$sql = "SELECT `EmpId` FROM `Employees` WHERE 1=1 $filterSql and `Tenent_Id`='$tenentId' and `Active`=1";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	array_push($underEmpList, $row["EmpId"]);
}

$empIds = implode("','", $underEmpList);

$sql = "SELECT 'Users' as type, COUNT(*) as count FROM `Employees` where `Active`=1 and `Tenent_Id`=$tenentId";
$sql .= " UNION ";
$sql .= "SELECT 'Devices' as type, COUNT(*) as count FROM `Devices` where `Tenent_Id`=$tenentId";
$sql .= " UNION ";
$sql .= "SELECT 'Forms' as type, COUNT(*) as count FROM `Menu` where `Tenent_Id`=$tenentId";
$sql .= " UNION ";
$sql .= "SELECT 'Sites' as type, COUNT(*) as count FROM `Location` where `Tenent_Id`=$tenentId";

// echo $sql;
$query=mysqli_query($conn,$sql);
$countArr = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($countArr, $row);
}

$avgHourSql="";
if($loginEmpRoleId != 4){
	$avgHourSql .= "and `EmpId` in ('$empIds')";
}
$avgDistSql="";
if($loginEmpRoleId != 4){
	$avgDistSql .= "and dt.Emp_Id in ('$empIds')";
}

$sql = "SELECT 'AvgWorkingHours' as type, concat(floor(`t`.`AvgWoringMint` / 60),'H ',`t`.`AvgWoringMint` MOD 60,'M') AS dataCount from (SELECT ROUND(AVG(`WorkingMint`)) as AvgWoringMint FROM `Attendance_WorkingHours` where 1=1 $avgHourSql and date_format(`AttendanceDate`,'%Y-%m')='$mtd' and `Tenent_Id`=$tenentId) t";
$sql .= " UNION ";
$sql .= "SELECT 'AvgDistance', ROUND(AVG(dt.Distance_KM),2) as dataCount FROM DistanceTravel dt join Employees e on dt.Emp_Id=e.EmpId where 1=1 $avgDistSql and date_format(dt.Visit_Date,'%Y-%m')='$mtd' and e.Tenent_Id=$tenentId";
$query=mysqli_query($conn,$sql);
$avgArr = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($avgArr, $row);
}

$doneSql = "";
if($loginEmpRoleId != 4){
	$doneSql .= "and a.EmpId in ('$empIds')";
}
$sql = "SELECT m.MenuId, m.Cat, m.Sub, m.Caption, count(a.ActivityId) as DoneCount FROM Menu m join Activity a on m.MenuId=a.MenuId and a.Event='Submit' where 1=1 $doneSql and m.Tenent_Id=$tenentId GROUP by m.MenuId";
$query=mysqli_query($conn,$sql);
$doneArr = array();
while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	$cat = $row["Cat"];
	$sub = $row["Sub"];
	$caption = $row["Caption"];
	$doneCount = $row["DoneCount"];

	// echo $cat.'--'.$sub.'--'.$caption;

	$menuName = $cat;
	if($sub != null && $sub != ''){
		$menuName = $sub;
	}
	if($caption != null && $caption != ''){
		$menuName = $caption;
	}

	$doneJson = array('menuId' => $menuId, 'menuName' => $menuName, 'doneCount' => $doneCount);
	array_push($doneArr, $doneJson);
}

// $sql = "SELECT Name as name, ROUND(AVG(WorkingMint)) as avgWoringMint FROM Attendance_WorkingHours where date_format(`AttendanceDate`,'%Y-%m')='$mtd' and `Tenent_Id`=$tenentId and  WorkingMint is not null GROUP by EmpId";

$avgWorkSql="";
if($loginEmpRoleId != 4){
	$avgWorkSql .= "and EmpId in ('$empIds')";
}
$sql = "SELECT date_format(AttendanceDate,'%Y-%m-%d') as attDate, round(avg(WorkingMint)) as avgWorkingMint FROM Attendance_WorkingHours where 1=1 $avgWorkSql and date_format(AttendanceDate,'%Y-%m')='$mtd' and Tenent_Id=1 GROUP by date_format(AttendanceDate,'%Y-%m-%d') order by AttendanceDate desc LIMIT 0,5";

$query=mysqli_query($conn,$sql);
$avgWorkingArr = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($avgWorkingArr, $row);
}

// $sql = "SELECT Visit_Date as visitDate, ROUND(avg(Distance_KM)) as avgDistance FROM DistanceTravel where date_format(Visit_Date,'%Y-%m')='$mtd' GROUP by Visit_Date order by Visit_Date desc LIMIT 0,5";
$avgDistSql="";
if($loginEmpRoleId != 4){
	$avgDistSql .= "and Emp_Id in ('$empIds')";
}

$sql = "SELECT t.Visit_Date as visitDate, ROUND(avg(t.Distance_KM)) as avgDistance from (SELECT Visit_Date, sum(Distance_KM) as Distance_KM FROM DistanceTravel where 1=1 $avgDistSql and date_format(Visit_Date,'%Y-%m')='$mtd' GROUP by Visit_Date, Emp_Id) t GROUP by t.Visit_Date desc LIMIT 0,5";

// echo $sql;

$query=mysqli_query($conn,$sql);
$avgDistanceArr = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($avgDistanceArr, $row);
}

$ticketSql="";
if($loginEmpRoleId != 4){
	$ticketSql .= "and EmpId in ('$empIds')";
}

$sql = "SELECT 'TotalTkt' as type, count(*) as count FROM Mapping where 1=1 $ticketSql and date_format(CreateDateTIme,'%Y-%m')='$mtd' and ActivityId !=0 and TktNumber is not null and Tenent_Id=1";
$sql .= " UNION ";
$sql .= "SELECT 'PendingTkt' as type, count(*) as count FROM Mapping where 1=1 $ticketSql and date_format(CreateDateTIme,'%Y-%m')='$mtd' and ActivityId =0 and TktNumber is not null and Tenent_Id=1";
$query=mysqli_query($conn,$sql);
$ticketArr = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($ticketArr, $row);
}

$output = array(
	'countList' => $countArr, 
	'avgList' => $avgArr, 
	'doneList' => $doneArr, 
	'avgWorkingList' => $avgWorkingArr,
	'avgDistanceList' => $avgDistanceArr,
	'ticketList' => $ticketArr
);
echo json_encode($output);

?>