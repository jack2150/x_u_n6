<?php 
/*
include("config.php");

$client_ip = $_SERVER['REMOTE_ADDR'];
$check_time = time() - (2 * 60); // within 2 mins
$access_key = md5($client_ip.$check_time);

$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');

mysql_select_db($sql_database);

// get existing ip
$result = mysql_query("select * from filters where ip='{$client_ip}' limit 1");

mysql_close($conn);

// check ip count
if (mysql_num_rows($result)) {
	// existing ip
	$filters = mysql_fetch_assoc($result);
	
	// filters, check ip access count, check time within 2 mins
	if ($filters["c"] > 10) {
		if ($filters["t"] > $check_time) {
			// block,
			exit;
		}
		else {
			mysql_query("update filters set c=c+1, t={$check_time} where ip='{$client_ip}' limit 1");
			// grant access
		}
	}
}
else {
	// not exist, insert new
	mysql_query("insert into filters values ('{$client_ip}',1,{$check_time},'{$access_key}') 
		on duplicate key update c=c+1, t={$check_time}");
	
	// grant access
	
}
$link = "http://www.xun6.net/download.php?{$_SERVER["QUERY_STRING"]}&k={$access_key}";
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Redirecting...</title>
<meta http-equiv="refresh" content="0;url=<?php echo $link; ?>" >
</head>

<body>
Redirecting and Verifying....
</body>
</html>
