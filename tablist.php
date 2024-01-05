<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];


// Check connection

if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit();
}

mysqli_set_charset($conn,'utf8');



$res = new StdClass;
	
$flag = 0;
$wrappedListArray = array();
$res->wrappedList = $wrappedListArray;
$res->responseCode = "0";
$res->responseMsg = "Failure";
$tabId = "";

if($roleId != null){
	$getTabSql= "Select * from Role where RoleId = $roleId";
	//echo $getTabSql;
}
$getTabResult = mysqli_query($conn,$getTabSql);
if(mysqli_num_rows($getTabResult) > 0){
	$tr = mysqli_fetch_Array($getTabResult);
	$tabId = $tr['TabId'];
	//echo $tabId;
	if($tabId != null && $tabId != ""){
		$tabSql= "Select * from Tab where Id in ($tabId) and Active = 1 order by Seq";
		//echo $tabSql;
		$tabResult = mysqli_query($conn,$tabSql);
		
		if(mysqli_num_rows($tabResult) > 0){
			while($t = mysqli_fetch_Array($tabResult)){
				$tObj = new StdClass;
				$tObj->tId = $t['Id'];
				$tObj->tabName = $t['TabName'];
				$tObj->icon = $t['Icon'];
				$tObj->isSearch = $t['IsSearch'];
				$tObj->url = $t['URL'];
				array_push($wrappedListArray,$tObj);
			}	
			$flag = 1;
		}
		

	}
}
$info = new StdClass;
if($flag ==1){
	$empInfoSql = "Select * from Employees where EmpId = '$empId' and Active = 1";
	$empInfoResult = mysqli_query($conn,$empInfoSql);
	if(mysqli_num_rows($empInfoResult) > 0){
		$e = mysqli_fetch_Array($empInfoResult);
		$info->name = $e['Name'];
		$info->roleId = $e['RoleId'];
		$info->fieldUser = $e['FieldUser'];
	}
	$infConSql = "Select * from configuration";
	$infConResult = mysqli_query($conn,$infConSql);
	if(mysqli_num_rows($infConResult) > 0){
		$ic = mysqli_fetch_Array($infConResult);
		$info->inf = $ic['inf'];
		$info->conn = $ic['conf'];
		$info->start = $ic['start'];
		$info->end = $ic['end'];
		$info->battery = $ic['Battery'];
		$info->image = $ic['image'];
		$info->profileUrl = $ic['ProfileUrl'];
	}
	
}


if($flag == 1){
	$res->wrappedList = $wrappedListArray;
	$res->info = $info;
	$res->responseCode = "200";
	$res->responseMsg = "Success";	
	
}

header('Content-type:application/json');
echo json_encode($res);
?>