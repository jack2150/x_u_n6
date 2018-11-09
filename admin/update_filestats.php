<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Update Filestats</title>
<script language="javascript" type="text/javascript" src="js/prototype-1.6.0.2.js"></script>
<script type="text/javascript">
function reload_field() {
	new Ajax.Updater('maintain_field', '../crontabs/filestats_maintain2.php', { method: 'get' });
	reloadTimer = setTimeout("reload_field()", 5000);
}

function reload_start() {
	reloadTimer = setTimeout("reload_field()", 5000);
}

function stop_reload() {
	clearTimeout(reloadTimer);
}
</script>
</head>

<body>
<h3>Ajax Reload Filestats Maintains</h3>
<div id="maintain_field">Nothing Here...</div>
<input type="button" onclick="reload_start();" value="Start!" />
<input type="button" onclick="stop_reload();" value="Stop!" />
<br />
<br />

<h3>Update Filestats in Fileservers</h3>
<?php
/**
 * Update filestats to fileserver database
 * and then update it into web database!
 */

set_time_limit(300);

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


while($server=mysql_fetch_assoc($servers)) {
	echo "[{$server["server_id"]}] <a href='http://{$server["domain"]}/crontabs/sessions_filestats.php' target='_blank'>a. Update Server {$server["name"]} From Sessions!</a><br />";
	echo "[{$server["server_id"]}] <a href='http://{$server["domain"]}/crontabs/update_filestats_all.php' target='_blank'>b. Update Server {$server["name"]} To Web Database!</a><br />";
	echo "<hr />";
}

?>
</body>
</html>