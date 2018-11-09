<?php
// delete this after completed
date_default_timezone_set('Asia/Hong_Kong');
//echo date("H:i:s");

/**
 * Earnings Report Functions
 */
function assign_revenue_variable() {
	global $user,$template,$baseWeb;
	
	if($user->revenue_program == 1) { $template->assign_var('showRevenue',1); } 
	else { $template->assign_var('showRevenue',0); header("location: $baseWeb/members.php"); }
}

function get_member_stats() {
	global $db,$user,$template;
	
	// get sum from user files and filestats
	$db->setQuery("select count(f.id) as total_files, sum(f.size) as space_usaged, sum(fs.dls) as total_downloads from files as f left join filestats as fs
		on f.upload_id=fs.upload_id where f.uid={$user->getValue('uid')}");
	$db->query();
	$stats = $db->loadRow();
	
	$UserStats["total_files"] = $stats["total_files"];
	$UserStats["space_usaged"] = convertsize($stats["space_usaged"]);
	$UserStats["total_downloads"] = $stats["total_downloads"];
	
	$template->assign_vars($UserStats);
}

function earnings_quick_stats() {
	global $db,$user,$template;
	/**
	 * Get Earnings Page Brief Stats
	 * 1. Today Earnings
	 * 2. Yesterday Earnings
	 * 3. Today Downloads
	 * 4. Yesterday Downloads
	 * 5. Today IP
	 * 6. Today Average Size
	 */
	$tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
	$today = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$yesterday = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
	
	// Get Yesterday only
	$db->setQuery("select earning from earnings where uid={$user->getValue('uid')} and time >= $yesterday and time <= $today");
	$db->query();
	$e_yesterday = $db->loadRow();
	
	// Get Today only
	$db->setQuery("select earning, total_dls, total_ips, avg_fs from earnings where uid={$user->getValue('uid')} and time >= $today and time <= $tomorrow");
	$db->query();
	$e_today = $db->loadRow();
	
	$template->assign_vars(array(
		'today_earnings'=>number_format($e_today["earning"],2,'.',','),
		'yesterday_earnings'=> number_format($e_yesterday["earning"],2,'.',','),
		'today_downloads'=>$e_today["total_dls"] ? $e_today["total_dls"] : 0,
		'today_ips'=>$e_today["total_ips"] ? $e_today["total_ips"] : 0,
		'today_avg_size'=>$e_today["avg_fs"] ? convertsize($e_today["avg_fs"]) : convertsize(0),
	));
	
	
	// remain section - do tomorrow
	$from_time = mktime(0,0,0,date("m"),1,date("Y"));
	$to_time = mktime(0,0,0,date("m")+1,1,date("Y"));
	
	// Get This Month only
	$db->setQuery("select sum(earning) as earnings, sum(total_ips) as ips, sum(total_dls) as downloads from earnings where uid={$user->getValue('uid')} and time >= $from_time and time <= $to_time");
	$db->query();
	$e_montly = $db->loadRow();
	
	// quick stat monthly
	$template->assign_vars(array(
		'monthly_earnings'=>$e_montly["earnings"] ? "USD$".number_format($e_montly["earnings"],2,'.',',') : "USD$0.00",
		'monthly_download'=>$e_montly["downloads"] ? $e_montly["downloads"] : 0,
		'monthly_ip'=>$e_montly["ips"] ? $e_montly["ips"] : 0,
	));
}

function show_earnings($from_month,$from_year,$to_month,$to_year) {
	global $db,$user,$template,$LANG;
	
	$template->assign_vars(array('Report_Month'=>$month,'Report_Year'=>$year));
	
	/**
	 * Get Earnings Row From Table and format it
	 */
	if ($from_month == $to_month && $from_year == $to_year) {
		$from_time = mktime(0,0,0,$from_month,1,$from_year);
		$to_time = mktime(0,0,0,$to_month+1,1,$to_year);
	}
	else {
		$from_time = mktime(0,0,0,$from_month,1,$from_year);
		$to_time = mktime(0,0,0,$to_month+1,1,$to_year);
	}

	$db->setQuery("select * from earnings where uid={$user->getValue('uid')} and time >= $from_time  and time < $to_time");
	$db->query();
	
	// if have more that 1 row earnings
	if ($db->getNumRows()) {
		$earnings = $db->loadRowList();
		$count = 0;
		foreach($earnings as $value => $earning) {
			// calculate sum
			$total_earning+=$earning['earning'];
			$total_nonvalidate+=$earning['total_nv'];
			$total_validate+=$earning['total_v'];
			$total_average+=$earning['avg_fs'];
			$total_ipaddress+=$earning['total_ips'];
			$total_download+=$earning['total_dls'];
			
			// format value
			$earning["time"] = date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}", $earning["time"]) . $LANG[Weekday][date("w", $earning["time"])];
			$earning["earning"] = "USD$".number_format($earning["earning"],2,".",",");
			$earning["avg_fs"] = convertsize($earning['avg_fs']);
			
			// odd effect
			$earning["odd_effect"] = $count % 2;
			
			$template->assign_block_vars('earnings',$earning);
			
			$count++;
		}
		
		
		// assign sum and avg value to template
		$template->assign_vars(array(
			'sum_earnings_ips'=>$total_ipaddress ? $total_ipaddress : 0,
			'avg_earnings_sizes'=>$total_average ? convertsize($total_average/($value+1)) : convertsize(0),
			'sum_earnings_vDL'=>$total_validate ? $total_validate : 0,
			'sum_earnings_nvDL'=>$total_nonvalidate ? $total_nonvalidate : 0,
			'sum_earnings_dls'=>$total_download ? $total_download : 0,
			'sum_earnings_total'=>$total_earning ? "USD$".number_format($total_earning,2,'.',',') : "USD$0.00",
			
			'avg_earnings_dl'=>$total_download ? ceil($total_download/$count) : 0,
			'avg_earnings_ips'=>$total_ipaddress ? ceil($total_ipaddress/$count) : 0,
			'avg_earnings_vDL'=>$total_validate ? ceil($total_validate/$count) : 0,
			'avg_earnings_nvDL'=>$total_nonvalidate ? ceil($total_nonvalidate/$count) : 0,
			'avg_earnings_total'=>$total_earning ? "USD$".round($total_earning/$count, 2) : "USD$0.00",
			'earnings_report'=>$count > 0 ? 1 : 0,
		));
		
	}
	else {
		// empty earnings page, show waiting message
		// assign sum and avg value to template
		$template->assign_vars(array(
			'sum_earnings_ips'=>0,
			'avg_earnings_sizes'=>convertsize(0),
			'sum_earnings_vDL'=>0,
			'sum_earnings_nvDL'=>0,
			'sum_earnings_dls'=>0,
			'sum_earnings_total'=>"USD$0.00",
			'avg_earnings_dl'=>0,
			'avg_earnings_ips'=>0,
			'avg_earnings_vDL'=>0,
			'avg_earnings_nvDL'=>0,
			'avg_earnings_total'=>"USD$0.00",
			'earnings_report'=>0,
		));
	}
}

