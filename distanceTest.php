<?php 
$api_key = "AIzaSyBzin8T1YLrzvFY80YjLH86nN1LeV2EzRU";
$origin = "28.6229897,77.3663686";
$distinations = "36.9354515,44.0343305";
$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$distinations.'&key='.$api_key;
$json_data=file_get_contents($url);
echo $json_data;
?>