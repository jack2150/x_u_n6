<?php
//echo "temporary shutdown! fixing largest ddos attack ever happen in xun6.com!";
//exit;
error_reporting(0);

// check key correct
function c_key() {
	include("config.php");
	
	$client_ip = $_SERVER['REMOTE_ADDR'];
	$current_time = time();
	$check_time = time() - (2 * 60); // within 2 mins
	$access_key = $_GET["k"];
	
	$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
		or die ('Error connecting to mysql');
		
	$result = mysql_query("select * from filters where ip='{$client_ip}' and t>=$check_time and t<$current_time k='{$access_key}' and c<10 limit 1");
	

	if (mysql_num_rows($result)) {
		// exist, update
		mysql_query("update filters set c=c+1, t={$check_time} where ip='{$client_ip}' limit 1");
	}
	else {
		// not exist
		exit;
	}
	mysql_close($conn);
}


function s_dl() {
$download_pages = 1;

//header("location: /index.php");

/**
 * Set page title 
 * and what page is it!
 */

define("IN_PAGE",'DOWNLOAD');

define("PAGE_TITLE",'SITEDOWNLOAD');

include "includes/inc.php";

// Client IP
$client_ip = $_SERVER['REMOTE_ADDR'];


if ($user->package_id != 3) {
	// redirect to useable domain!!!
	if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
		header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
	}
}


// Country Ad Customize
// for testing
//$_SERVER['REMOTE_ADDR'] = "114.30.47.10"; // tw
//$_SERVER['REMOTE_ADDR'] = "112.121.176.122"; // hk
//$_SERVER['REMOTE_ADDR'] = "60.217.229.222"; // cn
//$_SERVER['REMOTE_ADDR'] = "219.165.254.97"; // jp