/**
 * Payments Report Functions
 */
function payments_quick_stats() {
	global $db,$user,$template,$LANG;
	
	/**
	 * 1. Get This Month Lastest Condition and Date
	 * 2. Get Sum of Earnings, Paid, UnPaid
	 */
	$db->setQuery("select `condition`,`total_pay` from payments where uid={$user->getValue('uid')} order by date desc limit 1");
	$db->query();
	
	// if found lastest condition
	if ($db->getNumRows()) {
		// show lastest condition detail
		$lastest_detail = $db->loadRow();
		switch (trim($lastest_detail["condition"])) {
			case "earning":
				$template->assign_var("lastest_condition","USD$".$lastest_detail["total_pay"]."#".$LANG["ConditionEarning"]);
				break;
			case "verifying":
				$template->assign_var("lastest_condition","USD$".$lastest_detail["total_pay"]."#".$LANG["ConditionVerifying"]);
				break;
			case "processing":
				$template->assign_var("lastest_condition","USD$".$lastest_detail["total_pay"]."#".$LANG["ConditionProcessing"]);
				break;
			case "paid":
				$template->assign_var("lastest_condition","USD$".$lastest_detail["total_pay"]."#".$LANG["ConditionPaid"]);
				break;
		}
		
		// show total earn, paid and unpaid
		$db->setQuery("select total_pay, `condition` from payments where uid={$user->getValue('uid')} and (`condition`='earning' or `condition`='extra' or `condition`='paid') order by date");
		$db->query();
		$payments = $db->loadRowList();
		
		$total_earned = 0;
		$total_paid = 0;
		$total_unpaid = 0;
		foreach ($payments as $payment) {
			if ($payment["condition"] == "earning") {
				$total_earned += $payment["total_pay"];
			}
			else {
				$total_paid += $payment["total_pay"];
			}
		}
		$total_paid = $total_paid * -1;
		$total_unpaid = $total_earned - $total_paid;
		
		$template->assign_vars(array(
			'payments_total_earned'=>"USD$".number_format($total_earned,2,'.',','),
			'payments_total_paid'=>"("."USD$".number_format($total_paid,2,'.',',').")",
			'payments_total_unpaid'=>"USD$".number_format($total_unpaid,2,'.',','),
		));
		
		$template->assign_var("payments_report",1);
	}
	// still don't have any record
	else {
		$template->assign_vars(array(
			'lastest_condition'=>$LANG["No_Lastest_Condition"],
			'payments_total_earned'=>"USD$ 0.00",
			'payments_total_paid'=>"USD$ 0.00",
			'payments_total_unpaid'=>"USD$ 0.00",
		));
		
		$template->assign_var("payments_report",0);
		
		
		
		
		
		
	}
}

