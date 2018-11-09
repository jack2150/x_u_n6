<?php
// open file server database 
include("../config.php");

// open sql connection and select databases
$conn = mysql_pconnect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');

mysql_select_db($sql_database);

$month_start = mktime(0,0,0,10,1,2008);
$month_end = mktime(0,0,0,11,1,2008);

/**
 * Step 1: Get all record from fstemp
 */
$result = mysql_query("select sum(earning) as earning, uid from earnings where time >= $month_start and time < $month_end group by uid order by earning desc");

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($row["earning"] >= 10) {
		echo "User ID: {$row["uid"]}, Earning: {$row["earning"]}<br>";
	}
}

// close connection
mysql_close($conn);
?>