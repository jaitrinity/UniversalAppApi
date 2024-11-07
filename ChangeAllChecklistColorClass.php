<?php
class ChangeAllChecklistColorClass{
	function changeChecklistColor($color1, $color2){
		require_once 'dbConfiguration.php';

		$catBgFontColor = $color1.",".$color2;
		$subCatBgFontColor = $color1.",".$color2;
		$capBgFontColor = $color1.",".$color2;

		// $sql = "UPDATE `Menu` set `CatBgFontColor`='$catBgFontColor', `SubCatBgFontColor`='$subCatBgFontColor', `CapBgFontColor`='$capBgFontColor' where `MenuId` = 1";
		$sql = "UPDATE `Menu` set `CatBgFontColor`='$catBgFontColor', `SubCatBgFontColor`='$subCatBgFontColor', `CapBgFontColor`='$capBgFontColor'";
		mysqli_query($conn,$sql);

	}
}
?>