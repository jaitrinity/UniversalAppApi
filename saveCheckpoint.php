<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
require 'EmployeeTenentId.php';
mysqli_set_charset($conn,'utf8');

$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/saveCheckpoint_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($json)."\n", FILE_APPEND);
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
 if($assignId == ""){
 	$assignId = 0;
 }
 if($actId == null || $actId == "null"){
	 $actId = "";
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
	$empInfo = $classObj->getEmployeeInfo($conn,$empId);
	$tenentId = $empInfo["tenentId"];
	$state = $empInfo["state"];


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

		if($assignId == '0' && $actId == ''){
			$insertMapping = "INSERT INTO `Mapping`(`EmpId`,`MenuId`,`LocationId`,`Start`,`End`,`ActivityId`,`Tenent_Id`,`CreateDateTime`) values ('$empId','$mId','$lId',curdate(),curdate(),'$activityId','$tenentId', current_timestamp) ";
			mysqli_query($conn,$insertMapping);

			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`MenuId`,`Status`) VALUES ('$activityId',$mId,'Created')";
			
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

					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`,`ChkId`,`Value`,`DependChkId`,`Date_time`) VALUES (?,?,?,?,?)";
					$stmt = $conn->prepare($insertInTransDtl);
					// $stmt->bind_param("iisis", $activityId, $chkp_id, $answer, $dependent, $dateTime);
					$stmt->bind_param("iisis", $activityId, $chkp_id, $answer, $dependentChpId, $dateTime);
					try {
						$stmt->execute();
					} catch (Exception $e) {
						
					}	
				}

				$flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				$flowCpResult = mysqli_query($conn,$flowCpSql);
				while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
					$roleId = $flowCpRow["RoleId"];
					$flowStatus = $flowCpRow["Status"];
					$afterStatus = $flowCpRow["AfterStatus"];
					$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
					if($mId == 168 && $flowStatus == "Created"){
						$flowEmpId = $empId;
					}
					else{
						$flowEmpId = $flowCpRow["FlowEmpId"] == null ? 0 : $flowCpRow["FlowEmpId"];
						if($flowEmpId == 0){
							$flowEmpId = getFlowEmpId($empId, $roleId);
						}
					}
					
					if($flowEmpId !=0){
						$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
						mysqli_query($conn,$flowActSql);
					}
				}
			}
		}
		else{
			if($actId == ''){
				$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`MenuId`,`Status`) VALUES ('$activityId',$mId,'Created')";	
				mysqli_query($conn,$insertInTransHdr);

				$flowCpSql = "SELECT * FROM `FlowCheckpointMaster` where `MenuId` = $mId";
				$flowCpResult = mysqli_query($conn,$flowCpSql);
				while ($flowCpRow = mysqli_fetch_assoc($flowCpResult)){
					$roleId = $flowCpRow["RoleId"];
					$flowStatus = $flowCpRow["Status"];
					$afterStatus = $flowCpRow["AfterStatus"];
					$flowCheckpointId = $flowCpRow["FlowCheckpointId"];
					$flowEmpId = $flowCpRow["FlowEmpId"] == null ? 0 : $flowCpRow["FlowEmpId"];
					if($flowEmpId == 0){
						$flowEmpId = getFlowEmpId($empId, $roleId);
					}

					if($flowEmpId !=0){
						$flowActSql = "INSERT INTO `FlowActivityMaster`(`ActivityId`,`MenuId`,`Status`,`AfterStatus`,`EmpId`,`FlowCheckpointId`) VALUES ($activityId,$mId,'$flowStatus','$afterStatus','$flowEmpId','$flowCheckpointId')";
						mysqli_query($conn,$flowActSql);
					}
				}
			}
			
			$lastTransHdrId = $activityId;	
			$isTrainingRequired = "";
			$trainerEmpIdName = "";
			$totalQuesCount = 0;
			$correctQuesCount = 0;
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

				if($chkp_id == 3762){
					$isTrainingRequired = $answer;
				}

				if($chkp_id == 3763){
					$trainerEmpIdName = $answer;
				}


				if($mId == 168 && $status == "Created"){
					$cpSql = "SELECT `CorrectAnswer` FROM `Checkpoints` WHERE `CheckpointId`='$chkp_id'";
					$cpSqlQuery = mysqli_query($conn,$cpSql);
					$cpSqlRow = mysqli_fetch_assoc($cpSqlQuery);
					$correctAnswer = $cpSqlRow["CorrectAnswer"];
					if($correctAnswer == null || $correctAnswer == $answer){
						$correctQuesCount++;
					}
					$totalQuesCount++;
				}
			}
		}	
	}
	//Change in Mapping table from now onwards
	if($assignId != "0"){
		$updateAssignTaskSql = "UPDATE Mapping set ActivityId = '$activityId' where MappingId = $assignId";
		mysqli_query($conn,$updateAssignTaskSql);

	}
	if($actId != ''){
		$updateFlowActSql="UPDATE `FlowActivityMaster` set `FlowActivityId`=$activityId, `FlowEmpId`='$empId', `FlowSubmitDate`='$date1' where `ActivityId`=$actId and `Status`='$status'";	
		mysqli_query($conn,$updateFlowActSql);

		$upFlowSql="SELECT `AfterStatus` FROM `FlowActivityMaster` where `ActivityId`=$actId and `Status`='$status'";
		$upFlowQuery = mysqli_query($conn,$upFlowSql);
		$upFlowRow = mysqli_fetch_assoc($upFlowQuery);
		$afterStatus = $upFlowRow["AfterStatus"];

		$updateTransHdrSql = "UPDATE `TransactionHDR` set `Status`='$afterStatus' where `ActivityId` = $actId";
		mysqli_query($conn,$updateTransHdrSql);

		if($mId == 174 && $afterStatus == "TR01"){
			if($isTrainingRequired == "Yes"){
				$expTrEmpIdName = explode(" -- ", $trainerEmpIdName);
				$traEmpId = $expTrEmpIdName[0];

				$updateFlowActSql="UPDATE `FlowActivityMaster` set `EmpId`='$traEmpId' where `ActivityId`=$actId and `AfterStatus` in ('TR02','TR03')";	
				mysqli_query($conn,$updateFlowActSql);
			}
			else{
				$updateFlowActSql="UPDATE `FlowActivityMaster` set `EmpId`='Cancel' where `ActivityId`=$actId and `Status` in ('TR01','TR02','TR03')";	
				mysqli_query($conn,$updateFlowActSql);

				$updateTransHdrSql1 = "UPDATE `TransactionHDR` set `Status`='Cancel' where `ActivityId` = $actId";
				mysqli_query($conn,$updateTransHdrSql1);
			}
				
		}
		else if($mId == 168 && $status == "Created"){
			$updateTransHdrSql1 = "UPDATE `TransactionHDR` set `TotalQuesCount`='$totalQuesCount', `CorrectQuesCount`='$correctQuesCount' where `ActivityId` = $actId";
			mysqli_query($conn,$updateTransHdrSql1);

			if($trainerEmpIdName != ""){
				$expTrEmpIdName = explode(" -- ", $trainerEmpIdName);
				$traEmpId = $expTrEmpIdName[0];

				$updateFlowActSql="UPDATE `FlowActivityMaster` set `EmpId`='$traEmpId' where `ActivityId`=$actId and `AfterStatus` in ('TR02')";	
				mysqli_query($conn,$updateFlowActSql);
			}
		}
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

	file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/saveCheckpoint_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);

	if($assignId == '0' && $actId == ''){
		// if($mId == 120){
		// 	$poSql="SELECT d.ActivityId, d.ChkId, c.Description, d.Value as FillValue FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$activityId ORDER by d.SRNo";
		// 	$poResult = mysqli_query($conn,$poSql);
		// 	$client = "";
		// 	$table = "<table border=1 cellpadding=5 cellspacing=0>";
		// 	$table .= "<thead>";
		// 	$table .= "<tr>";
		// 	$table .= "<th>Description</th> <th>Value</th>";
		// 	$table .= "</tr>";
		// 	$table .= "</thead>";
		// 	$table .= "<tbody>";
		// 	while ($poRow = mysqli_fetch_assoc($poResult)){
		// 		$cId = $poRow["ChkId"];
		// 		$fValue = $poRow["FillValue"];
		// 		$desc = $poRow["Description"];
		// 		if($cId == 2697){
		// 			$client = $fValue;
		// 		}
		// 		else{
		// 			$table .= "<tr>";
		// 			$table .= "<td>$desc</td> <td>$fValue</td>";
		// 			$table .= "</tr>";
		// 		}
		// 	}
		// 	$table .= "</tbody>";
		// 	$table .= "</table>";

		// 	if($client != ""){
		// 		require 'SendMailClass.php';

		// 		$clientExp = explode(" --- ", $client);
		// 		$clientId = $clientExp[0];
		// 		$clientName = $clientExp[1];	

		// 		$clSql = "SELECT `EmailId` FROM `Employees` where `EmpId`='$clientId'";
		// 		$clResult = mysqli_query($conn,$clSql);
		// 		$clRow = mysqli_fetch_assoc($clResult);
		// 		$clientEmailId = $clRow["EmailId"];

		// 		$msg = "Dear $clientName,<br><br>";
		// 		$msg .= "Please find PO details: <br><br>";
		// 		$msg .= $table;

		// 		$subject = "PO Details";

		// 		$sendMailObj = new SendMailClass();
		// 		$sendMailObj->sendMail($clientEmailId, $subject, $msg, null);
		// 	}
		// }

		if($mId == 121){
			$prSql="SELECT d.ActivityId, d.ChkId, c.Description, d.Value as FillValue FROM TransactionDTL d join Checkpoints c on d.ChkId=c.CheckpointId where d.ActivityId=$activityId ORDER by d.SRNo";
			$prResult = mysqli_query($conn,$prSql);
			$vendor = "";
			$table = "<table border=1 cellpadding=5 cellspacing=0>";
			$table .= "<thead>";
			$table .= "<tr>";
			$table .= "<th>Description</th> <th>Value</th>";
			$table .= "</tr>";
			$table .= "</thead>";
			$table .= "<tbody>";
			while ($prRow = mysqli_fetch_assoc($prResult)){
				$cId = $prRow["ChkId"];
				$fValue = $prRow["FillValue"];
				$desc = $prRow["Description"];
				if($cId == 2702){
					$vendor = $fValue;
				}
				else{
					$table .= "<tr>";
					$table .= "<td>$desc</td> <td>$fValue</td>";
					$table .= "</tr>";
				}
			}
			$table .= "</tbody>";
			$table .= "</table>";

			if($vendor != ""){
				require 'SendMailClass.php';

				$vendorExp = explode(" --- ", $vendor);
				$vendorId = $vendorExp[0];
				$vendorName = $vendorExp[1];	

				$venSql = "SELECT `EmailId` FROM `Employees` where `EmpId`='$vendorId'";
				$venResult = mysqli_query($conn,$venSql);
				$venRow = mysqli_fetch_assoc($venResult);
				$vendorEmailId = $venRow["EmailId"];

				$msg = "Dear $vendorName,<br><br>";
				$msg .= "Please find PR details: <br><br>";
				$msg .= $table;

				$subject = "PR Details";

				$sendMailObj = new SendMailClass();
				$sendMailObj->sendMail($vendorEmailId, $subject, $msg, null);
			}
		}
	}
}

function getFlowEmpId($empId, $flowRoleId){
	global $conn; 
	$flowEmpId = "";
	$flowSql = "SELECT e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $flowRoleId ";
	$flowQuery = mysqli_query($conn,$flowSql);
	$ii=0;
	while ($flowRow = mysqli_fetch_assoc($flowQuery)) {
		$flowEmpId .= $flowRow["EmpId"];
		if($ii<mysqli_num_rows($flowQuery)-1){
			$flowEmpId .= ",";
 		}
		$ii++;
	}
	return $flowEmpId;
}



?>