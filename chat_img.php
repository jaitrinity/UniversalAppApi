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
$company=$_REQUEST["company"];
$chk_id=$_REQUEST["chk_id"];
$depend_upon=$_REQUEST["depend_upon"];
$caption=$_REQUEST["caption"];
$timestamp = $_REQUEST["timestamp"];
$latlong = $_REQUEST["latLong"];
$dateTime = $_REQUEST["dateTime"];



$cpId = "";
$dependId = "";
$cpIdlist = explode("_",$chk_id);
$dIdlist = explode("_",$depend_upon);
if(count($cpIdlist) > 2){
	$cpId = $cpIdlist[1];
}
else{
	$cpId = $cpIdlist[0];
}
$dependId = $dIdlist[0];

$prevValue = "";
$fileName = $_FILES["attachment"]["name"];
$target_file = $target_dir."".$t.$fileName;
	
	
if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) 
{
	$parts = explode('/', $_SERVER['REQUEST_URI']);
	$link = $_SERVER['HTTP_HOST']; 
	$fileURL = "http://".$link."/".$parts[1]."/".$target_file;

	$arr[]=array('error' => '200','message'=>'Save Successfully!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>$fileURL);
	header('Content-Type: application/json');
	echo json_encode($arr[0]);
} 
else 
{
	// $arr[]=array('error' => '201','message'=>'Error!','FileURL'=>'');
	$arr[]=array('error' => '201','message'=>'Error!','fileName'=> $fileName,'caption'=> $caption,'timestamp'=>$timestamp,'chk_id'=>$chk_id,'FileURL'=>'');

	header('Content-Type: application/json');
	echo json_encode($arr[0]);
    //echo "Sorry, there was an error uploading your file.";
    //exit();
}
?>