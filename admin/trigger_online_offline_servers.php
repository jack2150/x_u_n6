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
	$server = mysql_query("select * from server where order by server_id");
	while ($server = mysql_fetch_assoc($ref_result)) {
		echo "{$server["name"]}\n";
	}

}




?>

</body>
</html>
