<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$loginEmpRoleId = $jsonData->loginEmpRoleId;
$tenentId = $jsonData->tenentId;
$menuId = $jsonData->menuId;
$subCatMenuId = $jsonData->subCatMenuId;
$captionMenuId = $jsonData->captionMenuId;
$filterEmployeeId = $jsonData->filterEmployeeId;
$filterTransactionId = $jsonData->filterTransactionId;
$filterStartDate = $jsonData->filterStartDate;
$filterEndDate = $jsonData->filterEndDate;
$level = $jsonData->level;


$empList = [];
if($loginEmpRoleId == '4'){
	$empSql = "SELECT * FROM `Employees` WHERE `Tenent_Id` = $tenentId and `Active` = 1";
	$empQuery=mysqli_query($conn,$empSql);
	if(mysqli_num_rows($empQuery) !=0){
		while($row11 = mysqli_fetch_assoc($empQuery)){
			array_push($empList,$row11["EmpId"]);
		}
	}

}
else{
	array_push($empList,$loginEmpId);
}

$loginEmpId = implode("','", $empList);

if($level == 2){
	$menuId = $subCatMenuId;
}
else if($level == 3){
	$menuId = $captionMenuId;
}

$columnArr = [];
$chkIdArr = [];
$sql = "SELECT * FROM `Dynamic_Column_Header` where `Menu_Id` = $menuId and `Is_Active` = 1 ORDER BY `Display_Order`";
$query = mysqli_query($conn,$sql);
$rowCount=mysqli_num_rows($query);
if($rowCount == 0){
	array_push($columnArr,"Transaction id");
	array_push($columnArr,"Employee name");
	array_push($columnArr,"Submit datetime");
}
else{
	while($row = mysqli_fetch_assoc($query)){
		$colTi = $row["Column_Title"];
		$colTy = $row["Column_Type"];
		if($colTy == 'chkId'){
			$chkIdArr = explode(",", $colTi);
			foreach ($chkIdArr as $key => $value) {
				$chkId = $value;
				$sql2 = "SELECT `Description` FROM `Checkpoints` where `CheckpointId` = $chkId  ";
				$query2=mysqli_query($conn,$sql2);
				while($row2 = mysqli_fetch_assoc($query2)){
					array_push($columnArr,$row2["Description"]);
				}	
			}
		}
		else{
			array_push($columnArr,$colTi);
		}
	}
}

$wrappedList = array();
$sql = "SELECT * FROM `MenuTransaction` where `MenuId` = $menuId";
$result = mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
	$transId = $row["Transaction id"];
	$json = new StdClass;
	foreach ($columnArr as $key => $column) {
		$json -> $column = $row[$column]; 
		foreach ($chkIdArr as $key => $chkId) {
			$sql2 = "SELECT d.*, c.Description FROM TransactionDTL d join Checkpoints c on d.ChkId = c.CheckpointId where d.ActivityId = $transId and d.ChkId = $chkId";
			$query2 = mysqli_query($conn,$sql2);
			while ($row2 = mysqli_fetch_assoc($query2)) {
				$desc = $row2["Description"];
				$fillValue = $row2["Value"];
				if($column == $desc){
					$json -> $column = $fillValue; 
				}
			}
		}
	}
	// foreach ($chkIdArr as $key => $value) {
	// 	$chkId = $value;
	// 	$sql2 = "SELECT d., c.Description FROM TransactionDTL d join Checkpoints c on d.ChkId = c.CheckpointId where d.ActivityId = $transId and d.ChkId = $chkId";
	// 	$query2 = mysqli_query($conn,$sql2);
	// 	$row2 = mysqli_fetch_assoc($query2);
	// 	foreach ($row2 as $key => $value) {
	// 		// code...
	// 	}
	// 	while($row2 = mysqli_fetch_assoc($query2)){
	// 		// array_push($columnArr,$row2["Description"]);

	// 		$json -> $row2["Description"] = $row2["Value"]; 
	// 	}	
	// }
	array_push($wrappedList, $json);
}
$output = array(
	'columnArr' => $columnArr, 
	'wrappedList' => $wrappedList
);
echo json_encode($output);

