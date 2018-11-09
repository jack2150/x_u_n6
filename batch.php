<?php
//header("location: index.php");

define("IN_PAGE",'BATCH');

include "includes/inc.php";
include "includes/folder.inc.php";

$baseUrl='folders.php?';

# checking if logined
if($user->logined==0) header('location:login.php');
if ($user->account_status == -1) { $user->logout(); header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended'); }

$max_directory_level = 6;
$max_total_directory = 200;


if ($input[act] == "delete") {
	// batch delete files
	define("PAGE_TITLE","Batch_Delete");
	
	//echo "Server Maintenance - Function Disable";
	//exit;

	//print_r($input[folder_id]);
	//print_r($input[upload_id]);

	# processing
	if ($user->revenue_program == -1) {
		do_redirect("{$baseWeb}/members.php", $LANG[DeletePermission]);
	}
	
	if ($input["delete_confirm"] == 1) {
		// already confirm
		
		// check upload_id is not empty
		$files_selected = count($input[upload_id]);
		
		// check folder id is not empty
		$folder_selected = count($input[folder_id]);
		
		// check got files or folder select
		if ($files_selected == 0 && $folder_selected == 0) {
			do_redirect("{$baseWeb}/members.php", $LANG[No_Item_Select]);
		}
		
		// check confirm total folders and files match
		if ($files_selected != $input["total_files"] || $folder_selected != $input["total_folders"]) {
			do_redirect("{$baseWeb}/members.php", $LANG[No_Item_Select]);
		}
		
		if ($folder_selected) {
			// delete all folders at once

			// delete parent id and delete sub folder id
		
			// make a 1 dimesion parent_id and all sub level folder id list
			foreach ($input[folder_id] as $folder_id) {
				// get all folders that need to be delete
				$folder_id_list[] = "'".implode(getChildInheritList($folder_id,$max_directory_level),"','")."'";
			}
			
			$folder_id_list = implode($folder_id_list,",");
			
			if ($folder_id_list) {
				// delete folders
				$db->setQuery("update folders set deleted = 1 where uid='{$user->uid}' and deleted=0 and folder_id in ({$folder_id_list})");
				$db->query();
				
		    	// delete all files inside those folders
		    	$db->setQuery("update files set deleted = 1 where uid='{$user->uid}' and deleted=0 and folder_id in ({$folder_id_list})");
		    	$db->query();
			}
			
		}
	    

		// delete all files at once
		if ($files_selected) {
			// all files that need to be delete
			if ($files_selected) {
				$upload_id_list = "'".implode($input[upload_id],"','")."'";
			}
			
		    // delete all files inside those folders
		    $db->setQuery("update files set deleted = 1 where uid='{$user->uid}' and deleted=0 and upload_id in ({$upload_id_list})");
		    $db->query();
		}

		// redirect to member pages!
		do_redirect("{$baseWeb}/members.php?folder_id={$input[parent_id]}", $LANG[Batch_File_Deleted]);
				
	}
	else {
		// not comfirm yet
		
		// check upload_id is not empty
		$files_selected = count($input[upload_id]);
		
		// check folder id is not empty
		$folder_selected = count($input[folder_id]);
		
		// check got files or folder select
		if ($files_selected == 0 && $folder_selected == 0) {
			do_redirect("{$baseWeb}/members.php", $LANG[No_Item_Select]);
		}
		
		$template->assign_vars(array("total_folders"=>$folder_selected,"total_files"=>$files_selected));
		
		
		// if got files selected
		if ($files_selected) {
			$upload_id_list = "'".implode($input[upload_id],"','")."'";
			
			$db->setQuery("select name, upload_id from files where uid='{$user->uid}' and "
				." upload_id in ({$upload_id_list}) and deleted=0 order by id desc limit 20");
	    	$db->query();
	    	
	    	// check total delete upload_id match select upload_id
	    	if (count($input[upload_id]) == $db->getNumRows()) {
				$files = $db->loadRowList();

				$i = 0;
				foreach($files as $file)
			    {
			    	$i++;
			    	$file["no"] = $i;
			    	$file["name"] = base64_decode($file["name"]);
			    	$file["name"] = strlen($file["name"]) > 40 ? substr($file["name"],0,40)."..." : $file["name"];
			    	
			    	// until here - get files icon
			    	$file["file_icon"] = file_icon(getExt($file["name"],"."));
			    	$file["file_type"] = file_type(getExt($file["name"],"."));

			    	$template->assign_block_vars('files', $file);
			    }
				
	    	}
	    	else {
	    		// error wrong parameter, redirect to member page
	    		do_redirect("{$baseWeb}/members.php", $LANG[Invalid_Batch_Delete]);
	    	}
		}
		
		// if got folders selected
		if ($folder_selected) {
			$folder_id_list = "'".implode($input[folder_id],"','")."'";
			
			$db->setQuery("select name, folder_id from folders where uid='{$user->uid}' and "
				." folder_id in ({$folder_id_list}) and deleted=0 order by id desc limit 20");
	    	$db->query();
			
	    	// check total delete upload_id match select upload_id
	    	if (count($input[folder_id]) == $db->getNumRows()) {
				$folders = $db->loadRowList();

				$i = 0;
				foreach($folders as $folder)
			    {
			    	$i++;
			    	$folder["no"] = $i;
			    	$folder["name"] = urldecode($folder["name"]);
			    	$folder["name"] = strlen($folder["name"]) > 40 ? substr($folder["name"],0,60)."..." : $folder["name"];

			    	$template->assign_block_vars('folders', $folder);
			    }
				
	    	}
	    	else {
	    		// error wrong parameter, redirect to member page
	    		do_redirect("{$baseWeb}/members.php", $LANG[Invalid_Batch_Delete]);
	    	}
		}
	}
	
	$batchPage = "batch_delete.html";
}
elseif ($input[act] == "move") {
	// batch move files
	define("PAGE_TITLE","Move_Files");
	
	if ($input["move_confirm"]) {
		// check move files and folders
	    $total_folders = count($input["folder_id"]);
	    $total_files = count($input["upload_id"]);
	    
	    if ($total_folders != $input["total_folders"] && $total_files != $input["total_files"]) {
	    	// error, hacking, redirect
	    	do_redirect("{$baseWeb}/members.php?folder_id={$input[parent_id]}", $LANG[Invalid_Batch_Move]);
	    }
		
	    
	    // check max move files + folders
	    if ($total_folders + $total_files > 20) {
	    	// error, hacking, redirect
	    	do_redirect("{$baseWeb}/members.php?folder_id={$input[parent_id]}", $LANG[Invalid_Batch_Move]);
	    }
	    
		
		// check destination folder
		$errorMessage = "";
		$move_to_folder = -1;
		$des_folder_level = -1;
		$move_to_folder = $input["destination_folder_id"];
		// first check destination folder
		// check is root
		if ($move_to_folder == "root") {
			// move files and folder to root
			$move_to_folder = 0;
			$des_folder_level = 0;
			
		   	// check is all selected folder same level
		   	if ($total_folders) {
			    $move_folders_list = "'".implode($input["folder_id"], "','")."'";
				$db->setQuery("select level from folders where folder_id in ($move_folders_list) and uid = '{$user->uid}' and deleted = 0 limit 1");
				$db->query();
				$temp_folder_list = $db->loadRowList();
					    
				$temp_level = -1;
				foreach ($temp_folder_list as $temp_folder) {
					if ($temp_level == -1) {
						$temp_level = $temp_folder["level"];
					}
					else {
						if ($temp_level != $temp_folder["level"]) {
							$errorMessage = $LANG["Error_Multiple_Level_Selected"];
						}
					}
				}
					    
				// set des folder level
				$des_folder_level = $des_folder_level - $temp_level;
		   	}
		}
		
		
		// check is exists
		if ($move_to_folder && $des_folder_level) {
			$db->setQuery("select level from folders where folder_id = '{$move_to_folder}' and uid = '{$user->uid}' and deleted = 0 limit 1");
		    $db->query();
		    if ($db->getNumRows()) {
		    	$temp_folder = $db->loadRow();
		    	$des_folder_level = $temp_folder["level"] + 1;
		    	
		    	if ($total_folders) {
		    		// check is all selected folder same level
		    		$move_folders_list = "'".implode($input["folder_id"], "','")."'";
					$db->setQuery("select level from folders where folder_id in ($move_folders_list) and uid = '{$user->uid}' and deleted = 0 limit 1");
				    $db->query();
				    $temp_folder_list = $db->loadRowList();
				    
				    $temp_level = -1;
				    foreach ($temp_folder_list as $temp_folder) {
				    	if ($temp_level == -1) {
				    		$temp_level = $temp_folder["level"];
				    	}
				    	else {
				    		if ($temp_level != $temp_folder["level"]) {
				    			$errorMessage = $LANG["Error_Multiple_Level_Selected"];
				    		}
				    	}
				    }
				    
				    // set des folder level
					$des_folder_level = $des_folder_level - $temp_level;

			    	// check move files and folder is including this folder, folder1 -> folder1
			    	foreach ($input["folder_id"] as $temp_folder_id) {
			    		if ($move_to_folder == $temp_folder_id) {
			    			$errorMessage = $LANG["Error_Move_Same_Folder"];
			    		}
			    	}
			    	
			    	foreach ($input["folder_id"] as $temp_folder_id) {
			    		$folder_id_list = getChildInheritList($temp_folder_id, $max_directory_level);
			    		
			    		// cannot move from parent folder to child folder
			    		// can move from child folder to parent folder
			    		foreach ($folder_id_list as $child_folder_id) {
			    			if ($move_to_folder == $child_folder_id && $move_to_folder != $temp_folder_id) {
			    				$errorMessage = $LANG["Error_Move_Parent_Folder"];
			    			}
			    		}
			    		
			    		// check move to folder level is max level when original level + destination level
			    		$search_folder_value = "'".implode($folder_id_list,"','")."'";
			    		$db->setQuery("select max(level) as max_level from folders where folder_id in ({$search_folder_value})");
			    		$db->query();
			    		$temp_folder = $db->loadRow();
			    		$child_folder_level = $temp_folder["max_level"];
			    		
			    		if ($des_folder_level + $child_folder_level > $max_directory_level) {
			    			$errorMessage = $LANG["Error_Reach_Move_Inherit1"].$max_directory_level.$LANG["Error_Reach_Move_Inherit2"];
			    		}
			    		
			    		$folder_id_list = "";
			    	}
		    	}
		    }
		}
		

	    // every 2 minutes only can move files once
	    $max_move_time = time() - 2 * 60;
	    if ($user->last_update > $max_move_time) {
	    	$errorMessage = $LANG["Error_Max_Move_Time"];
	    }
	    
		if ($errorMessage) {
			$template->assign_var("errormessage", $LANG[Error_Text].$errorMessage);
		}
		else {
			// done, move folder and files
			// update level, parent_id and update folder_id
			if ($total_folders) {
				$move_folders_list = "'".implode($input["folder_id"], "','")."'";
				
				// update folders + all childs folder
				// first update original folder, only need to change parent id and level
				$db->setQuery("update folders set parent_id = '{$move_to_folder}', "
					." level = level + {$des_folder_level} where folder_id in ({$move_folders_list})");
			    $db->query();
				
				// second update child folder, only need to change level
				foreach ($input["folder_id"] as $temp_folder_id) {
					$temp_folder_list = getChildInheritList($temp_folder_id, $max_directory_level);
			    	
					// don't include original folder id
					foreach ($temp_folder_list as $temp_child_id) {
						if ($temp_child_id != $temp_folder_id) {
							$temp_child_list[] = $temp_child_id;
						}
					}
					
					if ($temp_child_list) {
				    	$move_childs_list = "'".implode($temp_child_list, "','")."'";
				    	$db->setQuery("update folders set level = level + {$des_folder_level} where folder_id in ({$move_childs_list})");
				    	$db->query();
					}
			    	
			    	$temp_child_list = "";
				}
			}
			
			if ($total_files) {
				$move_files_list = "'".implode($input["upload_id"], "','")."'";
				
				// update files
				$db->setQuery("update files set folder_id = '{$move_to_folder}' where upload_id in ({$move_files_list})");
			    $db->query();
			}
			
			// update user last update time
			$db->setQuery("update users set last_update = '".time()."' where id = '{$user->uid}' limit 1");
		    $db->query();
			
		    // redirect to members page
			do_redirect("{$baseWeb}/members.php?folder_id={$input[parent_id]}", $LANG[Batch_Move_Completed]);
		}
	}
	
	// not comfirm yet
		
	/**
	 * Create a folder listing explorer for user select
	 */
	$db->setQuery("select name, folder_id, level, parent_id from folders where uid='{$user->uid}' "
		." and deleted=0 and level = 0 order by time desc limit {$max_total_directory}");
	$db->query();
	$des_folder = $db->loadRowList();
	  
	$folder_count = 0;
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
		   	
		if ($db->getNumRows()) {
			$temp_folder[gotChild] = 1;
		}
		else {
			$temp_folder[gotChild] = 0;
		}
		   	
		$template->assign_block_vars('des_folder', $temp_folder);
		    	
		$folder_count++;
	}
	    
	// total level 1 folder
	if ($folder_count) {
		$template->assign_var("root_folder", 1);
	}
		
	// check upload_id is not empty
	$files_selected = count($input[upload_id]);
		
	// check folder id is not empty
	$folder_selected = count($input[folder_id]);
		
	// check got files or folder select
	if ($files_selected == 0 && $folder_selected == 0) {
		do_redirect("{$baseWeb}/members.php", $LANG[No_Item_Select]);
	}
		
	$template->assign_vars(array("total_folders"=>$folder_selected,"total_files"=>$files_selected));
		
		
	// if got files selected
	if ($files_selected) {
		$upload_id_list = "'".implode($input[upload_id],"','")."'";
		
		$db->setQuery("select name, upload_id from files where uid='{$user->uid}' and "
			." upload_id in ({$upload_id_list}) and deleted=0 order by id desc limit 20");
	   	$db->query();
	   	
	   	// check total delete upload_id match select upload_id
	   	if (count($input[upload_id]) == $db->getNumRows()) {
			$files = $db->loadRowList();
			$i = 0;
				
			foreach($files as $file)
		    {
		    	$i++;
	    		$file["no"] = $i;
		    	$file["name"] = base64_decode($file["name"]);
			    	$file["name"] = strlen($file["name"]) > 40 ? substr($file["name"],0,40)."..." : $file["name"];
			    	
				// until here - get files icon
				$file["file_icon"] = file_icon(getExt($file["name"],"."));
				$file["file_type"] = file_type(getExt($file["name"],"."));
				$template->assign_block_vars('files', $file);
			}
	   	}
	   	else {
	   		// error wrong parameter, redirect to member page
	   		do_redirect("{$baseWeb}/members.php", $LANG[Invalid_Batch_Move]);
	   	}
	}
		
	// if got folders selected
	if ($folder_selected) {
		$folder_id_list = "'".implode($input[folder_id],"','")."'";
		
		$db->setQuery("select name, folder_id from folders where uid='{$user->uid}' and "
			." folder_id in ({$folder_id_list}) and deleted=0 order by id desc limit 20");
    	$db->query();
		
    	// check total delete upload_id match select upload_id
    	if (count($input[folder_id]) == $db->getNumRows()) {
			$folders = $db->loadRowList();
			$i = 0;
			foreach($folders as $folder)
		    {
		    	$i++;
		    	$folder["no"] = $i;
		    	$folder["name"] = urldecode($folder["name"]);
		    	$folder["name"] = strlen($folder["name"]) > 40 ? substr($folder["name"],0,60)."..." : $folder["name"];
		    	$template->assign_block_vars('folders', $folder);
		    }
			
	   	}
	   	else {
	   		// error wrong parameter, redirect to member page
	   		do_redirect("{$baseWeb}/members.php", $LANG[Invalid_Batch_Move]);
	   	}
	}

	$batchPage = "batch_move.html";
}
elseif ($input[act] == "link") {
	// output download link
	define("PAGE_TITLE","Actions");
	
	// get all files data
	if ($input[upload_id]) {
		$upload_id_list = "'".implode($input[upload_id], "','")."'";
		
		$db->setQuery("select * from files where upload_id in ($upload_id_list) limit 20");
		$db->query();
		$files = $db->loadRowList();
		
		// format data
		$count_link = 0;
		foreach ($files as $file) {
			$file["name"] = urlencode(base64_decode($file["name"]));
			$file["size"] = convertsize($file["size"]);
			$file["filename"] = urldecode($file["name"]);
			$template->assign_block_vars('files',$file);
			
			$count_link++;
		}
		
		// set variable
		$template->assign_var("total_links",$count_link+1);
		
		// set page
		$batchPage = "batch_link.html";
		
		$template->assign_var("contactpage",1);
	}
	else {
		// error wrong parameter, redirect to member page
	   	do_redirect("{$baseWeb}/members.php", $LANG[Invalid_Batch_Link]);
	}
}
else {
	define("PAGE_TITLE","Actions");
	// error redirect to member page	
	do_redirect("{$baseWeb}/members.php", $LANG[Invalid_Batch_Action]);
}


$template->assign_var("parent_id",$input[parent_id]);


$template->assign_var("batchpage",1);
$template->assign_var("load_prototype",1);

require_once("header.php");
$template->set_filenames(array("body" => $batchPage));
$template->pparse("body");
include_once("footer.php");
?>