<?php 
// for($i=0;$i<100;$i++){
// 	// echo(rand() . "<br>");
// 	echo(rand(1,100000000) . "<br>");
// }

$start_date = new DateTime('2023-09-01 10:10:58');
$end_date = new DateTime('2023-09-01 10:25:00');
$since_start = $start_date->diff($end_date);
// echo $since_start->days.' days total<br>';
// echo $since_start->y.' years<br>';
// echo $since_start->m.' months<br>';
// echo $since_start->d.' days<br>';
echo $since_start->h.' hours<br>';
echo $since_start->i.' minutes<br>';
echo $since_start->s.' seconds<br>';
?>