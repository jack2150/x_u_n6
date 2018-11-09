<?php
//header("location: index.php");

define("IN_PAGE",'BATCH');

include "includes/inc.php";
include "includes/folder.inc.php";

if($user->logined==0) exit;
if ($user->account_status == -1) exit;


$max_directory_level = 3;
$max_total_directory = 200;

if ($input["act"] == "folder_list" && $input["id"]) {
	// get a list of folder
	$skin_dir = $baseWeb.'/skin/'.$user->setting[skin_dir];
	
	/**
	 * Create a folder listing explorer for user select
	 */
	$db->setQuery("select name, folder_id, level, parent_id from folders where uid='{$user->uid}' "
		." and deleted=0 and parent_id = '{$input["id"]}' order by time desc limit {$max_total_directory}");
	$db->query();
	$des_folder = $db->loadRowList();
	   
	foreach ($des_folder as $temp_folder) {
		// folder name format
	   	$temp_folder[name] = urldecode($temp_folder[name]);
	    	
	    // set folder level
	    $temp_folder[level] = $temp_folder[level] + 1;
	    $temp_folder[padding] = "pad" . $temp_folder[level] * 16 . "px";
	    
	    // check got child folder
	    $db->setQuery("select folder_id from folders where uid='{$user->uid}' "
			." and deleted=0 and parent_id='{$temp_folder["folder_id"]}' limit 1");
	    $db->query();
	    
	    $folder_list = "<li id='li-{$temp_folder[folder_id]}' class='{$temp_folder[padding]}'>";
	    
	    if ($db->getNumRows()) {
	    	$temp_folder[gotChild] = 1;
	    	$folder_list .= "<img id='expand-{$temp_folder[folder_id]}' class='open' "
	    		." src='{$skin_dir}/images/user/11px.gif' onclick='expand_folder(".'"'.$temp_folder[folder_id].'"'.");' />";
	    }
	    else {
	    	$temp_folder[gotChild] = 0;
	    	$folder_list .= "<img class='none' src='{$skin_dir}/images/user/11px.gif' />";
	    }
	    
	    $folder_list .= "<img class='folder_icon' src='{$skin_dir}/images/user/12-14px.gif' />";
	    
	    $folder_list .= "<a id='folder-{$temp_folder[folder_id]}' href='#' "
	    	. "onclick='select_folder(".'"'."{$temp_folder[folder_id]}".'"'.");'>"
	    	. "&nbsp;{$temp_folder[name]}&nbsp;</a></li>\n";
	    
	    echo $folder_list;
	    echo "\n";
	}
}
elseif ($input["act"] == "child_id" && $input["id"]) {
	echo implode(getChildInheritList($input["id"],$max_directory_level),",");
}
else {
	// error redirect to member page	
	exit;
}


exit;
?>