/**
 * Show Payments Report By Month
 * condition (all,credits,debits) for custom report
 */
function show_payments($from_month,$from_year,$to_month,$to_year,$condition="all") {
	global $db,$user,$template,$LANG;
	
	// create timestamp from input month and year
	$from_time = mktime(0,0,0,$from_month,1,$from_year);
	$to_time = mktime(0,0,0,$to_month+1,1,$to_year);
	
	if ($condition == "all") {
		// get unpaid revenue from the pass
		$db->setQuery("select sum(total_pay) as earning from payments where uid={$user->getValue('uid')} and date < {$from_time} and (`condition` = 'earning' or `condition` = 'extra') order by date");
		$db->query();
		$remaining1 = $db->loadRow();
		
		$db->setQuery("select sum(total_pay) as paid from payments where uid={$user->getValue('uid')} and date < {$from_time} and `condition` = 'paid' order by date");
		$db->query();
		$remaining2 = $db->loadRow();
		
		$pass_remain = number_format($remaining1["earning"] + $remaining2["paid"],2,".",",");
		
		// unset not used variable
		unset($remaining1,$remaining2);
		
		
		// start normal query
		$db->setQuery("select * from payments where uid={$user->getValue('uid')} and date >= {$from_time} and date <= {$to_time} order by date");
		$db->query();
	}
	elseif ($condition == "debits") {
		$db->setQuery("select * from payments where uid={$user->getValue('uid')} and (`condition` = 'earning' or `condition` = 'extra') order by date");
		$db->query();
	}
	elseif ($condition == "credits") {
		$db->setQuery("select * from payments where uid={$user->getValue('uid')} and (`condition` = 'processing' or `condition` = 'paid') order by date");
		$db->query();
	}
	
	
	
	if ($db->getNumRows()) {
		if ($pass_remain > 0) {
			$template->assign_var('pass_remain',"USD$".$pass_remain);
		}
		
		$template->assign_var('show_monthly_remain',0);
		$monthly_remain = $pass_remain;
		$started = 0;

		$current_year = date("Y",$payment['date']);
		$current_month = date("m",$payment['date']);
		
		$payments=$db->loadRowList();
		foreach($payments as $payment) {
			// show monthly summary earnings or remain
			if ($current_year < date("Y",$payment['date'])) {
				// if next year is bigger than current year
				
				if ($started) {
					// assign new current time
					$current_year = date("Y",$payment['date']);
					$current_month = date("m",$payment['date']);
						
					if ($current_month-1 == 0) {
						$temp["month"] = $LANG[Month][12];
					}
					else {
						$temp["month"] = $LANG[Month][$current_month-1];
					}
					
					
					// assign monthly sum to template
					$temp["type"] = 3;
					if ($monthly_remain >= 0) {
						$temp["monthly_remain"] = "USD$".number_format($monthly_remain,2,'.',',');
					}
					else {
						$temp_remain = $monthly_remain * -1;
						$temp["monthly_remain"] = "("."USD$".number_format($temp_remain,2,'.',',').")";
						$temp_remain = "";
						echo "zbcv";
					}
						
					// show block
					$template->assign_block_vars('payments',$temp);
					$temp = "";
				}
				else {
					$started = 1;
				}
			}
			else {
				if ($current_month < date("m",$payment['date'])) {
					if ($started) {
						// assing new current time
						$current_year = date("Y",$payment['date']);
						$current_month = date("m",$payment['date']);
						
						if ($current_month-1 == 0) {
							$temp["month"] = $LANG[Month][1];
						}
						else {
							$temp["month"] = $LANG[Month][$current_month-1];
						}
						
						
						// assign monthly sum to template
						$temp["type"] = 3;
						if ($monthly_remain >= 0) {
							$temp["monthly_remain"] = "USD$".number_format($monthly_remain,2,'.',',');
						}
						else {
							$temp_remain = $monthly_remain * -1;
							$temp["monthly_remain"] = "("."USD$".number_format($temp_remain,2,'.',',').")";
							$temp_remain = "";
						}
						
						
								
						// show block
						$template->assign_block_vars('payments',$temp);
						$temp = "";
					}
					else {
						$started = 1;
					}
				}
			}

			// set more user friendly date and month
			$temp["month"]=$LANG[Month][intval(date("m",$payment['date']))]." ".date("Y",$payment['date']);
			$temp["date"]=date("d{$LANG[Days]}",$payment['date']);
			
			// displaying related information and revenues
			$temp["type"] = 0;
			switch (trim($payment["condition"])) {
				case "earning":
					$monthly_remain += $payment["total_pay"];

					$temp["descr"] = $LANG["Desc_Cond_Earning"];
					$temp["debits"] = "USD$".$payment["total_pay"];
					$temp["type"] = 1;					
					break;
				case "extra":
					$monthly_remain += $payment["total_pay"];
					
					$temp["descr"] = $LANG["Desc_Cond_Extra"];
					$temp["debits"] = "USD$".$payment["total_pay"];
					$temp["type"] = 1;
					break;
				case "verifying":
					$temp["descr"] = $LANG["Desc_Cond_Verifying"];
					$temp["type"] = 2;
					break;
				case "processing":
					$temp["descr"] = $LANG["Desc_Cond_Processing"];
					$temp["credits"] = "USD$".$payment["total_pay"];
					$temp["type"] = 1;
					break;
				case "paid":
					$monthly_remain += $payment["total_pay"];
					
					$temp["descr"] = $LANG["Desc_Cond_Paid"];
					$payment["total_pay"] = $payment["total_pay"] * -1;
					$temp["credits"] = "("."USD$".number_format($payment["total_pay"],2,'.',',').")";
					$temp["type"] = 1;
					break;
				case "waiting":
					$temp["descr"] = $LANG["Desc_Cond_Waiting"];
					$temp["type"] = 2;
					break;
				default:
					break;
					
			}
			$template->assign_block_vars('payments',$temp);
			
			$last_month = $LANG[Month][intval(date("m",$payment['date']))];
			
			$payment = "";
			$temp = "";
		}
		
		// showing lastest monthly remain
		if ($monthly_remain != 0) {
			if ($monthly_remain >= 0) {
				$template->assign_var("last_month",$last_month);
				$template->assign_var("monthly_remain","USD$".number_format($monthly_remain,2,'.',','));
			}
			else {
				
				$template->assign_var("last_month",$last_month);
				$temp_remain = $monthly_remain * -1;
				$template->assign_var("monthly_remain","("."USD$".number_format($temp_remain,2,'.',',').")");
				$temp_remain = "";
			}
		}
		
		$template->assign_var("payments_report",1);
	}
	else {
		$template->assign_var("payments_report",0);
	}
}

