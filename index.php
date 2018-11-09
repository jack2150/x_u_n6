<?php

//echo "temporary shutdown! fixing largest ddos attack ever happen in xun6.com!";
//exit;

// redirect to useable domain!!!
/*
if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
	header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
}

if (preg_match("/\bxun6a.us\b/i", $_SERVER["SERVER_NAME"])) {
	header("location: http://www.xun6.net".$_SERVER["REQUEST_URI"]);
}
*/

define("IN_PAGE",'INDEX');
define("PAGE_TITLE",'SITENAME');
include "includes/inc.php";

$baseUrl='index.php?';
if($input[logout]==1)
{
	// clear cookies
    $user->logout();
    
    @$db->close_db();
    
    // cross domain clear cookies
    
	if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
		header("location: http://www.xun6.net/logout.php");
	}
	else {
		header("location: http://www.xun6.com/logout.php");
	}
    
	//header('location:'.$baseWeb.'/redirect.php?error=1&code=LogoutMessage');
}




showUploadForm();


if ($input["folder_id"]) {
	// check folder id exist or not deleted
	$db->setQuery("select name from folders where uid='{$user->uid}' and folder_id='{$input[folder_id]}' and deleted=0 limit 1");
	$db->query();
	if ($db->getNumRows()) {
		// folder exist
		$folder = $db->loadRow();
		
		$template->assign_vars(array(
			"folder_id" => $input[folder_id],
			"folder_name" => strlen(urldecode($folder["name"])) > 32 ? substr(urldecode($folder["name"]),0,32)."..." : urldecode($folder["name"]),
			"insideFolder" => 1,
			));
	}
	else {
		// not exist
		do_redirect("{$baseWeb}/index.php", $LANG["Folder_Not_Exists"]);
	}
}
else {
	$template->assign_var("folder_id", 0);
}





$load_prototype=1;
$load_uploadjs=1;

$indexPage=1;
$template->assign_var("requirecomman",1);
$template->assign_var("indexpage",$indexPage);

require_once("header.php");

$template->set_filenames(array(
	'body' => 'index.body.html')
	);
$template->pparse('body');

include "footer.php";
?>
