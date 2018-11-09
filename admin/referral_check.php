<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Referral Checking!!!</title>
</head>

<body>
<form action="" method="get">
<table cellspacing="2" cellpadding="2">
  <tr>
    <td>Referral User ID:</td>
    <td><input name="uid" type="text" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input name="" type="submit" /></td>
  </tr>
</table>
</form>

<?php 

if ($_GET) {
	// open file server database 
	include("../config.php");
	
	// open sql connection and select databases
	$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
		or die ('Error connecting to mysql');
	
	mysql_select_db($sql_database);	
	
	$user_id = trim($_GET["uid"]);
	
	// output subscr info
	$count = 0;
	$ref_result = mysql_query("select * from referrals where uid = '{$user_id}' order by time");
	while ($ref = mysql_fetch_assoc($ref_result)) {
		echo "<table cellspacing='2' cellpadding='2'>";
		echo "<caption><b>Subscr Record</b></caption>";
		echo "<tr><td>REF USERID:</td><td>{$ref["uid"]}</td></tr>";
		echo "<tr><td>REF Amount:</td><td>{$ref["amount"]}</td></tr>";
		echo "<tr><td>REF Package:</td><td>{$ref["package"]}</td></tr>";
		echo "<tr><td>REF Time:</td><td>".date("F j, Y, g:i a", $ref["time"])."</td></tr>";
		echo "</table><br><br>";
		
		$count++;
	}
		
	echo "Total Premium Sale: $count";

	

	// close connection
	mysql_close($conn);
}




?>

</body>
</html>
