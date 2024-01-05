<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData = json_decode($json);
$actId = $jsonData->actId;
$sql = "SELECT c.CheckpointId, c.Description, d.Value, c.TypeId, d.Lat_Long, d.Date_time  FROM TransactionDTL d join Checkpoints c on d.ChkId = c.CheckpointId WHERE d.ActivityId = $actId";
$result = mysqli_query($conn,$sql);
$row=mysqli_fetch_assoc($result);
$columnName = array();
foreach ($row as $key => $value) {
	array_push($columnName, $key);
}
$wrappedList = array();
mysqli_data_seek($result, 0);
while($row=mysqli_fetch_assoc($result)){
	$json = new StdClass;
	foreach ($columnName as $key => $value) {
		$json -> $value = $row[$value]; 
	}
	array_push($wrappedList, $json);
}
$output = array(
	'columnName' => $columnName, 
	'wrappedList' => $wrappedList
);	
echo json_encode($output);

?>