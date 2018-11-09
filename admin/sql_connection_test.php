<?php
$sql_host = $_GET["sql_host"];
$sql_user = $_GET["sql_user"];
$sql_pass = $_GET["sql_pass"];
$sql_db = $_GET["sql_db"];

$db = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die("cannot connect to sql host!!!");
	
if ($db) {
	echo "$sql_host - Connection ok!<br>";
	if (mysql_select_db($sql_db)) {
		echo "$sql_db - Database ok!<br>";
	}
	else {
		echo "$sql_db - Database error!<br>";
	}
}
else {
	echo "$sql_host - Connection error!<br>";
}

mysql_close($db);
?>