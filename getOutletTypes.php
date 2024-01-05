<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

if(isset($_REQUEST['aId'])){
	$actId = $_REQUEST['aId'];
}
else{
	$actId = "";
}


// Check connection

if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit();
}

mysqli_set_charset($conn,'utf8');

if($actId == null || $actId == ''){
	$whereClause = " and AId is null";
}
else{
	$whereClause = " and AId = $actId";
}


$res = new StdClass;
$flag = 0;
$wrappedListArray = array();
$res->wrappedList = $wrappedListArray;
$res->responseCode = "0";
$res->responseMsg = "Failure";
$tabId = "";


$sql = "Select * from OutletType where IsActive = 1".$whereClause ;
//echo $sql;
$sqlResult= mysqli_query($conn,$sql);
$sqlCount = mysqli_num_rows($sqlResult);

if($sqlCount > 0){
	while ($r = mysqli_fetch_Array($sqlResult)){
		$obj = new StdClass;
		$obj->typeId = $r['Id'];
		$obj->typeName = $r['TypeName'];
		$obj->typeIcon = $r['Icon'];
		$obj->locIcon = $r['LocIcon'];
		//$obj->keyword= $r['Keyword'];
		$typeName = $r['TypeName'];

		// $result1 = CallAPI("GET","http://216.48.181.73/UniversalApp/getEmpOutletList.php?empId=$empId&roleId=$roleId&typeName=$typeName",true);
		// $outletRes = json_decode($result1)

		require_once 'EmpOutletClass.php';
		$classObj = new EmpOutletClass;
		$result1 = $classObj->getEmpOutletList($empId,$roleId,$typeName);

		$outletRes = $result1;
		$obj->locations = $outletRes->wrappedList;
		//$obj->locations = [];
		array_push($wrappedListArray,$obj);
	}
	$flag = 1;
}




if($flag = 1){
	$res->wrappedList = $wrappedListArray;
	$res->responseCode = "200";
	$res->responseMsg = "Success";	
}

header('Content-type:application/json');
echo json_encode($res);
?>

<?php
// function CallAPI($method, $url, $data)
// {
//     $curl = curl_init();

//     switch ($method)
//     {
//         case "POST":
//             curl_setopt($curl, CURLOPT_POST, 1);

//             if ($data)
//                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//             break;
//         default:
//             if ($data)
//                 $url = sprintf("%s?%s", $url, http_build_query($data));
//     }

//     curl_setopt($curl, CURLOPT_URL, $url);
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

//     $result = curl_exec($curl);
// 	//echo $result."\n";
//     curl_close($curl);

//     return $result;
// }
?>