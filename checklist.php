<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
include("dbConfiguration.php");
$empId=$_REQUEST['empId'];
$roleId=$_REQUEST['roleId'];

$menuArr = array();
/* 
$sql = "SELECT distinct `MenuId` FROM `Mapping` WHERE `EmpId` = '$empId' and `Active` = 1 and ActivityId = 0 ";
$query=mysqli_query($conn,$sql);

while($row = mysqli_fetch_assoc($query)){
	$menuId = $row["MenuId"];
	array_push($menuArr,$menuId);
	
}

$verifierSql = "SELECT distinct mp.MenuId
				FROM Mapping mp
				join TransactionHDR th on (mp.ActivityId = th.ActivityId)	
				WHERE mp.Verifier = '$empId' and mp.Active = 1 and th.Status = 'Created'";
$verifierQuery=mysqli_query($conn,$verifierSql);
while($vrow = mysqli_fetch_assoc($verifierQuery)){
	$vMenuId = $vrow["MenuId"];
	array_push($menuArr,$vMenuId);
	
}

$approverSql = "SELECT distinct mp.MenuId
				FROM Mapping mp
				join TransactionHDR th on (mp.ActivityId = th.ActivityId)	
				WHERE mp.Approver = '$empId' and mp.Active = 1 and th.Status = 'Verified'";
$approverQuery=mysqli_query($conn,$approverSql);
while($arow = mysqli_fetch_assoc($approverQuery)){
	$aMenuId = $arow["MenuId"];
	array_push($menuArr,$aMenuId);
	
}

*/

$roleSql = "SELECT distinct `MenuId` FROM `Role` WHERE `RoleId` = '$roleId' ";
$roleQuery=mysqli_query($conn,$roleSql);
while($roleRow = mysqli_fetch_assoc($roleQuery)){
	$roleMenuId = $roleRow["MenuId"];
	$roleMenuIdExplode = explode(",", $roleMenuId);
	for($i=0;$i<count($roleMenuIdExplode);$i++){
		array_push($menuArr,$roleMenuIdExplode[$i]);
		/*if(!in_array($roleMenuIdExplode[$i], $menuArr)){
			array_push($menuArr,$roleMenuIdExplode[$i]);
		}*/
	}
	
}

$newArr = array_unique($menuArr);

$menuIds = convertListInOperatorValue($newArr);
//echo $menuIds;

