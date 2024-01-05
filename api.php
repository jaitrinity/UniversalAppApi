<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
//echo 'hello';
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$tenentId=$jsonData->tenentId;
$mobile=$jsonData->mobile;
$empName=$jsonData->empName;
$make=$jsonData->Make;
$model=$jsonData->Model;
$appVer=$jsonData->AppVer;
//echo "App version".$jsonData->app_version;
$os = $jsonData->os;
$token= $jsonData->token;
$osVer = $jsonData->osVer;
$networkType = $jsonData->networkType;



$sql = "SELECT e.*,r.Role as EmpRole,e1.Name as RMName
		FROM Employees e 
		left join Role r on (e.RoleId = r.RoleId)
		left join Employees e1 on (e.RMId = e1.EmpId)
		WHERE e.Mobile= '$mobile'";
		
$query=mysqli_query($conn,$sql);
$empId = "";
$roleId = "";
$fieldUser = "";
$msgStatus = "";
$owner = "";
$empMobile = "";
$empEmailId = "";
$empRole = "";
$area = "";
$city = "";
$state = "";
$rmName = "";
$profileUrl = "";

while($row = mysqli_fetch_assoc($query)){
	$empId = $row["EmpId"];
	$roleId = $row["RoleId"];
	$fieldUser = $row["FieldUser"];
	
	if($row["Name"] != null && $row["Name"] != ""){
		$owner  = $row["Name"];
	}
	if($row["Mobile"] != null && $row["Mobile"] != ""){
		$empMobile = $row["Mobile"];
	}
	if($row["EmailId"] != null && $row["EmailId"] != ""){
		$empEmailId = $row["EmailId"];
	}
	if($row["EmpRole"] != null && $row["EmpRole"] != ""){
		$empRole = $row["EmpRole"];	
	}
	if($row["Area"] != null && $row["Area"] != ""){
		$area = $row["Area"];
	}
	if($row["City"] != null && $row["City"] != ""){
		$city = $row["City"];
	}
	if($row["State"] != null && $row["State"] != ""){
		$state = $row["State"];
	}
	if($row["RMName"] != null && $row["RMName"] != ""){
		$rmName = $row["RMName"];
	}
	if($row["ProfileURL"] != null && $row["ProfileURL"] != ""){
		$profileUrl = $row["ProfileURL"];
	}	
}
$output = new StdClass;
if($empId != ""){
	$taskotp = "";
	// Default OTP(1234) to given number. Check number in `DefaultOtpNumber` column of `configuration` table.
	$confSql = "Select * from configuration";
	$confQuery = mysqli_query($conn, $confSql);
	$conf = mysqli_fetch_assoc($confQuery);
	$mobileStr = $conf["DefaultOtpNumber"];
	$mobileArr = explode(",", $mobileStr);
	if(in_array($mobile,$mobileArr)){
		$taskotp = 1234;	
	}
	else{
		$randomotp = rand(1000,9999);
		$taskotp = $randomotp;
	}
	
	$sql = "SELECT * FROM `OTP` where `Mobile_Number` = '$mobile' ";
	$query = mysqli_query($conn, $sql);
	if(mysqli_num_rows($query) != 0) {
		$updateSql = " update `OTP` set `OTP` = '$taskotp', `OtpCount` = `OtpCount` + 1, `Update_Date` = CURRENT_TIMESTAMP where `Mobile_Number` = '$mobile' ";
		mysqli_query($conn,$updateSql);
	}
	else{
		$insertSql = "insert into `OTP` (`Mobile_Number`,`OTP`,`OtpCount`,`Create_Date`) values ('$mobile', '$taskotp', 1, CURRENT_TIMESTAMP)";
		mysqli_query($conn,$insertSql);
	}
	// for not send OTP to given number.
	if(in_array($mobile,$mobileArr)){
		$msgStatus = true;
	}
	else{
		$msgStatus = sendOtp($mobile,$taskotp,$conn);
	}
		
	//$msgStatus = true;
	if($msgStatus == true){
		
		$deviceId = "";
		$deviceStatus = "";
		$chkDeviceQuery = mysqli_query($conn,"select * from Devices where EmpId = '$empId' and Mobile = '$mobile' and Model = '$model'");
		if(mysqli_num_rows($chkDeviceQuery)>0)
		{
			//echo "updated";
			$deviceStatus = "Updated";
			$deviceSql = "Update Devices set Token = '$token', Name = '$empName', Make = '$make', OS = '$os', OSVer = '$osVer',
			AppVer = '$appVer', NetworkType = '$networkType', Active = 1,`Update` = Now() 
			where EmpId = '$empId' and Mobile = '$mobile' and Model = '$model'";
			//echo $deviceSql;
		}
		else
		{
			//echo "inserted";
			$deviceStatus = "Inserted";
			$deviceSql = "insert into Devices (EmpId,Mobile,Token,Name,Make,Model,OS,OSVer,AppVer,NetworkType,Active,Registered,`Update`)
			values ('$empId','$mobile','$token','$empName','$make','$model','$os','$osVer','$appVer','$networkType',1,Now(),Now())";
										
		}
				
		if(mysqli_query($conn,$deviceSql)){
			if($deviceStatus == "Updated"){
				$dRow = mysqli_fetch_assoc($chkDeviceQuery);
				$deviceId = $dRow['DeviceId'];
			}
			else{
				$deviceId = mysqli_insert_id($conn);
			}
			$empConf = "SELECT * FROM `EmpProfileConfigration`";
			$empConfQuery = mysqli_query($conn, $empConf);
			$empConfRow = mysqli_fetch_assoc($empConfQuery);

			$output -> status = 'Success';
			$output -> code = 200;
			$output -> empId = $empId.','.$empConfRow["EmpId"];
			$output -> roleId = $roleId.','.$empConfRow["RoleId"];
			$output -> empRole = $empRole.','.$empConfRow["RoleName"];
			$output -> empName = $owner.','.$empConfRow["Name"];
			$output -> empMobile = $empMobile.','.$empConfRow["Mobile"];
			$output -> empEmailId = $empEmailId.','.$empConfRow["EmailId"];
			$output -> area = $area.','.$empConfRow["Area"];
			$output -> state = $state.','.$empConfRow["State"];
			$output -> city = $city.','.$empConfRow["City"];
			$output -> rmName = $rmName.','.$empConfRow["RmName"];
			$output -> fieldUser = $fieldUser.','.$empConfRow["FieldUser"];
			$output -> profileUrl = $profileUrl.','.$empConfRow["ProfileURL"];
			$output -> inf = $conf['inf'];
			$output -> conn = $conf['conf'];
			$output -> Start = $conf['start'];
			$output -> End = $conf['end'];
			$output -> Battery = $conf['Battery'];
			$output -> did = "$deviceId";
			$output -> otp = $taskotp;
		}
		else{
			$output -> status = 'Device Failure';
			$output -> code = 0;
			$output -> empId = $empId;
			$output -> roleId = $roleId;
		}
		
	}
	else{
		$output -> status = 'Otp Failure';
		$output -> code = 0;
		$output -> empId = $empId;
		$output -> roleId = $roleId;
	}
}
else{
	$output -> status = 'No record found';
	$output -> code = 0;
}

