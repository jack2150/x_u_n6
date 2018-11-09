<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Popup Report!!!</title>
</head>

<body>

<?php
/**
 * Show Total Premium Account that is Active!!!
 */
//error_reporting(0);

// expired within 12 minutes
$expired_datetime = time() - (12 * 60);

// open file server database 
include("../config.php");

// open sql connection and select databases
$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');

mysql_select_db($sql_database);

// get id
if ($_GET["id"]) {
	$pop_section = $_GET["id"];
}
else {
	$pop_section = "";
}

// start date
if ($_GET["start_date"]) {
	$start_date = mktime(0,0,0,date("n"),$_GET["start_date"],date("Y"));
}
else {
	$start_date = mktime(0,0,0,date("n"),date("d")-31,date("Y"));
}

$table_name = "popunder".$pop_section;


$result = mysql_query("select * from $table_name where time>=$start_date");

echo "<table border=0>";
echo "<tr><td>Date</td><td>Weekdays</td><td>Traffics</td></tr>";
while($temp = mysql_fetch_array($result, MYSQL_ASSOC)) {
	echo "<tr><td>".date("n/d/Y",$temp["time"])."</td><td>".date("l",$temp["time"])."</td><td>{$temp["counter"]}</td></tr>";
}
echo "</table>";



// close connection
mysql_close($conn);
?>

</body>
</html>
