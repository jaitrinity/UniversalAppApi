<?php 
include("dbConfiguration.php");
require 'EmployeeTenentId.php';
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

$empTenObj = new EmployeeTenentId();
$tenentId = $empTenObj->getTenentIdByEmpId($conn,$empId);

$responseArr = array();
$sql = "SELECT * FROM `Checkpoints` where (`Dependent` is null or `Dependent` = '' or `Dependent` = '0' or `Dependent` = '2' or `Dependent` = '3' or `Dependent` = '4' or `Dependent` = '6')";
$query = mysqli_query($conn,$sql);
while($chkpointRow = mysqli_fetch_assoc($query)){
	$t = rand();
	$json = new StdClass;
	$json -> chkpId = $chkpointRow["CheckpointId"];
	$json -> description = $chkpointRow["Description"];
	$json -> typeId = $chkpointRow["TypeId"];
	$json -> mandatory = $chkpointRow["Mandatory"];
	$json -> editable = $chkpointRow["Editable"];
	$json -> correct = $chkpointRow["Correct"];
	$json -> size = $chkpointRow["Size"];
	$json -> Score = $chkpointRow["Score"];
	$json -> language = $chkpointRow["Language"];
	$json -> Active = $chkpointRow["Active"];
	$json -> Is_Dept = $chkpointRow["Dependent"];
	$json -> Logic = $chkpointRow["Logic"];
	$json -> IsGeofence = $chkpointRow["IsGeofence"];
	$json -> answer = "";
	$json -> info = $chkpointRow["Info"];
	$json -> reuse = $t;

	if($chkpointRow['IsSql'] == 1){
	    $valueSql = $chkpointRow["Value"];
	    $stmt = mysqli_prepare($conn,$valueSql);
	    if(str_contains($valueSql, "?")){
	    	mysqli_stmt_bind_param($stmt, 'si', $empId,$tenentId);
	    }
	    mysqli_stmt_execute($stmt);
	    mysqli_stmt_store_result($stmt);
	    mysqli_stmt_bind_result($stmt,$project);
	    if(mysqli_stmt_num_rows($stmt) > 0){
	       $valueArray = array();
	       while($v = mysqli_stmt_fetch($stmt)){
	            array_push($valueArray,$project);
	       }
	       $json -> value =implode(',',$valueArray); 
		
	    }
	    else{
	        $json -> value = "";    
	    }
	    mysqli_stmt_close($stmt);
	}
	else{
	    $json -> value = $chkpointRow["Value"];    
	}
	array_push($responseArr,$json);
}


$sql = "SELECT `CheckpointId`, (case when `Logic` is null then '' else `Logic` end) as `Logic` FROM `Checkpoints` where (`Dependent` is not null and `Dependent` != '' and `Dependent` != '0' and `Dependent` != '2' and `Dependent` != '3' and `Dependent` != '4' and `Dependent` != '6') ";
$query = mysqli_query($conn,$sql);
$mainArr = [];
while($row = mysqli_fetch_assoc($query)){
	$chkId = $row["CheckpointId"];
	$logic = $row["Logic"];

	$depChkIdStr = "";
	$logChkIdStr = "";

	$opDep = explode(":", $logic);
	for($i=0;$i<count($opDep);$i++){
		$opDepChp = $opDep[$i];
		$logicArray = explode(",",$opDepChp);
		$logChkIdArr = [];
		$depChkIdArr = [];

		for($ii=0;$ii<count($logicArray);$ii++){
			$logChkId = $logicArray[$ii];
			if($logChkId !=''){
				array_push($logChkIdArr, $logChkId);
				$depChkId = $chkId.'_'.$logChkId;
				array_push($depChkIdArr, $depChkId);
			}
		}
		$logChkIdStr .= implode(",", $logChkIdArr);
		$depChkIdStr .= implode(",", $depChkIdArr);
		if($i < count($opDep)-1){
			$logChkIdStr .= ":";
			$depChkIdStr .= ":";
		}
	}
	$json = array('chkId' => $chkId, 'logChkId' => $logChkIdStr, 'depChkId' => $depChkIdStr);
	array_push($mainArr, $json);
}


