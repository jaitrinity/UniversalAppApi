<?php 
include("dbConfiguration.php");

require 'SendMailClass.php';
$classObj = new SendMailClass();

$yesterdayDate = date('Y-m-d', strtotime('-1 day'));
$subject = "Visit - ".$yesterdayDate;
$msg = "Hi";
$toMailId = "jai.prakash@trinityapplab.co.in";
$classObj->sendMail($toMailId, $subject, $msg, null);

?>
