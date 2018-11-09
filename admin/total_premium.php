<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Premium Sale Records</title>
</head>

<body>

<?php
/**
 * Show Total Premium Account that is Active!!!
 */
error_reporting(0);

// expired within 12 minutes
$expired_datetime = time() - (12 * 60);

// open file server database 
include("../config.php");

// open sql connection and select databases
$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');

mysql_select_db($sql_database);

if ($_GET["month"]) {
	$current_month = $_GET["month"];
}
else {
	$current_month = date("n");
}

if ($_GET["year"]) {
	$current_year = $_GET["year"];
}
else {
	$current_year = date("Y");
}

$start_date = 1;

$start_time = mktime(0,0,0,$current_month , $start_date, $current_year);

if ($current_month == 12) {
	$end_time = mktime(0,0,0,1,1,$current_year+1);
}
else {
	$end_time = mktime(0,0,0,$current_month+1, $start_date, $current_year);
}

// create an empty month
$date_of_month = array(31,29,31,30,31,30,31,31,30,31,30,31);

$this_month_date = $date_of_month[$current_month - 1];


for ($i = 0; $i <= $this_month_date; $i++) {
	$sale[$i] = 0;
	$amount[$i] = 0;	
	
	$create[$i] = 0;
	$upgrade[$i] = 0;
	$extend[$i] = 0;
	
	$trial[$i] = 0;
	
	$day3[$i] = 0;
	$day7[$i] = 0;
	$day30[$i] = 0;
	$day90[$i] = 0;
	$day180[$i] = 0;
	$day365[$i] = 0;
}

// delete captcha that is time before 12 minutes from now
$result = mysql_query("select * from subscrs where time>=$start_time and time <=$end_time order by time");

$count = 0;
while($temp = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$subscr_row[$count] = $temp;
	$count++;
	
	//echo $temp["period"];
}

foreach ($subscr_row as $subscr) {
	$temp_day = date("j",$subscr["time"]);
	
	$sale[$temp_day] += 1;
	$amount[$temp_day] += $subscr["amount"];
	
	if ($subscr["mode"] == "create") {
		$create[$temp_day]++;
	}
	elseif ($subscr["mode"] == "upgrade") {
		$upgrade[$temp_day]++;
	}
	elseif ($subscr["mode"] == "extend") {
		$extend[$temp_day]++;
	}
	else {
		// free trial
	}
	
	if ($subscr["amount"] == 0) {
		$trial[$temp_day]++;
		
		
	}
	
	// count period type
	if ($subscr["period"] == 3) {
		$day3[$temp_day]++;
		
		$amount[$temp_day] += 1.2;
	}
	elseif ($subscr["period"] == 7) {
		$day7[$temp_day]++;
	}
	elseif ($subscr["period"] == 30) {
		$day30[$temp_day]++;
	}
	elseif ($subscr["period"] == 90) {
		$day90[$temp_day]++;
	}
	elseif ($subscr["period"] == 180) {
		$day180[$temp_day]++;
	}
	elseif ($subscr["period"] == 365) {
		$day365[$temp_day]++;
	}
}

$total_accounts = 0;
$total_amounts = 0;
for ($i = 0; $i <= $this_month_date; $i++) {
	
	if ($sale[$i]) {
		echo "Month $current_month , Date $i, ".date("D", mktime(0,0,0,$current_month,$i,$current_year))." <br>";
		echo "--------------------------------------------------<br>";
		echo "Total Purchase: " . $sale[$i] . "<br>";
		echo "Total Amount: " . $amount[$i] .  "<br>";
		echo "Total Create: " . $create[$i] .  "<br>";
		echo "Total Upgrade: " . $upgrade[$i] . "<br>";
		echo "Total Extend: " . $extend[$i] . "<br>";
		echo "Total Trial: " . $trial[$i] . "<br>";
		echo "--------------------------------------------------<br>";
		echo "(3): ".$day3[$i]." , (7) ".$day7[$i]." , (30) ".$day30[$i]
			." , (90) ".$day90[$i]." , (180) ".$day180[$i]." , (365) ".$day365[$i]." <br>";
		echo "--------------------------------------------------<br><br>";
		
		// sum monthly
		$total_accounts += $sale[$i];
		$total_amounts += $amount[$i];
	}
	
	echo "********************************************<br><br>";
}

echo "<br><br>===========================<br>";
echo "Monthly Sales: " . $total_accounts . "<br>";
echo "Monthly Amounts: " . $total_amounts . "<br><br><br>";

// close connection
mysql_close($conn);
?>

</body>
</html>