/**
 * Generate Month and Year that Pass in
 */
function generate_month($month) {
	global $template,$LANG;
	
	$month_option_list = "";
	foreach ($LANG[Month] as $value => $text) {
		if ($month == $value) {
			$month_option_list .= "<option value='{$value}' selected='selected'>{$text}</option>";
		}
		else {
			$month_option_list .= "<option value='{$value}'>{$text}</option>";
		}
	}
	return $month_option_list;
}

function generate_year($year) {
	global $template,$LANG;
	
	$year_option_list = "";
	for ($i = 2008; $i <= date("Y"); $i++) {
		if ($year == $i) {
			$year_option_list .= "<option value='{$i}' selected='selected'>{$i}</option>";
		}
		else {
			$year_option_list .= "<option value='{$i}'>{$i}</option>";
		}
	}
	return $year_option_list;
}


/**
 * Edit Payee Information
 */
function edit_payments() {
	global $db,$user,$template,$LANG;
	

}

function show_payments_info($payment_email="",$payment_minimum="",$payment_method="paypal",$is_join=0) {
	global $db,$user,$template;
	
	
	if ($is_join) {
		$template->assign_var('joinrevenue',1);
		
		$template->assign_var('payment_paypal','checked="checked"');
		$template->assign_var('payment_email',$payment_email != "" ? $payment_email : "");
		$template->assign_var('payment_minimum',$payment_minimum != "" ? $payment_minimum : "");
	}
	else {
		$db->setQuery("select payment_method,payment_email,payment_minimum from users where id='".$user->getValue('uid')."' and revenue_program=1");
		$db->query();
		
		$payment_info=$db->loadRow();
	
		if ($payment_info['payment_method'] == "paypal") {
			$template->assign_var('payment_paypal','checked="checked"');
		}
		elseif ($payment_info['payment_method'] == "alipay") {
			$template->assign_var('payment_alipay','checked="checked"');
		}
		$template->assign_var('payment_email',$payment_email != "" ? $payment_email : $payment_info['payment_email']);
		$template->assign_var('payment_minimum',$payment_minimum != "" ? $payment_minimum : $payment_info['payment_minimum']);
	}
	
	$template->assign_block_vars('EditRevenue',array());
	$editRevenue=1;
	$template->assign_var('small_revenuepage',$editRevenue);
}



























?>