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
INSERT INTO popunder2 VALUES ({$today_date},1) 
ON DUPLICATE KEY UPDATE counter=counter+1;
");
//mysql_query("truncate table captcha");

// close connection
mysql_close($conn);


// random the popunder address
$pop = array();
//$pop[0] = "http://redirect.aleadpay.com/bvcmy.html";
//$randval = 0;
$pop[0] = "http://network.adsmarket.com/click/imNvnGfKfJSRZm7EYJx6momQacReynrDiJBp?ctype=ctz&dp=b";
$randval = 0;

header("location: ".$pop[$randval]);
?>