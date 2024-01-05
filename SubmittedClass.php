<?php 
class SubmittedClass{
	public function submitted($empId, $roleId){
		require_once 'dbConfiguration.php';
		global $conn;
		$submitList = array();
		$submitSql = "SELECT DISTINCT `ActivityId` from `FlowActivityMaster` where `FlowEmpId` = '$empId'";
		$submitSql .= "UNION SELECT DISTINCT `ActivityId` from `Activity` where `EmpId` = '$empId' and `Event` = 'Submit'";
		$submitQuery = mysqli_query($conn,$submitSql);
		$submitRowSize = mysqli_num_rows($submitQuery);
		if($submitRowSize != 0){
			while($subRow = mysqli_fetch_array($submitQuery)){
				$actId = $subRow["ActivityId"];

				$hisSql="Select m.Caption, m.Sub, m.Cat, m.Icons, m.CheckpointId, l.Name as locationName, a.MobileDateTime as SubmitDateTime, a.GeoLocation, a.ActivityId, th.Status from Activity a join Menu m on (a.MenuId = m.MenuId) join TransactionHDR th on (a.ActivityId = th.ActivityId) join Location l on (a.LocationId = l.LocationId) where a.ActivityId = $actId and a.Event = 'Submit'  order by a.MobileDateTime desc";
				$hisQuery = mysqli_query($conn,$hisSql);
				$hisRowSize = mysqli_num_rows($hisQuery);
				if($hisRowSize == 0)
					continue;
				
				$hisRow = mysqli_fetch_array($hisQuery);

				$cat = $hisRow["Cat"];
				$sub = $hisRow["Sub"];
				$caption = $hisRow["Caption"];
				$iconArr = explode(",",$hisRow['Icons']);

				$submitObj = new StdClass;
				$submitObj->name = $hisRow['locationName'];
				$submitObj->endDate= $hisRow['SubmitDateTime'];
				$submitObj->uniqueId= $actId;
				$submitObj->geoLocation = $hisRow['GeoLocation'];

				if($cat != '' && $sub == '' && $caption == ''){
					$submitObj->Caption = $cat.' - '.$actId;
					$submitObj->Icon = $iconArr[0];
				}
				else if($cat !='' && $sub != '' && $caption == ''){
					$submitObj->Caption = $sub.' - '.$actId;
					$submitObj->Icon = $iconArr[1];
				}
				else if($cat != '' && $sub != '' && $caption != ''){
					$submitObj->Caption = $caption.' - '.$actId;
					$submitObj->Icon = $iconArr[2];
				}
				$submitObj->subCategoryList = array();


				$fillActArr= array();
				$firstObj = array(
					'flowChkId' => $hisRow["CheckpointId"],
					'flowActId' => $hisRow["ActivityId"]
				);
				array_push($fillActArr, $firstObj);

				$fillSql="SELECT `FlowCheckpointId`, `FlowActivityId` FROM `FlowActivityMaster` where `ActivityId`=$actId and `FlowActivityId` is not null";
				$fillQuery=mysqli_query($conn,$fillSql);
				while($fillRow = mysqli_fetch_assoc($fillQuery)){
					$fillObj = array(
						'flowChkId' => $fillRow["FlowCheckpointId"],
						'flowActId' => $fillRow["FlowActivityId"]
					);
					array_push($fillActArr, $fillObj);
				}

				$this->getInfiniteLevelCheckpoints($fillActArr,$submitObj);

				array_push($submitList,$submitObj);
				
					
			}
		}

		$response = new StdClass;
		$response -> tabName ='Completed';
		$response -> menu = $submitList;

		// file_put_contents('/var/www/trinityapplab.co.in/UniApp/log/Submitted_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($response)."\n", FILE_APPEND);

		return $response;
	}

	function getInfiniteLevelCheckpoints($fillActArr,$submitObj){
		global $conn;

		$apcpArray = array();
		$allChkId = "";
		for($ii=0;$ii<count($fillActArr);$ii++){
			$fillObj = $fillActArr[$ii];
			$flowActId = $fillObj["flowActId"];
			$flowChkId = $fillObj["flowChkId"];
			if($allChkId == ""){
				$allChkId .= $flowChkId;
			}
			else{
				$allChkId .= ":".$flowChkId;
			}
			$filledCpString = str_replace(":",",",$flowChkId);

			if($flowActId != 0){
				$apFilledCpSql = "Select r2.*,r1.* 
				 from
				 (Select d.ChkId,d.Value as answer from TransactionDTL d
				 where d.ActivityId = '$flowActId' and d.DependChkId = 0
				 )r1
				 right join 
				 (Select c.* from Checkpoints c
				 where c.CheckpointId in ($filledCpString)
				 ) r2 on (r1.ChkId = r2.CheckpointId)";
				 // echo $apFilledCpSql;

				$nLevelFilledQuery=mysqli_query($conn,$apFilledCpSql);
				while($apfcp = mysqli_fetch_assoc($nLevelFilledQuery)){
					$apfcpObj = new StdClass;
					$apfcpObj->Chkp_Id = $apfcp['CheckpointId'];
					$apfcpObj->editable = '0';
					if($apfcp['answer'] != null){
						$apfcpObj->value = $apfcp['answer'];
					}
					else{
						$apfcpObj->value = "";
					}
					
					$apfdpArray = array();
					if($apfcp['Dependent'] == "1"){
						$apfdpSql = " Select r1.*,c.* from
										(Select d.ChkId,d.Value as answer from TransactionDTL d
										where d.ActivityId = '$flowActId' and d.DependChkId = (".$apfcp['CheckpointId'].")
										) r1
										join Checkpoints c on (r1.ChkId = c.CheckpointId)";
										
						$apfdpQuery = mysqli_query($conn,$apfdpSql);
						while($apfdp = mysqli_fetch_assoc($apfdpQuery)){
							$apfdpObj = new StdClass;
							$apfdpObj->Chkp_Id = $apfcp['CheckpointId']."_".$apfdp['CheckpointId'];
							$apfdpObj->editable = '0';
							$apfdpObj->value = $apfdp['answer'];
							array_push($apfdpArray,$apfdpObj);
						}
					}
					$apfcpObj->Dependents = $apfdpArray;
					array_push($apcpArray,$apfcpObj);
				}
			}
		}

		$submitObj->checkpointId = $allChkId;
		$cpId = $allChkId;

		$apisDataSend = "";
		$apcpIdArray = explode(":",$cpId);
		for($apcpId = 0; $apcpId < count($apcpIdArray); $apcpId++){
			if($apisDataSend == ""){
				$apisDataSend .= "0";
			}
			else{
				$apisDataSend .= ":0";
			}
		}
		$submitObj->isDataSend = $apisDataSend;
		$submitObj->value = $apcpArray;
	}
}
?>