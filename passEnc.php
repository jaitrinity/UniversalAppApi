<?php 
include("dbConfiguration.php");
$sql = "SELECT * FROM `Employees` where `Password` is null";
$success = 0;
$fail = 0;
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$id = $row["Id"];
	// $empId = $row["EmpId"];
	$password = $row["Password_Txt"];
	$encode = base64_encode($password);
	// $decode = base64_decode($encode);
	// echo $password." --- ".$encode.' --- '.$decode;

	$passEnc = "UPDATE `Employees` set `Password` = '$encode' where `Id` = $id";
	if(mysqli_query($conn,$passEnc)){
		$success++;
	}
	else{
		$fail++;
	}
}
$output = array('success' => $success, 'fail' => $fail);
echo json_encode($output);


// $str = '9958965924';
// $encode = base64_encode($str);
// echo 'Encode : '.$encode.'<br>';
// $decode = base64_decode($encode);
// echo 'Decode : '.$decode;

// $str1="JAi";
// $str2="Jai";
// $isEq = strcmp($str1,$str2);
// echo $isEq;
?>