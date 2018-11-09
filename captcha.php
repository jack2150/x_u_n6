<?php
/**
 * Captcha Key for Download
 */
error_reporting(0);

header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
header('Content-type: image/gif');

// generate random number
$private_key = mt_rand(100, 999);

// get specific data key
$captchaKey = $_GET[key];

// include, then open connection, then select db
include_once("config.php");
$db = mysql_connect($sql_host, $sql_user, $sql_pass);
mysql_select_db($sql_database);
@mysql_query("insert into captcha values (null , '".time()."', '"
	.$private_key."', '".$captchaKey."', '".$_SERVER[REMOTE_ADDR]."')");
mysql_close($db);

/**
 * [Feb 10 2011] replace with fopen
 * section: captcha 0,1,2,3,4,5
 */ 
$captcha_image = fopen("captcha/".mt_rand(0,2)."/".$private_key.".gif","rb");
echo fread($captcha_image,2048); fclose($captcha_image);

exit();
?>