for($j=0;$j<count($mainArr);$j++){
	$mainObj = $mainArr[$j];
	$chkId = $mainObj["chkId"];
	$logChkId = $mainObj["logChkId"];
	$depChkId = $mainObj["depChkId"];

	$chkpointSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($chkId)";
	$chkpointQuery=mysqli_query($conn,$chkpointSql);
	while($chkpointRow = mysqli_fetch_assoc($chkpointQuery)){
		$t = rand();
		$json = new StdClass;
		$json -> chkpId = $chkId;
		$json -> description = $chkpointRow["Description"];
		$json -> typeId = $chkpointRow["TypeId"];
		$json -> mandatory = $chkpointRow["Mandatory"];
		$json -> editable = $chkpointRow["Editable"];
		$json -> correct = $chkpointRow["Correct"];
		$json -> size = $chkpointRow["Size"];
		$json -> Score = $chkpointRow["Score"];
		$json -> language = $chkpointRow["Language"];
		$json -> Active = $chkpointRow["Active"];
		$json -> Is_Dept = $chkpointRow["Dependent"];
		$json -> Logic = $depChkId;
		$json -> IsGeofence = $chkpointRow["IsGeofence"];
		$json -> answer = "";
		$json -> info = $chkpointRow["Info"];
		$json -> reuse = $t;

		if($chkpointRow['IsSql'] == 1){
		    $valueSql = $chkpointRow["Value"];
		    $stmt = mysqli_prepare($conn,$valueSql);
		    if(str_contains($valueSql, "?")){
		    	mysqli_stmt_bind_param($stmt, 'si', $empId,$tenentId);
		    }
		    mysqli_stmt_execute($stmt);
		    mysqli_stmt_store_result($stmt);
		    mysqli_stmt_bind_result($stmt,$project);
		    if(mysqli_stmt_num_rows($stmt) > 0){
		       $valueArray = array();
		       while($v = mysqli_stmt_fetch($stmt)){
		            array_push($valueArray,$project);
		       }
		       $json -> value =implode(',',$valueArray); 
			
		    }
		    else{
		        $json -> value = "";    
		    }
		    mysqli_stmt_close($stmt);
		}
		else{
		    $json -> value = $chkpointRow["Value"];    
		}
		array_push($responseArr,$json);
	}


	//echo $chkId.",l=".$logChkId.',d='.$depChkId.' -- ';
	$logChkId = str_replace(":", ",", $logChkId);
	$logChkIdArr = explode(",", $logChkId);

	$depChkId = str_replace(":", ",", $depChkId);
	$depChkIdArr = explode(",", $depChkId);

	for($k=0;$k<count($logChkIdArr);$k++){
		$logicChkId = $logChkIdArr[$k];
		$logicChkId = $logicChkId == '' ? 0 : $logicChkId;
		$chkpointSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($logicChkId)";
		$chkpointQuery=mysqli_query($conn,$chkpointSql);
		while($chkpointRow = mysqli_fetch_assoc($chkpointQuery)){
			$chkIdd = $chkpointRow["CheckpointId"];
			$lo = $chkpointRow["Logic"];
			if($lo != ''){
				$opDepp = explode(":", $lo);
				$lo = "";
				for($is=0;$is<count($opDepp);$is++){
					$opDepChpp = $opDepp[$is];
					$logicArrayy = explode(",",$opDepChpp);
					$depChkIdArrr = [];

					for($iis=0;$iis<count($logicArrayy);$iis++){
						$logChkIdd = $logicArrayy[$iis];
						if($logChkIdd !=''){
							$depChkIdd = $chkIdd.'_'.$logChkIdd;
							array_push($depChkIdArrr, $depChkIdd);
						}
					}
					$lo .= implode(",", $depChkIdArrr);
					if($is < count($opDepp)-1){
						$lo .= ":";
					}
				}
			}

			$t = rand();
			$json = new StdClass;
			$json -> chkpId = $depChkIdArr[$k];
			$json -> description = $chkpointRow["Description"];
			$json -> typeId = $chkpointRow["TypeId"];
			$json -> mandatory = $chkpointRow["Mandatory"];
			$json -> editable = $chkpointRow["Editable"];
			$json -> correct = $chkpointRow["Correct"];
			$json -> size = $chkpointRow["Size"];
			$json -> Score = $chkpointRow["Score"];
			$json -> language = $chkpointRow["Language"];
			$json -> Active = $chkpointRow["Active"];
			$json -> Is_Dept = $chkpointRow["Dependent"];
			$json -> Logic = $lo;
			$json -> IsGeofence = $chkpointRow["IsGeofence"];
			$json -> answer = "";
			$json -> info = $chkpointRow["Info"];
			$json -> reuse = $t;

			if($chkpointRow['IsSql'] == 1){
			    $valueSql = $chkpointRow["Value"];
			    $stmt = mysqli_prepare($conn,$valueSql);
			    if(str_contains($valueSql, "?")){
			    	mysqli_stmt_bind_param($stmt, 'si', $empId,$tenentId);
			    }
			    mysqli_stmt_execute($stmt);
			    mysqli_stmt_store_result($stmt);
			    mysqli_stmt_bind_result($stmt,$project);
			    if(mysqli_stmt_num_rows($stmt) > 0){
			       $valueArray = array();
			       while($v = mysqli_stmt_fetch($stmt)){
			            array_push($valueArray,$project);
			       }
			       $json -> value =implode(',',$valueArray); 
				
			    }
			    else{
			        $json -> value = "";    
			    }
			    mysqli_stmt_close($stmt);
			}
			else{
			    $json -> value = $chkpointRow["Value"];    
			}
			array_push($responseArr,$json);
		}

	}

}

echo json_encode($responseArr);

?>