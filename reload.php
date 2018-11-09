<?php
/**
 * [May 17 2010] Account Upgrade Using PIN
 * 1. user or guest come to this pae
 * 1a. enter username
 * 2. enter pin code and click upgrade
 * 2a. wrong pin code, max try 6 times in 1 hours
 * 3. show pin code info and comfirm upgrade
 * 4. confirm and show detail
 * 5. done!
 */
//header("location: index.php");

include("includes/inc.php");
include("includes/resellers.inc.php");
$baseUrl='reload.php?';

if($user->logined==0) {
	// login as guest
	
	$logined = 0;
	$user_id = 0;
}
else {
	// login as user
	if ($user->account_status == -1) { 
		$user->logout(); 
		header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended'); 
	}
	
	$logined = 1;
	$user_id = $user->uid;	
	$template->assign_var("username",$user->username);
}

// start here
define("PAGE_TITLE",'Reload_Account');
if ($input["act"] == "verify") {
	// show detail and wait user confim
	
	if ($_POST)	 {
		// validate field exists
		$current_time = time();
		$input["username"] = trim($input["username"]);
		
		// check username exist
		if ($logined) {
			// if logined
			$db->setQuery("SELECT id, user, pass, email, gid, expire_date FROM 
				users WHERE id = '{$user_id}' limit 1");
			$db->query();
			$user_detail = $db->loadRow();
		}
		else {
			// not logined
			$db->setQuery("SELECT id, user, pass, email, gid, expire_date FROM 
				users WHERE user = '{$input["username"]}' limit 1");
			$db->query();
		
			if ($db->getNumRows()) {
				// exist
				$user_detail = $db->loadRow();
				
				$_SESSION["reload_other"] = 1;
			}
			else {
				// not exist
				$errorCode[] = $LANG[Reload_UsernameNotExist];
			}
		}
		
		// set user variable
		$user_id = $user_detail["id"];
		$user_name = $user_detail["user"];
		$user_pass = $user_detail["pass"];
		$user_email = $user_detail["email"];
		$user_gid = $user_detail["gid"];
		$user_expire = $user_detail["expire_date"];
		
		
		// check pin code and pass is correct and no expire
		$db->setQuery("select id, type, length from pin where pin='{$input["pincode"]}' and 
			pass='{$input["pinpass"]}' and used='0' and expire_time>='{$current_time}' limit 1");
		$db->query();
		
		if ($db->getNumRows()) {
			// correct
			$pin = $db->loadRow();
			$pin_id = $pin["id"];
			$pin_length = $pin["length"];
			$pin_type  = $pin["type"];
			
			switch ($pin["length"]) {
				case 3: $pin_price = 0.00; break;
				case 7: $pin_price = 0.00; break;
				case 30: $pin_price = 7.20; break;
				case 90: $pin_price = 21.6; break;
				case 180: $pin_price = 36; break;
				case 365: $pin_price = 57.6; break;
			}
		}
		else {
			// wrong
			$errorCode[] = $LANG[Reload_PinIncorrect];
		}
		
		// set mode and related		
		if ($pin_type == "freetrial") {
			// is free trial
			// limit only normal user can use trail
			if ($user_gid == 2) {
				$mode = "trial";
				$start_time = $current_time;
				$expire_time = $current_time + ($pin_length * 24 * 60 * 60);
			}
			else {
				$errorCode[] = $LANG[Reload_TrialOnlyUser];
			}
			
		}
		else {
			if ($user_gid == 2) {
				// upgrade account
				// a. subscr insert
				// Subscr mode: 1 - extend, 2 - upgrade, 3 - create, 4 - free try
				$mode = "upgrade";
				$start_time = $current_time;
				$expire_time = $current_time + ($pin_length * 24 * 60 * 60);
			}
			elseif ($user_gid == 3) {
				// extend
				$mode = "extend";
				$start_time = $user_expire;
				$expire_time = $user_expire + ($pin_length * 24 * 60 * 60);
			}
			elseif ($user_gid == 4) {
				// reopen
				$mode = "extend";
				$start_time = $user_expire;
				$expire_time = $user_expire + ($pin_length * 24 * 60 * 60);
			}
			else {
				// error or testing
				$mode = "test";
				$start_time = $current_time;
				$expire_time = $current_time + ($pin_length * 24 * 60 * 60);
			}
		}
		
		// check error 
		if ($errorCode) {
			// got error
			$template->assign_var("error_message",implode($errorCode, "<br />"));
			
			$template->assign_vars(array(
				"reload_username" => $input["username"],
				"pincode" => $input["pincode"],
				"pinpass" => $input["pinpass"],
			));
			
			// session error max try, 6 times
			
		}
		else {
			// correct
			/**
			 * 1. update pin set used
			 * 2. insert sub id
			 * 3. update users group and expire length
			 * 4. show detail in complete
			 * 5. send email to notice users
			 */ 
		
			// A. update pin table set used
			$db->setQuery("update pin set used='1', uid='{$user_id}', activate_time='{$start_time}' 
				where pin='{$input["pincode"]}' and pass='{$input["pinpass"]}' limit 1");
			$db->query();

			// B. insert subscrs table
			$subscr = new TABLE($db,'subscrs','id');
			$subscr->time = $current_time;
			$subscr->mode = $mode;
			$subscr->pin_id = $pin_id;
			$subscr->uid = $user_id;
			$subscr->user = $user_name;
			$subscr->pass = $user_pass;
			$subscr->email = $user_email;
			$subscr->period = $pin_length;
			$subscr->start_time = $start_time;
			$subscr->expire_time = $expire_time;
			$subscr->amount = $pin_price;
			// get country
			$user_ip = $_SERVER["REMOTE_ADDR"];
			$db->setQuery("SELECT c.code as country FROM ip2nationCountries c, ip2nation i 
				WHERE i.ip < INET_ATON('{$user_ip}') AND c.code = i.country ORDER BY i.ip DESC LIMIT 0,1");
			$db->query();
			$temp = $db->loadRow();
			$subscr->country = strtoupper($temp["country"]);
			
			$subscr->insert();
			$subscr_id = $subscr->insertid();
			
			// C. update users
			// when reopen, it will auto set to gid=3 and bandwidth=0
			$db->setQuery("update users set 
					gid = 3,
					subscr_id = '{$subscr_id}',
					subscr_unit = 'D',
					subscr_period = '{$pin_length}',
					subscr_fee = '{$pin_price}',
					expire_date = '{$expire_time}',
					bandwidth = 0
				where 
					id='{$user_id}' and 
					user='{$user_name}' and 
					pass='{$user_pass}' 
				limit 1");
			$db->query();
			
			// D. done redirect to related info
			// reset sessions
			$_SESSION["subscr_maxtry"] = 0;
			$_SESSION["subscr_id"] = $subscr_id;
			
			// redirect
			do_redirect("{$baseWeb}/reload.php?act=complete&id={$subscr_id}", $LANG[Reload_Success]);
		}
	}
	else {
		// error, redirect
		do_redirect("{$baseWeb}/reload.php", $LANG[Reload_Failed]);
	}
}
elseif ($input["act"] == "logout") {
	// logout and redirect to reload page
	$user->logout();
	
	do_redirect("{$baseWeb}/reload.php", $LANG[LogoutMessage]);
}
elseif ($input["act"] == "complete") {
	// complete and show detail
	// subscr only for pin
	// no auto reload page
	
	// check subscr id is provided
	if ($input["id"] || $input["pin"]) {
		/**
		 * 1. check subscr exist
		 * 2. get subscr data
		 * 3. display output
		 */

		// get subscr detail, must be user himself or session id exist
		if ($logined) {
			// is logined, check detail
			$db->setQuery("select * from subscrs where id='{$input["id"]}' limit 1");
			$db->query();
		
			if ($db->getNumRows()) {
				$subscr = $db->loadRow();
				
				// get pin
				if ($subscr["pin_id"] > 0) {
					$db->setQuery("select pin from pin where id='{$subscr["pin_id"]}' limit 1");
					$db->query();
					$pin = $db->loadRow();
				}
				else {
					header("location: {$baseWeb}/reload.php");
				}
				
				if ($subscr["mode"] == "trial") {
					$mode = $LANG[Type_Trial];
				}
				elseif ($subscr["mode"] == "upgrade") {
					$mode = $LANG[Type_Upgrade];
				}
				elseif ($subscr["mode"] == "extend") {
					$mode = $LANG[Type_Extend];
				}
				else {
					$mode = $LANG[Reload_Type];
				}
				
				// check belong to user or not
				if ($subscr["uid"] == $user->uid) {
					// belong to user
					$template->assign_vars(array(
						"subscr_id" => $subscr["id"],
						"reload_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["time"]),
						"reload_type" => $mode,
						"pin_code" => strtoupper($pin["pin"]),
						"reload_username" => $subscr["user"],
						"account_type" => $subscr["mode"] == "trial" ? $LANG["Trial"].$LANG["Premium"] : $LANG["Premium"],
						"activate_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["start_time"]),
						"expire_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["expire_time"]),
					));
					
				}
				else {
					// not belong to user, max check 3 times
					if ($_SESSION["reload_other"] && $_SESSION["subscr_id"] == $input["id"]) {
						$template->assign_vars(array(
							"subscr_id" => $subscr["id"],
							"reload_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["time"]),
							"reload_type" => $mode,
							"pin_code" => strtoupper($pin["pin"]),
							"reload_username" => $subscr["user"],
							"account_type" => $subscr["mode"] == "trial" ? $LANG["Trial"].$LANG["Premium"] : $LANG["Premium"],
							"activate_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["start_time"]),
							"expire_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["expire_time"]),
						));
					}
					else {
						// send warning and redirect to reload page
						do_redirect("{$baseWeb}/reload.php", $LANG[Reload_GuestMaxCheck]);
					}
				}
			}
			else {
				// not found
				header("location: {$baseWeb}/reload.php");
			}
		}
		else {
			// reseller can only check pin sell from him
			// if $_SESSION["reload_other"] set, then is guest reload other
			if ($_SESSION["reload_other"] && $_SESSION["subscr_id"] == $input["id"]) {
				// guest reload other user is set
				$db->setQuery("select * from subscrs where id='{$input["id"]}' limit 1");
				$db->query();
			
				if ($db->getNumRows()) {
					$subscr = $db->loadRow();
				
					// get pin
					if ($subscr["pin_id"] > 0) {
						$db->setQuery("select pin from pin where id='{$subscr["pin_id"]}' limit 1");
						$db->query();
						$pin = $db->loadRow();
					}
					else {
						header("location: {$baseWeb}/reload.php");
					}
				
					if ($subscr["mode"] == "trial") {
						$mode = $LANG[Type_Trial];
					}
					elseif ($subscr["mode"] == "upgrade") {
						$mode = $LANG[Type_Upgrade];
					}
					elseif ($subscr["mode"] == "extend") {
						$mode = $LANG[Type_Extend];
					}
					else {
						$mode = $LANG[Reload_Type];
					}
				}
				else {
					header("location: {$baseWeb}/reload.php");
				}
			
				$template->assign_vars(array(
					"subscr_id" => $subscr["id"],
					"reload_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["time"]),
					"reload_type" => $mode,
					"pin_code" => strtoupper($pin["pin"]),
					"reload_username" => $subscr["user"],
					"account_type" => $subscr["mode"] == "trial" ? $LANG["Trial"].$LANG["Premium"] : $LANG["Premium"],
					"activate_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["start_time"]),
					"expire_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["expire_time"]),
				));
				
				reseller_logined();
			}
			else {
				// reseller and fail
				if (reseller_logined()) {
					// reseller is logined, check the pin is belong to him
					$db->setQuery("select * from subscrs where pin_id='{$input["pin"]}' or id='{$input["id"]}' limit 1");
					$db->query();
			
					if ($db->getNumRows()) {
						$subscr = $db->loadRow();
					
						// get pin
						if ($subscr["pin_id"] > 0) {
							$db->setQuery("select pin from pin where id='{$subscr["pin_id"]}' 
								and reseller_id='".reseller_id()."' limit 1");
							$db->query();
							
							if ($db->getNumRows()) {
								$pin = $db->loadRow();
							}
							else {
								do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_PinNotFound]);
							}
							
						}
						else {
							do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_PinNotFound]);
						}
					}
					else {
						// not exists
						do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_PinNotFound]);
					}
					
					if ($subscr["mode"] == "trial") {
						$mode = $LANG[Type_Trial];
					}
					elseif ($subscr["mode"] == "upgrade") {
						$mode = $LANG[Type_Upgrade];
					}
					elseif ($subscr["mode"] == "extend") {
						$mode = $LANG[Type_Extend];
					}
					else {
						$mode = $LANG[Reload_Type];
					}
					
					$template->assign_vars(array(
						"subscr_id" => $subscr["id"],
						"reload_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["time"]),
						"reload_type" => $mode,
						"pin_code" => strtoupper($pin["pin"]),
						"reload_username" => $subscr["user"],
						"account_type" => $subscr["mode"] == "trial" ? $LANG["Trial"].$LANG["Premium"] : $LANG["Premium"],
						"activate_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["start_time"]),
						"expire_time" => date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$subscr["expire_time"]),
					));
				}
				else {
					header("location: {$baseWeb}/reload.php");
				}
			}			
		}
	
		$template->assign_var("reload_complete",1);
	}
	else {
		// no complete id, redirect to reload page
		//header("location: {$baseWeb}/reload.php");
		echo 23;
	}

}
else {
	// waiting input
	reseller_logined();
	
}




$template->assign_var("reloadpage",1);

require_once("header.php");
$template->set_filenames(array("body"=>"reload.html"));
$template->pparse('body');
include "footer.php";
?>