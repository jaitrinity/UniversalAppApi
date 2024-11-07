<?php
class NewAssign1{
	public function getNewAssign1Data($empId){
		include("dbConfiguration.php");

		$wrappedListArray = array();

		$geofenceSql = "SELECT `Geofence` FROM `configuration`";
		$geofenceQuery=mysqli_query($conn,$geofenceSql);
		$geofenceRow = mysqli_fetch_assoc($geofenceQuery);
		$configGeoFence = $geofenceRow["Geofence"];

			
		$assignSql = "SELECT mp.MenuId,mp.LocationId,mp.Start,mp.End,mp.MappingId,l.Name,l.GeoCoordinates,
				m.Caption,m.Sub,m.Cat,m.Icons,m.CheckpointId,m.GeoFence,m.Colors
				FROM Mapping mp 
				left join Menu m  on (mp.MenuId = m.MenuId)
				left join Location l on (mp.LocationId = l.LocationId)
				WHERE mp.EmpId = '$empId' AND date(mp.Start) <= date(now()) AND date(mp.End) >= date(now())
				AND mp.ActivityId = 0 AND mp.Active = 1 order by mp.MappingId desc ";
				
		$assignQuery=mysqli_query($conn,$assignSql);

		while($row = mysqli_fetch_assoc($assignQuery)){
			$assignObj = new StdClass;
			
			$iconArr = explode(",",$row['Icons']);

			$colors = $row["Colors"];
			$colorsExplode = explode(":", $colors);

			$catColors = $colorsExplode[0];
			$catColorsExplode = explode(",", $catColors);
			$catBgColor = $catColorsExplode[0];
			$catFontColor = $catColorsExplode[1];


			$subCatColors = $colorsExplode[1];
			$subCatColorsExplode = explode(",", $subCatColors);
			$subCatBgColor = $subCatColorsExplode[0];
			$subCatFontColor = $subCatColorsExplode[1];


			$captionColors = $colorsExplode[2];
			$captionColorsExplode = explode(",", $captionColors);
			$captionBgColor = $captionColorsExplode[0];
			$captionFontColor = $captionColorsExplode[1];
			
			$cat = $row["Cat"];
			$sub = $row["Sub"];
			$caption = $row["Caption"];
			
			$hh = $row["GeoFence"];
			$hhExplode = explode(":", $hh);
			$GeoCoordinate = $hhExplode[0];
			if($GeoCoordinate == ""){
				$GeoCoordinate = null;
			}
			$GeoFence = $hhExplode[1];
			if($GeoFence == ""){
				$GeoFence = null;
			}

			// for all todo checklist geofence..
			if($GeoFence == null)
				$GeoFence = $configGeoFence; 
			
			$isDataSend = "";
			$cpIdArray = explode(":",$row['CheckpointId']);
			for($cpId = 0; $cpId < count($cpIdArray); $cpId++){
				if($cpId == 0){
					$isDataSend .= "1";
				}
				else{
					$isDataSend .= ":1";
				}	
			}
			
			if($sub == '' && $caption == ''){
				$assignObj->Caption = $cat;
				$assignObj->Icon = $iconArr[0];
				$assignObj->bgColor = $catBgColor;
				$assignObj->fontColor = $catFontColor;
				$assignObj->menuId = $row["MenuId"];
				$assignObj->locationId = $row["LocationId"];
				$assignObj->startDate = $row["Start"];
				$assignObj->endDate = $row["End"];
				$assignObj->assignId = $row["MappingId"];
				$assignObj->name = $row["Name"];
				$assignObj->latlong = $row["GeoCoordinates"];
				$assignObj->GeoFence = $GeoFence ;
				$assignObj->GeoCoordinate = $GeoCoordinate ;

				$assignObj->activityId = '';
				$assignObj->status = '';
				$assignObj->uniqueId = $row["MappingId"];
				
				
				$assignObj->isDataSend = $isDataSend;
				$assignObj->checkpointId = $row['CheckpointId'];
				
				array_push($wrappedListArray,$assignObj);
			}
			else if($sub != '' && $caption == ''){
				// $assignObj->Caption = $cat;
				// $assignObj->Icon = $iconArr[0];
				// $assignObj->bgColor = $catBgColor;
				// $assignObj->fontColor = $catFontColor;
				
				$assignArray1 = array();
				$assignObj1 = new StdClass;
				$assignObj1->Caption = $sub;
				$assignObj1->Icon = $iconArr[1];
				$assignObj1->bgColor = $subCatBgColor;
				$assignObj1->fontColor = $subCatFontColor;
				$assignObj1->menuId = $row["MenuId"];
				$assignObj1->locationId = $row["LocationId"];
				$assignObj1->startDate = $row["Start"];
				$assignObj1->endDate = $row["End"];
				$assignObj1->assignId = $row["MappingId"];
				$assignObj1->name = $row["Name"];
				$assignObj1->latlong = $row["GeoCoordinates"];
				$assignObj1->GeoFence = $GeoFence ;
				$assignObj1->GeoCoordinate = $GeoCoordinate ;
				$assignObj1->activityId = '';
				$assignObj1->status = '';
				$assignObj1->uniqueId = $row["MappingId"];
				
				$assignObj1->isDataSend = $isDataSend;
				$assignObj1->checkpointId = $row['CheckpointId'];
			
				// array_push($assignArray1,$assignObj1);
				// $assignObj->subCategoryList = $assignArray1;
				
				// array_push($wrappedListArray,$assignObj);
				array_push($wrappedListArray,$assignObj1);
			}
			else if($sub != '' && $caption != ''){
				// $assignObj->Caption = $cat;
				// $assignObj->Icon = $iconArr[0];
				// $assignObj->bgColor = $catBgColor;
				// $assignObj->fontColor = $catFontColor;
				
				// $assignArray1 = array();
				// $assignObj1 = new StdClass;
				// $assignObj1->Caption = $sub;
				// $assignObj1->Icon = $iconArr[1];
				// $assignObj1->bgColor = $subCatBgColor;
				// $assignObj1->fontColor = $subCatFontColor;


				$assignArray2 = array();
				$assignObj2 = new StdClass;
				$assignObj2->Caption = $caption;
				$assignObj2->Icon = $iconArr[2];
				$assignObj2->bgColor = $captionBgColor;
				$assignObj2->fontColor = $captionFontColor;
				$assignObj2->menuId = $row["MenuId"];
				$assignObj2->locationId = $row["LocationId"];
				$assignObj2->startDate = $row["Start"];
				$assignObj2->endDate = $row["End"];
				$assignObj2->assignId = $row["MappingId"];
				$assignObj2->name = $row["Name"];
				$assignObj2->latlong = $row["GeoCoordinates"];
				$assignObj2->GeoFence = $GeoFence ;
				$assignObj2->GeoCoordinate = $GeoCoordinate ;
				$assignObj2->status = '';
				$assignObj2->activityId = '';
				$assignObj2->uniqueId = $row["MappingId"];
				$assignObj2->isDataSend = $isDataSend;
				$assignObj2->checkpointId = $row['CheckpointId'];
						
				// array_push($assignArray2,$assignObj2);
				// $assignObj1->subCategoryList = $assignArray2;
				
				// array_push($assignArray1,$assignObj1);			
				// $assignObj->subCategoryList = $assignArray1;
				
				// array_push($wrappedListArray,$assignObj);
		 		array_push($wrappedListArray,$assignObj2);

			}

		}

				
		$verifierSql = "Select mp.ActivityId,mp.MenuId,mp.LocationId,l.Name,l.GeoCoordinates,m.Verifier,mp.Start,mp.End,
						m.Caption,m.Sub,m.Cat,m.Icons,m.CheckpointId,m.GeoFence,h.Status
						from Mapping mp
						join TransactionHDR h on (mp.ActivityId = h.ActivityId)
						join Menu m on (m.MenuId = mp.MenuId)
						left join Location l on (mp.LocationId = l.LocationId)
						where mp.ActivityId !=0 and mp.Verifier = '$empId' and h.Status = 'Created'
						and h.VerifierActivityId is null order by mp.MappingId desc";				

		$verifierQuery=mysqli_query($conn,$verifierSql);

		while($v = mysqli_fetch_assoc($verifierQuery)){

			$actObj = new StdClass;
			$actObj->actId = $v['ActivityId'];
			$actObj->fillerCp = $v['CheckpointId'];
			$actObj->verifierCp = $v['Verifier'];

			
			$iconArr = explode(",",$v['Icons']);
			
			$cat = $v["Cat"];
			$sub = $v["Sub"];
			$caption = $v["Caption"];

			$vv = $v["GeoFence"];
			$vvExplode = explode(":",$vv);
			$GeoCoordinate = $vvExplode[0];
			if($GeoCoordinate == ""){
				$GeoCoordinate = null;
			}
			$GeoFence = $vvExplode[1];
			if($GeoFence == ""){
				$GeoFence = null;
			}

			// for all todo checklist geofence..
			if($GeoFence == null)
				$GeoFence = $configGeoFence;

			$vObj = new StdClass;
			$vObj1 = new StdClass;

			if($sub == '' && $caption == ''){
				
				$vObj->Caption = $cat;
				$vObj->Icon = $iconArr[0];

				$vObj->menuId = $v["MenuId"];
				$vObj->locationId = $v["LocationId"];
				$vObj->startDate = $v["Start"];
				$vObj->endDate = $v["End"];
				$vObj->assignId = "";
				$vObj->name = $v["Name"];
				$vObj->latlong = $v["GeoCoordinates"];
				$vObj->GeoFence = $GeoFence ;
				$vObj->GeoCoordinate = $GeoCoordinate ;
				$vObj->activityId = $v["ActivityId"];
				$vObj->status = $v['Status'];
				$vObj->uniqueId = $v["ActivityId"];
				
				$actObj->level = 1;

				$this->getVerifierCheckpoints($actObj,$vObj);
				
				array_push($wrappedListArray,$vObj);
			}
			else if($sub != '' && $caption == ''){

				$vObj->Caption = $cat;
				$vObj->Icon = $iconArr[0];
				
				$vArray1 = array();
				
				$vObj1->Caption = $sub;
				$vObj1->Icon = $iconArr[1];

				$vObj1->menuId = $v["MenuId"];
				$vObj1->locationId = $v["LocationId"];
				$vObj1->startDate = $v["Start"];
				$vObj1->endDate = $v["End"];
				$vObj1->assignId = "";
				$vObj1->name = $v["Name"];
				$vObj1->latlong = $v["GeoCoordinates"];
				$vObj1->GeoFence = $GeoFence ;
				$vObj1->GeoCoordinate = $GeoCoordinate ;
				$vObj1->activityId = $v["ActivityId"];
				$vObj1->status = $v['Status'];
				$vObj1->uniqueId = $v["ActivityId"];

				$actObj->level = 2;
				$this->getVerifierCheckpoints($actObj,$vObj1);

				// array_push($vArray1,$vObj1);

				// $vObj->subCategoryList = $vArray1;
				
				// array_push($wrappedListArray,$vObj);
				array_push($wrappedListArray,$vObj1);


			}
			else if($sub != '' && $caption != ''){

				$vObj->Caption = $cat;
				$vObj->Icon = $iconArr[0];
				
				$vArray1 = array();
				
				$vObj1->Caption = $sub;
				$vObj1->Icon = $iconArr[1];


				$vArray2 = array();
				$vObj2 = new StdClass;
				$vObj2->Caption = $caption;
				$vObj2->Icon = $iconArr[2];
				$vObj2->menuId = $v["MenuId"];
				$vObj2->locationId = $v["LocationId"];
				$vObj2->startDate = $v["Start"];
				$vObj2->endDate = $v["End"];
				$vObj2->assignId = "";
				$vObj2->name = $v["Name"];
				$vObj2->latlong = $v["GeoCoordinates"];
				$vObj2->GeoFence = $GeoFence ;
				$vObj2->GeoCoordinate = $GeoCoordinate ;
				$vObj2->activityId = $v["ActivityId"];
				$vObj2->status = $v['Status'];
				$vObj2->uniqueId = $v["ActivityId"];

				$actObj->level = 3;
				$this->getVerifierCheckpoints($actObj,$vObj2);
				
				// array_push($vArray2,$vObj2);
				// $vObj1->subCategoryList = $vArray2;

				// array_push($vArray1,$vObj1);
				// $vObj->subCategoryList = $vArray1;
				
				// array_push($wrappedListArray,$vObj);
				array_push($wrappedListArray,$vObj2);

			}
			

		}

		$approverSql = "Select mp.ActivityId,mp.MenuId,mp.LocationId,l.Name,l.GeoCoordinates,m.Verifier,m.Approver,mp.Start,mp.End,
				m.Caption,m.Sub,m.Cat,m.Icons,m.CheckpointId,h.VerifierActivityId,h.Status
				from Mapping mp
				join TransactionHDR h on (mp.ActivityId = h.ActivityId)
				join Menu m on (m.MenuId = mp.MenuId)
				left join Location l on (mp.LocationId = l.LocationId)
				where mp.ActivityId !=0 and mp.Approver = '$empId' and h.Status = 'Verified'
				and h.ApproverActivityId is null order by mp.MappingId desc";				

		$approverQuery=mysqli_query($conn,$approverSql);
		while($ap = mysqli_fetch_assoc($approverQuery)){

			$actObj = new StdClass;
			$actObj->actId = $ap['ActivityId'];
			$actObj->fillerCp = $ap['CheckpointId'];
			$actObj->verifierCp = $ap['Verifier'];
			$actObj->approverCp = $ap['Approver'];
			$actObj->verifierActId = $ap['VerifierActivityId'];

			
			$iconArr = explode(",",$ap['Icons']);
			
			$cat = $ap["Cat"];
			$sub = $ap["Sub"];
			$caption = $ap["Caption"];



			$aa = $ap["GeoFence"];
			$aaExplode = explode(":", $aa);
			$GeoCoordinate = $aaExplode[0];
			if($GeoCoordinate == ""){
				$GeoCoordinate = null;
			}
			$GeoFence = $aaExplode[1];
			if($GeoFence == ""){
				$GeoFence = null;
			}

			// for all todo checklist geofence..
			if($GeoFence == null)
				$GeoFence = $configGeoFence;

			$apObj = new StdClass;
			$apObj1 = new StdClass;
			
			if($sub == '' && $caption == ''){
				
				$apObj->Caption = $cat;
				$apObj->Icon = $iconArr[0];

				$apObj->menuId = $ap["MenuId"];
				$apObj->locationId = $ap["LocationId"];
				$apObj->startDate = $ap["Start"];
				$apObj->endDate = $ap["End"];
				$apObj->assignId = "";
				$apObj->name = $ap["Name"];
				$apObj->latlong = $ap["GeoCoordinates"];
				$apObj->GeoFence = $GeoFence ;
				$apObj->GeoCoordinate = $GeoCoordinate ;
				$apObj->activityId = $ap["ActivityId"];
				$apObj->status = $ap['Status'];
				$apObj->uniqueId = $ap["ActivityId"];
				
				$actObj->level = 1;

				$this->getApproverCheckpoints($actObj,$apObj);

				array_push($wrappedListArray,$apObj);
			}
			else if($sub != '' && $caption == ''){

				$apObj->Caption = $cat;
				$apObj->Icon = $iconArr[0];
				
				$apArray1 = array();
				
				$apObj1->Caption = $sub;
				$apObj1->Icon = $iconArr[1];

				$apObj1->menuId = $ap["MenuId"];
				$apObj1->locationId = $ap["LocationId"];
				$apObj1->startDate = $ap["Start"];
				$apObj1->endDate = $ap["End"];
				$apObj1->assignId = "";
				$apObj1->name = $ap["Name"];
				$apObj1->latlong = $ap["GeoCoordinates"];
				$apObj1->GeoFence = $GeoFence ;
				$apObj1->GeoCoordinate = $GeoCoordinate ;
				$apObj1->activityId = $ap["ActivityId"];
				$apObj1->status = $ap['Status'];
				$apObj1->uniqueId = $ap["ActivityId"];

				$actObj->level = 2;
				$this->getApproverCheckpoints($actObj,$apObj1);

				// array_push($apArray1,$apObj1);

				// $apObj->subCategoryList = $apArray1;
				
				// array_push($wrappedListArray,$apObj);

				array_push($wrappedListArray,$apObj1);
			}
			else if($sub != '' && $caption != ''){

				$apObj->Caption = $cat;
				$apObj->Icon = $iconArr[0];
				
				$apArray1 = array();
				
				$apObj1->Caption = $sub;
				$apObj1->Icon = $iconArr[1];


				$apArray2 = array();
				$apObj2 = new StdClass;
				$apObj2->Caption = $caption;
				$apObj2->Icon = $iconArr[2];
				$apObj2->menuId = $ap["MenuId"];
				$apObj2->locationId = $ap["LocationId"];
				$apObj2->startDate = $ap["Start"];
				$apObj2->endDate = $ap["End"];
				$apObj2->assignId = "";
				$apObj2->name = $ap["Name"];
				$apObj2->latlong = $ap["GeoCoordinates"];
				$apObj2->GeoFence = $GeoFence ;
				$apObj2->GeoCoordinate = $GeoCoordinate ;
				$apObj2->activityId = $ap["ActivityId"];
				$apObj2->status = $ap['Status'];
				$apObj2->uniqueId = $ap["ActivityId"];

				$actObj->level = 3;
				$this->getApproverCheckpoints($actObj,$apObj2);
				
				// array_push($apArray2,$apObj2);
				// $apObj1->subCategoryList = $apArray2;

				// array_push($apArray1,$apObj1);
				// $apObj->subCategoryList = $apArray1;
				
				// array_push($wrappedListArray,$apObj);
				array_push($wrappedListArray,$apObj2);

			}

		}

		$response = new StdClass;
		$response -> tabName = 'Pending';
		$response -> menu = $wrappedListArray;

		// file_put_contents('/var/www/trinityapplab.co.in/UniversalApp/log/NewAssign1_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($response)."\n", FILE_APPEND);

		return $response;
	}

