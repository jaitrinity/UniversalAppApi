<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;

$filterSql = "";
if($loginEmpRoleId == '4'){

}
else{
	$filterSql = "and (e.EmpId='$loginEmpId' or e.RMId='$loginEmpId')";
}

$sql = "SELECT t1.*, concat(floor((t1.IdleMinute / 60)),'.',(t1.IdleMinute % 60)) AS IdleHours from (SELECT t.EmpId, t.Name, a.GeoLocation, a.MobileDateTime, timestampdiff(MINUTE,a.MobileDateTime, CURRENT_TIMESTAMP) AS IdleMinute from (SELECT e.EmpId, e.Name, max(a.ActivityId) ActivityId FROM Employees e left join Activity a on e.EmpId = a.EmpId and a.Event='periodicdata' WHERE 1=1 $filterSql and e.Tenent_Id=$tenentId and e.Active=1 GROUP by e.EmpId) t left join Activity a on t.ActivityId=a.ActivityId where a.MobileDateTime is not null) t1";

$query=mysqli_query($conn,$sql);
$dataList = array();
if(mysqli_num_rows($query) !=0){
	while($row = mysqli_fetch_assoc($query)){
		$idleHours = floatval($row["IdleHours"]);
		$color = "";
		if($idleHours <= 3)
			$color = "GREEN";
		else if($idleHours > 3 && $idleHours <= 6)
			$color = "BLUE";
		else if($idleHours > 6 && $idleHours <= 12)
			$color = "YELLOW";
		else
			$color = "RED";

		$dataJson = array(
			'empId' => $row["EmpId"],
			'name' => $row["Name"], 
			'latlong' => $row["GeoLocation"],
			'datetime' => $row["MobileDateTime"],
			'idleHours' => $idleHours,
			'color' => $color
		);
		array_push($dataList, $dataJson);
	}
}
echo json_encode($dataList);
?>