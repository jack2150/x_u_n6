<?php
//echo "temporary shutdown! fixing network system error!";
//exit;
error_reporting(0);
// new add timeout
//set_time_limit(10);

$download_pages = 1;

//header("location: /index.php");

/**
 * Set page title 
 * and what page is it!
 */

define("IN_PAGE",'DOWNLOAD');

define("PAGE_TITLE",'SITEDOWNLOAD');

include "includes/inc.php";

/**
 * 7/28/11 xun6.com banned without ticket no
 * 100% got people want me to die!
 */ 

/*
 * Redirect for enable adsense again
 *
 */
 
/*
list($usec, $sec) = explode(' ', microtime());
mt_srand((float) $sec + ((float) $usec * 200));
 $go_to_index = mt_rand(0,2);
 if ($go_to_index == 2) {
       header("location: http://www.xun6.com");
 }
 * 
 */

/*
if ($user->package_id != 3) {
	// redirect to useable domain!!!
	if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
		header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
	}
	
	if (preg_match("/\bxun6a.us\b/i", $_SERVER["SERVER_NAME"])) {
		header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
	}
}
 * 
 */








// Country Ad Customize
// for testing
//$_SERVER['REMOTE_ADDR'] = "114.30.47.10"; // tw
//$_SERVER['REMOTE_ADDR'] = "112.121.176.122"; // hk

/*
if ($user->uid == 1) {
	$_SERVER['REMOTE_ADDR'] = "60.217.229.222"; // cn
}
 */

//$_SERVER['REMOTE_ADDR'] = "60.217.229.222"; // cn
//$_SERVER['REMOTE_ADDR'] = "219.165.254.97"; // jp
//$_SERVER['REMOTE_ADDR'] = "208.64.121.57"; // us
//$_SERVER['REMOTE_ADDR'] = "203.100.62.221"; // sg
//$_SERVER['REMOTE_ADDR'] = "118.100.94.41"; // my

// .com or .net variable
/*
if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
	$template->assign_var("domainname","com");
}
if (preg_match("/\bxun6.net\b/i", $_SERVER["SERVER_NAME"])) {
	$template->assign_var("domainname","net");
}
 * 
 */
	


