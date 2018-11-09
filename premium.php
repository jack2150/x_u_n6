<?php
header("location: http://www.xun6.com");

// redirect to useable domain!!!
/*
if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
	header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
}


if (preg_match("/\bxun6a.us\b/i", $_SERVER["SERVER_NAME"])) {
	header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
}
*/

define("IN_PAGE",'Temp');
define("PAGE_TITLE",'Temp');
include "includes/inc.php";

$LANG[Temp] = $LANG[Premium];

// Checking Login
if ($user->logined==1) { 
	$template->assign_var("user_logined", 1);
	$template->assign_var("is_premium",$user->package_id == 3 ? 1 : 0);
}
else {
	$template->assign_var("logined", 0);
}

if ($user->account_status == -1) {
	$user->logout(); header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended');
}

// Check Action
if ($_GET["act"] == "success" && is_numeric($_GET["pid"])) {
	// Success purchase, show related information
	$LANG[Temp] = $LANG[Premium] . " - " . $LANG[Premium_Success];
	$body_template = "premium_success.html";
	
	
	// set the package type
	$template->assign_var("package_type", $_GET["pid"]);
	
	// set account days
	if ($_GET["pid"] == 1) {
		// 30 days premium account
		$template->assign_var("premium_days", 30);
	}
	elseif ($_GET["pid"] == 2) {
		// 180 days premium account
		$template->assign_var("premium_days", 180);
	}
	elseif ($_GET["pid"] == 3) {
		// 360 days premium account
		$template->assign_var("premium_days", 360);
	}
	else {
		// reseller custom plan
		
	}
	
	// set is create new or extend existing account
	if ($user->logined) {
		if ($user->package_id == 3) {
			// extend account
			$template->assign_var("account_function", $LANG[Extended_Success]);
			$template->assign_var("history_page", "{$baseWeb}/profiles.php");
			$template->assign_var("back_message", $LANG[Back_To_Profiles]);
		}
		else {
			// upgrade account
			$template->assign_var("account_function", $LANG[Upgraded_Success]);
			$template->assign_var("history_page", "{$baseWeb}/profiles.php");
			$template->assign_var("back_message", $LANG[Back_To_Profiles]);
		}
	}
	else {
		// create new account
		$template->assign_var("account_function", $LANG[Created_Success]);
		$template->assign_var("history_page", "{$baseWeb}/index.php");
		$template->assign_var("back_message", $LANG[Back_To_Index]);
	}
}
elseif ($_GET["act"] == "cancel" && is_numeric($_GET["pid"])) {
	// Cancel pruchase, show coupon or come back later
	$LANG[Temp] = $LANG[Premium] . " - " . $LANG[Premium_Cancel];
	$body_template = "premium_cancel.html";
	
	// set the package type
	$template->assign_var("package_type", $_GET["pid"]);
	
	
	// Cancel purchase - show coupon, discount, free or just disable
	
	
	
	
	
	
	// set is create new or extend existing account
	if ($user->logined) {
		if ($user->package_id == 3) {
			// extend account
			$template->assign_var("account_function", $LANG[Extended_Success]);
			$template->assign_var("history_page", "{$baseWeb}/profiles.php");
			$template->assign_var("back_message", $LANG[Back_To_Profiles]);
		}
		else {
			// upgrade account
			$template->assign_var("account_function", $LANG[Upgraded_Success]);
			$template->assign_var("history_page", "{$baseWeb}/profiles.php");
			$template->assign_var("back_message", $LANG[Back_To_Profiles]);
		}
	}
	else {
		// create new account
		$template->assign_var("account_function", $LANG[Created_Success]);
		$template->assign_var("history_page", "{$baseWeb}/index.php");
		$template->assign_var("back_message", $LANG[Back_To_Index]);
	}
}
else {
	// empty act, show default packages page
	$client_ip = $_SERVER['REMOTE_ADDR'];
	$user_id = $user->uid;
	
	/**
	 * Referrals Method
	 */
	// check is using commision method - c
	$referral_user = 0;
	$referer_url = $_SERVER["HTTP_REFERER"];
	if ($referer_url) {
		//$str = explode("xun6.com/file/",$referer_url);
		$str = explode("/file/",$referer_url);
		if ($str) {
			$temp_uploadid = substr($str[1],0,9);
			
			$db->setQuery("select uid from files where upload_id = '{$temp_uploadid}' limit 1");
			$db->query();
			
			if ($db->getNumRows()) {
				$temp_row = $db->loadRow();
				
				// check user exist and is join revenue program
				$db->setQuery("select id from users where id='{$temp_row["uid"]}' and revenue_program=1 limit 1");
				$db->query();
				if ($db->getNumRows()) {
					$referral_user = "c".$temp_row["uid"];
				}
			}
		}
	}
	
	// check is using referral method - r
	// url: http://www.xun6.com/premium.php?ref=123
	// 123 is user id
	if ($input["ref"]) {
		if (is_numeric($input["ref"])) {
			$db->setQuery("select id from users where id='{$input["ref"]}' and revenue_program=1 limit 1");
			$db->query();
			
			if ($db->getNumRows()) {
				$referral_user = "r".$input["ref"];
			}
		}
	}
	
	
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
	
	$custom_field = $user_id . "_" . $client_ip . "_" . $account_type . "_" . $referral_user;
		
	// Set Referer Page
	$ref_page = $_SERVER[HTTP_REFERER];
	if (preg_match("/\bregister\b/i", $ref_page)) {
		$ref_page = "{$baseWeb}/members.php";
		$template->assign_var("register_sale", 1);
	}
	
	if ($ref_page) {
		$template->assign_var("history_page", $ref_page);
		$template->assign_var("custom_field", $custom_field);
	}
	else {
		$template->assign_var("history_page", $baseWeb);
		$template->assign_var("custom_field", $custom_field);
	}
	
	$body_template = "premium_packages.html";
}


// Template Engine
$template->assign_var("premiumpage", 1);

require_once("header.php");
$template->set_filenames(array("body" => $body_template));
$template->pparse("body");
include_once("footer.php");
?>