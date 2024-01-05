<?php
include("dbConfiguration.php");
require 'EmployeeTenentId.php';
mysqli_set_charset($conn,'utf8');

$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$req = $jsonData[0];

 $mapId=$req->mappingId;
 $empId=$req->Emp_id;
 $mId=$req->M_Id;
 $lId=$req->locationId;
 $event=$req->event;
 $geolocation=$req->geolocation;
 $distance=$req->distance;
 $mobiledatetime=$req->mobiledatetime;
 
 $caption = $req->caption;
 $transactionId = $req->timeStamp;
 $checklist = $req->checklist;
 $dId = $req->did;
 $assignId = $req->assignId;
 $actId = $req->activityId;
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

	$sql = "SELECT t.Verifier_Role, t.Approver_Role, r2.RoleId as verifier_role_id, r3.RoleId as approver_role_id from 
	(SELECT r1.Verifier_Role, r1.Approver_Role FROM `Menu` r1 where r1.MenuId = '$mId')  t 
	left join `Role` r2 on t.Verifier_Role = r2.Role 
	left join `Role` r3 on t.Approver_Role = r3.Role";

	$result = mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($result);
	$verifier_role_id = $row["verifier_role_id"];
	$approver_role_id = $row["approver_role_id"];

	$verifierMobile = "";
	$sql2 = "select e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $verifier_role_id ";
	$result2 = mysqli_query($conn,$sql2);
	$i=0;
	while ($row2 = mysqli_fetch_assoc($result2)) {
		$verifierMobile .= $row2["EmpId"];
		if($i<count($result2)-1){
			$verifierMobile .= ",";
 		}
		$i++;
	}

	$approverMobile = "";
	$sql3 = "select e2.EmpId from (SELECT e.EmpId, e.State, Tenent_Id FROM Employees e where e.EmpId = '$empId') t join Employees e2 on t.State = e2.State where e2.Tenent_Id = t.Tenent_id and e2.RoleId = $approver_role_id ";
	$result3 = mysqli_query($conn,$sql3);
	$ii=0;
	while ($row3 = mysqli_fetch_assoc($result3)) {
		$approverMobile .= $row3["EmpId"];
		if($ii<count($result3)-1){
			$approverMobile .= ",";
 		}
		$ii++;
	}
	$geolocation=$req->latLong;
	 $activitySql = "Insert into Activity(DId,MappingId,EmpId,MenuId,LocationId,Event,GeoLocation,Distance,MobileDateTime)"
					." values ('$dId','$mapId','$empId','$mId','$lId','$event','$geolocation','$distance','$date1')";
	if(mysqli_query($conn,$activitySql)){
		$activityId = mysqli_insert_id($conn);
	}
	
	if($checklist != null && count($checklist) != 0){
		$insertMapping = "INSERT INTO `Mapping`(`EmpId`, `MenuId`, `LocationId`, `Verifier`, `Approver`, `Start`, `End`, `ActivityId`, 
		`Tenent_Id`, `CreateDateTime`) values ('$empId', '$mId', '$lId', '$verifierMobile', '$approverMobile', curdate(), curdate(), 
		'$activityId', '$tenentId', current_timestamp) ";
		mysqli_query($conn,$insertMapping);

		if($actId == null  && $actId == ''){
			$insertInTransHdr="INSERT INTO `TransactionHDR` (`ActivityId`,`Status`) VALUES ('$activityId','Created')";
			
			if(mysqli_query($conn,$insertInTransHdr)){
				$lastTransHdrId = $conn->insert_id;
				for($ii=0;$ii<count($checklist);$ii++){
					$loopObj = $checklist[$ii];
					$answer = $loopObj->value;
					$chkp_idArray=explode("_",$loopObj->Chkp_Id);

					if(count($chkp_idArray) > 1){
						$chkp_id = $chkp_idArray[1];
					}
					else{
						$chkp_id = $chkp_idArray[0];
					}	
					
					$dependent=$v['Dependent'];
					if($dependent == ""){
						$dependent = 0;
					}
					
					$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
					mysqli_query($conn,$insertInTransDtl);
				}
				
			}
		}
		else{
			foreach($checklist as $k=>$v)
			{
				$answer=$v['value'];
				$chkp_idArray=explode("_",$v['Chkp_Id']);

				if(count($chkp_idArray) > 1){
					$chkp_id = $chkp_idArray[1];
				}
				else{
					$chkp_id = $chkp_idArray[0];
				}	
				
				$dependent=$v['Dependent'];
				if($dependent == ""){
					$dependent = 0;
				}
				
				$insertInTransDtl="INSERT INTO `TransactionDTL` (`ActivityId`, `ChkId`, `Value`, `DependChkId`) VALUES ($activityId, '$chkp_id', '$answer','$dependent')";
				mysqli_query($conn,$insertInTransDtl);
				
			}
			
		}

			
	}
	//Change in Mapping table from now onwards
	if($assignId != ""){
		$updateAssignTaskSql = "Update Mapping set ActivityId = '$activityId' where MappingId = $assignId";
		mysqli_query($conn,$updateAssignTaskSql);

	}
	if($actId != null && $actId != ''){
		$selectTransHdrSql = "Select * from TransactionHDR  where ActivityId = $actId";
		$selectTransHdrResult = mysqli_query($conn,$selectTransHdrSql);
		$thRow=mysqli_fetch_array($selectTransHdrResult);
		if($thRow['Status'] == 'Created'){
				$updateTransHdrSql = "Update TransactionHDR set Status = 'Verified',VerifierActivityId = '$activityId' where ActivityId = $actId";
		}
		else if($thRow['Status'] == 'Verified'){
			$updateTransHdrSql = "Update TransactionHDR set Status = 'Approved',ApproverActivityId = '$activityId' where ActivityId = $actId";
		}
		
		mysqli_query($conn,$updateTransHdrSql);
	}
	
	$output = new StdClass;
	if($lastTransHdrId != ""){
		$output -> error = "200";
		$output -> message = "success";
		$output -> TransID = "$activityId";
		$output -> reqJson = $jsonData;
	}
	else{
		$output -> error = "0";
		$output -> message = "something wrong";
		$output -> TransID = "$activityId";
		$output -> reqJson = $jsonData;
	}
	echo json_encode($output);
	
}



?>