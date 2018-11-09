<?php
/**
 * June 20 2009, Easy to views and documentation
 * Ready for update to including folder systems
 *
 */
function showFileLinks() {
	showFileAndFolderLinks();
}
function showFileAndFolderLinks()
{
	global $input,$baseUrl,$baseWeb,$db,$user,$template,$LANG,$members_title;
	
	// Set the number of links as default 20
    $number_link_per_page = 20;
    
    // if links number is less than 0 or 
    $input["s"] = intval($input["s"]);
	if ($input["s"] < 0) {
		$input["s"] = 0;
	}
	
	if (($input["s"] % 20) != 0) {
		$input["s"] = @ceil($input["s"] / 20) * 20;
	}
	
	// Count total files for user
	if ($input["folder_id"] || $input["folder_id"] != 0) {
		
		// check folder existance and deleted...
		$db->setQuery("select parent_id,name,mode from folders where uid='{$user->uid}' and folder_id='{$input["folder_id"]}' and deleted=0 limit 1");
    	$db->query();
    	if ($db->getNumRows()) {
    		$folder_query = " and folder_id='{$input["folder_id"]}' ";

    		// set template parent id
    		$temp = $db->loadRow();
    		$template->assign_var('parent_id',$temp["parent_id"]);
    		$template->assign_var('folder_id',$input["folder_id"]);
    		$template->assign_var('folder_name',urldecode($temp["name"]));
    		$template->assign_var('access_mode',$temp["mode"]);
    		
    		$LANG[SITEMEMBERS] = $LANG[SITEMEMBERS] . " - " . $LANG[Folder] . " - " . urldecode($temp["name"]);
    		
    		// set is current space is inside folder
    		$template->assign_var('insideFolder',1);
    	}
    	else {
    		// redirect to root directory
	    	$template->assign_var('folder_id',"0");
	    	$template->assign_var('folder_name',"");
	    	$template->assign_var('access_mode',0);
    		
    		do_redirect("{$baseWeb}/members.php", $LANG["Folder_Not_Exists"]);
    	}

    	// set search parent id
    	$parent_id=$input["folder_id"];
	}
	else {
		// inside root directory
		$template->assign_var('folder_id',"0");
	    $template->assign_var('folder_name',"");
    	$template->assign_var('access_mode',0);
		
		$folder_query = " and folder_id='0' ";
		
		$template->assign_var('parent_id',0);
		$template->assign_var('insideFolder',0);
		
		$parent_id=0;
	}
	
	// calculate all folder from user
	$db->setQuery("select count(id) as total_files from files where uid='{$user->uid}' and deleted=0 {$folder_query}");
	$db->query();
	$stats = $db->loadRow();
    
    /**
     * [Update] June 20 2009 - Folder Listing
     * [Update] Nov 14 2009 - Folder more than 20
     */
    $starting_folder = 0;
    $starting_folder = $input["s"];
    $db->setQuery("select * from folders where uid='{$user->uid}' and parent_id='{$parent_id}' and deleted=0 order by id desc limit {$starting_folder}, {$number_link_per_page}");
    $db->query();
    $total_folders = $db->getNumRows();
    $template->assign_var('gotFolders', $total_folders?1:0);
    $folders = $db->loadRowList();
    
    // set total_folders
    $db->setQuery("select count(id) as total_folders from folders where uid='{$user->uid}' and parent_id='{$parent_id}' and deleted=0");
    $db->query();
    $tempf = $db->loadRow();
    $count_total_folders = $tempf["total_folders"];

	// Update $number_link_per_page reduce folders
	if ($total_folders > $input["s"]) {
	 	$show_link_per_page = $input["s"] + $number_link_per_page - $total_folders;
	   	$starting_file = $input["s"];
	   	
	    // Make first a few row to folders
	    $i = 0;
	    foreach($folders as $folder)
	    {
	    	$i++;
	    	$folder["no"] = $i;
	    	$folder["urllink"] = $folder["name"];
	    	$folder["name"] = urldecode($folder["name"]);
	    	$folder["name"] = strlen($folder["name"]) > 40 ? substr($folder["name"],0,40)."..." : $folder["name"];
		   	$folder["time"] = date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$folder["time"]);
	    	$folder["password_tips"] = $folder["usepass"] ? $LANG["Have_Pass"] : $LANG["No_Pass"];
	    	$folder["usepass"] = $folder["usepass"] ? "bullet_key.gif" : "bullet_none.gif";
		   	$folder["descr"] = empty($folder["descr"]) ? $folder["name"] : $folder["descr"];
		   	
		   	$template->assign_block_vars('folders', $folder);
		}
		$template->assign_var('total_folder',$i);
	}
	else {
		$show_link_per_page = $number_link_per_page - $total_folders;
		if ($count_total_folders > $number_link_per_page && $total_folders) {
			$starting_file = 0;
		}
		else {
	  		$starting_file = $input["s"] - $count_total_folders;
		}
		
	  	// Make first a few row to folders
		$i = 0;
		foreach($folders as $folder)
		{
			$i++;
		    $folder["no"] = $i;
		    $folder["urllink"] = $folder["name"];
		    $folder["name"] = urldecode($folder["name"]);
		    $folder["name"] = strlen($folder["name"]) > 40 ? substr($folder["name"],0,40)."..." : $folder["name"];
		    $folder["time"] = date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$folder["time"]);
		    $folder["password_tips"] = $folder["usepass"] ? $LANG["Have_Pass"] : $LANG["No_Pass"];
		    $folder["usepass"] = $folder["usepass"] ? "bullet_key.gif" : "bullet_none.gif";
		    $folder["descr"] = empty($folder["descr"]) ? $folder["name"] : $folder["descr"];
		    	
		    $template->assign_block_vars('folders', $folder);
		}
		
		$template->assign_var('total_folder',$i);
	}
    
    if ($input["folder_id"] || $input["folder_id"] != 0) {
    	$folder_query = " and folder_id='{$input["folder_id"]}' ";
    }
    else {
	    $folder_query = " and folder_id='0' ";
    }
    
    // Select result from files
    // make it into 2 sql - 1 for files, 1 for filestats, merge in array
    //$db->setQuery("select f.*, fs.dls from files as f LEFT JOIN filestats as fs ON f.upload_id=fs.upload_id "
    //	. "where f.uid='{$user->uid}' and f.deleted=0 {$folder_query} order by f.time DESC limit {$starting_file}, {$show_link_per_page}");
    
    $db->setQuery("select * from files where uid='{$user->uid}' and deleted=0 {$folder_query} order by time DESC limit {$starting_file}, {$show_link_per_page}");
    $db->query();
    $files = $db->loadRowList();
    
    // Set templete show file links or not and set total files or later use
	if ($stats["total_files"] > 0) {
		$template->assign_var('gotFiles', 1);
		$total_files = $stats["total_files"] + $count_total_folders;
	}
	else {
		$total_files = $count_total_folders;
	}

	// Set current pages	
    $current_page = @($input["s"] / $number_link_per_page);

    // Build links by using function   
    $pageLinks=buildPageLinksNew(array('total'=>$total_files,'page'=>$number_link_per_page,
    	'cur_page'=>$current_page,'baseUrl'=>$baseUrl."&showlinks=1"));
    
    // Assign all row and variable to template 	
    $template->assign_vars(array('pageLinks'=>$pageLinks,'filenums'=>$total_files,'pages'=>$number_link_per_page));

    // Start building links for user
    buildLinksCode($files);	
}

function confirmPage($type,$url,$return,$extra) {
	global $input,$baseUrl,$baseWeb,$db,$user,$template,$LANG;
	
	// Assign variable to template
	$template->assign_vars(array(
		'type' => $type,
		'url' => $url,
		'return' => $return,
		'extra' => $extra,
	));
	
	if ($type == "folderdelete") {
		$template->assign_var('folderdetail',1);
		$template->assign_var('folderdelete',1);
	}
	else {
		
	}
	
	
	
	$otherPage=1;
	$template->assign_var('otherpage',$otherPage);
	
	require_once("header.php");
	$template->set_filenames(array(
		'body' => 'confirm.html')
		);
	$template->pparse('body');
	include "footer.php";
	
	exit;
}
?>