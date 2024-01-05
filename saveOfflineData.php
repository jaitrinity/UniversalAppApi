<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData = json_decode($json);
$success = 0;
$fail = 0;
$dataList = $jsonData->dataList;
for($i=0; $i<count($dataList); $i++){
	$dataObj = $dataList[$i];
	$data = $dataList[$i]->data;
	// echo $data;
	$sql = "INSERT INTO `OfflineData`(`Data`) VALUES (?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $data);
	try {
		if($stmt->execute()){
			$success++;
		}
		else{
			$fail++;
		}
	} catch (Exception $e) {
		header("HTTP/1.1 500 Internal Server error");
		$output = array('status' => 500, 'message' => 'Internal Server error');
	}
		
}

if($success == 0){
	header("HTTP/1.1 500 Internal Server error");
	$output = array('status' => 500, 'message' => 'Internal Server error');
}
else{
	header("HTTP/1.1 200 OK");
	$output = array('status' => 200, 'message' => $success.' data successfully inserted');
}


echo json_encode($output);

?>