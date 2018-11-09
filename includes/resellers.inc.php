<?php 
/**
 * Resellers Function 
 */

function reseller_logined() {
	global $baseWeb, $db, $input, $LANG, $template;
	
	$reseller_logined = 0;
	if ($_COOKIE["reseller_username"] && $_COOKIE["reseller_password"]) {
		// check the session id is correct
		if ($_COOKIE["reseller_sid"] == session_id()) {
			$db->setQuery("select password from resellers where username='{$_COOKIE["reseller_username"]}' limit 1");
			$db->query();
			$reseller = $db->loadRow();
			if ($_COOKIE["reseller_password"] == sha1($reseller["password"].session_id())) {
				// login success
				$reseller_logined = 1;
				$template->assign_var("reseller_logined", 1);
			}
			else {
				// not correct, redirect to login page
				header("location: {$baseWeb}/resellers.php?act=login");
			}
		}
		else {
			// clear cookie and redirect to login pages
			header("location: {$baseWeb}/resellers.php?act=login");
		}
	}
	
	return $reseller_logined;
}

function reseller_id() {
	global $db;
	
	$db->setQuery("select id from resellers where username = '{$_COOKIE["reseller_username"]}' limit 1");
	$db->query();
	$reseller = $db->loadRow();
	
	return $reseller["id"];

}

function show_pins($time_start, $time_end, $start_row, $number_row, $report_type = "date", $extra_query = "") {
	global $baseWeb, $db, $LANG, $template;
	
	// validate parameter
	$time_start ? $time_start : 0;
	$time_end ? $time_end : 0;
	$start_row ? $start_row : 0;
	$number_row ? $number_row : 20;
	
	// create query
	$query = 1;
	if ($report_type == "date") {
		// date search query
		if ($time_start && $time_end) {
			$query = "create_time >= $time_start and create_time < $time_end";
			
			$parameter = "act=report&type=date&displayfrommonth=".date("n",$time_start)."&displayfromyear=".date("Y",$time_start)
				."&displaytomonth=".date("n",$time_end)."&displaytoyear=".date("Y",$time_end);
		}
	}
	else {
		// method search query
		$query = $extra_query;
	
		$parameter = "act=report&type=method&displaytype=".$report_type;
	}
	
				
	$reseller_id = reseller_id();
	$db->setQuery("select * from pin where reseller_id = '{$reseller_id}' and $query order by create_time desc limit $start_row, $number_row");
	$db->query();
				
	if ($db->getNumRows()) {
		// got row, put into var
		$pins = $db->loadRowList();
				
		// until here
		$sum["total_amount"] = 0;
		$sum["total_pin"] = 0;
		$sum["total_used"] = 0;
		$sum["total_nouse"] = 0;
				
		foreach($pins as $value => $pin) {
			$sum["total_pin"]++;
			$sum["total_amount"] += $pin["price"];
					
			$pin["pin"] = "xun6"."****".substr($pin["pin"],8,4)."******".substr($pin["pin"],18,6);
			$pin["create_time"] = date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$pin["create_time"]);
			$pin["expire_time"] = date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$pin["expire_time"]);
			$pin["price"] = "USD $".number_format($pin["price"],2,".","");
					
			switch ($pin["length"]) {
				case 3: $pin["note"] = "3 {$LANG["Days"]}"; break;
				case 7: $pin["note"] = "7 {$LANG["Days"]}"; break;
				case 30: $pin["note"] = "1 {$LANG["Months"]}"; break;
				case 90: $pin["note"] = "3 {$LANG["Months"]}"; break;
				case 180: $pin["note"] = "6 {$LANG["Months"]}"; break;
				case 365: $pin["note"] = "1 {$LANG["Years"]}"; break;
			}
					
			if ($pin["type"] == "reseller") {
				$pin["note"] .= " / ". $LANG["Reseller_Paid"];
			}
			elseif ($pin["type"] == "free") {
				$pin["note"] .= " / ". $LANG["Reseller_Trial"];
			}
			else {
				$pin["note"] .= " / ". $LANG["Reseller_System"];
			}
			
			if ($pin["used"]) {
				$sum["total_used"]++;
			}
			else {
				$sum["total_nouse"]++;
			}

			$template->assign_block_vars('pins',$pin);
		}
				
		$template->assign_vars(array(
			"total_amount" => "USD $".number_format($sum["total_amount"],2,".",""),
			"total_pin" => $sum["total_pin"],
			"total_used" => $sum["total_used"],
			"total_nouse" => $sum["total_nouse"],
		));
		
		// page link control
		/**
		 * 1. sum total of row
		 * 2. count total of page
		 * 3. create link
		 * 4. first and last, next and previous
		 */
		$db->setQuery("select count(id) as total_pin from pin where reseller_id = '{$reseller_id}' and {$query}");
		$db->query();
		$reseller = $db->loadRow();
		
		$current_page = ($start_row / 20) + 1;
		
		if ($reseller["total_pin"] > $number_row) {
			// set total page
			$number_of_pages = floor($reseller["total_pin"] / $number_row);
			
			// limit only 10 links in link field
			$link_limit = 10;
			$current_sector = floor(($current_page - 1) / $link_limit);
			$start_count = $current_sector * 10;
			if ($number_of_pages > $start_count + 10) {
				$until_page = $start_count + 10;
			}
			else {
				$until_page = $number_of_pages + 1;
			}
			
			$link_list = "";
			for ($i = $start_count; $i < $until_page; $i++) {
				$link_num = $i + 1;
				
				if ($current_page == $link_num) {
					$link_list .= "<b>{$link_num}</b>";
				}
				else {
					$link_list .= "<a href='{$baseWeb}/resellers.php?{$parameter}&page={$link_num}'>{$link_num}</a> ";
				}
			}
			
			// set first, next, previous, last page
			if ($current_page > 1) {
				$first_page = 1;
				$first_link = "<a href='{$baseWeb}/resellers.php?{$parameter}&page={$first_page}' title='{$LANG["FirstPage"]}'>[ << ]</a> ";
			}
			if ($current_page <= $number_of_pages) {
				$last_page = $number_of_pages + 1;
				$last_link = "<a href='{$baseWeb}/resellers.php?{$parameter}&page={$last_page}' title='{$LANG["LastPage"]}'>[ >> ]</a> ";
			}
			if ($current_page > 1) {
				$previous_page = $current_page - 1;
				$previous_link = "<a href='{$baseWeb}/resellers.php?{$parameter}&page={$previous_page}' title='{$LANG["PreviousPage"]}'>[ < ]</a> ";
			}
			if ($current_page <= $number_of_pages) {
				$next_page = $current_page + 1;
				$next_link = "<a href='{$baseWeb}/resellers.php?{$parameter}&page={$next_page}' title='{$LANG["NextPage"]}'>[ > ]</a> ";
			}

			$template->assign_vars(array(
				"link_list" => $link_list,
				"first_page" => $first_link,
				"last_page" => $last_link,
				"next_page" => $next_link,
				"previous_page" => $previous_link,
			));
		}
		

		$template->assign_var("gotpin",1);
	}
	else {
		// no pin, show empty		
		$template->assign_var("gotpin",0);
	}
}