// $topThreeCheckpointsList = [];
// $sql1 = "select distinct `CheckpointId` from `Menu` where `MenuId` in ($menuId)";
// $query1=mysqli_query($conn,$sql1);
// while($row1 = mysqli_fetch_assoc($query1)){
// 	$cId = $row1["CheckpointId"];
// 	$cId = str_replace(":", ",", $cId);
// 	// echo $cId;
// 	$explodeCid = explode(",", $cId);

// 	for($i = 0;$i<count($explodeCid); $i++){
// 		$loopCId = $explodeCid[$i];
// 		$sql2 = "SELECT `Description` FROM `Checkpoints` where `CheckpointId` = $loopCId  ";
// 		$query2=mysqli_query($conn,$sql2);
// 		while($row2 = mysqli_fetch_assoc($query2)){
// 			$json = new StdClass;
// 			$json = array('checkpointId' => $loopCId, 'description' => $row2["Description"]);
// 			array_push($topThreeCheckpointsList,$json);
// 		}

// 	}
// }
// $topFirstCheckpointDesc = "";
// $topSecondCheckpointDesc = "";
// $topThirdCheckpointDesc = "";

// $topFirstCheckpointId = "";
// $topSecondCheckpointId = "";
// $topThirdCheckpointId = "";

// for($a=0;$a<count($topThreeCheckpointsList);$a++){
// 	$checkDesc = $topThreeCheckpointsList[$a]["description"];
// 	$checkId = $topThreeCheckpointsList[$a]["checkpointId"];
// 	// echo $checkDesc;
// 	if($a == 0){
// 		$topFirstCheckpointDesc = $checkDesc;
// 		$topFirstCheckpointId = $checkId;
// 	}
// 	else if($a == 1){
// 		$topSecondCheckpointDesc = $checkDesc;
// 		$topSecondCheckpointId = $checkId;
// 	}
// 	else if($a == 2){
// 		$topThirdCheckpointDesc = $checkDesc;
// 		$topThirdCheckpointId = $checkId;
// 	}
// }

// $output = array();
// $wrappedList = [];

// $unionSql = "select DISTINCT t.`ActivityId` from (
// SELECT `ActivityId` FROM `Mapping` where (`Mapping`.`EmpId` in ('$loginEmpId') OR `Mapping`.`Verifier` in ('$loginEmpId') OR `Mapping`.`Approver` in ('$loginEmpId')) and `Mapping`.`MenuId` = $menuId and `Mapping`.`ActivityId` != 0
// UNION
// select `ActivityId` from `Activity` where `Activity`.`EmpId` in ('$loginEmpId') and `Activity`.`MenuId` = $menuId and `Activity`.`Event` = 'Submit') t";


// $sql = "SELECT distinct `h`.`ActivityId`, `h`.`ServerDateTime`, `h`.`Status`, `h`.`VerifierActivityId`, 
// `h`.`ApproverActivityId`, `a`.`EmpId` as fillingByEmpId, `e`.`Name` as fillerByEmpName, a.GeoLocation as fillingByLatlong, `a1`.`MenuId`, `a1`.`EmpId` as verifiedByEmpId, 
// `e1`.`Name` as verifiedByEmpName, a1.GeoLocation as verifiedByLatlong, `a1`.`ServerDateTime` as verifiedDate, `a2`.`EmpId` as approvedByEmpId, 
// `e2`.`Name` as approvedByEmpName, a2.GeoLocation as approvedByLatlong, `a2`.`ServerDateTime` as approvedDate FROM `TransactionHDR` h 
// join `Activity` a on `h`.`ActivityId` = `a`.`ActivityId`
// left join `Activity` a1 on `h`.`VerifierActivityId` = `a1`.`ActivityId` 
// left join `Activity` a2 on `h`.`ApproverActivityId` = `a2`.`ActivityId`
// left join `Employees` e on `a`.`EmpId` = `e`.`EmpId` 
// left join `Employees` e1 on `a1`.`EmpId` = `e1`.`EmpId` 
// left join `Employees` e2 on `a2`.`EmpId` = `e2`.`EmpId` 
// where `h`.`ActivityId` in ($unionSql) order by `h`.`ActivityId` desc";

