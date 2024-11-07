<?php 
include("dbConfiguration.php");
$searchDate = $_REQUEST["searchDate"];
$filterSql = "";
if($searchDate == null || $searchDate == ''){
	$filterSql .= "and date(`MobileDateTime`)=curDate()";
}
else{
	$filterSql .= "and date(`MobileDateTime`)='$searchDate'";
}
$data = "<table border=1 cellpadding=3 cellspacing=0>
<tr>
	<th>Activity Id</th>
	<th>Event</th>
	<th>Geolocation</th>
	<th>MobileDateTime</th>
</tr>";
$sql = "SELECT `ActivityId`, `Event`, `GeoLocation`, `MobileDateTime` FROM `Activity` where `EmpId`='117' $filterSql ORDER BY `ActivityId`  DESC";
$query=mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$data .= "<tr>
				<td>".$row['ActivityId']."</td>
				<td>".$row['Event']."</td>
				<td>".$row['GeoLocation']."</td>
				<td>".$row['MobileDateTime']."</td>
			</tr>";
}
$data .= "</table>";
header('Content-Type: text/html');
echo $data;
?>