function show_single_pin($id) {
	global $db, $template, $LANG, $baseWeb;
	
	if (reseller_logined()) {
		// logined
		define("PAGE_TITLE",'Reseller_PINDetail');
	
		if (is_numeric($id)) {
			// search pin for user
			$db->setQuery("select * from pin where reseller_id = '".reseller_id()."' and id = '{$id}' limit 1");
			$db->query();
					
			if ($db->getNumRows()) {
				// pin found
				$pin = $db->loadRow();
				
				// get reseller_name
				$db->setQuery("select name, telephone, email, address from resellers where id='".reseller_id()."' limit 1");
				
				$db->query();
				if ($db->getNumRows()) {
					$reseller = $db->loadRow();
					$reseller_name = $reseller["name"];
					$reseller_tel = $reseller["telephone"];
					$reseller_email = $reseller["email"];
					$reseller_add = $reseller["address"];
				}
				else {
					$reseller_name = $LANG["Reseller_System"];
					$reseller_tel = "xxx-xxxxxxx";
					$reseller_email = "xun6.support@gmail.com";
					$reseller_add = "None";
				}
				
				// choose length
				switch ($pin["length"]) {
					case 3: $length_type = "3".$LANG["Days"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $0.00"; break;
					case 7: $length_type = "7".$LANG["Days"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $0.00"; break;
					case 30: $length_type = "1".$LANG["Reseller_DisplayMonthlyType"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $9.00"; break;
					case 90: $length_type = "3".$LANG["Reseller_DisplayMonthlyType"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $27.00"; break;
					case 180: $length_type = "6".$LANG["Reseller_DisplayMonthlyType"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $45.00"; break;
					case 365: $length_type = "1".$LANG["Reseller_DisplayYearType"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $72.00"; break;
					default: $length_type = "1".$LANG["Reseller_DisplayMonthlyType"].$LANG["Reseller_TypeOfPin"]; $sale_price = "USD $9.00"; 
				}
				
				// set type
				if ($pin["type"] == "reseller") {
					$length_type = $LANG["Reseller_Paid"]." - ".$length_type;
				}
				elseif ($pin["type"] == "trial") {
					$length_type = $LANG["Reseller_Trial"]." - ".$length_type;
				}
				else {
					$length_type = $LANG["Reseller_System"]." - ".$length_type;
				}
				
				$template->assign_vars(array(
					"pin" => strtoupper($pin["pin"]),
					"pass" => strtoupper($pin["pass"]),
					"type" => $pin["type"],
					"length_type" => $length_type,
					"price" => "USD $".number_format($pin["price"], 2, ".", ","),
					"used" => $pin["used"],
					"activate_time" => date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$pin["activate_time"]),
					"length" => $pin["length"],
					"create_time" => date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$pin["create_time"]),
					"expire_time" => date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$pin["expire_time"]),
					"sale_price" => $sale_price,
					"reseller_name" => $reseller_name,
					"reseller_tel" => $reseller_tel,
					"reseller_email" => $reseller_email,
					"reseller_add" => $reseller_add,
				));
				
				$template->assign_var("resellerssmall",1);
			}
			else {
				// no pin, redirect
				do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_PinNotFound]);
			}
		}
		else {
			// no pin, redirect
			do_redirect("{$baseWeb}/resellers.php", $LANG[Reseller_PinNotFound]);
		}
	}
	else {
		// not login, redirect to login
		header("location: {$baseWeb}/resellers.php?act=login");
	}
	
}
?>