// // echo $sql;

// $query=mysqli_query($conn,$sql);
// while($row = mysqli_fetch_assoc($query)){
// 	$activityId = $row["ActivityId"];
// 	$serverDateTime = $row["ServerDateTime"];
// 	$verifierActivityId = $row["VerifierActivityId"];
// 	$approverActivityId = $row["ApproverActivityId"];
// 	$verifiedByEmpName = $row["verifiedByEmpName"];
// 	$verifiedDate = $row["verifiedDate"];
// 	$approvedByEmpName = $row["approvedByEmpName"];
// 	$approvedDate = $row["approvedDate"];
// 	$status = $row["Status"];


// 	$fillingByEmpId = $row["fillingByEmpId"];
// 	$fillerByEmpName = $row["fillerByEmpName"];
// 	$fillingByLatlong = $row["fillingByLatlong"];

// 	$verifiedByEmpId = $row["verifiedByEmpId"];
// 	$verifiedByEmpName = $row["verifiedByEmpName"];
// 	$verifiedByLatlong = $row["verifiedByLatlong"];

// 	$approvedByEmpId = $row["approvedByEmpId"];
// 	$approvedByEmpName = $row["approvedByEmpName"];
// 	$approvedByLatlong = $row["approvedByLatlong"];

// 	$topFirstCheckpointValue = "";
// 	$topSecondCheckpointValue = "";
// 	$topThirdCheckpointValue = "";

// 	$transactionDetList = prepareTransactionDet($conn, $activityId);
// 	for($b=0;$b<count($transactionDetList);$b++){
// 		if($topFirstCheckpointId == $transactionDetList[$b]->checkpointId){
// 			$topFirstCheckpointValue = $transactionDetList[$b]->value;
// 		}
// 		if($topSecondCheckpointId == $transactionDetList[$b]->checkpointId){
// 			$topSecondCheckpointValue = $transactionDetList[$b]->value;
// 		}
// 		if($topThirdCheckpointId == $transactionDetList[$b]->checkpointId){
// 			$topThirdCheckpointValue = $transactionDetList[$b]->value;
// 		}
// 	}


// 	$isVerifierExist = false;
// 	$isApproverExist = false;
// 	// if($verifierActivityId == null){
// 		$mappingSql = "SELECT * FROM `Mapping` where `ActivityId` = '$activityId' and `MenuId` = '$menuId' ";
// 		$mappingQuery = mysqli_query($conn,$mappingSql);
// 		$mappingRow = mysqli_fetch_assoc($mappingQuery);

// 		if($mappingRow["Verifier"] != null && $mappingRow["Verifier"] != ""){
// 			$isVerifierExist = true;
// 		}
// 		if($mappingRow["Approver"] != null && $mappingRow["Approver"] != ""){
// 			$isApproverExist = true;
// 		}

// 	// }

// 	$isVerifier = false;
// 	if($verifierActivityId == null){
// 		$verifierSql = "SELECT * FROM `Mapping` where `ActivityId` = '$activityId' and `MenuId` = '$menuId' and `Verifier` in ('$loginEmpId') ";
// 		$verifierQuery=mysqli_query($conn,$verifierSql);
// 		if(mysqli_num_rows($verifierQuery) !=0){
// 			$isVerifier = true;
// 		}
// 	}
	
// 	$isApprover = false;
// 	if($approverActivityId == null){
// 		$approverSql = "SELECT * FROM `Mapping` where `ActivityId` = '$activityId' and `MenuId` = '$menuId' and `Approver` in ('$loginEmpId') ";
// 		$approverQuery=mysqli_query($conn,$approverSql);
// 		if(mysqli_num_rows($approverQuery) !=0){
// 			$isApprover = true;
// 		}
// 	}

