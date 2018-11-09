<?php
/**
 * Oct 27 2009 - Folder Pages
 * URL - http://www.xun6.com/folder/1234567/name.html
 * 
 * parameter - folder_id, folder_name
 */
//header("location: index.php");

define("IN_PAGE",'FOLDERS');

include "includes/inc.php";
include "includes/filelink.inc.php";
include "includes/folder.inc.php";

// default variable
$file_links_per_page = 20;

// input variable
$folder_id = $input["folder_id"];
$folder_name = $input["folder_name"];
$folder_page = $input["folder_page"];

$show_filelist = 0;

// check is folder exist
if (strlen($folder_id) == 7) {
	// ok, check folder exits
	$db->setQuery("select name, mode, usepass, password from folders where folder_id = '{$folder_id}' and deleted = 0 limit 1");
	$db->query();
	
	if ($db->getNumRows()) {
		// ok, folder exists
		$folder = $db->loadRow();
		
		// set folder url
		$template->assign_var("folder_url", "{$baseWeb}/folder/{$folder_id}/{$folder_page}/{$folder["name"]}.html");
		
		if ($folder["mode"]) {
			// ok, shareable
			// check folder page is number
			if (is_numeric($folder_page) && $folder_page > -1 && $folder_page < 1000) {
				$show_filelist = 1;
			}
			else {
				define("PAGE_TITLE","Folder");
				do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
			}
		}
		else {
			// not shareable, error redirect
			define("PAGE_TITLE","Folder");
			do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
		}	
	}
	else {
		// error folder not exits, redirect
		define("PAGE_TITLE","Folder");
		do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
	}
}
else {
	// error redirect to index
	define("PAGE_TITLE","Folder");
	do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
}

// check password is correct
$access_grant = 1;
if ($folder["usepass"]) {
	// session start
	session_start();
	
	// set folder title
	$LANG["Folder"] = $folder["name"] . " - " . $LANG["Folder"];
	define("PAGE_TITLE","Folder");
	
	
	// start checking password
	
	// set session id
	$no_input = 0;
	if ($input["session_id"]) {
		session_id($input["session_id"]);
	}
	else {
		$no_input = 1;
	}
	
	// if got input password
	if ($input["folder_password"]) {
		$_SESSION["folder_password"] = $input["folder_password"];
	}
	
	// start validating
	if ($_SESSION["folder_password"] == $folder["password"]) {
		$access_grant = 1;
		
	}
	else {
		// access failed
		$access_grant = 0;
		
		if ($no_input) {
			$template->assign_var("password_form", 1);
		}
		else {
			// show error
			$template->assign_var("error_message", $LANG["Folder_Password_Incorrect"]);
			$template->assign_var("password_form", 1);
		}
	}
	
	$template->assign_var("session_id", session_id());
}

