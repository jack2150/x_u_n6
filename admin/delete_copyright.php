<p>
<?php
if ($_POST["address"]) {
	$save1 = $save2 = $_POST["address"];

	$str1 = explode("xun6.com/file/",$save1);
	
	$count = 0;
	foreach ($str1 as $tmp) {
		if ($count > 0) {
			$list[] = substr($tmp,0,9);
		}
		else {
			$count++;
		}
	}
	
	$str2 = explode("xun6.net/file/",$save2);
	
	$count = 0;
	foreach ($str2 as $tmp) {
		if ($count > 0) {
			$list[] = substr($tmp,0,9);
		}
		else {
			$count++;
		}
	}
	
	$search = "'".@implode($list,"','")."'";
	
	
	
	print_r($search);
	echo "<br><br>\n\n";
	
	

	// delete files in web db
	$db->setQuery("UPDATE files SET deleted=2 WHERE upload_id in ($search)");
	$db->query();
	
	// array delete files
	$db->setQuery("SELECT * FROM files WHERE upload_id in ($search) order by server_id");
	$db->query();
	

	
	
	
	
	if ($db->getNumRows()) {
		$row=$db->loadRowList();

		// make a new arrary that sort in server_id
		foreach ($row as $file) {
			$upload_id[$file["server_id"]][] = $file["upload_id"];
		}
		
		// get server list
		$db->setQuery("select server_id,sql_host,sql_port,sql_username,sql_password,sql_db from server");
		$db->query();
		$server_sql = $db->loadRowList();
		
		foreach ($server_sql as $tmp) {
			$server_id = $tmp["server_id"];
			$server[$server_id]["sql_host"] = $tmp["sql_host"];
			$server[$server_id]["sql_username"] = $tmp["sql_username"];
			$server[$server_id]["sql_password"] = $tmp["sql_password"];
			$server[$server_id]["sql_db"] = $tmp["sql_db"];
		}

		$db->close_db();
		
		//print_r($upload_id);
		
		foreach ($upload_id as $key => $tmp2) {
			$tmp_search = "'".implode($tmp2,"','")."'";
			
			//print_r($server_sql);
			$file_server_db = @mysql_connect($server[$key]["sql_host"], $server[$key]["sql_username"], $server[$key]["sql_password"]);
			
			
			if ($file_server_db) {
				// connect to database
				if (@mysql_select_db($server[$key]["sql_db"],$file_server_db)) {
					// insert the same sql to file server
					mysql_query("UPDATE files SET deleted=2 WHERE upload_id in ($tmp_search)",$file_server_db);
					echo "<i>UPDATE files SET deleted=2 WHERE upload_id in ($tmp_search)</i>"."<br>";
				}
				else {
					@mysql_close($file_server_db);
					echo "No Selected Table Error!";
				}
			}
			else {
				@mysql_close($file_server_db);
				echo "Database Cannot Connect!";
			}

			echo "FILES DELETED: " . $key . " => " .$tmp_search . "<br><br>";
		}
	}
}
?>
</p>

<form action="" method="post">
<table border="0">
  <tr>
    <td>Address List:</td>
    <td><textarea name="address" cols="120" rows="8"></textarea></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Delete It!" /></td>
  </tr>
</table>
</form>
