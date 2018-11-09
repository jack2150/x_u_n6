<?php
//header("location: index.php");

define("IN_PAGE",'FOLDERS');



include "includes/inc.php";
include "includes/filelink.inc.php";
include "includes/folder.inc.php";

$baseUrl='folders.php?';

# checking if logined
if($user->logined==0) header('location:login.php');
if ($user->account_status == -1) { $user->logout(); header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended'); }

// Group Folders Number and Level
$max_directory_level = 6;
$max_total_directory = 200;


/**
 * Create Folder Section
 */
if ($input["act"] == "create") {
	define("PAGE_TITLE",'Create_Folder');
	if ($_POST)	{	
		// validate - name must not be empty or not more than 120 characters
		if (count(urlencode($input[folder_name])) >= 120 || empty($input[folder_name])) {
			$errorCode[] = $LANG[Folder_Name_Error];
		}
		
		// validate - permission must set (default is owner_only)
		if (($input[access_mode] != 0 && $input[access_mode] != 1) || !is_numeric($input[access_mode])) {
			$errorCode[] = $LANG[Selected_Error];
		}
		
		// validate - if use_pass then password must not empty
		if ($input[use_pass]) {
			// if using use_pass, check length and empty
			if (empty($input[folder_password])) {
				$errorCode[] = $LANG[Password_Empty];
			}
			elseif (count($input[folder_password]) >= 60) {
				$errorCode[] = $LANG[Password_Error];
			}
		}
		
	
		if (strlen($input[folder_descr]) > 1200) {
			$errorCode[] = $LANG[Folder_Descr_Error];
		}
		
		// search folder name not duplicate for that user!
		$db->setQuery("select id from folders where uid='{$user->uid}' and name='".urlencode($input[folder_name])."' and deleted=0 limit 1");
		$db->query();
		if($db->getNumRows()) {
			$errorCode[] = $LANG[Folder_Duplicate_Error];
			
			// if last 3 char = (int) then make (int+1)
			
			//$input[folder_name] = $input[folder_name]."(2)";
			if (substr($input[folder_name],-3,1) == "(" && substr($input[folder_name],-1,1) == ")" && is_numeric(substr($input[folder_name],-2,1))) {
				$next_folder = substr($input[folder_name],-2,1) + 1;
				$input[folder_name] = substr($input[folder_name],0,-3)."({$next_folder})";
			}
			else {
				$input[folder_name] = $input[folder_name]."(2)";
			}
		}
		
		// parent exist check
		if ($input[parent_id]) {
			$db->setQuery("select parent_id from folders where uid='{$user->uid}' and folder_id='{$input[parent_id]}' and deleted=0 limit 1");
			$db->query();
			if (!$db->getNumRows()) {
				// if parent id not exists
				do_redirect("{$baseWeb}/members.php", $LANG[Folder_Parent_ID_Not_Exists]);
			}
		}
		
		// directory level check
		$folder_level = 0;
		if ($input[parent_id]) {
			$db->setQuery("select level from folders where uid='{$user->uid}' and folder_id='{$input[parent_id]}' and deleted=0 limit 1");
			$db->query();
			$parent_folder = $db->loadRow();
			
			$folder_level = $parent_folder[level] + 1;
			
			if ($folder_level > $max_directory_level) {
				// if parent id not exists
				$errorCode[] = $LANG[Reach_Max_Inherit1].$max_directory_level.$LANG[Reach_Max_Inherit2];
			}
		}
		
		// total directory check
		$db->setQuery("select count(id) as total_folders from folders where uid='{$user->uid}' and deleted=0 limit 1");
		$db->query();
		$temp = $db->loadRow();
		if ($temp[total_folders] > $max_total_directory) {
			$errorCode[] = $LANG[Reach_Max_Folder1].$max_total_directory.$LANG[Reach_Max_Folder2];
		}
		
		
		
		// start check validate or show error message
		if ($errorCode) {
			// got error, show it on the top of the pages
			$template->assign_var("errormessage",implode($errorCode, "<br />"));
			
			// assign invalidate data back to form
			$template->assign_vars(array(
				"folder_name"=>$input[folder_name],
				"access_mode"=>$input[access_mode],
				"use_pass"=>$input[use_pass],
				"folder_password"=>$input[folder_password],
				"folder_descr"=>$input[folder_descr],
				"parent_id"=>$input[parent_id],
			));
		}
		else {
			// generate folder id
			do {
			    $folder_id = substr(md5(microtime()), 0, 7);
	            $db->setQuery("select id from folders where folder_id='$folder_id' limit 1");
	            $db->query();
	            $exists = $db->getNumRows();
		    } while ($exists);		
			
			// insert into sql
			$folderstable = new TABLE($db,"folders","id");
			$folderstable->name = urlencode($input[folder_name]);
			$folderstable->folder_id = $folder_id;
			$folderstable->parent_id = $input[parent_id] && strlen($input[parent_id]) == 7 ? $input[parent_id] : 0;
			$folderstable->level = $folder_level;
			$folderstable->uid = $user->uid;
			$folderstable->time = time();
			$folderstable->deleted = 0;
			$folderstable->mode = $input[access_mode];
			$folderstable->usepass = $input[use_pass] ? 1 : 0;
			$folderstable->password = $input[use_pass] ? $input[folder_password] : "";
			$folderstable->descr = $input[folder_descr];
			$folderstable->insert();
			
			// redirect to show folder detail pages
			define("PAGE_TITLE","Create_Folder");
			
			if ($input[parent_id]) {
				do_redirect("{$baseWeb}/members.php?folder_id={$input[parent_id]}", $LANG[Folder_Created]);
			}
			else {
				do_redirect("{$baseWeb}/members.php", $LANG[Folder_Created]);
			}
		}
	}
	else {
		if ($input[parent_id]) {
			$db->setQuery("select parent_id from folders where uid='{$user->uid}' and folder_id='{$input[parent_id]}' and deleted=0 limit 1");
			$db->query();
			if (!$db->getNumRows()) {
				// if parent id not exists
				do_redirect("{$baseWeb}/members.php", $LANG[Folder_Parent_ID_Not_Exists]);
			}
			else {
				$template->assign_var("parent_id",$input[parent_id]);
			}	
		}
	}

	
	
	// Assign Template	
	$folder_page = "folder_create.html";
}
/**
 * Modify Folder Section
 */
elseif ($input["act"] == "modify" && strlen($input["folder_id"]) == 7) {
	define("PAGE_TITLE",'Folder_Modify');
	
	// search folder id for exist or no
	$db->setQuery("select * from folders where uid='{$user->uid}' and folder_id='{$input[folder_id]}' and deleted=0 limit 1");
	$db->query();
	if($db->getNumRows()>0) {
		
		// folder id exists, start modify
		
		if ($_POST) {
			$folder = $db->loadRow();

			// validate - name must not be empty or not more than 120 characters
			if (count(urlencode($input[folder_name])) >= 120 || empty($input[folder_name])) {
				$errorCode[] = $LANG[Folder_Name_Error];
			}
			
			// validate - permission must set (default is owner_only)
			if (($input[access_mode] != 0 && $input[access_mode] != 1) || !is_numeric($input[access_mode])) {
				$errorCode[] = $LANG[Selected_Error];
			}
			
			// validate - if use_pass then password must not empty
			if ($input[use_pass]) {
				// if using use_pass, check length and empty
				if (empty($input[folder_password])) {
					$errorCode[] = $LANG[Password_Empty];
				}
				elseif (count($input[folder_password]) >= 60) {
					$errorCode[] = $LANG[Password_Error];
				}
			}
			
		
			if (strlen($input[folder_descr]) > 1200) {
				$errorCode[] = $LANG[Folder_Descr_Error];
			}
			
			// search folder name not duplicate for that user!
			$db->setQuery("select id from folders where uid='{$user->uid}' and name='".urlencode($input[folder_name])."' and folder_id<>'{$input[folder_id]}' and deleted=0 limit 1");
			$db->query();
			if($db->getNumRows()) {
				$errorCode[] = $LANG[Folder_Duplicate_Error];
				
				// if last 3 char = (int) then make (int+1)
				
				//$input[folder_name] = $input[folder_name]."(2)";
				if (substr($input[folder_name],-3,1) == "(" && substr($input[folder_name],-1,1) == ")" && is_numeric(substr($input[folder_name],-2,1))) {
					$next_folder = substr($input[folder_name],-2,1) + 1;
					$input[folder_name] = substr($input[folder_name],0,-3)."({$next_folder})";
				}
				else {
					$input[folder_name] = $input[folder_name]."(2)";
				}
			}
			
			
			
			
			// start check validate or show error message
			if ($errorCode) {
				// got error, show it on the top of the pages
				$template->assign_var("errormessage",implode($errorCode, "<br />"));
				
				// assign invalidate data back to form
				$template->assign_vars(array(
					"folder_id"=>$input[folder_id],
					"folder_name"=>$input[folder_name],
					"access_mode"=>$input[access_mode],
					"use_pass"=>$input[use_pass],
					"folder_password"=>$input[folder_password],
					"folder_descr"=>$input[folder_descr],
					"parent_id"=>$input[parent_id],
				));
			}
			else {
				// modify data
				$input[use_pass] = $input[use_pass] ? 1 : 0;
				
				// start update data
				$db->setQuery("update folders set
					name = '".urlencode($input[folder_name])."',
					mode = '{$input[access_mode]}',
					usepass = '{$input[use_pass]}',
					password = '{$input[folder_password]}',
					descr = '{$input[folder_descr]}'				
					where uid='{$user->uid}' and folder_id='{$input[folder_id]}' limit 1");
				$db->query();
				
				// redirect show success updated
				define("PAGE_TITLE",'Folder_Modify');
				if ($input[parent_id]) {
					do_redirect("{$baseWeb}/members.php?folder_id={$input[parent_id]}", $LANG[Folder_Updated]);
				}
				else {
					do_redirect("{$baseWeb}/members.php", $LANG[Folder_Updated]);
				}
			}
		}
		else {
			// folder exists
			$folder=$db->loadRow();
			
			// assign variable
			$template->assign_vars(array(
				"folder_id"=>$folder[folder_id],
				"folder_name"=>urldecode($folder[name]),
				"access_mode"=>$folder[mode],
				"use_pass"=>$folder[usepass],
				"folder_password"=>$folder[password],
				"folder_descr"=>$folder[descr],
				"parent_id"=>$folder[parent_id],
			));
		}
	}
	else {
		// if not exist redirect with folder not exist
		do_redirect("{$baseWeb}/members.php", $LANG["Folder_Not_Exists"]);
	}
	
	$folder_page = "folder_modify.html";
}
elseif ($input["act"] == "delete") {
	define("PAGE_TITLE",'Folder_Delete');
	
	//echo "Server Maintenance - Function Disable";
	//exit;
	
	$db->setQuery("select * from folders where uid='{$user->uid}' and folder_id='{$input["folder_id"]}' limit 1");
	$db->query();
	if($db->getNumRows()>0) {
		$folder=$db->loadRow();
		
		if ($folder["deleted"]) {
			do_redirect($baseWeb."/members.php",$LANG["Folder_Already_Deleted"]);
		}
		else {
			if ($input["delete_confirm"] == 1) {				
				// get all inherit
				$folders_list = getChildInheritList($input["folder_id"],$max_directory_level);
				
				// modify folders query
				$folders_list = "'" . implode($folders_list, "','") . "'";
				
				// delete all child foldres
				$db->setQuery("update folders set deleted = 1 where folder_id in ($folders_list)");
				$db->query();
				
				// delete all files inside child folders
				$db->setQuery("update files set deleted = 1 where folder_id in ($folders_list)");
				$db->query();
				
				sleep(2);
				
				if ($folder[parent_id]) {
					do_redirect($baseWeb."/members.php?folder_id={$folder[parent_id]}",$LANG["Folder_Delete_Completed"]);
				}
				else {
					do_redirect($baseWeb."/members.php",$LANG["Folder_Delete_Completed"]);
				}
			}
			else {
				// search folder id for exist or no
				// assign variable
				$template->assign_vars(array(
					'folder_id'=>$folder[folder_id],
					'folder_name'=>urldecode($folder[name]),
					'folder_time'=> date("F d Y, h:i a",$folder[time]),
					'level'=> $folder[level],
					'access_mode'=>$folder[mode] == 1 ? $LANG[All_Access_Tips] : $LANG[Owner_Only_Tips],
					'use_pass'=>$folder[usepass] == 1 ? $LANG[Use_Pass_Tips] : $LANG[No_Pass_Tips],
					'folder_password'=>$folder[usepass] == 1 ? $folder[password] : $LANG[No_Pass],
					'folder_descr'=>$folder[descr],
				));
			}
		}
	}
	else {
		do_redirect("{$baseWeb}/members.php", $LANG["Folder_Not_Exists"]);
	}
	
	$folder_page = "folder_delete.html";
}
/**
 * Other Redirect to Member Page 
 */
else {
	header('location:members.php');
}




$template->assign_var("folderspage",1);

require_once("header.php");
$template->set_filenames(array("body" => $folder_page));
$template->pparse('body');
include "footer.php";
?>