$menuSql = "SELECT `Cat` FROM `Menu` WHERE `MenuId` in ($menuIds)";
$menuQuery=mysqli_query($conn,$menuSql);
$catArr = array();
while($menuRow = mysqli_fetch_assoc($menuQuery)){
	$cat = $menuRow["Cat"];
	if(!in_array($cat, $catArr) && ($cat != null || $cat != '')){
		array_push($catArr,$cat);
	}
}
//echo json_encode($catArr);
//echo count($catArr);
$resultArr = array();
for($i = 0; $i < count($catArr); $i++){
	$subCatSql = "SELECT `Sub`,`Caption` FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
	$subCatQuery=mysqli_query($conn,$subCatSql);
	$levelType = "";
	while($subCatRow = mysqli_fetch_assoc($subCatQuery)){
		$sub = $subCatRow["Sub"];
		$caption = $subCatRow["Caption"];
		if($sub == '' && $caption == ''){
			// first level
			$levelType = 'FIRST';
		}
		else if($sub != '' && $caption == ''){
			// second level
			$levelType = 'SECOND';
		}
		else if($sub != '' && $caption != ''){
			// third level
			$levelType = 'THIRD';
		}
	}

	if($levelType == "FIRST"){
		$subCatSqlll = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
		$subCatQueryyy=mysqli_query($conn,$subCatSqlll);
		while($subCatRowww = mysqli_fetch_assoc($subCatQueryyy)){
			$aa = $subCatRowww["MenuId"];
			$bb = $subCatRowww["Cat"];
			$ee = $subCatRowww["CheckpointId"];
			$ff = $subCatRowww["Active"];
			$gg = $subCatRowww["Icons"];
			$hh = $subCatRowww["GeoFence"];
			$ii = $subCatRowww["Verifier"];
			$jj = $subCatRowww["Approver"];

			$hhExplode = explode(":", $hh);
			$GeoCoordinate = $hhExplode[0];
			if($GeoCoordinate == ""){
				$GeoCoordinate = null;
			}
			$GeoFence = $hhExplode[1];
			if($GeoFence == ""){
				$GeoFence = null;
			}


			$iconExplode = explode(",", $gg);
			$categoryIcon = $iconExplode[0];

			$json1 = array();
			$json1 = array(
				'menuId' => $aa,
				'Caption' => $catArr[$i],
				'Icon' => $categoryIcon, 
				'subCategoryList' => array(),
				'checkpointId' => $ee,
				'active' => $ff,
				'Editable' => "",
				'GeoCoordinate' => $GeoCoordinate,
				'GeoFence' => $$GeoFence,
				'verifier' => $ii,
				'approver' => $jj
			);
			array_push($resultArr,$json1);
		}
	}
	else if($levelType == "SECOND"){
		$subCatSqll = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
		$subCatQueryy=mysqli_query($conn,$subCatSqll);
		$categoryIcon = "";
		$resultSubCatArr = array();
		$subCatArr = array();
		while($subCatRoww = mysqli_fetch_assoc($subCatQueryy)){
			$subb = $subCatRoww["Sub"];
			if(!in_array($subb, $subCatArr) && ($subb != null || $subb != '') ){
				$aa = $subCatRoww["MenuId"];
				$bb = $subCatRoww["Sub"];
				$ee = $subCatRoww["CheckpointId"];
				$ff = $subCatRoww["Active"];
				$gg = $subCatRoww["Icons"];
				$hh = $subCatRoww["GeoFence"];
				$ii = $subCatRoww["Verifier"];
				$jj = $subCatRoww["Approver"];

				$hhExplode = explode(":", $hh);
				$GeoCoordinate = $hhExplode[0];
				if($GeoCoordinate == ""){
					$GeoCoordinate = null;
				}
				$GeoFence = $hhExplode[1];
				if($GeoFence == ""){
					$GeoFence = null;
				}

				$iconExplode = explode(",", $gg);
				$categoryIcon = $iconExplode[0];
				$subCategoryIcon = $iconExplode[1];

				$json2 = array();
				$json2 = array(
					'menuId' => $aa,
					'Caption' => $bb,
					'Icon' => $subCategoryIcon,
					'subCategoryList' => array(),
					'checkpointId' => $ee,
					'active' => $ff,
					'Editable' => "",
					'GeoCoordinate' => $GeoCoordinate,
					'GeoFence' => $GeoFence,
					'verifier' => $ii,
					'approver' => $jj
				);
				array_push($resultSubCatArr,$json2);
			}
		}
		$json1 = array();
		$json1 = array('Caption' => $catArr[$i],'Icon' => $categoryIcon, 'subCategoryList' => $resultSubCatArr);
		array_push($resultArr,$json1);

	}
	else if($levelType == "THIRD"){
		$subCatSqll = "SELECT `Sub` FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]'";
		$subCatQueryy=mysqli_query($conn,$subCatSqll);

		$categoryIcon = "";
		$resultSubCatArr = array();
		$subCatArr = array();
		while($subCatRoww = mysqli_fetch_assoc($subCatQueryy)){
			$subb = $subCatRoww["Sub"];
			if(!in_array($subb, $subCatArr) && ($subb != null || $subb != '') ){
				array_push($subCatArr,$subb);

				$subCategoryIcon = "";
				$resultCapArr = array();
				$captionArr = array();
				$captionSql = "SELECT * FROM `Menu` WHERE `MenuId` in ($menuIds) and `Cat` = '$catArr[$i]' and `Sub` = '$subb'";

				$captionQuery=mysqli_query($conn,$captionSql);
				while($captionRow = mysqli_fetch_assoc($captionQuery)){
					$caption = $captionRow["Caption"];
					if(!in_array($caption, $captionArr) && ($caption != null || $caption != '')){
						array_push($captionArr,$caption);

						$aa = $captionRow["MenuId"];
						$bb = $captionRow["Caption"];
						$ee = $captionRow["CheckpointId"];
						$ff = $captionRow["Active"];
						$gg = $captionRow["Icons"];
						$hh = $captionRow["GeoFence"];
						$ii = $captionRow["Verifier"];
						$jj = $captionRow["Approver"];

						$hhExplode = explode(":", $hh);
						$GeoCoordinate = $hhExplode[0];
						if($GeoCoordinate == ""){
							$GeoCoordinate = null;
						}
						$GeoFence = $hhExplode[1];
						if($GeoFence == ""){
							$GeoFence = null;
						}

						$iconExplode = explode(",", $gg);
						$categoryIcon = $iconExplode[0];
						$subCategoryIcon = $iconExplode[1];

						$json3 = array();
						$json3 = array(
							'menuId' => $aa,
							'Caption' => $bb,
							'Icon' => $iconExplode[2],
							'checkpointId' => $ee,
							'active' => $ff,
							'Editable' => "",
							'GeoCoordinate' => $GeoCoordinate,
							'GeoFence' => $GeoFence,
							'verifier' => $ii,
							'approver' => $jj
						);
						array_push($resultCapArr,$json3);
					}
				}
				$json2 = array();
				$json2 = array('Caption' => $subb,'Icon' => $subCategoryIcon, 'subCategoryList' => $resultCapArr);
				array_push($resultSubCatArr,$json2);
			}	
		}
		$json1 = array();
		$json1 = array('Caption' => $catArr[$i],'Icon' => $categoryIcon, 'subCategoryList' => $resultSubCatArr);
		array_push($resultArr,$json1);
	}
}
$confSql = "Select * from configuration";
$confQuery = mysqli_query($conn, $confSql);
$conf = mysqli_fetch_assoc($confQuery);
$confObj = new StdClass;
$confObj -> inf = $conf['inf'];
$confObj -> conn = $conf['conf'];
$confObj -> Start = $conf['start'];
$confObj -> End = $conf['end'];
$confObj -> Battery = $conf['Battery'];
$confObj -> Image = $conf['image'];
$res = new StdClass;
$res->menu = $resultArr;
$res->conf = $confObj;
//$output = array();
//$output = array('menu' => $resultArr);
echo json_encode($res);
?>

<?php
function convertListInOperatorValue($arrName){
	$inOperatorValue = "";
	for ($x = 0; $x < count($arrName); $x++) {
		$inOperatorValue = $inOperatorValue."'".$arrName[$x]."'";
		if($x < count($arrName)-1){
			$inOperatorValue = $inOperatorValue.",";
		}
	}
	return $inOperatorValue;
}
?>