echo json_encode($output);
	
?>

<?php
function sendOtp($mobile,$taskotp,$conn)
{
	//api for sending the otp
	$mobile=$mobile;
	$newotp .= "$taskotp";
	$msg = "Your one time password (OTP) is ".$taskotp." for Universal application.";
	$apikey = "ae6fa4-5cab56-4bc26d-caa56f-b27aab";
	$mobileNumber =$mobile;
	$senderId = "TRIAPP";
	$message = "$msg";
	$route = "default";
	$st = true;
	$postData = array(
	    'apikey' => $apikey,
	    'dest_mobileno' => $mobileNumber,
	    'message' => $message,
	    'senderid' => $senderId,
	    'route' => $route,
	    'response' => "Y",
            'msgtype' => "TXT"
	);
	$url="http://www.smsjust.com/sms/user/urlsms.php";
	// init the resource
	$ch = curl_init();
	curl_setopt_array($ch, array(
	    CURLOPT_URL => $url,
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_POST => true,
	    CURLOPT_POSTFIELDS => $postData
	    //,CURLOPT_FOLLOWLOCATION => true

	));
		//Ignore SSL certificate verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//get response
	$output = curl_exec($ch);
	//Print error if any
	if(curl_errno($ch))
	{
	    echo 'error:' . curl_error($ch);
		$st = false;
	}
	curl_close($ch);
	return $st;
	
}

?>