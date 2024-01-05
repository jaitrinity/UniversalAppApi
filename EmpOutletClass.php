<?php 
class EmpOutletClass{
	public function getEmpOutletList($empId, $roleId, $typeName){
		include("dbConfiguration.php");
		$typeName = str_replace("?","",$typeName);
		$res = new StdClass;
	
		$flag = 0;
		$wrappedListArray = array();
		$res->wrappedList = $wrappedListArray;
		$res->responseCode = "0";
		$res->responseMsg = "Failure";
		$tabId = "";


		if($typeName == 'Attendence'){
			$attSql = "Select r.*,TIMEDIFF(EndDateTime,StartDateTime) as workinghrs from
					(Select date(MobileDateTime) AttDate ,
					min((case when Event = 'start' then MobileDateTime end)) as StartDateTime,
					max((case when Event='stop' then MobileDateTime end)) as EndDateTime,
					max((case when Event = 'start' then GeoLocation end)) as StartGeoLocation,
					max((case when Event='stop' then GeoLocation end)) as EndGeoLocation
					from Activity where EVENT in ('start','stop') and EmpId = '$empId'
					group by date(MobileDateTime)
				    ) r";
			$attQuery = mysqli_query($conn,$attSql);

			if(mysqli_num_rows($attQuery) > 0){
			
				while ($atrow = mysqli_fetch_Array($attQuery)){
				
					$startGeoLocArray = explode(",",$atrow['StartGeoLocation']);
					$startTime = $atrow['StartDateTime'];
					$endTime = $atrow['EndDateTime'];
					$workingHrs = $atrow['workinghrs'];

					$data = "StartTime    :$startTime\nEnd Time :$endTime\nWorking Hrs :$workingHrs";


					$obj = new StdClass;
					$obj->outletName = $atrow['AttDate'].'(Start)';
					$obj->latitude = $startGeoLocArray[0] ;
					$obj->longitude = $startGeoLocArray[1];
					$obj->data = $data;
					array_push($wrappedListArray,$obj);


					$endGeoLocArray = explode(",",$atrow['EndGeoLocation']);

					$obj = new StdClass;
					$obj->outletName = $atrow['AttDate'].'(End)';
					$obj->latitude = $endGeoLocArray[0];
					$obj->longitude = $endGeoLocArray[1];
					$obj->data = $data;
					array_push($wrappedListArray,$obj);


				}
				$flag = 1;

			}


		}

		else if($typeName == 'ToDo'){

		}

		else if($typeName == 'Submitted'){

		}




		if($flag = 1){
			$res->wrappedList = $wrappedListArray;
			$res->responseCode = "200";
			$res->responseMsg = "Success";	
		}
		return $res;
	}
}
?>