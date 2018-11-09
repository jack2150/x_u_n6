<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Ajax Test Servers</title>
<script type="text/javascript" src="js/prototype.1.6.js"></script>

<script type="text/javascript">
current_count = 0;
intervalID = 0;

function test_server(sid) {
	new Ajax.Request('ajax_test_now.php', { method:'post',	parameters: { server_id: sid },
		onSuccess: function (transport) {
			$("emptydiv").insert({
				after: transport.responseText
			});
		}
	});
}

function test_all_servers() {
	if (current_count > total_server) {
		clearInterval(intervalID);
	}
	else {
		new Ajax.Request('ajax_test_now.php', { method:'post',	parameters: { server_id: serverList[current_count] },
			onSuccess: function (transport) {
				$("emptydiv").insert({
					after: transport.responseText
				});
				
				temp_sid = serverList[current_count];
				
				// update field
				$("status"+temp_sid).innerHTML = "<b>"+temp_sid+"</b>";
			},
			onFailure: function () {
				$("emptydiv").insert({
					after: "Server " + sid + " Failed!"
				});
				
				alert("Server failed: " + sid);
			},
			onException: function () {
				$("emptydiv").insert({
					after: "Server " + sid + " Failed!"
				});
				
				alert("Server failed: " + sid);
			}
		});

		current_count++;
	}
}

function run_test() {
	intervalID = setInterval ("test_all_servers()", 500);

}






</script>
</head>

<body>
<table>
<tr>
<?php
/**
 * This crontab change the upload server on 2,7,11,18 hours
 */
set_time_limit(60);
include ("../includes/http.php");  

// server id
$server_id = $_GET["server_id"];

/**
 * Step 1: Get all record from database 
 */
// open file server database 
include_once("../config.php");

// open sql connection and select databases
$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');
	
if (mysql_select_db($sql_database)) {
	// get all servers from 
	$servers = mysql_query("select * from server");
}
// close connection
mysql_close($conn);

$temp = server;
$count = -1;
while($server=mysql_fetch_assoc($servers)) {
	
	if ($count == 3) {
		echo "</tr>";
		echo "<tr>";
	}

	echo "<td>";
	echo "Test Server " . $server["name"] . " , Server ID: " .$server["server_id"] 
	. " , <a href='#' onclick='test_server({$server["server_id"]});' >Test Now</a>"
	. " , <sub id='status{$server["server_id"]}'>0</sub>\n\n";
	echo "</td>";
	
	if ($count == 3) {
		$count = 0;
	}
	else {
		$count++;
	}
	
	

	
	$server_id[] = $server["server_id"];
}
?>
</tr>
</table>
<script type="text/javascript">
var serverList = new Array();
<?php
$count = 0;
foreach ($server_id as $temp_id) {
	echo "serverList[{$count}] = {$temp_id};\n\n";
	$count++;
}
$count = $count - 1;
echo "total_server = {$count}\n";
?>

</script>

<br>
<br>
<div><a href="#" onclick="run_test();">[[ Test All Severs ]]</a></div>
<br>

<div id="emptydiv">&nbsp;</div>





</body>
</html>
