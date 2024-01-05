<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
require 'EmployeeTenentId.php';
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

$empTenObj = new EmployeeTenentId();
$tenentId = $empTenObj->getTenentIdByEmpId($conn,$empId);


$sql = "SELECT distinct `MenuId` FROM `Mapping` WHERE `EmpId` = '$empId'  and `Active` = 1 ";
$query=mysqli_query($conn,$sql);

$menuArr = array();
while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	array_push($menuArr,$menuId);
}

$sql = "SELECT distinct `MenuId` FROM `FlowActivityMaster` WHERE find_in_set('$empId', `EmpId`) <> 0 ";
$query=mysqli_query($conn,$sql);

while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	array_push($menuArr,$menuId);
}

$roleSql = "SELECT distinct MenuId FROM Role WHERE RoleId = '$roleId' ";

$roleQuery=mysqli_query($conn,$roleSql);
while($roleRow = mysqli_fetch_assoc($roleQuery)){
	$roleMenuId = $roleRow['MenuId'];
	$roleMenuIdExplode = explode(",",$roleMenuId);
	for($i=0;$i<count($roleMenuIdExplode);$i++){
		array_push($menuArr,$roleMenuIdExplode[$i]);
	}
	
}


$newArr = array_unique($menuArr);
$newArr = array_values($newArr);

$menuIds = convertListInOperatorValue($newArr);
//echo $menuIds;
$chkIdString = "";
$menuSql = "SELECT `MenuId`,`CheckpointId`,`Verifier`,`Approver`,`3rd_ChkId`,`4th_ChkId`,`5th_ChkId`,`6th_ChkId`,`7th_ChkId`,`8th_ChkId`,`9th_ChkId`,`10th_ChkId` FROM `Menu` WHERE `MenuId` in ($menuIds)";
$menuQuery=mysqli_query($conn,$menuSql);
while($menuRow = mysqli_fetch_assoc($menuQuery)){
		$chkId = $menuRow["CheckpointId"];
		$chkId = str_replace(":",",",$chkId);
		if($chkIdString == ""){
			$chkIdString .= $chkId;
		}
		else{
			$chkIdString .= ",".$chkId;
		}
}
$appSql = "SELECT DISTINCT `FlowCheckpointId` FROM `FlowActivityMaster` where `MenuId` in ($menuIds)";

$appQuery=mysqli_query($conn,$appSql);
while($appRow = mysqli_fetch_assoc($appQuery)){
	$flowCheckpointId = $appRow["FlowCheckpointId"];
	$flowCheckpointId = str_replace(":",",",$flowCheckpointId);
	if($chkIdString == ""){
		$chkIdString .= $flowCheckpointId;
	}
	else{
		$chkIdString .= ",".$flowCheckpointId;
	}
}

$rr=0;
$responseArr = array();
$newCpArr = explode(",", $chkIdString);
for($ii=0;$ii<count($newCpArr);$ii++){
	$chIds = $newCpArr[$ii];
	$chkpointSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($chIds)";
	$chkpointQuery=mysqli_query($conn,$chkpointSql);
	while($chkpointRow = mysqli_fetch_assoc($chkpointQuery)){
		$rr++;
		$t = rand().''.$rr;
		$json = new StdClass;
		$json -> chkpId = $chkpointRow["CheckpointId"];
		$json -> description = $chkpointRow["Description"];
		$json -> value = $chkpointRow["Value"];
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

		
		// getting of login checkpint id in loginChkIdArr
		$logic = $chkpointRow["Logic"];
		$isDependent = $chkpointRow["Dependent"];
		if($logic != "" && ($isDependent == 1 || $isDependent == 5)){
			$chkpointLogicString = "";
			$logicChkIdString1 = "";
			$logicChkIdArr1 = array();			
			$logicArray = explode(":",$logic);
			
			for($l=0; $l < count($logicArray);$l++){
				if(trim($logicArray[$l]," ")!= ""){
					
					if($logicChkIdString1 == ""){
						$logicChkIdString1 .= trim($logicArray[$l]," ");
					}
					else{
						$logicChkIdString1 .= ",".trim($logicArray[$l]," ");
					}
					$csLogicString = "";
					$commaseperatedlogicArray = explode(",",$logicArray[$l]);
					for($csl=0;$csl<count($commaseperatedlogicArray);$csl++){
						if($csLogicString != ""){
							$csLogicString .= ",".$chkpointRow["CheckpointId"]."_".$commaseperatedlogicArray[$csl];
						}
						else{
							$csLogicString .= $chkpointRow["CheckpointId"]."_".$commaseperatedlogicArray[$csl]; 
						}
					}
					if($chkpointLogicString != ""){
						$chkpointLogicString .= ":".$csLogicString;
					}
					else{
						$chkpointLogicString .= $csLogicString; 
					}
					
				}
				else{
					if($chkpointLogicString != ""){
						$chkpointLogicString .= ": ";
					}
					else{
						$chkpointLogicString .= " "; 
					}
				}
			}
			$rrr = 0;
			if($logicChkIdString1 != ""){
				$logicChkIdArr1 = explode(",",$logicChkIdString1);
				for($jj=0;$jj<count($logicChkIdArr1);$jj++){
					$newlogicIds1 = $logicChkIdArr1[$jj];
					$logicSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($newlogicIds1) ";
					$logicQuery=mysqli_query($conn,$logicSql);
					while($logicRow = mysqli_fetch_assoc($logicQuery)){
						$rrr++;
						$t1 = rand().''.$rrr;
						$logicJson = new StdClass;
						$logicJson -> chkpId = $chkpointRow["CheckpointId"]."_".$logicRow["CheckpointId"];
						$logicJson -> description = $logicRow["Description"];
						$logicJson -> value = $logicRow["Value"];
						$logicJson -> typeId = $logicRow["TypeId"];
						$logicJson -> mandatory = $logicRow["Mandatory"];
						$logicJson -> editable = $logicRow["Editable"];
						$logicJson -> correct = $logicRow["Correct"];
						$logicJson -> size = $logicRow["Size"];
						$logicJson -> Score = $logicRow["Score"];
						$logicJson -> language = $logicRow["Language"];
						$logicJson -> Active = $logicRow["Active"];
						$logicJson -> Is_Dept = $logicRow["Dependent"];
						$logicJson -> Logic = $logicRow["Logic"];
						$logicJson -> IsGeofence = $logicRow["IsGeofence"];
						$logicJson -> answer = "";
						$logicJson -> info = $logicRow["Info"];
						$logicJson -> reuse = $t1;
						array_push($responseArr,$logicJson);
					}
				}

					
			}
			$json -> Logic = $chkpointLogicString;
		}
		array_push($responseArr,$json);
	}
}

echo json_encode($responseArr);

// file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/checkpoint_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($responseArr)."\n", FILE_APPEND);
?>

<?php
function convertListInOperatorValue($arrName){
	$inOperatorValue = "";
	for ($x = 0; $x < count($arrName); $x++) {
		if($arrName[$x] != ""){
			if($x == 0){
				$inOperatorValue .= $arrName[$x];
			}
			else{
				$inOperatorValue .= ",".$arrName[$x];
			}	
		}
		
		
	}
	return $inOperatorValue;
}
?>