<?php

require_once 'dbConfiguration.php';
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

$json = new StdClass;
$json->returnCode = "0";
$json->returnMsg = "Failure";
$json->errorMsg = "";
$json->wrappedList = array();
$wrappedListArray = array();

$langSql = "Select * from Language";
$langQuery = mysqli_query($conn,$langSql);
$langSize = mysqli_num_rows($langQuery);

if($langSize > 0 ){
	while($l = mysqli_fetch_array($langQuery)){
		$lObj = new StdClass;
		$lObj->langId = $l['LangId'];
		$lObj->langName = $l['Name'];
		array_push($wrappedListArray,$lObj);
	}
	$json->returnCode = "200";
	$json->returnMsg = "Success";
	$json->wrappedList = $wrappedListArray;
}
else{
	$json->errorMsg = "No Language Found";
}

header('Content-type:application/json');
echo json_encode($json);
 

?>