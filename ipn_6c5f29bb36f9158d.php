<?php
/**
 * IPN page: Use to validate ipn get from paypal and generate new account
 */
/**
 * Subscr mode: 1 - extend, 2 - upgrade, 3 - create, 4 - free try
 */
/**
 * Login/Premium mode: 1 - login, non premium, 2 - login, premium, 3 - guest
 */

/*
CREATE TABLE IF NOT EXISTS `ipn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(12) NOT NULL,
  `valid` varchar(255) NOT NULL,
  `mc_gross` float DEFAULT '0',
  `protection_eligibility` varchar(255) DEFAULT NULL,
  `payment_date` varchar(255) DEFAULT NULL,
  `payer_status` varchar(255) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `business` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT '0',
  `verify_sign` varchar(255) DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `receiver_email` varchar(255) DEFAULT NULL,
  `mc_currency` varchar(255) DEFAULT '0',
  `item_number` int(11) DEFAULT NULL,
  `payment_gross` float DEFAULT NULL,
  `residence_country` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `subscrs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(12) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `ipn_id` int(11) DEFAULT '0',
  `pin_id` int(11) DEFAULT '0',
  `coupon_id` int(11) DEFAULT '0',
  `uid` int(11) NOT NULL,
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(600) NOT NULL,
  `period` int(6) NOT NULL DEFAULT '0',
  `start_time` int(10) NOT NULL,
  `expire_time` int(10) NOT NULL,
  `amount` float DEFAULT '0',
  `country` varchar(255) DEFAULT NULL,
  `referral_uid` varchar(17) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`pin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `subscr_id` int(11) NOT NULL,
  `status` varchar(120) NOT NULL DEFAULT 'waiting' COMMENT 'waiting, reject, complete',
  `time` int(12) NOT NULL,
  `type` varchar(120) NOT NULL COMMENT 'commission, affiliate',
  `amount` float(12,2) NOT NULL DEFAULT '0.00',
  `earning` float(12,2) NOT NULL DEFAULT '0.00',
  `percent` float(6,2) NOT NULL DEFAULT '0.00',
  `package` int(4) NOT NULL DEFAULT '30',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 */
// error_reporting(E_ALL); // php errors
// define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors
include "includes/inc.php";

include 'includes/xpertmail/MAIL.php';
include 'language/chinese_traditional/email.php';

mb_internal_encoding("utf-8");

// Checking loing - transfer to below in switch package seciont

// Get custom field for account type
if ($_POST["custom"]) {
	list($user_id, $client_ip, $account_mode, $referral_user) = split('_', $_POST["custom"]);
}
else {
	exit;
}