$db->setQuery("SELECT country FROM ip2nation 
	WHERE ip < INET_ATON('{$_SERVER['REMOTE_ADDR']}') 
	ORDER BY ip DESC LIMIT 0,1");
$db->query();
$temp=$db->loadRow();

$template->assign_var("country",$temp["country"]);

if ($temp["country"] == "cn") {
    $today_date = mktime(0,0,0,date("n"),date("j"),date("Y"));
    $db->setQuery("INSERT INTO popunder3 VALUES ({$today_date},1) 
        ON DUPLICATE KEY UPDATE counter=counter+1;");
    $db->query();
}

if ($temp["country"] == "tw") {
    $today_date = mktime(0,0,0,date("n"),date("j"),date("Y"));
    $db->setQuery("INSERT INTO popunder2 VALUES ({$today_date},1) 
        ON DUPLICATE KEY UPDATE counter=counter+1;");
    $db->query();
}

if ($temp["country"] == "hk") {
    $today_date = mktime(0,0,0,date("n"),date("j"),date("Y"));
    $db->setQuery("INSERT INTO popunder4 VALUES ({$today_date},1) 
        ON DUPLICATE KEY UPDATE counter=counter+1;");
    $db->query();
}

/**
 * Aug 07 2011 - Redirect to enable china user view webpage
 */
/*
if ($temp["country"] == "cn") {
    if ($user->package_id != 3) {
        if (preg_match("/^(www.xun6.com|www.xun6.net)/", strtolower($_SERVER["SERVER_NAME"]))) {
            header("location: http://proxy2.xun6.com".$_SERVER["REQUEST_URI"]);
        }
    }
}
 * 
 */

// for taiwan country popunder
/*
if ($temp["country"] == "tw") {
	$template->assign_var("enable_popunder",1);
	$template->assign_var("pop_section",1);
}
 * 
 */





unset($temp);

// Client IP
$client_ip = $_SERVER['REMOTE_ADDR'];

// Fetch Upload ID
$upload_id = preg_replace('/[^A-Z0-9]/','',strtoupper($input[id]));

//echo $client_ip . " " . $upload_id;

// Check fake page
if (strlen($upload_id) == 10) { $upload_id = substr($upload_id,0,-1); $fake_page = 1; $_SESSION["load"]++; }

// Set MySQL query 
$id_match = $input[type]==2 ? "f.id='$upload_id'" : "f.upload_id='$upload_id'";

// Set download expired time
$download_time = (time() - (1 * 60 * 60));

// first get files data
$db->setQuery("select * from files where upload_id = '{$upload_id}'");
$db->query();

$file_exists = $db->getNumRows();

$filerow=$db->loadRow();

// for premium
// $filerow["validate"] = 0;

// second get filestats data
$db->setQuery("select * from filestats where upload_id = '{$upload_id}'");
$db->query();

$filestats=$db->loadRow();

// put the variable into same array
$filerow["downloads"] = $filestats["dls"];
$filerow["lastdownload"] = $filestats["lastdownload"];
$filerow["bandwidth"] = $filestats["bandwidth"];

unset($filestats);


if(!$file_exists) { header('location:'.$baseWeb.'/redirect.php?error=1&code=DL_FileNotFound'); exit; }

/**
 * Dec 12 2008: Update, for showing Banner at file not found pages
 */
// if files is deleted, then point to to empty donwload page and show information
if($filerow["deleted"] == 1) { $errorcode = $LANG["DL_FileNotFound"];    }

// if files is copyright and deleted, show information too
if($filerow["deleted"] == 2) { 
	$errorcode = $LANG["DL_Copyrighted"]; 
	if (strtolower(basename($_SERVER['REQUEST_URI'])) != "copyright-infringement.html") {
		header("location: {$baseWeb}/file/".strtolower($upload_id)."/copyright-infringement.html");
		exit; }
}

// if files contain virus and deleted, show information too
if($filerow["deleted"] == 3) { $errorcode = $LANG["DL_VirusFound"]; }


// July 03 2009: Rework Lastdownload
if (!$filerow['lastdownload']) { $filerow['lastdownload'] = $LANG['NoDL']; }
else { $filerow['lastdownload'] = $LANG['LastDownload']." ".date("F j, Y", $filerow['lastdownload']);}

// July 31 2011: Bandwidth Usage
$filerow["bandwidth"] = $filerow["bandwidth"] ? convertsize($filerow["bandwidth"]) : 0;


// July 03 2009: Rework SQL Query and Check Download is Empty
$filerow[downloads] = $filerow[downloads]?$filerow[downloads]:0;

// change downloads
$filerow['downloads'] = is_numeric($filerow['downloads']) ? $filerow['downloads'] : 0;


/**
 * [CHANGELOG] SEPTEMBER 19 2008 - Move folder URL to folder/filename.ext URL
 */


if ($filerow["deleted"] != 2) {
    $current_url = strtolower(basename($_SERVER['REQUEST_URI']));
    $id_url = strtolower(basename($filerow['id'])).".html";
    if ($current_url != $id_url) {
        //header("location: {$baseWeb}/file/".strtolower($upload_id)."/".urlencode(base64_decode(strip_tags($filerow['name']))).".html");
        header("location: {$baseWeb}/file/".strtolower($upload_id)."/".$id_url);
        exit;
    }
    unset($id_url, $current_url);
}



/**
 * Change Ready Name
 */
$current_hour = date("G");
$template->assign_var("current_hour",$current_hour);

/**
 * Validated Files FakePage Section
 */
$template->assign_var("show_download_button",1);

/*
if ($user->package_id >= 3) {
	$template->assign_var("show_download_button",1); 
}
else {
	// July 31 2011 - fake page function
	// generate number - 1 morning , 2 night
	$max_value1 = 65;	
	$max_value2 = 60;
	$max_reload = 2;

	// check is fake
	if ($_SESSION["fakepage"]) {
		// is fake already - add count
		$_SESSION["reloadtime"]++;
		//echo "IS FAKE! - {$_SESSION["reloadtime"]} , ";
	}
	else {
		// not fake - generate new num
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 200));
		
		// hour different
		if ($current_hour > 7 && $current_hour < 20) {
			$rand_num = mt_rand(0,$max_value1);
			$match_value = floor($max_value1/2);
		}
		else {
			$rand_num = mt_rand(0,$max_value2);
			$match_value = floor($max_value1/2);
		}
		
		// check is match - become fake, remain normal
		if ($rand_num == $match_value) {
			$_SESSION["fakepage"] = 1;
			$_SESSION["reloadtime"] = 1;	
			//echo "MATCH! - BECOME FAKE , ";
		}
		// else { echo "{$rand_num} /= {$match_value} ,"; }
	}
	
	// check count - return normal, keep fake
	if ($_SESSION["reloadtime"]) {
		if ($_SESSION["reloadtime"] >= $max_reload) {
			$template->assign_var("show_download_button",1); 
			// unset
			unset($_SESSION["fakepage"]);
			unset($_SESSION["reloadtime"]);
			//echo "RETURN NORMAL , ";
		}
		else {
			$template->assign_var("show_download_button",0); 
			//echo "KEEP FAKE {$_SESSION["reloadtime"]} , ";
		}
	}
}
 * 
 */



/**
 * Assign all variable from file and server
 */
$filerow[http]   = $user->servers[$filerow[server_id]][http];
$filerow[domain] = $user->servers[$filerow[server_id]][domain];
$filerow[t_diff] = $user->servers[$filerow[server_id]][time_diff];
$upload_id   = $filerow[upload_id];
$file_id     = $filerow[id];
$type        = substr(strtolower(strrchr($filerow[name],'.')),1);

/**
 * Generate fake page keywords
 */
//$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name]))," - ".$filerow[descr]); 

$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],$upload_id,"(".convertsize($filerow[size]).")"); 

/*
$keywords_number = $file_id % 9;
if ($filerow[descr]) {
	if (strlen($filerow[descr]) > 60) {
		$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name])),"(".convertsize($filerow[size]).")"." - ".substr($filerow[descr],0,60))."..."; 
	}
	else {
		$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name])),"(".convertsize($filerow[size]).")"." - ".$filerow[descr]); 
	}
	
}
else {
	$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name])),"(".convertsize($filerow[size]).")"." - ".$LANG[Keywords][$keywords_number]);
}
 * 
 */

$template->assign_var("keyword",$LANG[Keywords][$keywords_number]);


/*
if ($fake_page) {
	$keywords_number = $file_id % 9;
	if ($keyword_title) {
		$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],""," - ".$LANG[Keywords][$keywords_number]);
	}
	else {
		if ($filerow[descr]) {
			$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name])),"(".convertsize($filerow[size]).")"." - ".$filerow[descr]); 
		}
		else {
			$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],"","(".convertsize($filerow[size]).")"." - ".$LANG[Keywords][$keywords_number]);
		}
	}
	$template->assign_var("keyword",$LANG[Keywords][$keywords_number]);
	$filerow[descr] = $LANG[Keywords][$keywords_number];
}
else { 
	if ($filerow[descr]) {
		$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name])),"(".convertsize($filerow[size]).")"." - ".$filerow[descr]); 
	}
	else {
		$keywords_number = $file_id % 9;
		$LANG[SITEDOWNLOAD] = sprintf($LANG[SITEDOWNLOAD],base64_decode(strip_tags($filerow[name])),"(".convertsize($filerow[size]).")"." - ".$LANG[Keywords][$keywords_number]); 
		$template->assign_var("keyword",$LANG[Keywords][$keywords_number]);
	}
}
*/


// original: get download url
$urls=getDownloadUrl(array('id'=>$filerow[id],'name'=>base64_decode(strip_tags($filerow[name])),'upload_id'=>$filerow[upload_id],'delete_id'=>$filerow[delete_id]));

extract($urls);




// Set validate
if ($filerow[validate] == 1) {
	$template->assign_var("validate", '1');
}
else {
	$template->assign_var("validate", '0');
}

// Transfer base64 file name to readable name
$filerow[name]=base64_decode(strip_tags($filerow[name]));

/**
 * Switch Case for File Type Related Download
 */
// July 31 2011
$unknown_ext = 0;
$file_ext = strtolower(substr(strrchr($filerow[name],'.'),1));
switch ($file_ext) {
	case "rar": case "zip": case "7z": case "cab": 
	case "arj": case "lzh": case "ace": case "tar": 
	case "gzip": case "uue": case "bz2":
		$ext_icon = "compress.gif";
		$file_type = $LANG["File_Type_COMPRESS"];
		$open_ext_text = $LANG["File_Type_COMPRESS_Text"];
		$open_ext_link = $LANG["File_Type_COMPRESS_Link"];
	    break;
	case "wmv":	case "vcd":	case "mpg":
	case "mp4":	case "rmvb": case "mov":
	case "3gp": case "flv":	case "asp":
	case "rm": case "avi": case "mkv":
		$ext_icon = "video.gif";
		$file_type = $LANG["File_Type_Video"];
		$open_ext_text = $LANG["File_Type_Video_Text"];
		$open_ext_link = $LANG["File_Type_Video_Link"];
	    break;
	case "wma": case "mp1":	case "mp2":
	case "mp3": case "ogg":	case "asf":
	case "ape": case "wav":	case "snd":
	case "ra":
		$ext_icon = "music.gif";
		$file_type = $LANG["File_Type_Music"];
		$open_ext_text = $LANG["File_Type_Music_Text"];
		$open_ext_link = $LANG["File_Type_Music_Link"];
    	break;
	case "bt": case "torrent": case "bittorrent":
		$ext_icon = "torrent.gif";
		$file_type = $LANG["File_Type_Torrent"];
		$open_ext_text = $LANG["File_Type_Torrent_Text"];
		$open_ext_link = $LANG["File_Type_Torrent_Link"];
    	break;
	case "jpg":	case "jpeg": case "gif":
	case "png":	case "bmp":	case "tif":
	case "tiff": case "crw": case "nef":
	case "raf":	case "cr2":	case "orf":
	case "erf":
		$ext_icon = "image.gif";
		$file_type = $LANG["File_Type_Image"];
		$open_ext_text = $LANG["File_Type_Image_Text"];
		$open_ext_link = $LANG["File_Type_Image_Link"];
    	break;
	case "pdf":
		$ext_icon = "pdf.gif";
		$file_type = $LANG["File_Type_PDF"];
		$open_ext_text = $LANG["File_Type_PDF_Text"];
		$open_ext_link = $LANG["File_Type_PDF_Link"];
		break;

	// office
	case "doc": case "docx": case "xls": case "xlsx": case "ppt": case "pptx": case "pub": case "xsf":
		$ext_icon = "office.gif";
		$file_type = $LANG["File_Type_Office"];
		$open_ext_text = $LANG["File_Type_Office_Text"];
		$open_ext_link = $LANG["File_Type_Office_Link"];
		break;
	// text
	case "txt": case "rtf": case "pdf": case "html": 
	case "htm": case "inc": case "ini": case "xml":
		$ext_icon = "text.gif";
		$file_type = $LANG["File_Type_Text"];
		$open_ext_text = $LANG["File_Type_Text_Text"];
		$open_ext_link = $LANG["File_Type_Text_Link"];
		break;
	// exe
	case "exe": case "msi": case "com": case "bat": case "app": case "cmd":
		$ext_icon = "program.gif";
		$file_type = $LANG["File_Type_Program"];
		$open_ext_text = $LANG["File_Type_Program_Text"];
		$open_ext_link = "";
	break;
	default:
		$ext_icon = "unknown.gif";
		$unknown_ext = 1;
		$file_type = $LANG["File_Type_Others"];
		$open_ext_text = $LANG["File_Type_Others_Text"];
		$open_ext_link = $LANG["File_Type_Others_Link"].$file_ext;
	break;
}
// July 31 2011
$template->assign_vars(array("file_ext" =>strtoupper($file_ext), "file_type" => $file_type, "ext_icon" => $ext_icon,
	"open_ext_text" => $open_ext_text, "open_ext_link" => $open_ext_link, "unknown_ext" => $unknown_ext) );


//$template->assign_var("filetype",$LANG[$filetips]);

// Shorten file name
$original_name = $filerow[name];
if (strlen($filerow[name]) >= 30) { $filerow[name]=substr($filerow[name], 0, 30)."..."; }

// August 2 2011 - Random News replace empty descr
if (!strlen($filerow[descr])) {
	// select news from today date, if not exist use 2011 same month and day
	//echo "SELECT news FROM randnews WHERE time='".date("Ymd",$filerow["time"])."' OR time='2011".date("md",$filerow["time"])."' LIMIT 1";
	
	// only less than 365 rows - if upload date not exists, use today
	$db->setQuery("SELECT news FROM randnews WHERE time='".date("Ymd",$filerow["time"])."' OR time='".date("Ymd",time())."' LIMIT 1");	
	// more than 365 rows - if upload date not exist, use last year today
	//$db->setQuery("SELECT news FROM randnews WHERE time='".date("Ymd",$filerow["time"])."' OR time='2011".date("md",$filerow["time"])."' LIMIT 1");
	$db->query();
	
	$randnew=$db->loadRow();
	//$filerow[descr]=$randnew["news"];
	
	$template->assign_var("randnews",$randnew["news"]);
	unset($randnew);
}


// set filerow to template
$template->assign_vars($filerow);

// apply the download options by user group
// method 1: decide the downlaod options by the file
$dl_group=$filerow[uid]==0?$user->guest_group:$user->groups[$filerow[gid]];

// method 2:decide the downlaod options by the user who try to download
$dl_group=$user->uid==0?$user->guest_group:$user->groups[$user->package_id];

// check download status
$can_download = 1;

// the request is from POST or download direct is allowed although the request is through GET
// define('CHECK_DL', IS_POST||$dl_group[dl_direct]);

/**
 * Download Proccess: 1. check the captchacode match
 */
// Only Direct Download is Skip Captcha & Password
if ($dl_group[dl_direct] || $user->package_id == 3) {
	$can_download = 1;
}
else {
	if ($input[captchacode] && $input[captchakey]) {
		//$raw_authkey = $_SESSION[authkey];
		$raw_authkey = 0;
		// 1.1: Check in database for existing record
		$time_expired = time()-(12*60);

		$db->setQuery("select ip, ckey from captcha where time > '$time_expired' and num = '{$input[captchacode]}' and ckey = '{$input[captchakey]}' limit 1");
		$db->query();
		if ($db->getNumRows()) { 
			$temp_captcha = $db->loadRow();
			$client_ip = $temp_captcha[ip];
			$captchaKey = $temp_captcha[ip];
			
		 	$raw_authkey = $input[captchacode];
		}
		else {
		   	$can_download = 0;
			//if the check is from post, show error
			$can_download = 0;
			if(IS_POST) $errorcode = $LANG["DL_CaptchaInvalid"]."<br/>".$LANG["PREMIUM_UNLIMIT"];
		}
		$template->assign_vars(array(
			'captcha_code' => $input[captchacode],
			'captcha_key' => $input[captchakey],
		));
		unset($time_expired);
	}
	else {
		$can_download = 0;
	}


	/**
	 * Downlaod Proccess: 2. check the password match
	 */
	if($can_download&&$dl_group[dl_password]&&$filerow[password]&&$input[downloadpw]!=$filerow[password]) {
	    //if direct download is allowed,ignore the check
	    // if(!$dl_group[dl_direct]) $can_download = 0;
		
	    //if the check is from post, show error
	    $can_download = 0;
	    if(IS_POST) $errorcode = $LANG["DL_PasswordErr"]."<br/>".$LANG["PREMIUM_UNLIMIT"];
	}
}




/**
 * Can Download Section
 */
$template->assign_var("server_online",1); 
if($can_download==1)
{
    /**
     * Generate Random Number to Store in Session
     */
    if (!isset($_SESSION[rand_num])) {
    	$_SESSION[rand_num] = mt_rand(11,99);
    }

    /**
     * Related related field and Access Key
     */
    $remotetime = time() + $dl_group[dl_timeout]; // remote expired time
    $access_key = substr(md5(trim($upload_id.$raw_authkey.$_SESSION[rand_num].$client_ip)),0,12);
    
    if ($dl_group[dl_direct]) {
    	$access_key = substr(md5(trim($upload_id.$raw_authkey.$user->uid.$client_ip)),0,12);
    }
    
	/**
	* Connect to external file server database
	*/
	$db->setQuery("select http,domain,offline,cdn from server where server_id='{$filerow['server_id']}' limit 1");
	$db->query();
	if ($db->getNumRows()) {
	    $server_sql = $db->loadRow();

	    // [Update July 15 2009] OLD DB Connect - Use Response.php
		if ($server_sql[offline]) {
			$errorcode = $errorcode ? $errorcode : $LANG["DL_FileServerOffline"];
			$can_download = 0;
			$template->assign_var("server_online",0); 
		}
		else {
			$template->assign_var("server_online",1); 
			
			// check is premium files
			$db->setQuery("select id from users where id='{$filerow['uid']}' and (gid=3 or gid=4) limit 1");
			$db->query();
			if ($db->getNumRows()) {
				// is premium user files
				$pfiles = 1;
			}
			else {
				// not premium user files
				$pfiles = 0;
			}
			
	   		// get revenue program
			$db->setQuery("select revenue_program from users where id='{$filerow['uid']}' limit 1");
			$db->query();
			if ($db->getNumRows() > 0 && $user->revenue_program == 0) {				
				$temp_user = $db->loadRow();
				$revenue_program = 1;
			}
			else {
				$revenue_program = 0;
			}
			
			// Setup dl options parameter
			$p = "{$dl_group[dl_direct]}-{$dl_group[dl_resume]}-{$dl_group[dl_speed]}-{$dl_group[dl_threads]}-{$dl_group[dl_maxsbyip]}"
				. "-{$dl_group[dl_timeout]}-{$user->setting[allow_proxy]}-{$user->setting[download_prefix]}-{$dl_group[dl_sizebyhour]}";
						
			// GET parameter setup
			$parament = "id=".$upload_id;
			$parament .= "&uid=".$user->uid;
			$parament .= "&time=".$remotetime;
			$parament .= "&dl=".$p;
			$parament .= "&size=".$filerow[size];
			$parament .= "&auth=".$raw_authkey;
			$parament .= "&accs=".$access_key;
			$parament .= "&ip=".$client_ip;
			$parament .= "&r=".$revenue_program;
			$parament .= "&s=".$filerow[domain];
			// new premium user files
			$parament .= "&pfiles=".$pfiles;
			// new added
			$parament .= "&file=".$filerow[file];
			$parament .= "&ftime=".$filerow[time];
			$parament .= "&name=".urlencode($original_name);
			
			
			// echo "http://".$filerow[domain]."/response.php?"
			// [FEB192011] Set timeout for response connections!
			$ctx = stream_context_create(array('http'=>array('timeout'=>3)));
			$response_content = @file_get_contents("{$server_sql["http"]}{$server_sql['cdn']}/response.php?{$parament}", 0, $ctx);
			//$response_content = @file_get_contents("{$server_sql["http"]}{$server_sql['domain']}/response.php?{$parament}", 0, $ctx);
			/*
			if ($enableCDN) {
				$response_content = @file_get_contents("{$server_sql["http"]}{$server_sql['cdn']}/response.php?{$parament}", 0, $ctx);
			}
			else {
				$response_content = @file_get_contents("{$server_sql["http"]}{$server_sql['domain']}/response.php?{$parament}", 0, $ctx);
			}
			*/
		
			if ($response_content) {
				if (intval($response_content)) {
					//$downloadfileurl = $baseWeb.'/redirect.php?error=1&code=DL_FileServerFail&fid='.$upload_id;

					switch ($response_content) {
						case "1": $errorcode = $LANG["DL_IP_MAX_DONE"].$LANG["PREMIUM_UNLIMIT"]; break;
						case "2": $errorcode = $LANG["DL_IP_MAX_OPEN"].$LANG["PREMIUM_UNLIMIT"]; break;
						case "3": $errorcode = $LANG["DL_IP_MAX_BW"].$LANG["PREMIUM_UNLIMIT"]; break;
						default: $errorcode = $LANG["DL_FileServerFail"]."<br/>".$LANG["DL_CONTACT_SUPPORT"];
					}
					$can_download = 0;
				}
				else {
					$downloadfileurl = $response_content;
				}
			}
			else {
				//$downloadfileurl = $baseWeb.'/redirect.php?error=1&code=DL_FileServerFail&fid='.$upload_id;
				$errorcode = $errorcode ? $errorcode : $LANG["DL_FileServerFail"]."<br/>".$LANG["DL_CONTACT_SUPPORT"];
				$can_download = 0;
			}
		}
	}
	else {
	  	// header('location:'.$baseWeb.'/redirect.php?error=1&code=DL_FileServerFail&fid='.$upload_id);
	  	$errorcode = $errorcode ? $errorcode : $LANG["DL_FileServerFail"]."<br/>".$LANG["DL_CONTACT_SUPPORT"];
	  	$can_download = 0;
	}
}

/**
 * If set direct download, then just goto the url without show page
 */
//$dl_group[dl_direct] = 1; // testing mode
if($dl_group[dl_direct] == 1) { 
	if ($can_download) {
		if ($filerow["deleted"]) {
			$can_download = 0;
			$template->assign_var("file_deleted",1); 
		}
		else {
			if ($user->dl_direct) {
				header("location: ".$downloadfileurl);
			}
			else {
				$template->assign_var("p_dl_url",$downloadfileurl); 
			}
		}
	}
}

/**
 * Assign related variable to interface
 */
$dl_part = parse_url($downloadfileurl);

$template->assign_vars(array(
	"filesize"=>convertsize($filerow[size]),
	"upload_id"=>$upload_id,
	"downloadurl"=>$downloadurl,
	"downloadfileurl"=>$downloadfileurl,
	"dl_protocol"=>$dl_part[scheme],
	"dl_domain"=>$dl_part[host],
	"dl_dirname"=>dirname($dl_part[path]),
	"dl_basename"=>urlencode($original_name),
	"deleteurl"=>$deleteurl,
	"waittime"=>$user->dl_waittime,
	"access_key"=>$access_key,
	"before_can"=>$can_download ? 0 : 1,
	"errorcode"=>isset($errorcode)?$errorcode:"",
	"file_deleted"=>$filerow["deleted"],
	"swap_position"=>mt_rand(0,30)==1?1:0,
	"user_group"=>$user->package_id,
));

/**
 * set different wait time by file size
 */
if ($user->package_id == 3) {
	$sizewaittime = 0;
}
else {
	$waittime_options = array('0KB'=>12,'1MB'=>18,'5MB'=>22,'10MB'=>26,'20MB'=>30,'50MB'=>42,'75MB'=>60,'100MB'=>66,'200MB'=>72);
	foreach($waittime_options as $sizestr => $waittime) {
	    eval("\$filesize = (str_replace(array('KB','MB','GB'),array('*1024','*1024*1024','*1024*1024*1024'),\$sizestr));");
		eval("\$filesize = $filesize;");
	    if($filerow[size]>$filesize) $sizewaittime = (strlen($waittime>=2)&&$waittime<1 ? intval($user->dl_waittime*$waittime):$waittime);
	}
	
	if ($_POST["countdown"] && $_POST["nextrandom"]) {
		$sizewaittime = $_POST["countdown"];
		$nextrandom = $_POST["countdown"] - mt_rand(5,8);
		$template->assign_vars(array(
			"countdown" => $_POST["countdown"],
			"nextrandom" => $nextrandom < 0 ? 0 : $nextrandom,
		));
	}
	else {
		$nextrandom = $sizewaittime - mt_rand(5,8);
		$template->assign_vars(array(
			"countdown" => $sizewaittime,
			"nextrandom" => $nextrandom < 0 ? 0 : $nextrandom,
		));
	}

	unset($nextrandom);
}


if(isset($sizewaittime)) $template->assign_var('waittime',$sizewaittime);

/**
 * After check all condition, it show download button or captcha/password
 */

if($can_download==1) { 
	$template->assign_block_vars('download_section',array()); 
}

else { 
	$template->assign_var('captcha_code', mt_rand(0,99));
	$template->assign_var('captcha_enabled',$dl_group[dl_captcha]);
    $template->assign_var('downloadpw_needed',$dl_group[dl_password]&&strlen($filerow[password]));
}

/**
 * Set the interface variable
 */
$downloadPage=1;
$template->assign_var("downloadpage",$downloadPage);
$template->assign_vars(array(
	"captchaURL" => $captchaURL,
	"captchaKey" => substr(md5($upload_id.microtime()),0,15)
	));

/**
 * Start templates
 */

require_once("header.php");

// fake page section: decide the template based the server and package
//$templatefile=array("download.html");

$templatefile=array("download_3column.html");

//if ($fake_page) { $templatefile=array('downloadz.html'); } else { $templatefile=array('download.html'); }
foreach($templatefile as $file)
{
    if(file_exists('skin/'.$user->setting['skin_dir'].'/'.$file)) {$showtemplate=$file;break;}
}

$template->set_filenames(array(
	'body' => $showtemplate)
	);

$template->pparse('body');

include "footer.php";
?>