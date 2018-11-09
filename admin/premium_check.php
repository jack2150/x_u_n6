<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Premium Checking!!!</title>
</head>

<body>
<form action="" method="get">
<table cellspacing="2" cellpadding="2">
  <tr>
    <td>Email:</td>
    <td><input name="email" type="text" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>OR</td>
  </tr>
  <tr>
    <td>UserID:</td>
    <td><input name="uid" type="text" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>OR</td>
  </tr>
  <tr>
    <td>Username:</td>
    <td><input name="username" type="text" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>OR</td>
  </tr>
  <tr>
    <td>PIN:</td>
    <td><input name="pin" type="text" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
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
	$username = trim($_GET["username"]);
	$user_email = trim($_GET["email"]);
	$pin = trim($_GET["pin"]);
	
	if ($user_email) {
		// using email
		$result = mysql_query("select * from users where email = '{$user_email}' limit 1");
		$users = mysql_fetch_assoc($result);
	}
	elseif ($user_id) {
		// using uid
		$result = mysql_query("select * from users where id = '{$user_id}' limit 1");
		$users = mysql_fetch_assoc($result);
	}
	elseif ($pin) {
		$result1 = mysql_query("select * from pin where pin='{$pin}' limit 1");
		$pins = mysql_fetch_assoc($result1);
		$result = mysql_query("select * from users where id='{$pins["uid"]}' limit 1");
		$users = mysql_fetch_assoc($result);
	}
	else {
		// using username
		$result = mysql_query("select * from users where user = '{$username}' limit 1");
		$users = mysql_fetch_assoc($result);
	}
	
	echo "<hr>";
	echo "<br>";
	echo "<table cellspacing='2' cellpadding='2'>";
	echo "<caption><b>User Record</b></caption>";
	echo "<tr><td>UserID:</td><td>{$users["id"]}</td></tr>";
	echo "<tr><td>Email:</td><td>{$users["email"]}</td></tr>";
	echo "<tr><td>Username:</td><td>{$users["user"]}</td></tr>";
	echo "<tr><td>Password:</td><td>{$users["pass"]}</td></tr>";
	echo "<tr><td>Expire Date:</td><td>".date("F j, Y, g:i a", $users["expire_date"])."</td></tr>";
	echo "</table><br><br>";
	echo "<hr>";
	
	// output subscr info
	$subscr_result = mysql_query("select * from subscrs where uid = '{$users["id"]}' order by time");
	while ($subscrs = mysql_fetch_assoc($subscr_result)) {
		echo "<table cellspacing='2' cellpadding='2'>";
		echo "<caption><b>Subscr Record</b></caption>";
		echo "<tr><td>Subscr ID:</td><td>{$subscrs["uid"]}</td></tr>";
		echo "<tr><td>Subscr IPN:</td><td>{$subscrs["ipn_id"]}</td></tr>";
		echo "<tr><td>Subscr PIN:</td><td>{$subscrs["pin_id"]}</td></tr>";
		echo "<tr><td>Subscr Amount:</td><td>{$subscrs["amount"]}</td></tr>";
		echo "<tr><td>Subscr Time:</td><td>".date("F j, Y, g:i a", $subscrs["time"])."</td></tr>";
		echo "<tr><td>Subscr Start Time:</td><td>".date("F j, Y, g:i a", $subscrs["start_time"])."</td></tr>";
		echo "<tr><td>Subscr Expire Time:</td><td>".date("F j, Y, g:i a", $subscrs["expire_time"])."</td></tr>";
		echo "<tr><td>Subscr Country:</td><td>".$subscrs["country"]."</td></tr>";
		echo "</table><br><br>";
			
		if ($subscrs["ipn_id"]) {
			// ipn info
			$ipn_result = mysql_query("select * from ipn where id = '{$subscrs["ipn_id"]}' limit 1");
			$ipn = mysql_fetch_assoc($ipn_result);
			
			echo "<table cellspacing='2' cellpadding='2'>";
			echo "<caption><b>IPN Record</b></caption>";
			echo "<tr><td>IPN Valid:</td><td>{$ipn["valid"]}</td></tr>";
			echo "<tr><td>IPN Amount:</td><td>{$ipn["mc_gross"]}</td></tr>";
			echo "<tr><td>IPN Date:</td><td>{$ipn["payment_date"]}</td></tr>";
			echo "<tr><td>IPN Email:</td><td>{$ipn["payer_email"]}</td></tr>";
			echo "</table>";
			
		}
		if ($subscrs["pin_id"]) {
			$pin_result = mysql_query("select * from pin where id = '{$subscrs["pin_id"]}' limit 1");
			$pin = mysql_fetch_assoc($pin_result);
			
			echo "<table cellspacing='2' cellpadding='2'>";
			echo "<caption><b>PIN Record</b></caption>";
			echo "<tr><td>PIN No:</td><td>{$pin["pin"]}</td></tr>";
			echo "<tr><td>PIN Pass:</td><td>{$pin["pass"]}</td></tr>";
			echo "<tr><td>PIN Type:</td><td>{$pin["type"]}</td></tr>";
			echo "<tr><td>PIN Used:</td><td>{$pin["used"]}</td></tr>";
			echo "<tr><td>PIN Price:</td><td>{$pin["price"]}</td></tr>";
			echo "<tr><td>PIN Active Time:</td><td>".date("F j, Y, g:i a", $pin["activate_time"])."</td></tr>";
			echo "<tr><td>PIN Reseller ID:</td><td>{$pin["reseller_id"]}</td></tr>";
			echo "</table>";
		}
		
		echo "<hr>";
	}
		

	

	// close connection
	mysql_close($conn);
}




?>

</body>
</html>