// 	$pendingForApprove = "Yes";
// 	$pendingForVerify = "Yes";

// 	$myRoleForTask = "";
// 	if($isVerifier){
// 		$myRoleForTask = "Verifier";
// 	}
// 	else if($isApprover){
// 		$myRoleForTask = "Approver";
// 	}

// 	if(($isVerifierExist || $isVerifier) && $status == "Created"){
// 		$pendingForVerify = "No";
// 	}
// 	if(($isApproverExist || $isApprover) && ($status == "Created" || $status == "Verified")){
// 		$pendingForApprove = "No";
// 	}

// 	if(!$isVerifierExist)
// 		$pendingForVerify = "NA";

// 	if(!$isApproverExist)
// 		$pendingForApprove = "NA";

	
// 	$json = new StdClass;
// 	$json -> pendingForApprove = $pendingForApprove;
// 	$json -> menuId = $menuId;
// 	$json -> transactionId = $activityId;
// 	$json -> verifierTId = $verifierActivityId;
// 	$json -> approvedTId = $approverActivityId;
// 	$json -> dateTime = $serverDateTime;
// 	$json -> approveDetList = [];
// 	$json -> myRoleForTask = $myRoleForTask;
// 	$json -> transactionDetList = $transactionDetList;
// 	$json -> topFirstCheckpointDesc = $topFirstCheckpointDesc;
// 	$json -> topThirdCheckpointDesc = $topThirdCheckpointDesc;
// 	$json -> fillingByEmpId = $fillingByEmpId;
// 	$json -> fillingBy = $fillerByEmpName;
// 	$json -> fillingByLatlong = $fillingByLatlong;
// 	$json -> verifiedByEmpId = $verifiedByEmpId;
// 	$json -> verifiedBy = $verifiedByEmpName;
// 	$json -> verifiedByLatlong = $verifiedByLatlong;
// 	$json -> approvedByEmpId = $approvedByEmpId;
// 	$json -> approvedBy = $approvedByEmpName;
// 	$json -> approvedByLatlong = $approvedByLatlong;
// 	$json -> verifiedDate = $verifiedDate;
// 	$json -> approvedDate = $approvedDate;
// 	$json -> topFirstKey = "topFirstCheckpointValue";
// 	$json -> topSecondCheckpointValue = $topSecondCheckpointValue;
// 	$json -> actionCheckpointList = [];
// 	$json -> verifyDetList = [];
// 	$json -> topSecondKey = "topSecondCheckpointValue";
// 	$json -> topFirstCheckpointValue = $topFirstCheckpointValue;
// 	$json -> pendingForVerify = $pendingForVerify;
// 	$json -> topSecondCheckpointDesc = $topSecondCheckpointDesc;
// 	$json -> topThirdCheckpointValue = $topThirdCheckpointValue;
// 	$json -> topThirdKey = "topThirdCheckpointValue";
// 	$json -> status = $status;
	
// 	array_push($wrappedList,$json);

// }

// $output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000', 'count' => $level);
// echo json_encode($output);
?>

<?php
// function prepareTransactionDet($conn, $transId){
// 	$sql = "SELECT `Checkpoints`.`CheckpointId`, `Checkpoints`.`Description`, `TransactionDTL`.`Value`, `TransactionDTL`.`DependChkId`, `Checkpoints`.`Value` as cp_options, `Checkpoints`.`TypeId` FROM  `TransactionDTL` join `Checkpoints` on  `TransactionDTL`.`ChkId` = `Checkpoints`.`CheckpointId`  WHERE `TransactionDTL`.`ActivityId` = $transId ";
// 	$query=mysqli_query($conn,$sql);

// 	$transactionDetList = [];
// 	while($row = mysqli_fetch_assoc($query)){
// 		$json = new StdClass;
// 		$json -> checkpointId = $row["CheckpointId"];
// 		$json -> checkpoint = $row["Description"];
// 		$json -> value = $row["Value"];
// 		$json -> typeId = $row["TypeId"];
// 		array_push($transactionDetList,$json);
// 	}
// 	return $transactionDetList;

// }

?>