	function getVerifierCheckpoints($cpObj,$vObj){
		global $conn;
		// global $vObj;
		// global $vObj1;
		// global $vObj2;

		$actId = $cpObj->actId ;
		$fillerCp = $cpObj->fillerCp ;
		$verifierCp  = $cpObj->verifierCp ;
		$level = $cpObj->level;
		
		// if($level == 1){	
			$vObj->checkpointId = $fillerCp.':'.$verifierCp;
			$cpId = $vObj->checkpointId;
		// }
		// else if($level == 2){
		// 	$vObj1->checkpointId = $fillerCp.':'.$verifierCp;
		// 	$cpId = $vObj1->checkpointId;
		// }
		// else if($level == 3){
		// 	$vObj2->checkpointId = $fillerCp.':'.$verifierCp;
		// 	$cpId = $vObj2->checkpointId;

		// }

		$vcpIdArray = array();
		$visDataSend = "";
		$vcpIdArray = explode(':',$cpId);




		for($vcpId = 0; $vcpId < count($vcpIdArray); $vcpId++){
			
			
			if($vcpId == 0){
				$visDataSend .= "0";

			}
			else if($vcpId == count($vcpIdArray)-1){

				$visDataSend .= ":1";

			}
			else{
				$visDataSend .= ":0";
			}	
		}



		// if($level == 1){	
			$vObj->isDataSend = $visDataSend;
		// }
		// else if($level == 2){
		// 	$vObj1->isDataSend = $visDataSend;
		// }
		// else if($level == 3){
		// 	$vObj2->isDataSend = $visDataSend;

		// }


		
		$cpArray = array();
		$filledCpString = str_replace(":",",",$fillerCp);
		$verifierCpString = str_replace(":",",",$verifierCp);

		$filledcpSql = "Select r2.*,r1.* 
				from
				(Select d.ChkId,d.Value as answer from TransactionDTL d
				where d.ActivityId = '$actId' and d.DependChkId = 0
				)r1
				right join 
				(Select c.* from Checkpoints c
				where c.CheckpointId in ($filledCpString)
				) r2 on (r1.ChkId = r2.CheckpointId)";

		$filledcpQuery=mysqli_query($conn,$filledcpSql);
		while($fcp = mysqli_fetch_assoc($filledcpQuery)){
			$fcpObj = new StdClass;
			$fcpObj->Chkp_Id = $fcp['CheckpointId'];
			$fcpObj->editable = '0';
			if($fcp['answer'] != null){
				$fcpObj->value = $fcp['answer'];
			}
			else{
				$fcpObj->value = "";
			}
			
			$fdpArray = array();
			if($fcp['Dependent'] == "1"){
				$fdpSql = " Select r1.*,c.* from
					    (Select d.ChkId,d.Value as answer from TransactionDTL d
						where d.ActivityId = '$actId' and d.DependChkId = (".$fcp['CheckpointId'].")
					    ) r1
					    join Checkpoints c on (r1.ChkId = c.CheckpointId)";
								
				$fdpQuery = mysqli_query($conn,$fdpSql);
				while($fdp = mysqli_fetch_assoc($fdpQuery)){
					$fdpObj = new StdClass;
					$fdpObj->Chkp_Id = $fcp['CheckpointId']."_".$fdp['CheckpointId'];
					$fdpObj->editable = '0';
					$fdpObj->value = $fdp['answer'];
					array_push($fdpArray,$fdpObj);
				}
			}
			$fcpObj->Dependents = $fdpArray;
			array_push($cpArray,$fcpObj);
		} 
		 $verifiercpSql = "Select c.* from Checkpoints c where c.CheckpointId in ($verifierCpString)";

		$verifiercpQuery=mysqli_query($conn,$verifiercpSql);
		while($vcp = mysqli_fetch_assoc($verifiercpQuery)){
			$vcpObj = new StdClass;
			$vcpObj->Chkp_Id = $vcp['CheckpointId'];
			$vcpObj->editable = $vcp['Editable'];
			$vcpObj->value = "";
			$vdpArray = array();
			if($vcp['Dependent'] == "1"){
				$vcplogicArray = explode(":",trim($vcp['Logic']," "));
				$vcplogicString = "";
				for($vcpl=0;$vcpl< count($vcplogicArray);$vcpl++){
					if($vcpl == 0  && $vcplogicArray[$vcpl] != null && $vcplogicArray[$vcpl] != ""){
						$vcplogicString .= $vcplogicArray[$vcpl];
					}
					else if($vcplogicArray[$vcpl] != null && $vcplogicArray[$vcpl] != ""){
						$vcplogicString .= ",".$vcplogicArray[$vcpl];
					}
					
				}
				$vdpSql = " Select c.* from Checkpoints c where c.CheckpointId in ($vcplogicString)";
								
				$vdpQuery = mysqli_query($conn,$vdpSql);
				while($vdp = mysqli_fetch_assoc($vdpQuery)){
					$vdpObj = new StdClass;
					$vdpObj->Chkp_Id = $vdp['CheckpointId'];
					$vdpObj->editable = $vdp['Editable'];
					$vdpObj->value = "";
					array_push($vdpArray,$vdpObj);
				}
			}
			$vcpObj->Dependents = $vdpArray;
			array_push($cpArray,$vcpObj);
		} 
		// if($level == 1){	
			$vObj->value = $cpArray;
		// }
		// else if($level == 2){
		// 	$vObj1->value = $cpArray;
		// }
		// else if($level == 3){
		// 	$vObj2->value = $cpArray;

		// }


	}

