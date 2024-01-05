<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

$menuArr = array();


if($roleId != null){
	$getTabSql= "Select * from Role where RoleId = $roleId";
}
$getTabResult = mysqli_query($conn,$getTabSql);
if(mysqli_num_rows($getTabResult) > 0){
	$tr = mysqli_fetch_Array($getTabResult);
	$mapMenuIds = $tr['MenuId'];
	$tabId = $tr['TabId'];

	if($tabId != null || $tabId != ""){
		$tabSql = "Select * from Tab where Id in ($tabId) and Active = 1 order by Seq";
		$tabQuery=mysqli_query($conn,$tabSql);
		$hashMap = new StdClass;
		
		//$jsonResArray = array();
		while($tabRow = mysqli_fetch_assoc($tabQuery)){
				
				

				if($tabRow['TabName'] == "Pending"){
					//echo $tabRow['TabName']."\n";

					$result1 = CallAPI("GET","http://216.48.181.73/UniversalApp/newAssign1.php?empId=$empId",true);
					$hashMap->{$tabRow["Id"]} = json_decode($result1);

					
				}
				else if($tabRow['TabName'] == "Completed"){
					//echo $tabRow['TabName']."\n";

					$result1 = CallAPI("GET","http://216.48.181.73/UniversalApp/submitted.php?empId=$empId&roleId=$roleId",true);
					$hashMap->{$tabRow["Id"]} = json_decode($result1);
				}
				else if($tabRow['TabName'] == "All"){
					$jsonResArray = array();
					$jsonRes = "";

					$res1 = CallAPI("GET","http://216.48.181.73/UniversalApp/newAssign1.php?empId=$empId",true);

					$res2 = CallAPI("GET","http://216.48.181.73/UniversalApp/submitted.php?empId=$empId&roleId=$roleId",true);
			
					$menuIds = $mapMenuIds;
					
					$res3 = CallAPI("GET","http://216.48.181.73/UniversalApp/getTabMenu.php?menuId=$menuIds",true);

					// $jsonRes1 = json_decode($res1);
					// $jsonRes2 = json_decode($res2);
					// $jsonRes3 = json_decode($res3);

					// $jsonRes4 = $jsonRes1->menu;
					// if(count($jsonRes4) != 0){
					//  	for($i=0;$i< count($jsonRes4);$i++){
					// 		array_push($jsonResArray,$jsonRes4[$i]);
					// 	}
					// }
					// $jsonRes5 = $jsonRes2->menu;
					// if(count($jsonRes5) != 0){
					//  	for($j=0;$j< count($jsonRes5);$j++){
					// 		array_push($jsonResArray,$jsonRes5[$j]);
					// 	}
					// }
					
					// $jsonRes6 = $jsonRes3->menu;
					// if(count($jsonRes6) != 0){
					//  	for($j=0;$j< count($jsonRes6);$j++){
					// 		array_push($jsonResArray,$jsonRes6[$j]);
					// 	}
					// }

					// $jsonRes->menu = $jsonResArray;
					// $hashMap->{$tabRow["Id"]} = $jsonRes;

					$all-> Pending = json_decode($res1);
					$all-> Completed = json_decode($res2);
					$all-> Library  = json_decode($res3);
					$hashMap->{$tabRow["Id"]} = $all;
				}
				else if($tabRow['TabName'] == "Maps"){
					$menuIds = "34";
					$result1 = CallAPI("GET","http://216.48.181.73/UniversalApp/getTabMenu.php?menuId=$menuIds",true);
					$hashMap->{$tabRow["Id"]} = json_decode($result1);
				}		
				
				else{
					$menuIds = $mapMenuIds;
					$result1 = CallAPI("GET","http://216.48.181.73/UniversalApp/getTabMenu.php?menuId=$menuIds",true);
					$hashMap->{$tabRow["Id"]} = json_decode($result1);
				}		
				
			

		}

		echo json_encode($hashMap);
	}
}

?>






<?php
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