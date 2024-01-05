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

$verifierSql = "SELECT distinct mp.MenuId
				FROM Mapping mp
				join TransactionHDR th on (mp.ActivityId = th.ActivityId)	
				WHERE mp.Verifier = '$empId' and mp.Active = 1 and th.Status = 'Created'";
$verifierQuery=mysqli_query($conn,$verifierSql);
while($vrow = mysqli_fetch_assoc($verifierQuery)){
	$vMenuId = $vrow["MenuId"];
	array_push($menuArr,$vMenuId);
}

$approverSql = "SELECT distinct mp.MenuId
				FROM Mapping mp
				join TransactionHDR th on (mp.ActivityId = th.ActivityId)	
				WHERE mp.Approver = '$empId' and mp.Active = 1 and th.Status = 'Verified'";
$approverQuery=mysqli_query($conn,$approverSql);
while($arow = mysqli_fetch_assoc($approverQuery)){
	$aMenuId = $arow["MenuId"];
	array_push($menuArr,$aMenuId);
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
$responseArr = array();
if($menuIds == ''){
	echo json_encode($responseArr);
	return;
}
$chkIdString = "";
$menuSql = "SELECT `MenuId`,`CheckpointId`,`Verifier`,`Approver` FROM `Menu` WHERE `MenuId` in ($menuIds)";
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
		
		if($menuRow["Verifier"] != ""){
			$verifier = $menuRow["Verifier"];	
			$verifier = str_replace(":",",",$verifier);
			$chkIdString .= ",".$verifier;
		}
		if($menuRow["Approver"] != ""){
			$approver = $menuRow["Approver"];	
			$approver = str_replace(":",",",$approver);
			$chkIdString .= ",".$approver;
		}

}



$newCpArr = array_unique(array($chkIdString));
$newCpArr = array_values($newCpArr);
$cpIds = convertListInOperatorValue($newCpArr);
// echo $cpIds;
$cpIds = substr($cpIds,0,strlen($cpIds));

$reuseChkArr = explode(",", $cpIds);
$chkpointSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($cpIds)";
$chkpointQuery=mysqli_query($conn,$chkpointSql);
while($chkpointRow = mysqli_fetch_assoc($chkpointQuery)){
	$chkId = $chkpointRow["CheckpointId"];
	$isAlreadyArr = array();
	for($i=0;$i<count($reuseChkArr);$i++){
		$reuseChkId = $reuseChkArr[$i];
		if($chkId == $reuseChkId){
			$json = new StdClass;
			$tempReuseChkId = "";
			if(count($isAlreadyArr) > 0){
				$tempReuseChkId = $chkId.'.'.count($isAlreadyArr);
				$json -> chkpId = $tempReuseChkId;
			}
			else{
				$tempReuseChkId = $chkId;
				$json -> chkpId = $tempReuseChkId;
			}
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

			if($chkpointRow['IsSql'] == 1){
			   // $empId = "34";
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
								$csLogicString .= ",".$tempReuseChkId."_".$commaseperatedlogicArray[$csl];
							}
							else{
								$csLogicString .= $tempReuseChkId."_".$commaseperatedlogicArray[$csl]; 
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
				if($logicChkIdString1 != ""){
			
					$logicChkIdArr1 = explode(",",$logicChkIdString1);
					$logicIds1 = convertListInOperatorValue($logicChkIdArr1);
					$newlogicArr1 = array_unique(array($logicIds1));
					$newlogicArr1 = array_values($newlogicArr1);
					
					$newlogicIds1 = convertListInOperatorValue($newlogicArr1);
					$logicSql = "SELECT * FROM `Checkpoints` WHERE `CheckpointId` in ($newlogicIds1) ";
					$logicQuery=mysqli_query($conn,$logicSql);
					while($logicRow = mysqli_fetch_assoc($logicQuery)){
						$logicJson = new StdClass;
						$logicJson -> chkpId = $tempReuseChkId."_".$logicRow["CheckpointId"];
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
						array_push($responseArr,$logicJson);
					}
				}
				$json -> Logic = $chkpointLogicString;
			}
			array_push($responseArr,$json);
			
			array_push($isAlreadyArr, $reuseChkId);
		}
	}
}
	
echo json_encode($responseArr);
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