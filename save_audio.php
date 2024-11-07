<?php 

require_once 'dbConfiguration.php';
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

?>
<?php
$dir = date("M-Y-d");
if (!file_exists('/var/www/trinityapplab.co.in/UniversalApp/files/'.$dir)) {
    mkdir('/var/www/trinityapplab.co.in/UniversalApp/files/'.$dir, 0777, true);
}

$t=date("YmdHis");
$target_dir = "files/".$dir."/";

$activityId=$_REQUEST["trans_id"];
$company='';
$chk_id='';
$depend_upon='';
$caption='';
$timestamp = '';
$latlong = '';
$dateTime = '';

$requestJson = array('activityId' => $activityId, 'company' => $company, 'chk_id' => $chk_id, 'depend_upon' => $depend_upon, 'caption' => $caption, 'timestamp' => $timestamp, 'latlong' => $latlong, 'dateTime' => $dateTime );

file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/save_img_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($requestJson)."\n", FILE_APPEND);



$cpId = "";
$dependId = "0";
$cpIdlist = explode("_",$chk_id);
// $dIdlist = explode("_",$depend_upon);
if(count($cpIdlist) > 1){
	$cpId = $cpIdlist[1];
	$dependId = $cpIdlist[0];
}
else{
	$cpId = $cpIdlist[0];
}
// $dependId = $dIdlist[0];

$prevValue = "";
$fileName = $_FILES["attachment"]["name"];
$target_file = $target_dir."".$t.$fileName;
	
	
if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) 
{
	$parts = explode('/', $_SERVER['REQUEST_URI']);
	$link = $_SERVER['HTTP_HOST']; 
	$fileURL = "http://".$link."/".$parts[1]."/".$target_file;
	
	$selectQuery = "SELECT `Audio` from `TransactionHDR` where `ActivityId` = '$activityId' and `Audio` like 'http%'";
	// file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/save_img_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$selectQuery."\n", FILE_APPEND);
	// echo $selectQuery;
	$selectData = mysqli_query($conn,$selectQuery);
	$rowcount = mysqli_num_rows($selectData);
	if($rowcount > 0){
		$sr = mysqli_fetch_assoc($selectData);
		$prevValue = $sr['Audio'];
		$query = "UPDATE `TransactionHDR` set `Audio` = '$prevValue,$fileURL' where `ActivityId` = '$activityId'";	
	}
	else{
		$query = "UPDATE `TransactionHDR` set `Audio` = '$fileURL' where `ActivityId` = '$activityId' ";	
	}
	// file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/save_img_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$query."\n", FILE_APPEND);
	mysqli_query($conn,$query);

	$arr[]=array('error' => '200','message'=>'Save Successfully!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>$fileURL);
	header('Content-Type: application/json');
	echo json_encode($arr[0]);
	file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/save_img_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($arr[0])."\n", FILE_APPEND);
} 
else 
{
	// $arr[]=array('error' => '201','message'=>'Error!','FileURL'=>'');
	$arr[]=array('error' => '201','message'=>'Error!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>'');

	header('Content-Type: application/json');
	echo json_encode($arr[0]);
	file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/save_img_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($arr[0])."\n", FILE_APPEND);
    //echo "Sorry, there was an error uploading your file.";
    //exit();
}
?>