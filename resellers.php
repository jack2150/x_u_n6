<?php
/**
 * [Apr 26 2010]
 * Reseller Pagge:
 * 1. Register
 * 2. Login
 * 3. Portal - Listing
 * 4. Order
 * 5. Check
 * 6. Complain
 */
/**
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL DEFAULT 'waiting',
  `one_month` int(11) NOT NULL DEFAULT '0',
  `three_month` int(11) NOT NULL DEFAULT '0',
  `six_month` int(11) NOT NULL DEFAULT '0',
  `one_year` int(11) NOT NULL DEFAULT '0',
  `total_amount` float(12,2) NOT NULL DEFAULT '0.00',
  `payment` varchar(255) DEFAULT NULL,
  `extra_note` text,
  `reseller_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
*/

//header("location: index.php");

include("includes/inc.php");
include("includes/resellers.inc.php");
$baseUrl='resellers.php?';
$user->logout();

// start here
// check type
if ($input["act"] == "register") {
	// reseller register here
	define("PAGE_TITLE",'Reseller_Register');
	
	setcookie("reseller_username", "");
	setcookie("reseller_password", "");
	setcookie("reseller_sid", "");
	session_destroy();
	
	// check form submit
	if ($_POST) {
		// form submit, show error
		$errorCode = array();
		
		// 1. name must be exist and cannot longer than 255 chars
		if (strlen($input["name"]) < 4 || strlen($input["name"]) > 254) {
			$errorCode[] = $LANG[Reseller_Register_Error_Name];
		}
		
		// 2. email must be valid format, cannot blank, and shorter than 255 chars
		if (!eregi("([A-Za-z0-9|-|_|@|.|#|%|!|^|&|*]{6,60})",$input["email"])) {
			$errorCode[] = $LANG[Reseller_Register_Error_Email];
		}
		
		// 3. company name cannot longer than 255 chars
		if (strlen($input["company"]) < 4 || strlen($input["company"]) > 254) {
			$errorCode[] = $LANG[Reseller_Register_Error_Company];
		}
		
		// 4. address cannot longer than 1200 chars
		if (strlen($input["address"]) > 1200) {
			$errorCode[] = $LANG[Reseller_Register_Error_Address];
		}

		// 5. country must not blank and shorter than 120 chars
		if (strlen($input["country"]) < 3 || strlen($input["country"]) > 120) {
			$errorCode[] = $LANG[Reseller_Register_Error_Country];
		}
		
		// 6. telephone must exists and cannot longer that 255 chars
		if (strlen($input["telephone"]) < 3 || strlen($input["telephone"]) > 254) {
			$errorCode[] = $LANG[Reseller_Register_Error_Telephone];
		}		
		
		// 7. website cannot longer than 255 chars
		if (strlen($input["website"]) > 254) {
			$errorCode[] = $LANG[Reseller_Register_Error_Website];
		}
		
		
		
		// 9. username must valid format, longer than 6 chars and shorter than 120 chars
		// cannot duplicate and only english (allow - and _)
		if (!eregi("([_A-Za-z0-9-]{6,60})",$input["username"]) || strlen($input["username"]) < 6 || strlen($input["username"]) > 120) {
			$errorCode[] = $LANG[Reseller_Register_Error_Username];
		}
		else {
			// duplicate username check
			$db->setQuery("select id from resellers where username='{$input["username"]}' limit 1");
			$db->query();
			
			if ($db->getNumRows()) {
				$errorCode[] = $LANG[Reseller_Register_Duplicate_Username];
			}
		}
		
		// 10. password must be shorter than 120 chars and more than 6 chars
		if (!eregi("([_A-Za-z0-9-]{6,60})",$input["password1"]) || strlen($input["password1"]) < 6 || strlen($input["password1"]) > 120) {
			$errorCode[] = $LANG[Reseller_Register_Error_Password];
		}
		else {
			// 11. password confirm must be match
			if ($input["password1"] != $input["password2"]) {
				$errorCode[] = $LANG[Reseller_Register_Error_PassConfirm];
			}
		}
		
		// 12. deposit must be more than 200 and less than 10,000
		if ($input["deposit"] < 200) {
			$errorCode[] = $LANG[Reseller_Register_Error_Deposit];
		}
		
		// 13. client payment cannot blank and shorter than 255 chars
		if (strlen($input["clientpaymentmethod"]) == 0 || strlen($input["clientpaymentmethod"]) > 254) {
			$errorCode[] = $LANG[Reseller_Register_Error_ClientPayment];
		}
		
		// 15. customer payment cannot blank and shorter than 255 chars
		if (strlen($input["customerpaymentmethod"]) == 0 || strlen($input["customerpaymentmethod"]) > 254) {
			$errorCode[] = $LANG[Reseller_Register_Error_CustomerPayment];
		}
		
		
		// 16. reseller introduce, must not blank and shorter than 1200 chars
		if (strlen($input["introduction"]) > 1200) {
			$errorCode[] = $LANG[Reseller_Register_Error_Introduction];
		}
		
		// 17. agree term must be checked
		if (!$input["agreeterm"]) {
			$errorCode[] = $LANG[Reseller_Register_Error_AgreeTerm];
		}
		
		
		// check error
		if ($errorCode) {
			// show error
			$template->assign_var("error_message",$LANG["Error_Text"].implode($errorCode, "<br />".$LANG["Error_Text"]));
			
			// show input data
			$template->assign_vars(array(
				"name" => $input["name"],
				"email" => $input["email"],
				"company" => $input["company"],
				"address" => $input["address"],
				"country" => $input["country"],
				"telephone" => $input["telephone"],
				"website" => $input["website"],
				"username" => $input["username"],
				"password1" => $input["password1"],
				"password2" => $input["password2"],
				"deposit" => $input["deposit"],
				"clientpaymentmethod" => $input["clientpaymentmethod"],
				"pintype" => $input["pintype"],
				"customerpaymentmethod" => $input["customerpaymentmethod"],
				"introduction" => $input["introduction"],
				"agreeterm" => $input["agreeterm"] ? "checked" : "",
			));
			
		}
		else {
			// validated
			
			// check 12 hours ip register
			$current_time = time();
			$within_time = $current_time - (12 * 60 * 60);
			$client_ip = $_SERVER["REMOTE_ADDR"];
			
			$db->setQuery("select id from resellers where ip = '{$client_ip}' and regdate > {$within_time} limit 1");
			$db->query();
			
			if ($db->getNumRows()) {
				// exist, show warnings cannot register
				do_redirect("{$baseWeb}", $LANG[Reseller_Register_LimitIP]);
			}
			else {
				// not exists continue
				
				// insert data to resellers table
				// set status waiting
				
				$reseller = new TABLE($db,'resellers','id');
				$reseller->name = $input[name];
				$reseller->email = $input[email];
				$reseller->company = $input[company];
				$reseller->address = $input[address];
				$reseller->country = $input[country];
				$reseller->telephone = $input[telephone];
				$reseller->website = $input[website];
				$reseller->username = $input[username];
				$reseller->password = $input[password1];
				$reseller->deposit = $input[deposit];
				$reseller->clientpay = $input[clientpaymentmethod];
				$reseller->customerpay = $input[customerpaymentmethod];
				$reseller->introduction = $input[introduction];
				
				$reseller->regdate = $current_time;
				$reseller->ip = $client_ip;
				
				$reseller->insert();
				
				
				// send email to admin
				$to      = "xun6.support@gmail.com";
				$subject = "New Reseller - Register!";
				$headers = 'To: Myself <xun6.support@gmail.com>' . "\r\n";
				$headers .= 'From: Xun6 Support <xun6.support@gmail.com>' . "\r\n";
				$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-Type: text/plain; charset="UTF-8"' . "\r\n";			
				
				$message .= "Detail Below: \n\n";
				$message .= "Name:  {$input[name]}\n";
				$message .= "Email: {$input[email]}\n";
				$message .= "Company: {$input[company]}\n";
				$message .= "Address: {$input[address]}\n";
				$message .= "Country: {$input[country]}\n";
				$message .= "Tel: {$input[telephone]}\n";
				$message .= "URL: {$input[website]}\n\n";
				$message .= "Username: {$input[username]}\n";
				$message .= "Password: {$input[password1]}\n\n";
				$message .= "Deposit: {$input[deposit]}\n";
				$message .= "CPAY: {$input[clientpaymentmethod]}\n";
				$message .= "UPAY: {$input[customerpaymentmethod]}\n";
				$message .= "Desc: {$input[introduction]}\n\n\n";
				$message .= "Check it should approve or not\n";
				$message .= "and reply email with payment name and info!\n\n";
				
				@mail($to, $subject, $message, $headers);

				do_redirect("{$baseWeb}", $LANG[Reseller_Register_Success]);
			}	
		}
	}
	else {
		// show empty form
		
	}
	
	$template->assign_var("resellerspage",1);
	$reseller_page = "reseller_register.html";
}
elseif ($input["act"] == "login") {
	//$_SESSION["max_login_try"] = 0;
	if ($_SESSION["max_login_try"] > 12) {
		// disable login, redirect to index
		do_redirect("{$baseWeb}", $LANG[Reseller_DisableLogin]);
	}
	else {
		if (reseller_logined()) {
			// already logined, redirect to portal
			do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_Logined]);
		}
		else {
			// reseller login page
			setcookie("reseller_username", "");
			setcookie("reseller_password", "");
			setcookie("reseller_sid", "");
			
			
			define("PAGE_TITLE",'Reseller_Login');
			
			$input["reseller_username"] = trim($input["reseller_username"]);
			$input["reseller_password"] = trim($input["reseller_password"]);
			
			if ($_POST) {
				// try to login
				$errorCode = "";
				if (!eregi("([_A-Za-z0-9-]{6,60})",$input["reseller_username"]) || strlen($input["reseller_username"]) < 6 || strlen($input["reseller_username"]) > 120) {
					$errorCode = $LANG[Reseller_LoginError];
				}
				else {
					// duplicate username check
					$db->setQuery("select id, status from resellers where username='{$input["reseller_username"]}' and password='{$input["reseller_password"]}' limit 1");
					$db->query();
					
					if ($db->getNumRows()) {
						// exist, username and password match
						$reseller = $db->loadRow();
						
						// check status
						if ($reseller["status"] == "waiting" || $reseller["status"] == "expire" || $reseller["status"] == "banned") {
							$errorCode = $LANG[Reseller_LoginStatusError];
						}
						else {
							// correct, redirect to portal
							// save in cookies and sessions
							session_start();
							$_SESSION["reseller_username"] = $input["reseller_username"];
							$_SESSION["reseller_password"] = $input["reseller_password"];
							$reseller_sid = session_id();
							
							
							$logined_time = time();
							setcookie("reseller_username", $input["reseller_username"], $logined_time + 3600);
							setcookie("reseller_password", sha1($input["reseller_password"].$reseller_sid), $logined_time + 3600);
							setcookie("reseller_sid", $reseller_sid, $logined_time + 3600);
							
							do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_Logined]);
						}
					}
					else {
						$errorCode = $LANG[Reseller_LoginError];
					}
				}
				
				if ($errorCode) {
					// max try is 12 times
					$_SESSION["max_login_try"]++;
						
					$template->assign_var("error_message",$LANG["Error_Text"].$errorCode);
				}
			}
		}
		
		$template->assign_var("resellerssmall",1);
		$reseller_page = "reseller_login.html";
	}
}
elseif ($input["act"] == "logout") {
	// clear cookies
	// clear sessions
	define("PAGE_TITLE",'Reseller_Logout');
	
	setcookie("reseller_username", "");
	setcookie("reseller_password", "");
	setcookie("reseller_sid", "");
	session_destroy();
	do_redirect("{$baseWeb}/resellers.php?act=login", $LANG[Reseller_Logined]);
}
elseif ($input["act"] == "show") {
	show_single_pin($input["pin"]);
	$reseller_page = "reseller_show.html";
}
elseif ($input["act"] == "check") {
	define("PAGE_TITLE",'Reseller_CheckPIN');
	
	if ($_POST || $input["pin_code"]) {
		// check pin exist
		$db->setQuery("select id from pin where reseller_id = '".reseller_id()."' and pin = '{$input["pin_code"]}' limit 1");
		$db->query();
					
		if ($db->getNumRows()) {
			// pin found
			$pin = $db->loadRow();
					
			show_single_pin($pin["id"]);
			$reseller_page = "reseller_show.html";
		}
		else {
			// not found
			// show the pin code and display error
			$template->assign_var("error_message",$LANG["Reseller_PinNotFound"]);
			$template->assign_var("resellerssmall",1);
			$reseller_page = "reseller_check.html";	
		}			
	}
	else {
		$template->assign_var("resellerssmall",1);
		$reseller_page = "reseller_check.html";	
	}
}
elseif ($input["act"] == "order") {
	define("PAGE_TITLE",'Reseller_OrderPIN');
	// reseller order pin from xun6
	/**
	 * 1. reseller place order
	 * 2. admin replace him with payment detail
	 * 3. reseller paid
	 * 4. generate pin from backend
	 * 5. reply reseller
	 * 6. reseller check portal with new pin list
	 */
	
	if (reseller_logined()) {
		if ($_POST) {
			// submit order
			
			// check fields
			/**
			 * a. 1 month
			 * b. 3 month
			 * c. 6 month
			 * d. 1 year
			 * e. payment
			 * d. note
			 */
			if ($input["pintype"]) {
				$total_amount = 0;
				
				$one_month = 0;
				$three_month = 0;
				$six_month = 0;
				$one_year = 0;
				
				foreach ($input["pintype"] as $temp_type) {
					$temp_type = intval($temp_type);
					switch ($temp_type) {
						case 30: 
							// check quantity and price match
							$quantity = intval($input["p30_q"]);
							$p30_price = 0;
							if ($quantity > 0 && $quantity <= 200) {
								$p30_price = 7.2;
							}
							elseif ($quantity > 200 && $quantity <= 500) {
								$p30_price = 7.02;
							}
							elseif ($quantity > 500 && $quantity <= 1000) {
								$p30_price = 6.84;
							}
							elseif ($quantity > 1000 && $quantity <= 5000) {
								$p30_price = 6.66;
							}
							elseif ($quantity > 5000 && $quantity <= 9999) {
								$p30_price = 6.48;
							}
							else {
								// error, 0 or >10000 or chars
								$errorCode[] = $LANG[Reseller_PriceInvalid];
							}
							
							$one_month_quantity = $quantity;
							$one_month_price = $p30_price;
							$one_month_total = $one_month_quantity * $one_month_price;
							
							
							// calc total amount
							$total_amount += $quantity * $p30_price;
						
							break;
						case 90: 
							$quantity = intval($input["p90_q"]);
							$p90_price = 0;
							if ($quantity > 0 && $quantity <= 200) {
								$p90_price = 21.6;
							}
							elseif ($quantity > 200 && $quantity <= 500) {
								$p90_price = 21.06;
							}
							elseif ($quantity > 500 && $quantity <= 1000) {
								$p90_price = 20.52;
							}
							elseif ($quantity > 1000 && $quantity <= 5000) {
								$p90_price = 19.98;
							}
							elseif ($quantity > 5000 && $quantity <= 9999) {
								$p90_price = 19.44;
							}
							else {
								// error, 0 or >10000 or chars
								$errorCode[] = $LANG[Reseller_PriceInvalid];
							}
							
							$three_month_quantity = $quantity;
							$three_month_price = $p90_price;
							$three_month_total = $three_month_quantity * $three_month_price;
							
							// calc total amount
							$total_amount += $quantity * $p90_price;
							
							break;
						case 180: 
							$quantity = intval($input["p180_q"]);
							$p180_price = 0;
							if ($quantity > 0 && $quantity <= 200) {
								$p180_price = 36.00;
							}
							elseif ($quantity > 200 && $quantity <= 500) {
								$p180_price = 35.10;
							}
							elseif ($quantity > 500 && $quantity <= 1000) {
								$p180_price = 34.20;
							}
							elseif ($quantity > 1000 && $quantity <= 5000) {
								$p180_price = 33.30;
							}
							elseif ($quantity > 5000 && $quantity <= 9999) {
								$p180_price = 32.40;
							}
							else {
								// error, 0 or >10000 or chars
								$errorCode[] = $LANG[Reseller_PriceInvalid];
							}
							
							$six_month_quantity = $quantity;
							$six_month_price = $p180_price;
							$six_month_total = $six_month_quantity * $six_month_price;
							
							// calc total amount
							$total_amount += $quantity * $p180_price;
						
							break;
						case 365: 
							$quantity = intval($input["p365_q"]);
							$p365_price = 0;
							if ($quantity > 0 && $quantity <= 200) {
								$p365_price = 57.60;
							}
							elseif ($quantity > 200 && $quantity <= 500) {
								$p365_price = 56.16;
							}
							elseif ($quantity > 500 && $quantity <= 1000) {
								$p365_price = 54.72;
							}
							elseif ($quantity > 1000 && $quantity <= 5000) {
								$p365_price = 53.28;
							}
							elseif ($quantity > 5000 && $quantity <= 9999) {
								$p365_price = 51.84;
							}
							else {
								// error, 0 or >10000 or chars
								$errorCode[] = $LANG[Reseller_PriceInvalid];
							}
							
							$one_year_quantity = $quantity;
							$one_year_price = $p365_price;
							$one_year_total = $one_year_quantity * $one_year_price;
							
							// calc total amount
							$total_amount += $quantity * $p365_price;
						
							break;
					}
				}
				
				// check payment, cannot empty
				if (empty($input["payment_method"])) {
					$errorCode[] = $LANG[Reseller_PaymentMissing];
				}
				
				// all done, clear to go
			}
			else {
				// no select any pin type
				$errorCode[] = $LANG[Reseller_NoSelectPinType];
			}
			
			if ($errorCode) {
				// got error
				$template->assign_var("error_message",$LANG["Error_Text"].implode($errorCode, "<br />".$LANG["Error_Text"]));
			}
			else {
				// not error, save to db 
				$order = new TABLE($db,'orders','id');
				$order->reseller_id = reseller_id();
				$order->status = "waiting";
				$order->one_month = $one_month_quantity;
				$order->three_month = $three_month_quantity;
				$order->six_month = $six_month_quantity;
				$order->one_year = $one_year_quantity;
				$order->total_amount = $total_amount;
				$order->payment = $input["payment_method"];
				$order->extra_note = $input["extra_note"];
				$order->insert();
				
				// and send email to admin
				$message = "";
				// get reseller detail
				$db->setQuery("select id, name, username, email, telephone from resellers where id = '".reseller_id()."' limit 1");
				$db->query();
				
				if ($db->getNumRows()) {
					// reseller found
					$reseller = $db->loadRow();
					
					$message .= "Reseller ID: {$reseller["id"]} \n";
					$message .= "Reseller Name: {$reseller["name"]} \n";
					$message .= "Reseller Email: {$reseller["email"]} \n";
					$message .= "Reseller Tel: {$reseller["telephone"]} \n\n";
				}
				
				$to      = "xun6.support@gmail.com";
				$subject = "New - Reseller Order!";
				$headers = 'To: Xun6 Support <xun6.support@gmail.com>' . "\r\n";
				$headers .= 'From: '.$reseller["username"].' <'.$reseller["email"].'>' . "\r\n";
				$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-Type: text/plain; charset="UTF-8"' . "\r\n";			
				
				$message .= "Detail Below: \n\n";
				$message .= "One Month Plan: {$one_month_quantity} x {$one_month_price} = {$one_month_total}\n";
				$message .= "Three Month Plan: {$three_month_quantity} x {$three_month_price} = {$three_month_total}\n";
				$message .= "Six Month Plan: {$six_month_quantity} x {$six_month_price} = {$six_month_total}\n";
				$message .= "One Year Plan: {$one_year_quantity} x {$one_year_price} = {$one_year_total}\n\n";
				$message .= "Total Amount: USD $ {$total_amount} \n\n";
				
				$message .= "Reply this email to reseller with payment information!\n";
				
				@mail($to, $subject, $message, $headers);

				// redirect tell ordered
				do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_Order_Success]);
			}
		}
		else {
			// no order submit, display empty form
		}
	}
	else {
		// not login
		header("location: {$baseWeb}/resellers.php?act=login");
	}
	
	
	
	$template->assign_var("resellerssmall",1);
	$reseller_page = "reseller_order.html";
}
elseif ($input["act"] == "reload") {
	// reseller help user reload in portal
	
	$reseller_page = "";
}

