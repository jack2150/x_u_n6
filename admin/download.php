<?php
include_once("config.php");
$db = mysql_connect($sql_host, $sql_user, $sql_pass);
mysql_select_db($sql_database);	
$result = mysql_query("select http, domain, upload_dir from server where server_id='$_GET[server]' limit 1");
if (mysql_num_rows($result)) {
	$tb_server = mysql_fetch_assoc($result);
	
	$year = date("Y",$_GET['time']);
	$month = date("m",$_GET['time']);
	$day = date("d",$_GET['time']);
	$hour = date("H",$_GET['time']);
	header("location: {$tb_server['http']}{$tb_server['domain']}/admin/download.php?size={$_GET['size']}&f={$tb_server['upload_dir']}/{$year}/{$month}/{$day}/{$hour}/{$_GET['f']}/{$_GET['f']}&name={$_GET['name']}&ext={$_GET['ext']}");
	//echo "location: {$tb_server['http']}{$tb_server['domain']}/admin/download.php?size={$_GET['size']}&f={$tb_server['upload_dir']}/{$year}/{$month}/{$day}/{$hour}/{$_GET['f']}/{$_GET['f']}&name={$_GET['name']}&ext={$_GET['ext']}";
}
mysql_close($db);

//echo $_GET['server'];
?>