$db->setQuery("SELECT country FROM ip2nation 
	WHERE ip < INET_ATON('{$_SERVER['REMOTE_ADDR']}') 
	ORDER BY ip DESC LIMIT 0,1");
$db->query();
$temp=$db->loadRow();

$template->assign_var("country",$temp["country"]);

// for taiwan country popunder
if ($temp["country"] == "tw") {
	$current_time = time();
	//$today_date = mktime(12,0,0,date("n"),date("j"),date("Y"));
	$today_date = mktime(12,0,0,date("n"),date("j"),date("Y"));
	$tomorrow_date = mktime(0,0,0,date("n"),date("j")+1,date("Y"));
	
	// check counter in mysqldb
	$db->setQuery("select counter from popunder where time = {$today_date} limit 1");
	$db->query();
	
	if ($db->getNumRows()) {
		// if have record then check counter
		$popunder = $db->loadRow();
		
		if ($popunder["counter"] <= 115000) {
			// current time running
			if ($current_time >= $today_date && $current_time <= $tomorrow_date) {
				$template->assign_var("enable_popunder",1);
				$template->assign_var("pop_section","1"); 
			}
		}
		else {
			$template->assign_var("enable_popunder",0);
			$template->assign_var("pop_section","0");
		}
	}
	else {
		// if without record then run also
		if ($current_time >= $today_date && $current_time <= $tomorrow_date) {
			$template->assign_var("enable_popunder",1);
			$template->assign_var("pop_section","1");
		}
	}
}

unset($temp);



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

unset($filestats);


if(!$file_exists) { header('location:'.$baseWeb.'/redirect.php?error=1&code=DL_FileNotFound'); exit; }

/**
 * Dec 12 2008: Update, for showing Banner at file not found pages
 */
// if files is deleted, then point to to empty donwload page and show information
if($filerow["deleted"] == 1) { $errorcode = $LANG[DL_FileNotFound]; $template->assign_var("file_deleted",1); $filerow["validate"]=0; }

// if files is copyright and deleted, show information too
if($filerow["deleted"] == 2) { 
	$errorcode = $LANG[DL_Copyrighted]; 
	$template->assign_var("file_deleted",1); 
	$filerow["validate"]=0;
	
	if (strtolower(basename($_SERVER['REQUEST_URI'])) != "copyright-infringement.html") {
		header("location: {$baseWeb}/files/".strtolower($upload_id)."/copyright-infringement.html");
		exit;
	}
}

// if files contain virus and deleted, show information too
if($filerow["deleted"] == 3) { $errorcode = $LANG[DL_VirusFound]; $template->assign_var("file_deleted",1); $filerow["validate"]=0; }

// July 03 2009: Rework Lastdownload
if (!$filerow['lastdownload']) { $filerow['lastdownload'] = $LANG['NoDL']; }
else { $filerow['lastdownload'] = $LANG['LastDownload']." ".date("F j, Y", $filerow['lastdownload']);}

// July 03 2009: Rework SQL Query and Check Download is Empty
$filerow[downloads] = $filerow[downloads]?$filerow[downloads]:0;

// change downloads
$filerow['downloads'] = is_numeric($filerow['downloads']) ? $filerow['downloads'] : 0;


/**
 * [CHANGELOG] SEPTEMBER 19 2008 - Move folder URL to folder/filename.ext URL
 */
if (strtoupper(basename($_SERVER['REQUEST_URI'])) == strtoupper($upload_id)) {
	if ($filerow["deleted"] == 2) {
		header("location: {$baseWeb}/files/".strtolower($upload_id)."/copyright-infringement.html");
		exit;
	}
	else {
		header("location: {$baseWeb}/files/".strtolower($upload_id)."/".urlencode(base64_decode(strip_tags($filerow['name']))).".html");
		exit;
	}
}


/**
 * Validated Files FakePage Section
 */
$template->assign_var("show_download_button",1);

if ($user->package_id == 3) {
	$template->assign_var("show_download_button",1); 
}
else {
	//$show_button = 1;
	$template->assign_var("show_download_button",1); 
	
	list($usec, $sec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));
	
	// validate file chance
	$show_button = 1;
	// 0 1 2 3 4 5
	if (isset($_SESSION["reloadtime"]) && $_SESSION["reloadtime"] < 3) {
		$_SESSION["reloadtime"]++;
		$show_button = 0;
	}
	else {
		unset($_SESSION["reloadtime"]);
		unset($_SESSION["fakepage"]);
		$show_button = 1;
	}
	
	if (isset($_SESSION["fakepage"]) && $_SESSION["fakepage"] == 1) {
		$show_button = 0;
	}
	else {
		unset($_SESSION["reloadtime"]);
		unset($_SESSION["fakepage"]);
		$show_button = 1;
	}
	
	if ($show_button) {
		$random_number = mt_rand(0,19);
		
		if ($random_number == 9) { 
			$_SESSION["reloadtime"] = 0;
			$_SESSION["fakepage"] = 1;
			$show_button = 0;
		}
		else { 
			unset($_SESSION["reloadtime"]);
			unset($_SESSION["fakepage"]);
			$show_button = 1;
		}	
	}
		if ($show_button) {
		$template->assign_var("show_download_button",1); 
	}
	else {
		$template->assign_var("show_download_button",0); 
	}
}



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

$template->assign_var("keyword",$LANG["SiteKeyword"]);
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


// fake-page: make longer upload id
if ($fake_page) { $filerow[upload_id] = $filerow[upload_id].substr($filerow[upload_id],0,1); }

// original: get download url
$urls=getDownloadUrl(array('id'=>$filerow[id],'name'=>base64_decode(strip_tags($filerow[name])),'upload_id'=>$filerow[upload_id],'delete_id'=>$filerow[delete_id]));

extract($urls);


// fake-page: change back to short upload id
if ($fake_page) { $filerow[upload_id] = $upload_id;}

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
switch (strtolower(substr(strrchr($filerow[name],'.'),1))) {
	case "rar": case "zip": case "7z": case "cab": 
	case "arj": case "lzh": case "ace": case "tar": 
	case "gzip": case "uue": case "bz2":
	    $filetips = "ZipFileTips";
	    break;
	case "wmv":	case "vcd":	case "mpg":
	case "mp4":	case "rmvb": case "mov":
	case "3gp": case "flv":	case "asp":
	case "rm":
	    $filetips = "MovieFileTips";
	    break;
	case "wma": case "mp1":	case "mp2":
	case "mp3": case "ogg":	case "asf":
	case "ape": case "wav":	case "snd":
	case "ra":
		$filetips = "AudioFileTips";
    	break;
	case "bt": case "torrent": case "bittorrent":
		$filetips = "TorrentFileTips";
    	break;
	case "jpg":	case "jpeg": case "gif":
	case "png":	case "bmp":	case "tif":
	case "tiff": case "crw": case "nef":
	case "raf":	case "cr2":	case "orf":
	case "erf":
		$filetips = "ImageFileTipe";
		$extratips = "pisaca_pack";
    	break;
	case "htm": case "html": case "css":
	case "js": case "xml": case "dhtml":
	case "xhtml": case "rss":
		$filetips = "WebFileTipe";
		$extratips = "google_firefox";
    	break;
	case "pdf":
		$filetips = "PdfFileTipe";
		break;
	case "swf":
		$filetips = "SwfFileTipe";
		break;
}