elseif ($input["act"] == "export") {
	// show reseller purchase success, account problem and etc...
	define("PAGE_TITLE",'Reseller_ExportPIN');
	
	// note, reseller_id must be same!
	if (reseller_logined()) {
		if ($_POST) {
			// generate query
			$input["export_num"] = $input["export_num"] ? $input["export_num"] : 10;
			$input["export_length"] = $input["export_length"] ? $input["export_length"] : 30;
			
			if ($input["export_num"] == "all") {
				$input["export_num"] = 100000;
			}
			
			
			// order by
			if ($input["export_sort"]) {
				$order_by = " ORDER BY create_time ASC LIMIT 0,{$input["export_num"]}";
				
				$text_detail = "SORT: NEW_TO_OLD";
			}
			else {
				$order_by = " ORDER BY create_time DESC LIMIT 0,{$input["export_num"]}";
				
				$text_detail = "SORT: OLD_TO_NEW";
			}
		
			// where condition
			$where_length = " WHERE length='{$input["export_length"]}' and used='{$input["export_used"]}' and reseller_id='".reseller_id()."'";
			
			
			
			// select field type
			$export_field = implode($input[exportfield],", ");
			$export_field = $export_field ? $export_field : "pin, pass";
			$select_column = "SELECT ".$export_field." FROM pin ";
			
			// run query 
			$db->setQuery($select_column.$where_length.$order_by);
			$db->query();
					
			if ($db->getNumRows()) {
				// save variable
				$text_header = $text_detail.", PACKAGE TYPE: {$input["export_length"]} DAYS, EXPORT FIELD: {$export_field} \r\n\r\n";
				$text_body = "";
			
				// got result, format it
				$pins = $db->loadRowList();
				foreach($pins as $value => $pin) {
					$text_body .= implode($pin, " ") . "\r\n";
				}
				
				// export in txt
				#
				header('Content-type: application/txt');
				header('Content-Disposition: attachment; filename="pin'.date("Y.n.d").'.txt"');
				echo $text_header.$text_body;
				exit;
			}
			else {
				// no result, export nothing
				$text_header = $text_detail.", PACKAGE TYPE: {$input["export_length"]} DAYS, EXPORT FIELD: {$export_field} \r\n\r\n";
				$text_body = "NO PIN!!!\r\n";
			
				// export in txt
				#
				#header('Content-type: application/txt');
				#header('Content-Disposition: attachment; filename="pin'.date("Y.n.d").'.txt"');
				#echo $text_header.$text_body;
				exit;
			}
		}
	}
	else {
		header("location: {$baseWeb}/resellers.php?act=login");
	}
	
	
	
	
	$reseller_page = "reseller_export.html";
	$template->assign_var("resellerspage",1);
}
/*
elseif ($input["act"] == "profile") {
	
	
	$reseller_page = "";
}
*/
else {
	// check logined with resellers
	// if login, then show portal
	// if not login, then redirect to index
	define("PAGE_TITLE",'Reseller_Portal');
	
	if (reseller_logined()) {
		// login successed, show portal
		
		/**
		 * Reseller Type:
		 * Normal - $200 Every Month
		 * Agency - $10000 Every Month
		 */

		// update once every six hour only
		$set_time = time() - (6 * 60 * 60);
		$db->setQuery("select amounts, pins, updated from resellers where id = '".reseller_id()."' limit 1");
		$db->query();

		if ($db->getNumRows()) {
			// reseller found
			$reseller = $db->loadRow();
			
			$total_amounts = 0;
			$total_pins = 0;
			
			if ($reseller["updated"] <= $set_time) {
				// need update
				// get sum and count
				$db->setQuery("select sum(price) as amounts, count(id) as pins from pin where reseller_id = '".reseller_id()."'");
				$db->query();
				$summary = $db->loadRow();
				
				$summary_amounts = $summary["amounts"] ? $summary["amounts"] : 0;
				
				// update into resellers
				$current_time = time();
				$db->setQuery("update resellers set amounts = {$summary_amounts}, pins = {$summary["pins"]}, 
					updated = {$current_time} where id = '".reseller_id()."' limit 1");
				$db->query();
				
				// set and output
				$total_amounts = $summary["amounts"];
				$total_pins = $summary["pins"];
			}
			else {
				// no need update
				$total_amounts = $reseller["amounts"];
				$total_pins = $reseller["pins"];
			}
			
			// update total amounts and total pins
			// 1. sum total amounts and pins
			// 2. update into system

			// show total amount and pin
			$template->assign_vars(array(
				"total_amounts" => "USD $".$total_amounts,
				"total_pins" => $total_pins,
			));
		}
		else {
			header("location: {$baseWeb}/resellers.php?act=login");
		}
	
		
	
		// set row limit
		if ($input["page"]) {
			if (is_numeric($input["page"])) {
				$start_row = ($input["page"] - 1) * 20;
				$number_row = 20;
			}
			else {
				$input["page"] = "";
				$start_row = 0;
				$number_row = 20;
			}
		}
		else {
			$start_row = 0;
			$number_row = 20;
		}
		// check report type
		if ($input["act"] == "report") {
			// if using report type
			if ($input["type"] == "method") {
				switch ($input["displaytype"]) {
					case "thismonth":
						$time_start = mktime(0,0,0,date("n"),1,date("Y"));
						$time_end = mktime(0,0,0,date("n")+1,1,date("Y"));
						show_pins($time_start, $time_end, $start_row, $number_row, "date"); 
						break;
					case "lastmonth":
						$time_start = mktime(0,0,0,date("n")-1,1,date("Y"));
						$time_end = mktime(0,0,0,date("n"),1,date("Y"));
						show_pins($time_start, $time_end, $start_row, $number_row, "date");
						break;
					case "thisyear": 
						$time_start = mktime(0,0,0,1,1,date("Y"));
						$time_end = mktime(0,0,0,1,1,date("Y")+1);
						show_pins($time_start, $time_end, $start_row, $number_row, "date");
						break;
					case "lastyear":
						$time_start = mktime(0,0,0,1,1,date("Y")-1);
						$time_end = mktime(0,0,0,1,1,date("Y"));
						show_pins($time_start, $time_end, $start_row, $number_row, "date");
						break;
					case "activated": 
						$query = "used = 1";
						show_pins(1, 1, $start_row, $number_row, "activated", $query);
						break;
					case "noactivate":
						$query = "used = 0";
						show_pins(1, 1, $start_row, $number_row, "noactivate", $query);
						break;
					case "monthly":
						$query = "length = 30";
						show_pins(1, 1, $start_row, $number_row, "monthly", $query);
						break;
					case "sixmonth":
						$query = "length = 180";
						show_pins(1, 1, $start_row, $number_row, "sixmonth", $query);
						break;
					case "yearly": 
						$query = "length = 365";
						show_pins(1, 1, $start_row, $number_row, "yearly", $query);
						break;
					case "paid": 
						$query = "type = 'reseller'";
						show_pins(1, 1, $start_row, $number_row, "paid", $query);
						break;
					case "trial": 
						$query = "type = 'freetrial'";
						show_pins(1, 1, $start_row, $number_row, "trial", $query);
						break;
					default: 
						$time_start = mktime(0,0,0,date("n"),1,date("Y"));
						$time_end = mktime(0,0,0,date("n")+1,1,date("Y"));
						show_pins($time_start, $time_end, $start_row, $number_row, "date");
				}
			}
			elseif ($input["type"] == "date") {
				$time_start = mktime(0,0,0,$input["displayfrommonth"],1,$input["displayfromyear"]);
				$time_end = mktime(0,0,0,$input["displaytomonth"],1,$input["displaytoyear"]);

				show_pins($time_start, $time_end, $start_row, $number_row);
			}
			else {
				// not type was select, show this month
				// set this month time
				$time_start = mktime(0,0,0,date("m"),1,date("Y"));
				$time_end = mktime(0,0,0,date("m")+1,1,date("Y"));
				
				show_pins($time_start, $time_end, $start_row, $number_row);
			}
		}
		else {
			// not type was select, show this month
			// set this month time
			$time_start = mktime(0,0,0,date("m"),1,date("Y"));
			$time_end = mktime(0,0,0,date("m")+1,1,date("Y"));
				
			show_pins($time_start, $time_end, $start_row, $number_row);
		}
		
		$template->assign_vars(array(
			"from_month" => $input["displayfrommonth"] ? $input["displayfrommonth"] : date("n"),
			"from_year" => $input["displayfromyear"] ? $input["displayfromyear"] : date("Y"),
			"to_month" => $input["displaytomonth"] ? $input["displaytomonth"] : date("n") + 1,
			"to_year" => $input["displaytoyear"] ? $input["displaytoyear"] : date("Y"),
			"displaytype" => $input["displaytype"] ? $input["displaytype"] : "thismonth",
		));

		$template->assign_var("resellerspage",1);
		$reseller_page = "reseller_portal.html";
	}
	else {
		// tell reseller login		
		header("location: {$baseWeb}/resellers.php?act=login");
	}
}

require_once("header.php");
$template->set_filenames(array("body"=>$reseller_page));
$template->pparse('body');
include "footer.php";
?>