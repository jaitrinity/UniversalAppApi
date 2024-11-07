<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != 'POST'){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$loginEmpId = $jsonData->loginEmpId;
$menuId = $jsonData->menuId;
$transactionId = $jsonData->transactionId;
$verifierTId = $jsonData->verifierTId;
$approvedTId = $jsonData->approvedTId;
$status = $jsonData->status;

$cpSql = "SELECT `CheckpointId`, `Verifier`, `Approver` FROM `Menu` where `MenuId`=$menuId";
$cpQuery=mysqli_query($conn,$cpSql);
while($cpRow = mysqli_fetch_assoc($cpQuery)){
	$cpId = str_replace(":", ",", $cpRow["CheckpointId"]);
	$verifierCpId = str_replace(":", ",", $cpRow["Verifier"]);
	$approverCpId = str_replace(":", ",", $cpRow["Approver"]);
}

$depId = "";
$depSql = "SELECT `Logic`  FROM `Checkpoints` WHERE `CheckpointId` IN ($cpId) and `Dependent`=1";

$depQuery=mysqli_query($conn,$depSql);
while($depRow = mysqli_fetch_assoc($depQuery)){
	$depId .= str_replace(":", ",", $depRow["Logic"]);
}

$allCp = $cpId.''.$depSql;
// echo $allCp.'--';


$doneDetList = [];
// $flowActList=array();
// $doneSql="SELECT `FlowCheckpointId`, `FlowActivityId` FROM `FlowActivityMaster` where `ActivityId` = '$transactionId' and `FlowActivityId` is not null";

$doneSql = "SELECT '$verifierCpId' as `FlowCheckpointId`,  `VerifierActivityId` as `FlowActivityId` FROM `TransactionHDR` where `ActivityId`=$transactionId and `VerifierActivityId` is not null
UNION
SELECT '$approverCpId' as `FlowCheckpointId`, `ApproverActivityId` as `FlowActivityId` FROM `TransactionHDR` where `ActivityId`=$transactionId and `ApproverActivityId` is not null";

// echo $doneSql;


$doneQuery=mysqli_query($conn,$doneSql);
while($doneRow = mysqli_fetch_assoc($doneQuery)){
	// $flowChkId = $doneRow["FlowCheckpointId"];
	$flowActId = $doneRow["FlowActivityId"];
	// array_push($flowActList, $flowActId);
	$flowDetList = doneStatusDet($conn,$flowActId);
	$flowJson = array(
		'flowActId' => $flowActId,
		'flowDetList' => $flowDetList
	);
	array_push($doneDetList, $flowJson);
}


$verifierCheckpointIdList = "";
$myRoleForTask = "";
$isVerifier = false;
$locationId = "";
if($verifierTId == null){
	$verifierSql = "SELECT * FROM `Mapping` where `ActivityId` = '$transactionId' and `MenuId` = '$menuId' and `Verifier` = '$loginEmpId' ";
	$verifierQuery=mysqli_query($conn,$verifierSql);
	while($row1 = mysqli_fetch_assoc($verifierQuery)){
		$locationId = $row1["LocationId"];
	}
	// echo mysqli_num_rows($verifierQuery);
	if(mysqli_num_rows($verifierQuery) !=0){
		$isVerifier = true;
		if($isVerifier){
			$verifierCheckpointIdList = getVerifierAndApproverCheckpointId($conn,$menuId,'Verifier');
		}
		// $verifyDetList = getVerifierCheckpoint($conn, $menuId);
	}
}


$approverCheckpointIdList = "";
$isApprover = false;
if($approvedTId == null){
	$approverSql = "SELECT * FROM `Mapping` where `ActivityId` = '$transactionId' and `MenuId` = '$menuId' and `Approver` = '$loginEmpId' ";
	$approverQuery=mysqli_query($conn,$approverSql);
	if($locationId == ""){
		while($row1 = mysqli_fetch_assoc($approverQuery)){
			$locationId = $row1["LocationId"];
		}
	}
		
	if(mysqli_num_rows($approverQuery) !=0){
		$isApprover = true;
		if($isApprover){
			$approverCheckpointIdList = getVerifierAndApproverCheckpointId($conn,$menuId,'Approver');
		}
		// $approveDetList = getApproverCheckpoint($conn, $menuId);
	}
}

// $sql = "SELECT `Checkpoints`.`CheckpointId`, `Checkpoints`.`Description`, `TransactionDTL`.`Value`, `TransactionDTL`.`DependChkId`, `Checkpoints`.`Value` as cp_options, `Checkpoints`.`TypeId` FROM  `TransactionDTL` join `Checkpoints` on  `TransactionDTL`.`ChkId` = `Checkpoints`.`CheckpointId`  WHERE `TransactionDTL`.`ActivityId` = '$transactionId' order by `TransactionDTL`.`SRNo` ";
$sql = "SELECT c.`CheckpointId`, c.`Description`, d.`Value`, d.`DependChkId`, c.`Value` as cp_options, c.`TypeId`, d.`Date_time` FROM  `TransactionDTL` d join `Checkpoints` c on  d.`ChkId` = c.`CheckpointId`  WHERE d.`ActivityId` = '$transactionId' order by d.`SRNo` ";

// $sql = "SELECT c.`CheckpointId`, c.`Description`, d.`Value`, d.`DependChkId`, c.`Value` as cp_options, c.`TypeId` FROM Checkpoints c left join TransactionDTL d on c.CheckpointId=d.ChkId and d.ActivityId=$transactionId WHERE c.CheckpointId IN ($allCp) ORDER by FIELD(c.CheckpointId,$allCp)";
// echo $sql.'--';
$query=mysqli_query($conn,$sql);

$dependCheckpointDetList = [];
while($roww = mysqli_fetch_assoc($query)){
	$checkpointIdd = $roww["CheckpointId"];
	$descriptionn = $roww["Description"];
	$valuee = $roww["Value"];
	$typeIdd = $roww["TypeId"];
	$dependChkIdd = $roww["DependChkId"];
	$dateTimee = $row["Date_time"];
	if($dependChkIdd != 0){
		$jsonDett = new StdClass;
		$jsonDett -> checkpointId = $checkpointIdd;
		$jsonDett -> checkpoint = $descriptionn;
		$jsonDett -> value = $valuee;
		$jsonDett -> typeId = $typeIdd;
		$jsonDett -> dependChkId = $dependChkIdd;
		$jsonDett -> dateTime = $dateTimee;
		
		array_push($dependCheckpointDetList,$jsonDett);
	}
}

mysqli_data_seek( $query, 0 );

$transactionDetList = [];

// this while is for show dependent checkpoint in new line with . seperater
$sr = 1;
while($row = mysqli_fetch_assoc($query)){
	$checkpointId = $row["CheckpointId"];
	$description = $row["Description"];
	$value = $row["Value"];
	$dependChkId = $row["DependChkId"];
	$cp_options = $row["cp_options"];
	$typeId = $row["TypeId"];
	$dateTime = $row["Date_time"];

	$forVerifier = "No";
	$forApprover = "No";
	if($verifierCheckpointIdList != "") $forVerifier = "Yes";
	if($approverCheckpointIdList != "") $forApprover = "Yes";

	if($dependChkId == 0){
		$jsonDet = new StdClass;
		if($typeId == 17){
			$jsonDet -> srNumber = "";
		}
		else{
			$jsonDet -> srNumber = $sr;
		}
		
		// $jsonDet -> srNumber = $checkpointId;
		$jsonDet -> checkpointId = $checkpointId;
		$jsonDet -> checkpoint = $description;
		// $jsonDet -> options = "";
		$jsonDet -> value = $value;
		$jsonDet -> typeId = $typeId;
		$jsonDet -> dateTime = $dateTime;
		$jsonDet -> forVerifier = $forVerifier;
		$jsonDet -> forApprover = $forApprover;

		array_push($transactionDetList,$jsonDet);

		$depSrNo = 1;
		for($j=0;$j<count($dependCheckpointDetList);$j++){
			$dependentChpId = $dependCheckpointDetList[$j]->checkpointId;
			$dependentChp = $dependCheckpointDetList[$j]->checkpoint;
			$dependenChpValue = $dependCheckpointDetList[$j]->value;
			$dependenTypeId = $dependCheckpointDetList[$j]->typeId;
			$dependenDependChkId = $dependCheckpointDetList[$j]->dependChkId;
			$dependenDateTime = $dependCheckpointDetList[$j]->dateTime;

			if($checkpointId == $dependenDependChkId){
				$jsonDettt = new StdClass;
				if($dependenTypeId == 17){
					$jsonDettt -> srNumber = "";
				}
				else{
					$jsonDettt -> srNumber = $sr.'.'.$depSrNo;
				}
				
				// $jsonDettt -> srNumber = $checkpointId.'.'.$dependentChpId;
				$jsonDettt -> checkpointId = $dependentChpId;
				$jsonDettt -> checkpoint = $dependentChp;
				$jsonDettt -> value = $dependenChpValue;
				$jsonDettt -> typeId = $dependenTypeId;
				$jsonDettt -> dateTime = $dependenDateTime;
				$jsonDettt -> forVerifier = $forVerifier;
				$jsonDettt -> forApprover = $forApprover;
				// $jsonDet -> dependenChpValue = $dependenChpValue;
				array_push($transactionDetList,$jsonDettt);

				if($dependenTypeId != 17){
					$depSrNo++;
				}
				
			}	
		}

		if($typeId != 17){
			$sr++;
		}
		
	}
}

$pendingForApprove = "No";
$pendingForVerify = "No";

$myRoleForTask = "";
if($isVerifier){
	$myRoleForTask = "Verifier";
}
else if($isApprover){
	$myRoleForTask = "Approver";
}
if($isVerifier && $status == "Created"){
	$pendingForApprove = "Yes";
	$pendingForVerify = "Yes";
}
if($isVerifier && $status == "Verified"){
	$pendingForApprove = "Yes";
}
if($isApprover && $status == "Created"){
	$pendingForApprove = "Yes";
	$pendingForVerify = "Yes";
}
if($isApprover && $status == "Verified"){
	$pendingForApprove = "Yes";
}


$verifyDetList = [];
$approveDetList = [];
if($verifierTId != null)
$verifyDetList = prepareStatusDet($conn,$verifierTId);
if($approvedTId != null)
$approveDetList = prepareStatusDet($conn,$approvedTId);

// $doneDetList = [];
// if(count($flowActList) !=0){
// 	$doneActIds = implode(",", $flowActList);
// 	$doneDetList = doneStatusDet($conn,$doneActIds);
// }


$actionCheckpointList = [];
if($pendingForVerify == "Yes" && $verifierCheckpointIdList != ""){
	$actionCheckpointList = prepareActionCheckpointDet($conn, $verifierCheckpointIdList);
}
else if($pendingForVerify == "No" && $pendingForApprove == "Yes" && $approverCheckpointIdList != ""){
	$actionCheckpointList = prepareActionCheckpointDet($conn, $approverCheckpointIdList);
}

$output = array();
$wrappedList = [];

$json = new StdClass;
$json -> pendingForApprove = $pendingForApprove;
$json -> menuId = $menuId;
$json -> transactionId = $activityId;
$json -> dateTime = $serverDateTime;
$json -> myRoleForTask = $myRoleForTask;
$json -> transactionDetList = $transactionDetList;
$json -> verifyDetList = $verifyDetList;
$json -> approveDetList = $approveDetList;
$json -> doneDetList = $doneDetList;
// $json -> topFirstCheckpointDesc = "";
// $json -> topThirdCheckpointDesc = "";
// $json -> verifiedBy = "";
// $json -> approvedBy = "";
// $json -> topFirstKey = "";
// $json -> topSecondCheckpointValue = "";
$json -> actionCheckpointList = $actionCheckpointList;
// $json -> topSecondKey = "";
// $json -> topFirstCheckpointValue = "";
$json -> pendingForVerify = $pendingForVerify;
$json -> locationId = $locationId;
// $json -> verifierTId = "";
// $json -> approvedTId = "";
// $json -> topSecondCheckpointDesc = "";
// $json -> topThirdCheckpointValue = "";
// $json -> topThirdKey = "";
// $json -> status = "";

array_push($wrappedList,$json);

$output = array('wrappedList' => $wrappedList, 'responseDesc' => 'SUCCESSFUL', 'responseCode' => '100000', 'count' => $level);
echo json_encode($output);
?>

<?php
function prepareActionCheckpointDet($conn, $commaSeparateCp){
	$actionCheckpointList = [];
	$explodeVcp = implode(',',explode(",", $commaSeparateCp));
	$checkpointSql = "SELECT * FROM `Checkpoints` where `CheckpointId` in ($explodeVcp) ";
	// echo $checkpointSql;
	$checkpointQuery=mysqli_query($conn,$checkpointSql);
	$sr = 1;
	while($checkpointRow = mysqli_fetch_assoc($checkpointQuery)){
		$checkpointId = $checkpointRow["CheckpointId"];
		// echo $checkpointId;
		$description = $checkpointRow["Description"];
		$value = $checkpointRow["Value"];
		$typeId = $checkpointRow["TypeId"];
		$dependent = $checkpointRow["Dependent"];
		$logic = $checkpointRow["Logic"];
		$size = $checkpointRow["Size"];

		$logicCpArr = array();
		if($dependent == 1){
			$logicCheckpoint = explode(":", $logic);
			for($j=0;$j<count($logicCheckpoint);$j++){
				if($logicCheckpoint[$j] != " "){
					$explodeLogicCheckpoint = explode(",", $logicCheckpoint[$j]);
					for($jj=0;$jj<count($logicCheckpoint);$jj++){
						$logicCpSql = "SELECT * FROM `Checkpoints` where `CheckpointId` in (".$logicCheckpoint[$jj].")  ";
						$logicCpQuery=mysqli_query($conn,$logicCpSql);
						while($logicCpRow = mysqli_fetch_assoc($logicCpQuery)){
							$logicJson = array(
								'checkpointId' => $logicCpRow["CheckpointId"],
								'description' => $logicCpRow["Description"],
								'value' => $logicCpRow["Value"],
								'typeId' => $logicCpRow["TypeId"]

							);
							array_push($logicCpArr,$logicJson);
						}
					}
				}
					
			}
				
		}

		$jsonDet = new StdClass;
		$jsonDet -> srNumber = $sr;
		$jsonDet -> typeId = $typeId;
		$jsonDet -> checkpointId = $checkpointId;
		$jsonDet -> checkpoint = $description;
		$jsonDet -> value = $value;
		$jsonDet -> size = $size;
		$jsonDet -> logic = $logic;
		$jsonDet -> logicCpArr = $logicCpArr;
		
		array_push($actionCheckpointList,$jsonDet);

		$sr++;
	}

	return $actionCheckpointList;
}

function doneStatusDet($conn, $transId){
	$sql = "SELECT c.`CheckpointId`, c.`Description`, d.`Value`, d.`DependChkId`, c.`Value` as cp_options, c.`TypeId`, d.`Date_time` FROM  `TransactionDTL` d join `Checkpoints` c on  d.`ChkId` = c.`CheckpointId`  WHERE d.`ActivityId` in ($transId) order by d.`SRNo`";

	$query=mysqli_query($conn,$sql);

	$dependCheckpointDetList = [];
	while($roww = mysqli_fetch_assoc($query)){
		$checkpointIdd = $roww["CheckpointId"];
		$descriptionn = $roww["Description"];
		$valuee = $roww["Value"];
		$typeIdd = $roww["TypeId"];
		$dependChkIdd = $roww["DependChkId"];
		$dateTimee = $roww["Date_time"];
		if($dependChkIdd != 0){
			$jsonDett = new StdClass;
			$jsonDett -> checkpointId = $checkpointIdd;
			$jsonDett -> checkpoint = $descriptionn;
			$jsonDett -> value = $valuee;
			$jsonDett -> typeId = $typeIdd;
			$jsonDett -> dependChkId = $dependChkIdd;
			$jsonDett -> dateTime = $dateTimee;
			
			array_push($dependCheckpointDetList,$jsonDett);
		}
	}

	mysqli_data_seek( $query, 0);

	

	$statusDetList = [];
	$sr = 1;
	while($row = mysqli_fetch_assoc($query)){

		$dependChkId = $row["DependChkId"];
		if($dependChkId == 0){
			$json = new StdClass;
			$json -> srNumber = $sr;
			$json -> typeId = $row["TypeId"];
			$json -> checkpointId = $row["CheckpointId"];
			$json -> checkpoint = $row["Description"];
			// $json -> forApprover = "";
			// $json -> forVerifier = "";
			$json -> options = $row["cp_options"];
			$json -> value = $row["Value"];
			$json -> dateTime = $row["Date_time"];

			array_push($statusDetList,$json);

			$depSrNo = 1;
			for($j=0;$j<count($dependCheckpointDetList);$j++){
				$dependentChpId = $dependCheckpointDetList[$j]->checkpointId;
				$dependentChp = $dependCheckpointDetList[$j]->checkpoint;
				$dependenChpValue = $dependCheckpointDetList[$j]->value;
				$dependenTypeId = $dependCheckpointDetList[$j]->typeId;
				$dependenDependChkId = $dependCheckpointDetList[$j]->dependChkId;

				if($row["CheckpointId"] == $dependenDependChkId){
					$jsonDettt = new StdClass;
					$jsonDettt -> srNumber = $sr.'.'.$depSrNo;
					// $jsonDettt -> srNumber = $checkpointId.'.'.$dependentChpId;
					$jsonDettt -> checkpointId = $dependentChpId;
					$jsonDettt -> checkpoint = $dependentChp;
					$jsonDettt -> value = $dependenChpValue;
					$jsonDettt -> typeId = $dependenTypeId;
					// $jsonDettt -> forVerifier = $forVerifier;
					// $jsonDettt -> forApprover = $forApprover;
					// $jsonDet -> dependenChpValue = $dependenChpValue;
					array_push($statusDetList,$jsonDettt);

					$depSrNo++;
				}	
			}

			$sr++;
		}		

	}

	return $statusDetList;

}

function prepareStatusDet($conn, $transId){
	$sql = "SELECT c.`CheckpointId`, c.`Description`, d.`Value`, d.`DependChkId`, c.`Value` as cp_options, c.`TypeId`, d.`Date_time` FROM  `TransactionDTL` d join `Checkpoints` c on  d.`ChkId` = c.`CheckpointId`  WHERE d.`ActivityId` = $transId order by d.`SRNo` ";

	$query=mysqli_query($conn,$sql);

	$dependCheckpointDetList = [];
	while($roww = mysqli_fetch_assoc($query)){
		$checkpointIdd = $roww["CheckpointId"];
		$descriptionn = $roww["Description"];
		$valuee = $roww["Value"];
		$typeIdd = $roww["TypeId"];
		$dependChkIdd = $roww["DependChkId"];
		$dateTimee = $roww["Date_time"];
		if($dependChkIdd != 0){
			$jsonDett = new StdClass;
			$jsonDett -> checkpointId = $checkpointIdd;
			$jsonDett -> checkpoint = $descriptionn;
			$jsonDett -> value = $valuee;
			$jsonDett -> typeId = $typeIdd;
			$jsonDett -> dependChkId = $dependChkIdd;
			$jsonDett -> dateTime = $dateTimee;
			
			array_push($dependCheckpointDetList,$jsonDett);
		}
	}

	mysqli_data_seek( $query, 0);

	

	$statusDetList = [];
	$sr = 1;
	while($row = mysqli_fetch_assoc($query)){

		$dependChkId = $row["DependChkId"];
		if($dependChkId == 0){
			$json = new StdClass;
			$json -> srNumber = $sr;
			$json -> typeId = $row["TypeId"];
			$json -> checkpointId = $row["CheckpointId"];
			$json -> checkpoint = $row["Description"];
			// $json -> forApprover = "";
			// $json -> forVerifier = "";
			$json -> options = $row["cp_options"];
			$json -> value = $row["Value"];
			$json -> dateTime = $row["Date_time"];

			array_push($statusDetList,$json);

			$depSrNo = 1;
			for($j=0;$j<count($dependCheckpointDetList);$j++){
				$dependentChpId = $dependCheckpointDetList[$j]->checkpointId;
				$dependentChp = $dependCheckpointDetList[$j]->checkpoint;
				$dependenChpValue = $dependCheckpointDetList[$j]->value;
				$dependenTypeId = $dependCheckpointDetList[$j]->typeId;
				$dependenDependChkId = $dependCheckpointDetList[$j]->dependChkId;

				if($row["CheckpointId"] == $dependenDependChkId){
					$jsonDettt = new StdClass;
					$jsonDettt -> srNumber = $sr.'.'.$depSrNo;
					// $jsonDettt -> srNumber = $checkpointId.'.'.$dependentChpId;
					$jsonDettt -> checkpointId = $dependentChpId;
					$jsonDettt -> checkpoint = $dependentChp;
					$jsonDettt -> value = $dependenChpValue;
					$jsonDettt -> typeId = $dependenTypeId;
					// $jsonDettt -> forVerifier = $forVerifier;
					// $jsonDettt -> forApprover = $forApprover;
					// $jsonDet -> dependenChpValue = $dependenChpValue;
					array_push($statusDetList,$jsonDettt);

					$depSrNo++;
				}	
			}

			$sr++;
		}		

	}

	return $statusDetList;

}

function getVerifierCheckpoint($conn, $menuId){
	$verifyDetList = [];
	$sql = "SELECT `Verifier` FROM `Menu` where `MenuId` = '$menuId' ";
	$query=mysqli_query($conn,$sql);
	// echo mysqli_num_rows($query);
	while($row = mysqli_fetch_assoc($query)){
		$verifierCheckpoint = $row["Verifier"];

		$explodeVcp = implode(',',explode(",", $verifierCheckpoint));
		$checkpointSql = "SELECT * FROM `Checkpoints` where `CheckpointId` in ($explodeVcp) ";
		$checkpointQuery=mysqli_query($conn,$checkpointSql);
		$sr = 1;
		while($checkpointRow = mysqli_fetch_assoc($checkpointQuery)){
			$checkpointId = $checkpointRow["CheckpointId"];
			// echo $checkpointId;
			$description = $checkpointRow["Description"];
			$value = $checkpointRow["Value"];
			$typeId = $checkpointRow["TypeId"];

			$jsonDet = new StdClass;
			$jsonDet -> srNumber = $sr;
			$jsonDet -> typeId = $typeId;
			$jsonDet -> checkpointId = $checkpointId;
			$jsonDet -> checkpoint = $description;
			$jsonDet -> value = $value;
			
			array_push($verifyDetList,$jsonDet);

			$sr++;
		}

	}

	return $verifyDetList;


}
function getApproverCheckpoint($conn, $menuId){
	$approveDetList = [];
	$sql = "SELECT `Approver` FROM `Menu` where `MenuId` = '$menuId' ";
	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$approverCheckpoint = $row["Approver"];

		$explodeAcp = implode(',',explode(",", $approverCheckpoint));

		$checkpointSql = "SELECT * FROM `Checkpoints` where `CheckpointId` in ($explodeAcp) ";
		$checkpointQuery=mysqli_query($conn,$checkpointSql);
		$sr = 1;
		while($checkpointRow = mysqli_fetch_assoc($checkpointQuery)){
			$checkpointId = $checkpointRow["CheckpointId"];
			$description = $checkpointRow["Description"];
			$value = $checkpointRow["Value"];
			$typeId = $checkpointRow["TypeId"];

			$jsonDet = new StdClass;
			$jsonDet -> srNumber = $sr;
			$jsonDet -> typeId = $typeId;
			$jsonDet -> checkpointId = $checkpointId;
			$jsonDet -> checkpoint = $description;
			$jsonDet -> value = $value;
			
			array_push($approveDetList,$jsonDet);

			$sr++;


		}

	}

	return $approveDetList;

}

function getVerifierAndApproverCheckpointId($conn, $menuId, $type){
	$checkpointList = "";
	$sql = "";
	if($type == "Verifier"){
		$sql = "SELECT DISTINCT `Verifier` as checkpointId FROM `Menu` where `MenuId` = $menuId ";
	}
	else if($type == "Approver"){
		$sql = "SELECT DISTINCT `Approver` as checkpointId FROM `Menu` where `MenuId` = $menuId ";
	}

	$query=mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$checkpointList = $row["checkpointId"];
	}
	return $checkpointList;
}
?>