$template->assign_var("filetype",$LANG[$filetips]);

if ($extratips) { $template->assign_var($extratips,1); }

// Shorten file name
$original_name = $filerow[name];
if (strlen($filerow[name]) >= 40) { $filerow[name]=substr($filerow[name], 0, 40)." ..."; }

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
// Only Direct Download is Skip Captcha
if ($dl_group[dl_direct]) {
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
			if(IS_POST) $errorcode = 'DL_CaptchaInvalid';
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
}

/**
 * Downlaod Proccess: 2. check the password match
 */
if($can_download&&$dl_group[dl_password]&&$filerow[password]&&$input[downloadpw]!=$filerow[password]) {
    //if direct download is allowed,ignore the check
    // if(!$dl_group[dl_direct]) $can_download = 0;
	
    //if the check is from post, show error
    $can_download = 0;
    if(IS_POST) $errorcode = 'DL_PasswordErr';
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
	$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db,offline from server where server_id='{$filerow['server_id']}' limit 1");
	$db->query();
	if ($db->getNumRows()) {
	    $server_sql = $db->loadRow();

	    // [Update July 15 2009] OLD DB Connect - Use Response.php
		if ($server_sql[offline]) {
			$errorcode = "DL_FileServerOffline";
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
			
			$response_content = @file_get_contents("http://".$filerow[domain]."/response.php?".$parament);
		
			if ($response_content) {
				if (intval($response_content)) {
					//$downloadfileurl = $baseWeb.'/redirect.php?error=1&code=DL_FileServerFail&fid='.$upload_id;

					switch ($response_content) {
						case "1": $errorcode = "DL_IP_MAX_DONE"; break;
						case "2": $errorcode = "DL_IP_MAX_OPEN"; break;
						case "3": $errorcode = "DL_IP_MAX_BW"; break;
						default: $errorcode = "DL_FileServerFail";
					}
					$can_download = 0;
				}
				else {
					$downloadfileurl = $response_content;
				}
			}
			else {
				//$downloadfileurl = $baseWeb.'/redirect.php?error=1&code=DL_FileServerFail&fid='.$upload_id;
				$errorcode = "DL_FileServerFail";
				$can_download = 0;
			}
		}
	}
	else {
	  	// header('location:'.$baseWeb.'/redirect.php?error=1&code=DL_FileServerFail&fid='.$upload_id);
	  	$errorcode = "DL_FileServerFail";
	  	$can_download = 0;
	}
}

/**
 * If set direct download, then just goto the url without show page
 */
//$dl_group[dl_direct] = 1; // testing mode
if($dl_group[dl_direct] == 1) { 
	if ($can_download) {
		if ($user->dl_direct) {
			header("location: ".$downloadfileurl);
		}
		else {
			$template->assign_var("p_dl_url",$downloadfileurl); 
		}
	}
}

/**
 * Set no error
 */
if($can_download) { 
	$errorcode = ""; 
}

/**
 * Assign related variable to interface
 */
$dl_part = parse_url($downloadfileurl);

$template->assign_vars(array(
	'filesize'=>convertsize($filerow[size]),
	'upload_id'=>$upload_id,
	'downloadurl'=>$downloadurl,
	'downloadfileurl'=>$downloadfileurl,
	'dl_protocol'=>$dl_part[scheme],
	'dl_domain'=>$dl_part[host],
	'dl_dirname'=>dirname($dl_part[path]),
	'dl_basename'=>urlencode($original_name),
	'deleteurl'=>$deleteurl,
	'waittime'=>$user->dl_waittime,
	'access_key'=>$access_key,
	'before_can'=>$can_download ? 0 : 1,
	'errorcode'=>isset($LANG[$errorcode])?$LANG[$errorcode]."<br>".$LANG[PLEASE_BUY_PREMIUM]:"",
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
$templatefile=array('download.html');
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
}

c_key();
s_dl();
?>