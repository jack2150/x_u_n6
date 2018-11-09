<?php
define("IN_PAGE",'Temp');
define("PAGE_TITLE",'Temp');
include "includes/inc.php";

$LANG[Temp] = $LANG[Premium];

$client_ip = $_SERVER['REMOTE_ADDR'];
$user_id = $user->uid;

// Checking Login
// Account type: 1 - extend, 2 - upgrade, 3 - create
if ($user->logined==1) { 
	if ($user->package_id == 3) {
		$account_type = 1;
	}
	else {
		$account_type = 2;
	}
}
else {
	$account_type = 3;
}

$custom_field = $user_id . "_" . $client_ip . "_" . $account_type;

@$db->close_db();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Testing Paypal</title>
</head>

<body>
<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="custom" value="<?php echo $custom_field ?>" />
<input type="hidden" name="hosted_button_id" value="1090412">
<input type="image" src="https://www.sandbox.paypal.com/zh_XC/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="PayPal — 最安全便捷的在线支付方式！">
<img alt="" border="0" src="https://www.sandbox.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
</form>



</body>
</html>