	function getApproverCheckpoints($cpObj,$apObj){
		global $conn;
		// global $apObj;
		// global $apObj1;
		// global $apObj2;

		$actId = $cpObj->actId ;
		$fillerCp = $cpObj->fillerCp ;
		$verifierCp  = $cpObj->verifierCp ;
		$approverCp  = $cpObj->approverCp ;
		
		$verifierActId = $cpObj->verifierActId;

		$level = $cpObj->level;
		
		// if($level == 1){	
			$apObj->checkpointId = $fillerCp.":".$verifierCp.":".$approverCp;
			$cpId = $apObj->checkpointId;
		// }
		// else if($level == 2){
		// 	$apObj1->checkpointId = $fillerCp.":".$verifierCp.":".approverCp;
		// 	$cpId = $apObj1->checkpointId;
		// }
		// else if($level == 3){
		// 	$apObj2->checkpointId = $fillerCp.":".$verifierCp.":".approverCp;
		// 	$cpId = $apObj2->checkpointId;

		// }

		$apisDataSend = "";
		$apcpIdArray = explode(":",$cpId);
		for($apcpId = 0; $apcpId < count($apcpIdArray); $apcpId++){
			if($apcpId == 0){
				$apisDataSend .= "0";
			}
			else if($apcpId == count($apcpIdArray)-1){
				$apisDataSend .= ":1";
			}
			else{
				$apisDataSend .= ":0";
			}	
		}

		// if($level == 1){
			$apObj->isDataSend = $apisDataSend;
		// }
		// else if($level == 2){
		// 	$apObj1->isDataSend = $apisDataSend;
		// }
		// else if($level == 3){
		// 	$apObj2->isDataSend = $apisDataSend;
		// }
		

		$apcpArray = array();
		$apfilledCpString = str_replace(":",",",$fillerCp);
		$apverifierCpString = str_replace(":",",",$verifierCp );
		$apapproverCpString = str_replace(":",",",$approverCp );

		$apfilledcpSql = "Select r2.*,r1.* 
				 from
				 (Select d.ChkId,d.Value as answer from TransactionDTL d
				 where d.ActivityId = '$actId' and d.DependChkId = 0
				 )r1
				 right join 
				 (Select c.* from Checkpoints c
				 where c.CheckpointId in ($apfilledCpString)
				 ) r2 on (r1.ChkId = r2.CheckpointId)";

		$apfilledcpQuery=mysqli_query($conn,$apfilledcpSql);
		while($apfcp = mysqli_fetch_assoc($apfilledcpQuery)){
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
								where d.ActivityId = '$actId' and d.DependChkId = (".$apfcp['CheckpointId'].")
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
		$apverifiedcpSql = "Select r2.*,r1.* 
				 from
				 (Select d.ChkId,d.Value as answer from TransactionDTL d
				 where d.ActivityId = '$verifierActId' and d.DependChkId = 0
				 )r1
				 right join 
				 (Select c.* from Checkpoints c
				 where c.CheckpointId in ($apverifierCpString)
				 ) r2 on (r1.ChkId = r2.CheckpointId)";

				 // echo $apverifiedcpSql;


		$apverifiedcpQuery=mysqli_query($conn,$apverifiedcpSql);
		 
		 while($apvcp = mysqli_fetch_assoc($apverifiedcpQuery)){
			$apvcpObj = new StdClass;
			$apvcpObj->Chkp_Id = $apvcp['CheckpointId'];
			$apvcpObj->editable = '0';
			if($apvcp['answer'] != null){
				$apvcpObj->value = $apvcp['answer'];
			}
			else{
				$apvcpObj->value = "";
			}
			
			$apvdpArray = array();
			if($apvcp['Dependent'] == "1"){
				$apvdpSql = " Select r1.*,c.* from
								(Select d.ChkId,d.Value as answer from TransactionDTL d
								where d.ActivityId = '$verifierActId' and d.DependChkId = (".$apvcp['CheckpointId'].")
								) r1
								join Checkpoints c on (r1.ChkId = c.CheckpointId)";
								
				$apvdpQuery = mysqli_query($conn,$apvdpSql);
				while($apvdp = mysqli_fetch_assoc($apvdpQuery)){
					$apvdpObj = new StdClass;
					$apvdpObj->Chkp_Id = $apvcp['CheckpointId']."_".$apvdp['CheckpointId'];
					$apvdpObj->editable = '0';
					$apvdpObj->value = $apvdp['answer'];
					array_push($apvdpArray,$apvdpObj);
				}
			}
			$apvcpObj->Dependents = $apvdpArray;
			array_push($apcpArray,$apvcpObj);
		}
		
		$apapprovercpSql = "Select c.* from Checkpoints c where c.CheckpointId in ($apapproverCpString)";
		$apapprovercpQuery=mysqli_query($conn,$apapprovercpSql);
		while($apcp = mysqli_fetch_assoc($apapprovercpQuery)){
			$apcpObj = new StdClass;
			$apcpObj->Chkp_Id = $apcp['CheckpointId'];
			$apcpObj->editable = $apcp['Editable'];
			$apcpObj->value = "";
			$apdpArray = array();
			if($apcp['Dependent'] == "1"){
				$apcplogicArray = explode(":",trim($apcp['Logic']," "));
				$apcplogicString = "";
				for($apcpl=0;$apcpl< count($apcplogicArray);$apcpl++){
					if($apcpl == 0  && $apcplogicArray[$apcpl] != null && $apcplogicArray[$apcpl] != ""){
						$apcplogicString .= $apcplogicArray[$apcpl];
					}
					else if($apcplogicArray[$apcpl] != null && $apcplogicArray[$apcpl] != ""){
						$apcplogicString .= ",".$apcplogicArray[$apcpl];
					}
					
				}
				$apdpSql = " Select c.* from
								   Checkpoints c where c.CheckpointId in ($apcplogicString)";
								
				$apdpQuery = mysqli_query($conn,$apdpSql);
				while($apdp = mysqli_fetch_assoc($apdpQuery)){
					$apdpObj = new StdClass;
					$apdpObj->Chkp_Id = $apdp['CheckpointId'];
					$apdpObj->editable = $apdp['Editable'];
					$apdpObj->value = "";
					array_push($apdpArray,$apdpObj);
				}
			}
			$apcpObj->Dependents = $apdpArray;
			array_push($apcpArray,$apcpObj);
		}


	 	// if($level == 1){	
			$apObj->value = $apcpArray;
		// }
		// else if($level == 2){
		// 	$apObj1->value = $apcpArray;
		// }
		// else if($level == 3){
		// 	$apObj2->value = $apcpArray;

		// }

	}
}
?>