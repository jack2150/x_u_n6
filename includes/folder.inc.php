<?php
/**
 * This function get number of inherit from folder to root!
 */
function getParentInheritNum($folder_id) {
	global $db,$baseWeb,$user;
	
	/**
	 * from parent id jump to the root 0 parent id
	 */
	if ($folder_id) {
		$total_inherit = 0;
		$temp_id = $folder_id;
		while ($temp_id) {
			
			// get parent id from record
			$db->setQuery("select parent_id from folders where uid='{$user->uid}' and folder_id='{$temp_id}' and deleted=0 limit 1");
			$db->query();
			$temp = $db->loadRow();
			
			// assign new $parent_id
			$temp_id = $temp["parent_id"];
			
			if ($temp_id) {
				$total_inherit++;
			}
		}
	
		return $total_inherit;
	}
	else {
		return 0;
	}
}

/**
 * This function get all inherit child from a folder id
 */
function getChildInheritList($folder_id,$max_level=6) {
	global $db,$baseWeb,$user;

	// search current folder level
	$db->setQuery("select level from folders where uid='{$user->uid}' and folder_id='{$folder_id}' and deleted=0");
	$db->query();
	$folders = $db->loadRow();
	//$folders = $db->loadObject();
	
	// start assign a start or middle level folder
	$folder_id_list[$folders["level"]][0] = $folder_id;
		
	// format $folder_id_list[level][number]
	for ($folder_level = $folders["level"]; $folder_level <= $max_level; $folder_level++) {
		
		// search next level folder id from level folder id list
		foreach ($folder_id_list[$folder_level] as $seach_folder_id) {
			$db->setQuery("select folder_id, level from folders where uid='{$user->uid}' and parent_id='{$seach_folder_id}' and deleted=0");
			$db->query();
			$temp_folder_list = $db->loadRowList();

			// assign each next level to array
			foreach ($temp_folder_list as $temp_folder_id) {
				$folder_id_list[$temp_folder_id["level"]][] = $temp_folder_id["folder_id"];
			}
		}
		
		// if next level don't have data, then exit
		if (!$folder_id_list[$folder_level+1]) {
			break;
		}
	}
	
	// make a simple 1 dimesion array
	foreach ($folder_id_list as $temp_folder_list) {
		foreach ($temp_folder_list as $sub_folder_list) {
			$result_folder_list[] = $sub_folder_list;
		}
	}

	return $result_folder_list;
}

function folderListing($parent_id,&$folder_list) {
	global $db,$baseWeb,$user;

	$db->setQuery("select folder_id from folders where uid='{$user->uid}' and deleted=0 and parent_id={$parent_id}");
	$db->query();
	
	if ($db->getNumRows()) {
		return $folder_list;
	}
	else {
		$folders = $db->loadRowList();
		$folder_list = array_merge($folder_list,$folders);
		return array_merge(folderListing(),$folder_list);
	}


	
	
	
	
	
	
	
	
	
	
	
}


function fibonacci ($n)
{
  if ($n == 1 || $n == 2)
  {
    return 1;
  }
  else
  {
    return fibonacci( $n - 1)+fibonacci( $n - 2 );
  }
}























?>