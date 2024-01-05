<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");

$sql = "SELECT * FROM `Banner` where `IsActive` = 1";
$query=mysqli_query($conn,$sql);
$bannerArr = array();
while($row = mysqli_fetch_assoc($query)){
	$title = $row["Title"];
	$image = $row["Image"];
	$link = $row["Link"];

	$json = array(
		'title' => $title,
		'image' => $image,
		'link' => $link
	);
	array_push($bannerArr,$json);
}
echo json_encode($bannerArr);

?>