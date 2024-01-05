<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$api_key = "AIzaSyDkCjzv4fVu7wlsp31Tu0AnpbyQaxm4Kz8";
$empId = $jsonData->empId;
$visitDate = $jsonData->visitDate;
$visitList = $jsonData->visitList;

$delSql = "DELETE FROM `DistanceTravel` where `Emp_Id`='$empId' and `Visit_Date`='$visitDate'";
mysqli_query($conn,$delSql);
$totalDistance=0;
$successArr = array();
$errorArr = array();
for($i=0;$i<count($visitList);$i++){
	$visitObj = $visitList[$i];
	$srNo = $visitObj->srNo;
	$dateTime = $visitObj->dateTime;
	$latlong = $visitObj->latlong;
	$event = $visitObj->event;

	$geoLocation = str_replace("/", ",", $latlong);
	$latitude= explode(",", $geoLocation)[0] ;
	$longitude= explode(",", $geoLocation)[1];
	if($i==0){
		$origin=$latitude.",".$longitude;
		$origin_lat=$latitude;
		$origin_long=$longitude;
		$dest_lat=$latitude;
		$dest_long=$longitude;
		$distance = 0;
		// if($origin_lat != $dest_lat){
			$distinations=$latitude.",".$longitude;
			$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$distinations.'&key='.$api_key;
			$json_data=file_get_contents($url);	
			$distance=fnlGetDistance($json_data);
			$distanceSql = "INSERT into `DistanceTravel` (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`) values ('$srNo', '$empId', '$visitDate', '$dateTime', '$origin_lat', '$origin_long', '$dest_lat', '$dest_long', '$distance', '$event')";
			if(mysqli_query($conn,$distanceSql)){
				array_push($successArr, $empId);
			}
			else{
				array_push($errorArr, $empId);
			}
			$totalDistance += $distance;
		// }
	}
	else{
		$dest_lat=$latitude;
		$dest_long=$longitude;
		$distance = 0;
		// if($origin_lat != $dest_lat){
			$distinations=$latitude.",".$longitude;
			$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$distinations.'&key='.$api_key;
			$json_data=file_get_contents($url);	
			$distance=fnlGetDistance($json_data);
			$distanceSql = "INSERT into `DistanceTravel` (`Activity_Id`, `Emp_Id`, `Visit_Date`, `Visit_Date_Time`, `Latitude_Start`, `Longitude_Start`, `Latitude_End`, `Longitude_End`, `Distance_KM`, `Event`) values ('$srNo', '$empId', '$visitDate', '$dateTime', '$origin_lat', '$origin_long', '$dest_lat', '$dest_long', '$distance', '$event')";
			if(mysqli_query($conn,$distanceSql)){
				array_push($successArr, $empId);
			}
			else{
				array_push($errorArr, $empId);
			}
			$totalDistance += $distance;
		// }

		$origin=$latitude.",".$longitude;
		$origin_lat=$latitude;
		$origin_long=$longitude;
	}
}


$output = array(
	'date' => $visitDate, 
	'successArr' => $successArr, 
	'errorArr' => $errorArr,
	'totalDistance' => round($totalDistance,2)
);
echo json_encode($output);

// file_put_contents('/var/www/trinityapplab.co.in/NVGroup/log/distanceCalculatelog_'.date("Y").'.log', json_encode($output)."\n", FILE_APPEND);

?>
<?php
function fnlGetDistance($json_data)
{
	$json_a=json_decode($json_data,true);
	$total_distance=0;
	foreach($json_a as $key => $value) 
	{
		if($key=="rows")
		{
			foreach($value as $key1 => $value1) 
			{
				foreach($value1 as $key2 => $value2) 
				{
					foreach($value2 as $key3 => $value3) 
					{
						foreach($value3 as $key4 => $value4) 
						{
							if($key4=="distance")
							{
								foreach($value4 as $key5 => $value5) 
								{
									if($key5=="text")
									{
										// $total_distance=$total_distance + str_replace(" km","",$value5);
										$dist = $value5;
										// echo $dist;
										if(strpos($dist, 'km') !== false){
											// echo $dist;
											$dist1 = str_replace(" km","",$dist);
											// echo $dist1.'--';
											$dist = $dist1*1000;
										}
										else{
											$dist1 = str_replace(" m","",$dist);
											// echo $dist1.'--';
											$dist = $dist1;
										}
										$total_distance = ($total_distance + $dist)/1000;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return $total_distance;
}
function CallAPI($method, $url, $data)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
	//echo $result."\n";
    curl_close($curl);

    return $result;
}
?>
