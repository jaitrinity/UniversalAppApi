<?php
define('API_ACCESS_KEY','AAAAhTZjYTU:APA91bEiLVxljmgkFBLay8Im7qc_CnBG0PVFS75c_BK_6EJVo2ZYIPjuX7337iOQ8SfP-P5PxWmvexjIrEWNI4hV7ty84e9ylURqpYfoTsf8zv8NYET0fyQUpfx31jvnj1rmLfRquEJx');
//define('API_ACCESS_KEY','AAAApcvGpIk:APA91bGE4oZ3mHoGuQTgMY3rsQ-FacO5AhjiHxLkVj9KSm_rQbyaW-09ch9RtBb8birb3exaqEuZ6iwcO0WB-8WMESVOvl05KnMlOQqpVqzSGeCg7CGXwxdTVloPyTXOKDyiytfStNAu');
 
$fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//$token = "di1gdmyyyug:APA91bE6Mvih8PjMctVW7eS5UkQCQtwvchm6acRTumBytyn1TApMhImVvHrnlB2HvnCFqyIaT7pKU4qoHue3dUCKm6INjmK0QJ2x-eMkoJFqCYUN5qbrplveygCACY8vv8D6o-hqrvMi";
 
$tabId = "2";
 
include("dbConfiguration.php");


$dSql = "Select m.EmpId,d.Token as token,m.MappingId,m.Start,m.End,mn.Cat as MenuName,l.Name as LocName
		from Mapping m
		join Devices d on (m.EmpId = d.EmpId)
		join Menu mn on (m.MenuId = mn.MenuId)
		left join Location l on (m.LocationId = l.LocationId)
		where date(m.CreateDateTime) = date(Now()) and m.ActivityId = 0
		 and date(m.End) >= date(NOW())";

$dQuery = mysqli_query($conn,$dSql);
$dCount = mysqli_num_rows($dQuery);

if($dCount > 0){
	while($dRow = mysqli_fetch_assoc($dQuery)){
		
		$notObj = new StdClass;
		$notObj->token = $dRow['token'];

		//$notObj->token = $token;
		$notObj->id = $dRow['MappingId'];
		$notObj->eventTitle = $dRow['MenuName'];
		$notObj->name = $dRow['LocName'];
		$notObj->fromDate = $dRow['Start'];
		$notObj->toDate = $dRow['End'];
		$notObj->tabId = $tabId;
		sendNotification($notObj);
	}
}



//$token = "dchQ92g0S-6VwdxRHuOKGX:APA91bGZTqfTQUnyBVyLk8scCFi0WKmqeDS_CXBVWneb0wcSKIbJU5xnY41oa-UMA_HgA-FRqzVJYzXqneSa7EgXcCYjMVlYxMiseU7n-D7CJZ7UJbvqTfnGNVOMHp9X9fHPSD3AnHGd";


//sendNotification($obj);

function sendNotification($obj){

	global $fcmUrl;

	$token = $obj->token;
	
	$subtitle = $obj->eventTitle ;
	$body = "Location - ".$obj->name."\n"."Start - ".$obj->fromDate."\n"."End - ".$obj->toDate;
	
	$notification = [
            'title' =>'New Task Assigned',
	     'subtitle' => $subtitle,
            'body' => $body,
            'icon' =>'myIcon', 
            'sound' => 1
        
	];
	
	$data = $obj;
	
       // $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
            'to'        => $token, //single token
            'notification' => $notification,
            'data' => $data
        ];

        $headers = [
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        ];

	echo json_encode($fcmNotification);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);

	if(curl_errno($ch))
	{
	    echo 'error:' . curl_error($ch);
	}

        curl_close($ch);

        echo $result;

}
    
		
?>