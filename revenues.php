<?php
/**
 * Revenues script contain 3 sections
 * 1. show daily or monlty earnings report
 * 2. show payments history and payment details
 * 3. edit payment and payee information
 */

define("IN_PAGE",'MEMBERS');


include "includes/inc.php";
include "includes/revenues.inc.php";

$baseUrl='revenues.php?';

// checking if logined
if($user->logined==0) header('location:login.php');
if ($user->account_status == -1) { $user->logout(); header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended'); }

// checking for user joined revenues program
if ($input["action"] != "join") {
	if($user->revenue_program==0) do_redirect($baseWeb."/members.php",$LANG["HaventJoinRevenue"]);
}


if($input["action"] == "earnings") {
	define("PAGE_TITLE","EarningHistory");
	
	// show revenue menu
	assign_revenue_variable();
	
	// show earnings stat
	earnings_quick_stats();

	$template->assign_var("report_type1","checked");
	$template->assign_var("report_type2","");
	
	if ($input["report_type"] == 1) {
		// showing by special type
		if ($input["type"] == "this_month" || $input["type"] == "last_month" || $input["type"] == "half_year" || $input["type"] == "this_year" || $input["type"] == "last_year") {
			$template->assign_var("report_type1","checked");
			$template->assign_var("report_type2","");
			if ($input["type"] == "this_month") {
				// show this month report
				show_earnings(date("m"),date("Y"),date("m"),date("Y"));
				
				$template->assign_var('from_month',generate_month(date("m")));
				$template->assign_var('to_month',generate_month(date("m")));
				$template->assign_var('from_year',generate_year(date("Y")));
				$template->assign_var('to_year',generate_year(date("Y")));
				
				$template->assign_var("option_type","this_month");
			}
			elseif ($input["type"] == "last_month") {
				// show this month report
				show_earnings(date("m")-1,date("Y"),date("m")-1,date("Y"));
				
				$template->assign_var('from_month',generate_month(date("m")));
				$template->assign_var('to_month',generate_month(date("m")));
				$template->assign_var('from_year',generate_year(date("Y")));
				$template->assign_var('to_year',generate_year(date("Y")));
				
				$template->assign_var("option_type","last_month");
			}
			elseif ($input["type"] == "half_year") {
				// show this month report
				show_earnings(date("m")-6,date("Y"),date("m"),date("Y"));
				
				$template->assign_var('from_month',generate_month(date("m")));
				$template->assign_var('to_month',generate_month(date("m")));
				$template->assign_var('from_year',generate_year(date("Y")));
				$template->assign_var('to_year',generate_year(date("Y")));
				
				$template->assign_var("option_type","half_year");
			}
			elseif ($input["type"] == "this_year") {
				// show this month report
				show_earnings(1,date("Y"),12,date("Y"));
				
				$template->assign_var('from_month',generate_month(date("m")));
				$template->assign_var('to_month',generate_month(date("m")));
				$template->assign_var('from_year',generate_year(date("Y")));
				$template->assign_var('to_year',generate_year(date("Y")));
				
				$template->assign_var("option_type","this_year");
			}
			elseif ($input["type"] == "last_year") {
				// show this month report
				show_earnings(1,date("Y")-1,12,date("Y")-1);
				
				$template->assign_var('from_month',generate_month(1));
				$template->assign_var('to_month',generate_month(12));
				$template->assign_var('from_year',generate_year(date("Y")-1));
				$template->assign_var('to_year',generate_year(date("Y")-1));
			}
			else {
				// show this month report
				show_earnings(date("m"),date("Y"),date("m"),date("Y"));
				
				$template->assign_var('from_month',generate_month(date("m")));
				$template->assign_var('to_month',generate_month(date("m")));
				$template->assign_var('from_year',generate_year(date("Y")));
				$template->assign_var('to_year',generate_year(date("Y")));
				
				$template->assign_var("option_type","last_year");
			}
			
		}
		else {
			// show this month report
			show_earnings(date("m"),date("Y"),date("m"),date("Y"));
			
			$template->assign_var('from_month',generate_month(date("m")));
			$template->assign_var('to_month',generate_month(date("m")));
			$template->assign_var('from_year',generate_year(date("Y")));
			$template->assign_var('to_year',generate_year(date("Y")));
		}
	}
	elseif ($input["report_type"] == 2)  {
		// showing by date
		if ($input["from_month"] && $input["from_year"] && $input["from_month"] > 0 && $input["from_month"] < 13 && $input["from_year"] > 2007 && $input["from_year"] <= date("Y") &&
			$input["to_month"] && $input["to_year"] && $input["to_month"] > 0 && $input["to_month"] < 13 && $input["to_year"] > 2007 && $input["to_year"] <= date("Y")) {
			// show custom report
			
			if (($input["from_year"] > $input["to_year"]) || ($input["from_month"] > $input["to_month"] && $input["from_year"] == $input["to_year"])) {
				// show this month report
				
				//show_earnings(date("m"),date("Y"),date("m"),date("Y"));
				show_earnings($input["from_month"],$input["from_year"],$input["to_month"],$input["to_year"]);
				
				$template->assign_var('from_month',generate_month(date("m")));
				$template->assign_var('to_month',generate_month(date("m")));
				$template->assign_var('from_year',generate_year(date("Y")));
				$template->assign_var('to_year',generate_year(date("Y")));
			}
			else {
				show_earnings($input["from_month"],$input["from_year"],$input["to_month"],$input["to_year"]);
				
				$template->assign_var('from_month',generate_month($input["from_month"]));
				$template->assign_var('to_month',generate_month($input["to_month"]));
				$template->assign_var('from_year',generate_year($input["from_year"]));
				$template->assign_var('to_year',generate_year($input["to_year"]));
			}
			
			$template->assign_var("report_type1","");
			$template->assign_var("report_type2","checked");
		}
		else {
			// show this month report
			show_earnings(date("m"),date("Y"),date("m"),date("Y"));
			
			$template->assign_var('from_month',generate_month(date("m")));
			$template->assign_var('to_month',generate_month(date("m")));
			$template->assign_var('from_year',generate_year(date("Y")));
			$template->assign_var('to_year',generate_year(date("Y")));
		}
	}
	else {
		// no report type, show default list
		show_earnings(date("m"),date("Y"),date("m"),date("Y"));
			
		$template->assign_var('from_month',generate_month(date("m")));
		$template->assign_var('to_month',generate_month(date("m")));
		$template->assign_var('from_year',generate_year(date("Y")));
		$template->assign_var('to_year',generate_year(date("Y")));
	}
	
	$reportPage=1;
	$template->assign_var('showearning',1);
	$contentPage = "revenue_earnings.html";
	
}
elseif ($input["action"] == "payments") {
	define("PAGE_TITLE","PaymentReport");
	
	// show revenue menu
	assign_revenue_variable();
	
	// show payment quickstat
	payments_quick_stats();
	
	
	if ($input["report_type"] == "" || $input["report_type"] == "last_three_month") {
		// show payments report for last 3 months
		show_payments(date("m")-2,date("Y"),date("m"),date("Y"));
	}
	elseif ($input["report_type"] == "last_six_month") {
		// show payments report for last 3 months
		show_payments(date("m")-5,date("Y"),date("m"),date("Y"));
	}
	elseif ($input["report_type"] == "all_debits") {
		// show all credits only
		show_payments(1,2008,1,2016,"debits");
	}
	elseif ($input["report_type"] == "all_credits") {
		// show all credits only
		show_payments(1,2008,1,2016,"credits");
	}
	elseif ($input["report_type"] == "from_the_start") {
		// show from the start which is may 2008 when revenue program start
		show_payments(5,2008,date("m"),date("Y"));
	}
	else {
		$input["report_type"] = "last_three_month";
		// show default payments report
		show_payments(date("m")-3,date("Y"),date("m"),date("Y"));
	}
	
	// assign selected custom report type back to template
	$template->assign_var("option_type",$input["report_type"]);

	$template->assign_var('showpayment',1);
	$contentPage = "revenue_payments.html";
}
elseif ($input["action"] == "editpayments") {
	define("PAGE_TITLE","EditPayments");
	
	if ($_POST) {
		if (trim($input['payment_method']) == "paypal" or trim($input['payment_method']) == "alipay") {
			if (check_email_address(trim($input['payment_email']))) {
				if (is_numeric(trim($input['payment_minimum'])) and trim($input['payment_minimum'])>=30) {
					$db->setQuery("update users set payment_method='".$input['payment_method']."', payment_email='".$input['payment_email']."', payment_minimum='".$input['payment_minimum']."' where id='".$user->getValue('uid')."'");
					$db->query();
					do_redirect($baseWeb."/revenues.php?action=editpayments",$LANG['RevenueEdited']);
				}
				else {
					$information=$LANG['PaymentMinimumInvalid'];
				}
			}
			else {
				$information=$LANG['PaymentEmailInvalid'];
			}
		}
		else {
			$information=$LANG['PaymentDetailMissing'];
		}
		if ($information) {
			// show error and display last input
			$template->assign_var("information",$information);
			show_payments_info($input["payment_email"],$input["payment_minimum"],$input["payment_method"]);
		}
	}
	else {
		show_payments_info();
	}
	
	$contentPage = "revenue_payinfo.html";
}
elseif ($input["action"] == "join") {
	define("PAGE_TITLE","JoinRevenue");
	define("MIN_DL_JOIN_REVENUE", 200);

	// check is user already join revenue
	$db->setQuery("select revenue_program from users where id='".$user->getValue('uid')."' limit 1");
	$db->query(); $user_record=$db->loadRow();
	if ($user_record['revenue_program']) { do_redirect("{$baseWeb}/members.php",$LANG[AlreadyJoined]); }
		
	// check is user have enough download
	/*
	$db->setQuery("select sum(fs.dls) as total from filestats as fs, files as f where f.uid='".$user->getValue('uid')."' limit 200");	
	$db->query(); $total_downloads=$db->loadRow();
	if ($total_downloads['total'] < MIN_DL_JOIN_REVENUE) { do_redirect("{$baseWeb}/members.php",$LANG[NeedMoreDownloadToJoin]); }
	*/
	
	if ($_POST) {	
		// validate checked agree term
		if (!$input['agree_term']) { 
			$errorCode[] = $LANG['HaventAgreeRevenueTerm']; 
		}
		
		// validate payment method
		if (trim($input['payment_method']) != "paypal" && trim($input['payment_method']) != "alipay") {
			$errorCode[] = $LANG['PaymentDetailMissing'];
		}
		
		// validate email account
		
		
		if (!check_email_address(trim($input['payment_email']))) {
			$errorCode[] = $LANG['PaymentEmailInvalid'];
		}

		// validate minimum payment
		if (!is_numeric(trim($input['payment_minimum'])) || trim($input['payment_minimum']) < 10) {
			$errorCode[] = $LANG['PaymentMinimumInvalid'];
		}
		
		if ($errorCode) {
			// got error, display it and display previous data
			
			foreach($errorCode as $errorText) {
				$information .= "{$errorText}<br />";
				//echo $errorText;
			}
			$template->assign_var("information",$information);
			
			if ($input['agree_term']) {
				$template->assign_var("term_checked","checked");
			}
			
			
			show_payments_info($input['payment_email'],$input['payment_minimum'],$input['payment_method'],1);
		}
		else {
			// no error, update and insert to database
			
			// upadte pay info into users record
			$db->setQuery("update users set 
				revenue_program=1,
				payment_method='".trim($input['payment_method']).
				"',payment_email='".trim($input['payment_email']).
				"',payment_minimum='".trim($input['payment_minimum']).
				"',revenue_join_date='".time().
				"' where id='".$user->getValue('uid')."'");
			$db->query();
			
			// insert free USD$0.20 for user
			$db->setQuery("insert into earnings (time,uid,total_dls,total_ips,avg_fs,total_v,total_nv,earning) 
				values ('".time()."','".$user->getValue('uid')."','0','0','0','0','0','2.00')");
			$db->query();
			
			// redirect to related pages
			do_redirect("{$baseWeb}/revenues.php?action=earnings",$LANG['RevenueWelcome']);
		}
	}
	else {
		show_payments_info("","","paypal",1);
	}

	$contentPage = "revenue_payinfo.html";
}
elseif ($input["action"] == "commissions") {
	define("PAGE_TITLE","Commission_Program");
	
	// show revenue menu
	assign_revenue_variable();
	
	// calculate this month revenue
	$this_month = mktime(0,0,0,date("n"),1,date("Y"));
	$next_month = mktime(0,0,0,date("n")+1,1,date("Y"));
	$db->setQuery("select count(id) as total_account, sum(earning) as total_earning, max(time) as latest_sales ".
		" from referrals where uid = ".$user->uid." and time >= $this_month and time <= $next_month");
	$db->query();
	if ($db->getNumRows()) {
		$temp = $db->loadRow();
		
		
		$template->assign_vars(array(
			"current_sales" => $temp["total_account"],
			"current_earning" => "USD$ ".number_format($temp["total_earning"],2,".",","),
			"latest_sales" => $temp["latest_sales"] ? date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$temp["latest_sales"]) :
			$LANG["Commission_NoSale"],
		));
	}
	
	// including commission
	/**
	 * 1. get all row data for this month
	 * 2. sum data row
	 * 3. monthly display section
	 */
	
	// check custom field
	if ($input["custom"]) {
		switch ($input["custom"]) {
			case "thismonth": 
				$from_time = mktime(0,0,0,date("n"),1,date("Y"));
				$to_time = mktime(0,0,0,date("n")+1,1,date("Y"));
				$query = " time >= $from_time and time < $to_time "; 
				break;
			case "lastmonth":
				$from_time = mktime(0,0,0,date("n")-1,1,date("Y"));
				$to_time = mktime(0,0,0,date("n"),1,date("Y"));
				$query = " time >= $from_time and time < $to_time "; 
				break;
			case "halfyear":
				$from_time = mktime(0,0,0,date("n")-5,1,date("Y"));
				$to_time = mktime(0,0,0,date("n")+1,1,date("Y"));
				$query = " time >= $from_time and time < $to_time "; 
				break;
			case "thisyear":
				$from_time = mktime(0,0,0,date("n")+1,1,date("Y")-1);
				$to_time = mktime(0,0,0,date("n")+1,1,date("Y"));
				$query = " time >= $from_time and time < $to_time "; 
				break;
			case "complete":
				$query = " status = 'complete' "; 
				break;
			case "reject": 
				$query = " status = 'reject' "; 
				break;
			case "waiting":
				$query = " status = 'waiting' "; 
				break;
			case "fromfile":
				$query = " type = 'commission' "; 
				break;
			case "fromurl":
				$query = " type = 'affiliate' "; 
				break;
			case "package30":
				$query = " 	package = '30' "; 
				break;
			case "package180":
				$query = " 	package = '180' "; 
				break;
			case "package365":
				$query = " 	package = '365' "; 
				break;
			default:
				$from_time = mktime(0,0,0,$input["month"],1,$input["year"]);
				$to_time = mktime(0,0,0,$input["month"]+1,1,$input["year"]);
				$query = " time >= $from_time and time < $to_time "; 
		}
		
		$template->assign_vars(array(
			"custom" => $input["custom"],
			"month" => date("n"),
			"year" => date("Y"),
		));
	}
	else {
		// check search field
		if (!($input["month"] && $input["year"] && is_int(intval($input["month"])) && is_int(intval($input["year"])))) {
			$input["month"] = date("n");
			$input["year"] = date("Y");
		}

		$from_time = mktime(0,0,0,$input["month"],1,$input["year"]);
		$to_time = mktime(0,0,0,$input["month"]+1,1,$input["year"]);
		
		$query = " time >= $from_time and time < $to_time "; 
		
		$template->assign_vars(array(
			"custom" => "thismonth",
			"month" => $input["month"],
			"year" => $input["year"],
		));
	}
	

	
	// set time

	
	// get data row from
	$db->setQuery("select * from referrals where uid = ".$user->uid.
		" and $query order by time desc");
	$db->query();
	
	//echo "select * from referrals where uid = ".$user->uid.
	//	" and $query order by time desc limit 40";
	
	//output row to template
	if ($db->getNumRows()) {
		$commissions = $db->loadRowList();
		
		$total_account = 0;
		$total_amount = 0;
		$total_earning = 0;
		
		foreach($commissions as $value => $commission) {
			// sum data
			$total_account++;
			$total_amount += $commission["amount"];
			$total_earning += $commission["earning"];
		
			// format data
			$commission["time"] = date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}", $commission["time"]);
			
			$commission["amount"] = "USD$ ".number_format($commission["amount"],2,".",",");
			$commission["earning"] = "USD$ ".number_format($commission["earning"],2,".",",");
			
			$commission["package"] = $commission["package"].$LANG["Commission_DayPackage"];
		
			$template->assign_block_vars('commissions',$commission);
		}
		
		// output sum data
		$template->assign_vars(array(
			"total_account" => $total_account,
			"total_amount" => "USD$ ".number_format($total_amount,2,".",","),
			"total_earning" => "USD$ ".number_format($total_earning,2,".",","),
		));

		$template->assign_var("commissions_report",1);
	}
	else {
		$template->assign_vars(array(
			"total_account" => 0,
			"total_amount" => "USD$ 0.00",
			"total_earning" => "USD$ 0.00",
		));
		
		$template->assign_var("commissions_report",0);
	}
	
	
	$template->assign_var('commissionpage',1);
	$contentPage = "revenue_commisions.html";
}
else {
	define("PAGE_TITLE","Revenue_Wrong_Path");
	// false action, redirect to members space
	do_redirect($baseWeb."/members.php",$LANG["Revenue_Wrong_Path"]);
}

/**
 * Account Type
 */
switch ($user->package_id) {
  	case 2:
		$template->assign_var("account_type","<b>".$LANG["Member"]."</b>");
   		break;
   	case 3:
   		$template->assign_var("account_type","<b class='p'>".$LANG["Premium"]."</b>");
   		break;
   	case 4:
   		$template->assign_var("account_type","<b class='t'>".$LANG["Tester"]."</b>");
   		break;
   	default:
   		$template->assign_var("account_type","<b>".$LANG["Member"]."</b>");
   		break;
}
    
$template->assign_var("groups_id",$user->package_id);


// output template
require_once("header.php");
$template->set_filenames(array(
	'body' => $contentPage)
	);
$template->pparse('body');
include "footer.php";
?>