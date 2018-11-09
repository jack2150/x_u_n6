<?php
error_reporting(E_ALL);
set_time_limit(0);

include("_ssh.php");
if ($_GET["server_id"]) {
	echo 1;
	$connection = ssh2_connect($servers["host"][$_GET["server_id"]], 22);
	echo 2;
	if (ssh2_auth_password($connection, $servers["user"][$_GET["server_id"]], $servers["pass"][$_GET["server_id"]])) {
	  echo "Authentication Successful!<br>";
	} 
	else {
	  die('Authentication Failed...');
	}
	echo 3;
	
	$stream = ssh2_exec($connection, 'service mysqld restart');
	stream_set_blocking($stream, true);
	$output1 = stream_get_contents($stream);
	
	echo $output1 . "<br />";
	
	$stream = ssh2_exec($connection, 'service httpd restart');
	stream_set_blocking($stream, true);
	$output2 = stream_get_contents($stream);
	
	echo $output2 . "<br />";
}
else {
	echo "Server not select!";
}
?>