// get all files from that belong to that folder
if ($access_grant) {
	if ($show_filelist && is_numeric($folder_page) && $folder_page > 0 && $folder_page < 1000) {
		// set title
		$folder_name = $folder["name"] = urldecode($folder["name"]);
		$LANG["Folder"] = $folder["name"] . " - " . $LANG["Folder"];
		define("PAGE_TITLE","Folder");
		
		
		// get related information
		$db->setQuery("select count(id) as total_files from files where folder_id = '{$folder_id}' and deleted = 0");
		$db->query();
		$folder_stat = $db->loadRow();
		
		// echo $folder_stat["total_files"];
		
		// get files data from table
		if ($folder_stat["total_files"]) {
			// ok, folder got files
			
			// checking, if more than max links
			
			
			
			// paging
			$total_files = $folder_stat["total_files"]; // 200
			$links_per_page = $file_links_per_page; // 40
			$current_page = $folder_page; // 1 , 2
			
			if ($current_page > 1) {
				$current_start = ($current_page - 1) * $links_per_page; // 1 * 40 = 40 or 2 * 40 = 80
			}
			else {
				$current_start = 0; // 1 * 40 = 40 or 2 * 40 = 80
			}
			
			// if total_file is more than 40
			if ($total_files > $links_per_page) {
				// it will be more than 1 pages (2 pages)
				
				// check how many total pages?
				$max_total_pages = ceil($total_files / $links_per_page);
				
				// check current, if more than max total pages then is hacking
				if ($max_total_pages >= $current_page) {
					// replace the sql with combine 2 array!
					/*
					$db->setQuery("select f.name, f.upload_id, f.size, fs.dls from files as f left join filestats as fs on f.upload_id = fs.upload_id
						 where f.folder_id = '{$folder_id}' and f.deleted = 0 order by time desc limit {$current_start}, {$links_per_page}");
					$db->query();
					$files = $db->loadRowList();
					*/
					$db->setQuery("select name, upload_id, size from files 
						where folder_id = '{$folder_id}' and deleted = 0 
						order by time desc limit {$current_start}, {$links_per_page}");
					$db->query();
					$files = $db->loadRowList();
					
					
					$show_page_links = 1;
					
					// generate links here
					
					// generate start and end links
					if ($current_page > 1) {
						$firstLink = "<a href='{$baseWeb}/folder/{$folder_id}/1/{$folder_name}.html'>{$LANG["FirstPage"]}</a>";
					}
					
					if ($current_page < $max_total_pages) {
						$lastLink = "<a href='{$baseWeb}/folder/{$folder_id}/{$max_total_pages}/{$folder_name}.html'>{$LANG["LastPage"]}</a>";
					}
					
					// generate next and previous links
					if ($current_page > 1) {
						$previousPage = $current_page - 1;
						$previousLink = "<a href='{$baseWeb}/folder/{$folder_id}/{$previousPage}/{$folder_name}.html'>{$LANG["PreviousPage"]}</a>";
					}
					if ($current_page < $max_total_pages) {
						$nextPage = $current_page + 1;
						$nextLink = "<a href='{$baseWeb}/folder/{$folder_id}/{$nextPage}/{$folder_name}.html'>{$LANG["NextPage"]}</a>";
					}
					
					$numPages = "";
					// generate number links
					for ($i = 1; $i <= $max_total_pages; $i++) {
						
						
						if ($i == $current_page) {
							$numLinks .= "<b>{$i}</b>";
						}
						else {
							$numLinks .= "<a href='{$baseWeb}/folder/{$folder_id}/{$i}/{$folder_name}.html'>{$i}</a>";
						}
					}
	
					$template->assign_vars(array(
						"firstPage" => $firstLink,
						"lastPage" => $lastLink,
						"previousPage" => $previousLink,
						"nextPage" => $nextLink,
						"numPages" => $numLinks
					));
				}
				else {
					// error folder not exits, redirect
					define("PAGE_TITLE","Folder");
					do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
				}
				
			}
			else {
				// it is less than 1 page
				// only 1 pages, if current page is more than 1, then is hacking
				if ($current_page == 1) {
					$db->setQuery("select name, upload_id, size from files 
						where folder_id = '{$folder_id}' and deleted = 0 
						order by time desc limit {$links_per_page}");
					$db->query();
					$files = $db->loadRowList();
					
					
					$show_page_links = 0;
				}
				else {
					// error folder not exits, redirect
					define("PAGE_TITLE","Folder");
					do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
				}
				
				// no need generate links
			}
			
			// output the files data into template variable
			foreach ($files as $temp_file) {
	        	$temp_file[name] = base64_decode($temp_file[name]);
	        	$url_filename = urlencode($temp_file[name]);
				$temp_file[link] = "{$baseWeb}/file/{$temp_file[upload_id]}/{$url_filename}.html";
				
				// check max file name
				if (strlen($temp_file[name]) > 58) {
					$temp_file[name] = substr($temp_file[name],0,58)." ...";
				}
				
	        	
				$temp_file[file_icon] = file_icon(getExt($temp_file[name],'.'));
				$temp_file[file_type] = file_type(getExt($temp_file[name],'.'));
				$temp_file[size] = convertsize($temp_file[size]);
				//$temp_file[dls] = $temp_file[dls] ? $temp_file[dls] : 0;
				
				
				
				$template->assign_block_vars('files', $temp_file);
			}
			
			
			
			
			$template->assign_var('show_page_links', $show_page_links);
			$template->assign_var('gotFiles', 1);
		}
		else {
			// folder is empty - show information
			$template->assign_var('gotFiles', 0);
		}
	}
	else {
		// error redirect to index
		do_redirect("{$baseWeb}", $LANG[Invalid_Folder_URL]);
	}
}

$template->assign_var("folderpage", 1);

require_once("header.php");
$template->set_filenames(array("body" => "folder.html"));
$template->pparse('body');
include "footer.php";
?>