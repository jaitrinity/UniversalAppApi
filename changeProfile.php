<?php 

require_once 'dbConfiguration.php';
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

?>
<?php
$dir = "Profile";
if (!file_exists('/var/www/trinityapplab.co.in/UniversalApp/files/'.$dir)) {
    mkdir('/var/www/trinityapplab.co.in/UniversalApp/files/'.$dir, 0777, true);
}
$t=date("YmdHis");
$target_dir = "files/".$dir."/";

$empId=$_REQUEST["empId"];
$target_file = $target_dir."".$t.$_FILES["profile"]["name"];
	
$isWrite = move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file); 
if ($isWrite) 
{
	$parts = explode('/', $_SERVER['REQUEST_URI']);
	$link = $_SERVER['HTTP_HOST']; 
	$fileURL = "http://".$link."/".$parts[1]."/".$target_file;

	$sql = "UPDATE `Employees` set `ProfileURL` = '$fileURL'  where `EmpId` = '$empId'";	
	mysqli_query($conn,$sql);

	$arr = array('error' => '200', 'fileURL' => $fileURL, 'message'=>'Save Successfully!');
	header('Content-Type: application/json');
	echo json_encode($arr);
} 
else 
{
	$arr = array('error' => '201', 'message'=>'Error!');
	header('Content-Type: application/json');
	echo json_encode($arr);
}
?>