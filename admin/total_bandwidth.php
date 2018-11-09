<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Bandwidth Record Warnings!!!</title>
</head>

<body>

<?php
/**
 * Show Total Premium Account that is Active!!!
 */
function convertsize($size,$mode=0)
{
   $times = 0;
   $comma = '.';
   while ($size>1024){
      $times++;
      $size = $size/1024;
   }
   $size2 = floor($size);
   $rest = $size - $size2;
   $rest = $rest * 100;
   $decimal = floor($rest);

   $addsize = $decimal;
   if ($decimal<10) {$addsize .= '0';};

   if ($times == 0){$addsize=$size2;} else
   {$addsize=$size2.$comma.substr($addsize,0,2);}

   switch ($times) {
      case 0 : $mega = " Byte"; break;
      case 1 : $mega = " KB"; break;
      case 2 : $mega = " MB"; break;
      case 3 : $mega = " GB"; break;
      case 4 : $mega = ' TB'; break;
   }
   if($mode==1&&(($pos=strrpos($addsize,'.')))!==false)$addsize=substr($addsize,0,$pos);
   $addsize .= $mega;
   return $addsize;
}

error_reporting(0);

// expired within 12 minutes
$expired_datetime = time() - (12 * 60);

// open file server database 
include("../config.php");

// open sql connection and select databases
$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');

mysql_select_db($sql_database);

$result = mysql_query("select * from users where gid=3 or gid=4");

$users = array();
while($temp = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$temp_bw = doubleval($temp["bandwidth"]);
	$users[$temp["id"]]["bandwidth"] = $temp_bw;
	$users[$temp["id"]]["total_dl"] = $temp["totaldownloads"];
	$users[$temp["id"]]["username"] = $temp["user"];
	$users[$temp["id"]]["expire_date"] = $temp["expire_date"];
	$users[$temp["id"]]["subscr_period"] = $temp["subscr_period"];
	$users[$temp["id"]]["predl_size"] = ceil($temp_bw / $temp["totaldownloads"]);
	
}

asort($users);

$show_size = 100 * 1024 * 1024;
echo "<table border=0>";
foreach ($users as $key => $val) {
	if ($val["bandwidth"] >= $show_size) {
    	echo "<tr><td>UID: $key </td><td>NAME: {$val["username"]}</td>
    		<td>EXPIRE: ".date("n/d/Y",$val["expire_date"])."</td>
    		<td>LENGTH: ".$val["subscr_period"]."</td>
    		<td>BW: ".convertsize($val["bandwidth"])."</td><td>DL: {$val["total_dl"]}</td>
    		<td>AVERAGE: ".convertsize($val["predl_size"])."</td></tr>";
	}
}
echo "</table>";

// close connection
mysql_close($conn);
?>

</body>
</html>
