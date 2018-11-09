<?php
/**
 * Users: A pages that manager user account type, edit detailes, usages and others
 */

define("IN_PAGE",'PROFILES');

include("includes/inc.php");

$baseUrl = "profiles.php?";

# checking if logined
if($user->logined==0) header('location:login.php');

$current_time = time();

/**
 * Select action
 */
if ($input["act"] == "pass") {
	// Assign page name
	$LANG[Profile_Modify_Password] = $LANG[Edit_Profile] . " - " . $LANG[Profile_Modify_Password];
	define("PAGE_TITLE","Profile_Modify_Password");
	
	/**
	 * Modify Password
	 */
	$input["current_password"] = trim($input["current_password"]);
	$input["new_password"] = trim($input["new_password"]);
	$input["reconfirm_password"] = trim($input["reconfirm_password"]);
	
	// check old password is match
	$db->setQuery("select pass from users where id = '$user->uid'");
	$db->query();
	$row = $db->loadRow();
	if ($row["pass"] != $input["current_password"]) {
		$information[] = $LANG["PasswordNotMatchCurrent"];
	}
		
	
	// check new password format is correct
	if (!eregi("([A-Za-z0-9|-|_|@|.|#|%|!|^|&|*]{6,60})",$input["new_password"])) {
		$information[] = $LANG["InvalidPasswordFormat"];
	}
	
	// check new password and reconfirm password is match	
    if($input["new_password"] != $input["reconfirm_password"])
    {
        $input[reconfirm_password] = "";
        $information[] = $LANG["PasswordNotMatch"] ;
    }

	// update
	// update
	if ($information) {
		// got error, output error
		$information = $LANG["Error_Text"].implode($information,"<br>".$LANG["Error_Text"]);
		
		$template->assign_vars(array(
			"profile_total_files" => $user->files,
                        "profile_total_folders"=> $user->folders,
			"profile_storage_used" => convertsize($user->webspace),
			"profile_deleted_files" => $user->deleted_files,
			"profile_deleted_space" => convertsize($user->deleted_space, 1),
			"hosted_files_stream" => $user->hosted_files_stream,
                        "direct_checked" => $user->dl_direct ? "checked" : "",
		));
		
		$template->assign_var("information1",$information);
	}
	else {
		// no error, update
		$db->setQuery("update users set pass = '{$input["new_password"]}' where id = '{$user->uid}' and pass = '{$input["current_password"]}' limit 1");
		$db->query();
		
		// redirect - until here
		header("location:{$baseWeb}/redirect.php?edit=1&page=profiles&code=Profile_Modify_Password_Success");
		
	}
}
elseif ($input["act"] == "email") {
	// Assign page name
	$LANG[Profile_Modify_Email] = $LANG[Edit_Profile] . " - " . $LANG[Profile_Modify_Email];
	define("PAGE_TITLE","Profile_Modify_Email");
	
	/**
	 * Modify Email
	 */
	
	// check old email is match
	if ($user->email != $input["current_email"]) {
		$information[] = $LANG["EmailNotMatch"] ;
	}
	
	// check new email format is correct
	if (!ereg("^([A-Za-z0-9\.|-|_]{1,60})([@])([A-Za-z0-9\.|-|_]{1,40})(\.)([A-Za-z]{2,3})$",$input["new_email"])) {
		$information[] = $LANG["InvalidEmailFormat"] ;
	}
	
	// update
	if ($information) {
		// got error, output error
		$information = $LANG["Error_Text"].implode($information,"<br>".$LANG["Error_Text"]);
		
		$template->assign_vars(array(
			"profile_total_files" => $user->files,
                        "profile_total_folders"=> $user->folders,
			"profile_storage_used" => convertsize($user->webspace),
			"profile_deleted_files" => $user->deleted_files,
			"profile_deleted_space" => convertsize($user->deleted_space, 1),
			"hosted_files_stream" => $user->hosted_files_stream,
                        "direct_checked" => $user->dl_direct ? "checked" : "",
		));
		
		$template->assign_var("information2",$information);
		
	}
	else {
		// no error, update
		$db->setQuery("update users set email = '{$input["new_email"]}' where id = '{$user->uid}' and email = '{$user->email}' limit 1");
		$db->query();
		
		// redirect - until here
		header("location:{$baseWeb}/redirect.php?edit=1&page=profiles&code=Profile_Modify_Email_Success");
	}
	
	
	
	
}
elseif ($input["act"] == "carrier") {
        define("PAGE_TITLE","Peering_Carrier");
	if ($input["carrier"] && $input["carrier"] > 0 && $input["carrier"] < 5) {
            $db->setQuery("update users set carrier = {$input["carrier"]} where id = '{$user->uid}' limit 1");
            $db->query();
            
            do_redirect("{$baseWeb}/profiles.php", $LANG[Carrier_Success]);
        }
        else {
            do_redirect("{$baseWeb}/profiles.php", $LANG[Carrier_Fail]);
        }
        
	// set profile details!
	$template->assign_vars(array(
		"profile_total_files" => $user->files,
		"profile_total_folders"=> $user->folders,
		"profile_storage_used" => convertsize($user->webspace),
		"profile_deleted_files" => $user->deleted_files,
		"profile_deleted_space" => convertsize($user->deleted_space, 1),
		"hosted_files_stream" => $user->hosted_files_stream,
		"direct_checked" => $user->dl_direct ? "checked" : "",
	));
}
elseif ($input["act"] == "transfer") {
	// Assign page name
	$LANG[Profile_Modify_Transfer] = $LANG[Edit_Profile] . " - " . $LANG[Profile_Modify_Transfer];
	define("PAGE_TITLE","Profile_Modify_Transfer");
	
	/**
	 * Modify Email
	 */
	
	// check is premium account
	if ($user->package_id != 3) {
		//header("location:{$baseWeb}/redirect.php?edit=1&page=profiles&code=Profile_Not_Premium");
		
		do_redirect("{$baseWeb}/profiles.php", $LANG[Profile_Not_Premium]);
	}
	
	// check the transfer
	if ($input[direct_downloads]) {
		$db->setQuery("update users set dl_direct = 1 where id = '{$user->uid}' and gid = 3 limit 1");
		$db->query();
		
		do_redirect("{$baseWeb}/profiles.php", $LANG[Profile_Transfer_Success1]);
	}
	else {
		$db->setQuery("update users set dl_direct = 0 where id = '{$user->uid}' and gid = 3 limit 1");
		$db->query();
		
		//header("location:{$baseWeb}/redirect.php?edit=1&page=profiles&code=Profile_Transfer_Success2");
		
		do_redirect("{$baseWeb}/profiles.php", $LANG[Profile_Transfer_Success2]);
	}
	
	// set profile details!
	$template->assign_vars(array(
		"profile_total_files" => $user->files,
		"profile_total_folders"=> $user->folders,
		"profile_storage_used" => convertsize($user->webspace),
		"profile_deleted_files" => $user->deleted_files,
		"profile_deleted_space" => convertsize($user->deleted_space, 1),
		"hosted_files_stream" => $user->hosted_files_stream,
		"direct_checked" => $user->dl_direct ? "checked" : "",
	));
}
else {
	// Assign page name
	define("PAGE_TITLE","Edit_Profile");
	
	/**
	 * Get Files, Folders, Storage Details
	 */
	
	if ($user->last_update < ($current_time - 3 * 60))  {
		// Update first
		$db->setQuery("select count(f.id) as nums,sum(f.size) as webspace from files as f where f.uid ='{$user->uid}' and f.deleted=0");
		$db->query();
		$row1=$db->loadRow();
		
		$db->setQuery("select count(f.id) as nums,sum(f.size) as webspace from files as f where f.uid ='{$user->uid}' and f.deleted=1");
		$db->query();
		$row2=$db->loadRow();
		
		$db->setQuery("select sum(fs.dls) as downloads from filestats as fs left join files as f on fs.upload_id = f.upload_id where f.uid ='{$user->uid}'");
		$db->query();
		$row3=$db->loadRow();
		
		if (!$row3["downloads"]) {
			$row3["downloads"] = 0;
		}
		
		$db->setQuery("select count(id) as total_folders from folders where uid='{$user->uid}' and deleted=0");
		$db->query();
		$row4=$db->loadRow();
		
		$db->setQuery("update users set
			webspace='{$row1["webspace"]}',
			files='{$row1["nums"]}',
			folders='{$row4["total_folders"]}',
			deleted_space='{$row2["webspace"]}',
			deleted_files='{$row2["nums"]}',
			hosted_files_stream='{$row3["downloads"]}',
			last_update='{$current_time}'
			where id='{$user->uid}' limit 1");
		$db->query();
		
		
		// then assign to template
		$template->assign_vars(array(
			"profile_total_files" => $row1["nums"],
			"profile_total_folders"=> $row4["total_folders"],
			"profile_storage_used" => convertsize($row1["webspace"]),
			"profile_deleted_files" => $row2["nums"],
			"profile_deleted_space" => convertsize($row2["webspace"], 1),
			"hosted_files_stream" => $row3["downloads"],
			"direct_checked" => $user->dl_direct ? "checked" : "",
		));
	}
	else {
		// then assign to template
		$template->assign_vars(array(
			"profile_total_files" => $user->files,
			"profile_total_folders"=> $user->folders,
			"profile_storage_used" => convertsize($user->webspace),
			"profile_deleted_files" => $user->deleted_files,
			"profile_deleted_space" => convertsize($user->deleted_space, 1),
			"hosted_files_stream" => $user->hosted_files_stream,
			"direct_checked" => $user->dl_direct ? "checked" : "",
		));
	}

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
    		$template->assign_var("account_type","<b class='p'>".$LANG["Premium"]."</b>");
    		break;
    	case 10:
    		$template->assign_var("account_type","<b class='t'>".$LANG["Tester"]."</b>");
    		break;
    	default:
    		$template->assign_var("account_type","<b>".$LANG["Member"]."</b>");
    		break;
    }
   
$template->assign_var("groups_id",$user->package_id);
   
/**
 * Get Account Details
 */
if ($user->package_id == 3) {
	 $expire_date = $user->USER[expire_date] ? date("Y{$LANG[Years]} m{$LANG[Months]} d{$LANG[Days]}",$user->USER[expire_date]) : $LANG["Profile_No_Expire"];
}
else {
	 $expire_date = $LANG["Profile_No_Expire"];
}

$template->assign_vars(array(
	"profile_expire_date" => $expire_date,
	"profile_joined_date" => date("Y{$LANG[Years]} m{$LANG[Months]} d{$LANG[Days]}",$user->reg_date),
	"profile_last_login" => date("y{$LANG[Years]} m{$LANG[Months]} d{$LANG[Days]} H:i:s",$user->last_login),
	"profile_last_ip" => $user->login_ip,
));




// Assign page design
$template->assign_var("profilepage",1);

// bottom revenue menu bar
if($user->revenue_program) {
	$template->assign_var('showRevenue',1);
}
else {
	//$db->setQuery("SELECT SUM(downloads) as total FROM files WHERE uid='".$user->getValue('uid')."'");
	$db->setQuery("select sum(fs.dls) as total from filestats as fs, files as f where fs.upload_id=f.upload_id and f.uid='".$user->uid."'");
	$db->query();
	$total_downloads=$db->loadRow();
	
	if ($total_downloads['total'] > MIN_DL_JOIN_REVENUE) {
		$information=$LANG['CanJoinRevenueNow'];
	}
	else {
		$information=$LANG['NeedMoreDownloadToJoin'];
	}
	$template->assign_var('showRevenue',0);
	$template->assign_var('information',$information);
}



/**
 * Carrier
 */
switch ($user->carrier) {
    case 2: $template->assign_var('carrier_selected2',"checked"); break;
    case 2: $template->assign_var('carrier_selected3',"checked"); break;
    case 2: $template->assign_var('carrier_selected4',"checked"); break;
    default: $template->assign_var('carrier_selected1',"checked");;
}

require_once("header.php");
$template->set_filenames(array("body" => "profiles.html"));
$template->pparse("body");
require_once("footer.php");
?>