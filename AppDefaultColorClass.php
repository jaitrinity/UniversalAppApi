<?php
class AppDefaultColorClass{
	function getAppDefaultColor(){
		require_once 'dbConfig.php';

		$sql = "SELECT * FROM `Accounts` where `Company_Name`='UniversalApp_216'";
		$query=mysqli_query($conn,$sql);
		$row = mysqli_fetch_assoc($query);
		$color1 = $row["Color1"];
		$color2 = $row["Color2"];
		$colors = $color1.",".$color2.":".$color1.",".$color2.":".$color1.",".$color2;
		return $colors;
	}
}
?>