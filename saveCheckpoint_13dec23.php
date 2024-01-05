<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
require 'EmployeeTenentId.php';
mysqli_set_charset($conn,'utf8');

$json = file_get_contents('php://input');
$jsonData=json_decode($json,true);
$req = $jsonData[0];
//echo json_encode($req);

$mapId=$req['mappingId'];
$empId=$req['Emp_id'];
$mId=$req['M_Id'];
$lId=$req['locationId'];
$event=$req['event'];
$geolocation=$req['geolocation'];
$distance=$req['distance'];
$mobiledatetime=$req['mobiledatetime'];
$startDateTime = $req['startDateTime'];
$caption = $req['caption'];
$transactionId = $req['timeStamp'];
$checklist = $req['checklist'];
$dId = $req['did'];
$assignId = $req['assignId'];
$actId = $req['activityId'];
$status = $req["status"];
$demographicSampling = $req['demographicSampling'];
$checkpointSampling1 = $req['checkpointSampling1'];
$checkpointSampling2 = $req['checkpointSampling2'];
$checkpointSampling3 = $req['checkpointSampling3'];
$checkpointSampling4 = $req['checkpointSampling4'];
$checkpointSampling5 = $req['checkpointSampling5'];


 $lastTransHdrId = "";
 $activityId = 0;
 if($lId == ""){
 	$lId = '1';
 }

 if($mId == ''){
 	$mId = '0';
 }

 if($mapId == ''){
 	$mapId = '0';
 }
 
 if($actId == ''){
	 $actId = null;
 }

