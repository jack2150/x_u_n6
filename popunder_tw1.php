<?php
// set counter
$today_date = mktime(0,0,0,date("n"),date("j"),date("Y"));

// open file server database 
include("config.php");

// open sql connection and select databases
$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');

mysql_select_db($sql_database);

// delete captcha that is time before 12 minutes from now
mysql_query("
INSERT INTO popunder VALUES ({$today_date},1) 
ON DUPLICATE KEY UPDATE counter=counter+1;
");
//mysql_query("truncate table captcha");

// close connection
mysql_close($conn);

// random the popunder address
$pop = array();
/*
$pop[0] = "http://tracking1.aleadpay.com/Tracking/Clicks/Y21waWQ9Mjg4NyZ0c2lkPTM2NCAg/";
$pop[1] = "http://tracking1.aleadpay.com/Tracking/Clicks/Y21waWQ9MzA1MCZ0c2lkPTM2NCAg/";
$pop[2] = "http://tracking1.aleadpay.com/Tracking/Clicks/Y21waWQ9MzU4MiZ0c2lkPTM2NCAg/";
$pop[3] = "http://tracking1.aleadpay.com/Tracking/Clicks/Y21waWQ9MzQ3MyZ0c2lkPTM2NCAg/";
$pop[4] = "http://tracking1.aleadpay.com/Tracking/Clicks/Y21waWQ9MzQ4OCZ0c2lkPTM2NCAg/";
$pop[5] = "http://tracking1.aleadpay.com/Tracking/Clicks/Y21waWQ9MzQ2OCZ0c2lkPTM2NCAg/";
$randval = rand(0, 5);
*/
$pop[0] = "http://tracking1.aleadpay.com/Tracking/Clicks/116/";

$randval = 0;

header("location: ".$pop[$randval]);
?>