// cannot receive user information in here

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// repeat hack protection
// search existing - same verify sign and same email
$db->setQuery("select count(id) as repeat_ipn from ipn where 
	payer_email='{$_POST["payer_email"]}' and 
	verify_sign='{$_POST["verify_sign"]}'");
$db->query();
$hack_protect = $db->loadRow();

if ($hack_protect["repeat_ipn"] > 0) {
	exit;
}
unset($hack_protect);


// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ("ssl://www.paypal.com", 443, $errno, $errstr, 30);


if (!$fp) {
	// HTTP ERROR
	$db->setQuery("INSERT INTO ipn VALUES ('',
		'".time()."',
		'HTTP_ERROR',
		'{$_POST["mc_gross"]}',
		'{$_POST["protection_eligibility"]}',
		'{$_POST["payment_date"]}',
		'{$_POST["payer_status"]}',
		'{$_POST["payer_email"]}',
		'{$_POST["business"]}',
		'{$_POST["quantity"]}',
		'{$_POST["verify_sign"]}',
		'{$_POST["payment_type"]}',
		'{$_POST["receiver_email"]}',
		'{$_POST["mc_currency"]}',
		'{$_POST["item_number"]}',
		'{$_POST["payment_gross"]}',
		'{$_POST["residence_country"]}',
		'{$_POST["custom"]}',
		'0'
	)"); $db->query();
	
	@fclose ($fp);
} 
else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {
			// PAYMENT VALIDATED & VERIFIED!
			
			// insert into ipn table
			$db->setQuery("INSERT INTO ipn VALUES ('',
				'".time()."',
				'VALID',
				'{$_POST["mc_gross"]}',
				'{$_POST["protection_eligibility"]}',
				'{$_POST["payment_date"]}',
				'{$_POST["payer_status"]}',
				'{$_POST["payer_email"]}',
				'{$_POST["business"]}',
				'{$_POST["quantity"]}',
				'{$_POST["verify_sign"]}',
				'{$_POST["payment_type"]}',
				'{$_POST["receiver_email"]}',
				'{$_POST["mc_currency"]}',
				'{$_POST["item_number"]}',
				'{$_POST["payment_gross"]}',
				'{$_POST["residence_country"]}',
				'{$_POST["custom"]}',
				'0'
			)"); $db->query();
			// get last insert id
			$ipn_id = $db->insertid();
			
			// if amount lower than 6 usd
			if (floatval($_POST["mc_gross"]) <= 6) {
				// is hacking
				exit;
			}
			
			// check payer_status verified or unverifed
			if ($_POST["payer_status"] != "verified") {
				if ($_POST["payer_status"] != "unverified") {
					exit;	
				}
			}
			
			// check quantity valid
			if (intval($_POST["quantity"]) != 1) {
				exit;
			}
			
			
			// setup start time
			$current_time = time();
			
			// Generate a new ID or upgrade or extend Account
			// after than save it into premium table
			// choose item number and save into subscription table and update user table
			$extend_days = 0;
			switch ($_POST["item_number"]) {
				// e - extend, u - upgrade, c - create
				case "1":
					// 30 days packages
					// set the expire time of account
					$extend_days = 30;
					break;
				case "2":
					// 180 days
					$extend_days = 180;						
					break;
				case "3":
					// 360 days
					$extend_days = 365;
					break;
			}
			
			if ($account_mode == 1) {
				// get account detail
				$db->setQuery("SELECT * FROM users WHERE id = '{$user_id}' and (gid = 3 or gid = 4)");
				$db->query();
				$user_detail = $db->loadRow();
				
				// extend account
				$username = $user_detail[user];
				$password = $user_detail[pass];
				$useremail = $user_detail[email];
							
				$extend_time = $extend_days * 24 * 60 * 60;
						
				// setup for user table
				$subscr_period = $user_detail["subscr_period"] == NULL ? $extend_days : $user_detail["subscr_period"] + $extend_days;
				$subscr_fee = $user_detail["subscr_fee"] == NULL ? $_POST["mc_gross"] : $user_detail["subscr_fee"] + $_POST["mc_gross"];

				// setup for subscription table
				$start_time = $user_detail["expire_date"] == NULL ? $current_time : $user_detail["expire_date"];
				$expire_time = $user_detail["expire_date"] == NULL ? $current_time + $extend_time : $user_detail["expire_date"] + $extend_time;
								
				// insert new subsciption into table
				$db->setQuery("INSERT INTO subscrs VALUES (
					'',
					'".time()."',
					'extend',
					'{$ipn_id}',
					'',
					'',
					'{$user_id}',
					'{$username}',
					'{$password}',
					'{$useremail}',
					'{$extend_days}',
					'{$start_time}',
					'{$expire_time}',
					'{$_POST["mc_gross"]}',
					'{$_POST["residence_country"]}',
					'{$referral_user}'
				)"); $db->query();
						
				$subscr_id = $db->insertid();
							
				// update user table with new detail
				$db->setQuery("UPDATE users SET
					gid = 3,
					subscr_id = '{$subscr_id}',
					subscr_unit = 'D',
					subscr_period = '{$subscr_period}',
					subscr_fee = '{$subscr_fee}',
					expire_date = '{$expire_time}',
					bandwidth = 0 
					WHERE id = '{$user_id}' AND (gid = 3 or gid = 4) 
				"); $db->query();
				
					
				$email_type = "extend";
			}
			elseif ($account_mode == 2) {
				// upgrade account
				// get account detail
				$db->setQuery("SELECT * FROM users WHERE id = '{$user_id}' and gid = 2");
				$db->query();
				$user_detail = $db->loadRow();
				
				// extend account
				$username = $user_detail[user];
				$password = $user_detail[pass];
				$useremail = $user_detail[email];
				
				// until here and check above problem
				$extend_time = $extend_days * 24 * 60 * 60;

				// setup for user table
				$subscr_period = $extend_days;
				$subscr_fee = $_POST["mc_gross"];

				// setup for subscription table
				$start_time = $current_time;
				$expire_time = $current_time + $extend_time;
							
				// insert new subsciption into table
				$db->setQuery("INSERT INTO subscrs VALUES (
					'',
					'".time()."',
					'upgrade',
					'{$ipn_id}',
					'',
					'',
					'{$user_id}',
					'{$username}',
					'{$password}',
					'{$useremail}',
					'{$extend_days}',
					'{$start_time}',
					'{$expire_time}',
					'{$_POST["mc_gross"]}',
					'{$_POST["residence_country"]}',
					'{$referral_user}'
				)"); $db->query();
						
				$subscr_id = $db->insertid();
							
				// update user table with new detail
				$db->setQuery("UPDATE users SET
					gid = 3,
					subscr_id = '{$subscr_id}',
					subscr_unit = 'D',
					subscr_period = '{$subscr_period}',
					subscr_fee = '{$subscr_fee}',
					expire_date = '{$expire_time}'
					WHERE id = '{$user_id}' AND gid = 2
				"); $db->query();
				
					
				$email_type = "upgrade";
			}
			else {
				
				// Generate new username and password to create new account
				// the username and password can change in profile pages (username 1 times)
				// do while check username not exist
				do {
					$username = "u" . mt_rand(100000,999999) . mt_rand(10,99);
					
					$db->setQuery("select * from users where user='{$username}' limit 1");
            		$db->query();
            
            		$exists = $db->getNumRows();
				} while ($exists);
				
				$password = "p" . mt_rand(100000,999999) . mt_rand(10,99);
				$useremail = $_POST["payer_email"];
				
				// setup variable
				$extend_time = $extend_days * 24 * 60 * 60;
				$expire_time = $current_time + $extend_time;
				
				// setup for user table
				$subscr_period = $extend_days;
				$subscr_fee = $_POST["mc_gross"];
	
				// setup for subscription table
				$start_time = $current_time;
				$expire_time = $current_time + $extend_time;
								
				// insert new subsciption into table
				$db->setQuery("INSERT INTO subscrs VALUES (
					'',
					'".time()."',
					'create',
					'{$ipn_id}',
					'',
					'',
					'-1',
					'{$username}',
					'{$password}',
					'{$useremail}',
					'{$extend_days}',
					'{$start_time}',
					'{$expire_time}',
					'{$_POST["mc_gross"]}',
					'{$_POST["residence_country"]}',
					'{$referral_user}'
				)"); $db->query();
				
				$subscr_id = $db->insertid();
				
				// create new account
		        $adduser = new TABLE($db,'users','id');
		        $adduser->user = $username;
		        $adduser->pass = $password;
		        $adduser->email = $useremail;
		        $adduser->gid = 3;
		        $adduser->regdate = $current_time;
				$adduser->ip = "0.0.0.0";
				
				$adduser->last_login = $current_time;
				$adduser->login_ip = "0.0.0.0";
				
				$adduser->webspace = 0;
				$adduser->deleted_space = 0;
				$adduser->hosted_files_stream = 0;
				
				$adduser->validate_code = $_POST["verify_sign"];
				
				// subscr detail
				$adduser->subscr_id = $subscr_id;
				$adduser->subscr_unit = "D";
				$adduser->subscr_period = $subscr_period;
				$adduser->subscr_fee = $subscr_fee;
				$adduser->expire_date = $expire_time;
				
				$adduser->insert();
				$insert_uid = $adduser->insertid();
				
				// update to subscr table again
				$db->setQuery("UPDATE subscrs SET uid = '{$insert_uid}' WHERE id = '{$subscr_id}'"); 
				$db->query();
				
				$email_type = "create";
			}
			
			
			// Insert Commission or Affiliate Records
			// check is commission or referral
			if ($referral_user) {
				// got referral
				// select referral type, commission 5% and affiliate 10%
				if (substr($referral_user, 0, 1) == "c") {
					$referral_type = "commission";
					
					// calculate commission
					if ($extend_days == 30) {
						// type 30 days
						$earning = 0.45;
					}
					elseif ($extend_days == 180) {
						// type 180 days
						$earning = 2.25;
					}
					else {
						// type 365 days
						$earning = 3.6;				
					}
					
					$percent = 5;
				}
				elseif (substr($referral_user, 0, 1) == "r") {
					$referral_type = "affiliate";
					
					// calculate commission
					if ($extend_days == 30) {
						// type 30 days
						$earning = 0.9;
					}
					elseif ($extend_days == 180) {
						// type 180 days
						$earning = 4.5;
					}
					else {
						// type 365 days
						$earning = 7.2;				
					}
					
					$percent = 10;
				}
				
				// Insert Referrals DB
				$referral = new TABLE($db,'referrals','id');
				
		        $referral->uid = substr($referral_user, 1);
		        $referral->subscr_id = $subscr_id;
		        $referral->status = "waiting";
		        $referral->time = $current_time;
		        $referral->type = $referral_type;
		        $referral->amount = $_POST["mc_gross"];
		        $referral->earning = $earning;
		        $referral->percent = $percent;
		        $referral->package = $extend_days;
		        
		        $referral->insert();
			}

			// Send email to both paypal email and user email
			// Send both chinese and english language
			// LOCAL MAIL FUNCTION
			/*
			$email->template->set_filenames(array(
    			"email_chinese" => $email_type1,
    			"email_english" => $email_type2,
    		));
    		
    		// prepare chinese mail data
    		$subject='=?UTF-8?B?'.base64_encode($LANG[SiteName].$LANG[PREMIUM].$LANG[Login_Detail]).'?=';
			$email->template->assign_vars(array(
				"subject" => $subject,
			    "username" => $username,
			    "password" => $password,
			    "email" => $useremail,
			    "package" => $extend_days,
			    "start_date" => date("D M j G:i:s T Y", $start_time),
			    "expire_date" => date("D M j G:i:s T Y", $expire_time),
			));
	    	
			// sending chinese email
			$email->to($useremail);
			$email->from($user->setting[adminemail]);
			$email->send("email_chinese");

			// prepare english mail data
    		$subject=$LANG[SiteName].$LANG[PREMIUM].$LANG[Login_Detail];
			$email->template->assign_vars(array(
				"subject" => $subject,
			    "username" => $username,
			    "password" => $password,
			    "email" => $useremail,
			    "package" => $extend_days,
			    "start_date" => date("D M j G:i:s T Y", $start_time),
			    "expire_date" => date("D M j G:i:s T Y", $expire_time),
			));
			
			// send english email
			$email->to($useremail);
			$email->from($user->setting[adminemail]);
			$email->send("email_english");
			*/

			// Sample Data - Remove after test
			//$useremail = "bvc100x@yahoo.com";
			
			// GMAIL POP3 MAIL SYSTEM
			// set mail type
			$extend_days1 = "{$extend_days} {$EMAIL[Chinese_Traditional_Days]}";
			$start_time1 = date("m{$LANG[Months]} d{$LANG[Days]} Y{$LANG[Years]}", $start_time);
			$expire_time1 = date("m{$LANG[Months]} d{$LANG[Days]} Y{$LANG[Years]}", $expire_time);
			
			$extend_days2 = "{$extend_days} {$EMAIL[English_Days]}";
			$start_time2 = date("M d Y", $start_time);
			$expire_time2 = date("M d Y", $expire_time);
			
			if ($email_type == "extend") {
				$message_subject1 = $EMAIL[Chinese_Traditional_Extend_Subject];
				$message_start1 = $EMAIL[Chinese_Traditional_Extend_Note];
				
				$message_subject2 = $EMAIL[English_Extend_Subject];
				$message_start2 = $EMAIL[English_Extend_Note];
			}
			elseif ($email_type == "upgrade") {
				$message_subject1 = $EMAIL[Chinese_Traditional_Upgrade_Subject];
				$message_start1 = $EMAIL[Chinese_Traditional_Upgrade_Note];
				
				$message_subject2 = $EMAIL[English_Upgrade_Subject];
				$message_start2 = $EMAIL[English_Upgrade_Note];
			}
			elseif ($email_type == "create") {
				$message_subject1 = $EMAIL[Chinese_Traditional_Create_Subject];
				$message_start1 = $EMAIL[Chinese_Traditional_Create_Note];
				
				$message_subject2 = $EMAIL[English_Create_Subject];
				$message_start2 = $EMAIL[English_Create_Note];
			}
			

			
			$m = new MAIL;
			$m->From('xun6.marketing@gmail.com', $EMAIL[SystemSender], 'UTF-8');
			$m->AddTo($useremail, $username, 'UTF-8');
			
			// chinese email
			$m->Subject($message_subject1, 'UTF-8');
			
			$bodytext = "{$EMAIL[Chinese_Traditional_DearUser]} \n\n";
			$bodytext .= "{$message_start1} \n\n";
			$bodytext .= "########################################## \n\n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_Username]} {$username} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_Password]} {$password} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_Email]} {$useremail} \n\n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_Package]} {$extend_days1} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_CreateDate]} {$start_time1} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_ExpireDate]} {$expire_time1} \n\n";
			$bodytext .= "########################################## \n\n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_Tutorial1]} \n{$EMAIL[Link1]} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_Tutorial2]} \n{$EMAIL[Link2]} \n\n";
			$bodytext .= "########################################## \n\n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_EndingSign1]} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_EndingSign2]} \n";
			$bodytext .= "{$EMAIL[Chinese_Traditional_EndingSign3]} \n";

			$m->text($bodytext, 'UTF-8');

			$c = $m->Connect('smtp.gmail.com', 465, 'xun6.marketing@gmail.com', 'qwer1234', 'tls');
			
			$m->Send($c);
			
			// english email

			$m->Subject($message_subject2, 'UTF-8');
			
			$bodytext = "{$EMAIL[English_DearUser]} \n\n";
			$bodytext .= "{$message_start2} \n\n";
			$bodytext .= "########################################## \n\n";
			$bodytext .= "{$EMAIL[English_Username]} {$username} \n";
			$bodytext .= "{$EMAIL[English_Password]} {$password} \n";
			$bodytext .= "{$EMAIL[English_Email]} {$useremail} \n\n";
			$bodytext .= "{$EMAIL[English_Package]} {$extend_days2} \n";
			$bodytext .= "{$EMAIL[English_CreateDate]} {$start_time2} \n";
			$bodytext .= "{$EMAIL[English_ExpireDate]} {$expire_time2} \n\n";
			$bodytext .= "########################################## \n\n";
			$bodytext .= "{$EMAIL[English_Tutorial1]} \n{$EMAIL[Link1]} \n";
			$bodytext .= "{$EMAIL[English_Tutorial2]} \n{$EMAIL[Link2]} \n\n";
			$bodytext .= "########################################## \n\n";
			$bodytext .= "{$EMAIL[English_EndingSign1]} \n";
			$bodytext .= "{$EMAIL[English_EndingSign2]} \n";
			$bodytext .= "{$EMAIL[English_EndingSign3]} \n";

			$m->text($bodytext, 'UTF-8');

			$c = $m->Connect('smtp.gmail.com', 465, 'xun6.marketing@gmail.com', 'qwer1234', 'tls');
			
			$m->Send($c);
			
			// complete send, close connection
			$m->Disconnect(); 
		}
		elseif (strcmp ($res, "INVALID") == 0) {
			// PAYMENT INVALID & INVESTIGATE MANUALY!
			// Save it into trasaction fail table
			$db->setQuery("INSERT INTO ipn VALUES ('',
				'".time()."',
				'INVALID',
				'{$_POST["mc_gross"]}',
				'{$_POST["protection_eligibility"]}',
				'{$_POST["payment_date"]}',
				'{$_POST["payer_status"]}',
				'{$_POST["payer_email"]}',
				'{$_POST["business"]}',
				'{$_POST["quantity"]}',
				'{$_POST["verify_sign"]}',
				'{$_POST["payment_type"]}',
				'{$_POST["receiver_email"]}',
				'{$_POST["mc_currency"]}',
				'{$_POST["item_number"]}',
				'{$_POST["payment_gross"]}',
				'{$_POST["residence_country"]}',
				'{$_POST["custom"]}',
				'0'
			)"); $db->query();
		}
	}
	
	@fclose ($fp);
}


// close connection
@$db->close_db();
?>