if ((strpos($mobiledatetime, 'AM') !== false) || (strpos($mobiledatetime, 'PM')) || (strpos($mobiledatetime, 'am') !== false) || (strpos($mobiledatetime, 'pm')))   {
	$date = date_create_from_format("Y-m-d h:i:s A","$mobiledatetime");
	$date1 = date_format($date,"Y-m-d H:i:s");
}
else{
	$date1 = $mobiledatetime;
} 

 if($event == 'Submit'){
 	$classObj = new EmployeeTenentId();
	$tenentId = $classObj->getTenentIdByEmpId($conn,$empId);

	$sql = "SELECT `Verifier_RoleId`,`Approver_RoleId` FROM `Menu` where `MenuId` = '$mId'";
	$result = mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($result);
	$verifier_role_id = $row["Verifier_RoleId"];
	$approver_role_id = $row["Approver_RoleId"];

	$verifierMobile = "";
	if($verifier_role_id != null){
		$sql2 = "SELECT e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $verifier_role_id ";
		$result2 = mysqli_query($conn,$sql2);
		$i=0;
		while ($row2 = mysqli_fetch_assoc($result2)) {
			$verifierMobile .= $row2["EmpId"];
			if($i<mysqli_num_rows($result2)-1){
				$verifierMobile .= ",";
	 		}
			$i++;
		}
	}
		

	$approverMobile = "";
	if($approver_role_id != null){
		$sql3 = "SELECT e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $approver_role_id ";
		$result3 = mysqli_query($conn,$sql3);
		$ii=0;
		while ($row3 = mysqli_fetch_assoc($result3)) {
			$approverMobile .= $row3["EmpId"];
			if($ii<mysqli_num_rows($result3)-1){
				$approverMobile .= ",";
	 		}
			$ii++;
		}
	}


	$start_date = new DateTime($startDateTime);
	$end_date = new DateTime($date1);
	$since_start = $start_date->diff($end_date);
	$hours = $since_start->h.'H';
	$minutes = $since_start->i.'M';
	$seconds = $since_start->s.'S';
	$timeDuration = $hours.':'.$minutes.':'.$seconds;
		
	$geolocation=$req['latLong'];
	$activitySql = "INSERT into `Activity` (`DId`, `MappingId`, `EmpId`, `MenuId`, `LocationId`, `Event`, `GeoLocation`, `Distance`, `MobileDateTime`, `StartDateTime`, `TimeDuration`) values ('$dId', '$mapId', '$empId', '$mId', '$lId', '$event', '$geolocation', '$distance', '$date1', '$startDateTime', '$timeDuration')";
	if(mysqli_query($conn,$activitySql)){
		$activityId = mysqli_insert_id($conn);
	}
	
	if($checklist != null && count($checklist) != 0){
		$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `LocationId`, `Verifier`, `Approver`, `Start`, `End`, `ActivityId`, `Tenent_Id`, `CreateDateTime`) values ('$empId', '$mId', '$lId', '$verifierMobile', '$approverMobile', curdate(), curdate(), '$activityId', '$tenentId', current_timestamp) ";
		mysqli_query($conn,$insertMapping);

		if($actId == null  && $actId == ''){
			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','Created')";
			
			if(mysqli_query($conn,$insertInTransHdr)){
				$lastTransHdrId = $conn->insert_id;
				foreach($checklist as $k=>$v)
				{
					$answer=$v['value'];
					$dateTime=$v['dateTime'];
					$chkp_idArray=explode("_",$v['Chkp_Id']);
					$dependentChpId=0;
					if(count($chkp_idArray) > 1){
						$chkp_id = $chkp_idArray[1];
						$dependentChpId = $chkp_idArray[0];
					}
					else{
						$chkp_id = $chkp_idArray[0];
					}	
					
					// $dependent=$v['Dependent'];
					// if($dependent == ""){
					// 	$dependent = 0;
					// }

					// $insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`) VALUES (?,?,?,?)";
					// $stmt = $conn->prepare($insertInTransDtl);
					// $stmt->bind_param("iisi", $activityId, $chkp_id, $answer, $dependent);

					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`,`Date_time`) VALUES (?,?,?,?,?)";
					$stmt = $conn->prepare($insertInTransDtl);
					// $stmt->bind_param("iisis", $activityId, $chkp_id, $answer, $dependent, $dateTime);
					$stmt->bind_param("iisis", $activityId, $chkp_id, $answer, $dependentChpId, $dateTime);
					try {
						$stmt->execute();
					} catch (Exception $e) {
						
					}
					
				}
				
			}
		}
		else{
			foreach($checklist as $k=>$v)
			{
				$answer=$v['value'];
				$dateTime=$v['dateTime'];
				$chkp_idArray=explode("_",$v['Chkp_Id']);
				$dependentChpId=0;
				if(count($chkp_idArray) > 1){
					$chkp_id = $chkp_idArray[1];
					$dependentChpId = $chkp_idArray[0];
				}
				else{
					$chkp_id = $chkp_idArray[0];
				}	
				
				// $dependent=$v['Dependent'];
				// if($dependent == ""){
				// 	$dependent = 0;
				// }

				// $insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`) VALUES (?,?,?,?)";
				// $stmt = $conn->prepare($insertInTransDtl);
				// $stmt->bind_param("iisi", $activityId, $chkp_id, $answer, $dependent);

				$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`,`Date_time`) VALUES (?,?,?,?,?)";
				$stmt = $conn->prepare($insertInTransDtl);
				// $stmt->bind_param("iisis", $activityId, $chkp_id, $answer, $dependent, $dateTime);
				$stmt->bind_param("iisis", $activityId, $chkp_id, $answer, $dependentChpId, $dateTime);
				try {
					if($stmt->execute()){
						$isAllSave = true;
					}
				} catch (Exception $e) {
					
				}
				
			}
			$lastTransHdrId = $activityId;
			
		}

			
	}
	//Change in Mapping table from now onwards
	if($assignId != ""){
		$updateAssignTaskSql = "UPDATE Mapping set ActivityId = '$activityId' where MappingId = $assignId";
		mysqli_query($conn,$updateAssignTaskSql);

	}
	if($actId != null && $actId != ''){
		$selectTransHdrSql = "SELECT * from TransactionHDR  where ActivityId = $actId";
		$selectTransHdrResult = mysqli_query($conn,$selectTransHdrSql);
		$thRow=mysqli_fetch_array($selectTransHdrResult);
		if($thRow['Status'] == 'Created'){
				$updateTransHdrSql = "UPDATE TransactionHDR set Status = 'Verified',VerifierActivityId = '$activityId' where ActivityId = $actId";
		}
		else if($thRow['Status'] == 'Verified'){
			$updateTransHdrSql = "UPDATE TransactionHDR set Status = 'Approved',ApproverActivityId = '$activityId' where ActivityId = $actId";
		}
		
		mysqli_query($conn,$updateTransHdrSql);
	}
	
	$output = new StdClass;
	if($lastTransHdrId != ""){
		$output -> error = "200";
		$output -> message = "success";
		$output -> TransID = "$activityId";
		if($demographicSampling != ""){
			$upSam = "UPDATE `Sampling` set `TargetDone` = `TargetDone` + 1 where `Sampling` like '%$demographicSampling%' and `MenuId` = $mId";
			mysqli_query($conn,$upSam);
		}
		if($checkpointSampling1 != ""){
			$upSam = "UPDATE `Sampling` set `TargetDone` = `TargetDone` + 1 where `Sampling` like '%$checkpointSampling1%' and `MenuId` = $mId";
			mysqli_query($conn,$upSam);
		}
		if($checkpointSampling2 != ""){
			$upSam = "UPDATE `Sampling` set `TargetDone` = `TargetDone` + 1 where `Sampling` like '%$checkpointSampling2%' and `MenuId` = $mId";
			mysqli_query($conn,$upSam);
		}
		if($checkpointSampling3 != ""){
			$upSam = "UPDATE `Sampling` set `TargetDone` = `TargetDone` + 1 where `Sampling` like '%$checkpointSampling3%' and `MenuId` = $mId";
			mysqli_query($conn,$upSam);
		}
		if($checkpointSampling4 != ""){
			$upSam = "UPDATE `Sampling` set `TargetDone` = `TargetDone` + 1 where `Sampling` like '%$checkpointSampling4%' and `MenuId` = $mId";
			mysqli_query($conn,$upSam);
		}
		if($checkpointSampling5 != ""){
			$upSam = "UPDATE `Sampling` set `TargetDone` = `TargetDone` + 1 where `Sampling` like '%$checkpointSampling5%' and `MenuId` = $mId";
			mysqli_query($conn,$upSam);
		}
		
	}
	else{
		$output -> error = "0";
		$output -> message = "something wrong";
		$output -> TransID = "$activityId";
	}
	echo json_encode($output);
	
}



?>