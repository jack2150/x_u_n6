<?
//echo "Server Maintenance - Function Disable";
//exit;

define("IN_PAGE",'DELETE');
define("PAGE_TITLE",'SITEDELETE');

include "includes/inc.php";

# validate the input
$input[id] = preg_replace('/[^A-Z0-9]/','',strtoupper($input[id]));
$upload_id = substr($input[id],0,-4);
$delete_id = substr($input[id],-4);

# load file row
$db->setQuery("select f.*, fs.dls as downloads, fs.lastdownload as lastdl from files as f left join filestats as fs on f.upload_id = fs.upload_id 
               where f.delete_id='$delete_id' and f.upload_id='$upload_id' and f.deleted=0
               limit 1
               ");
$db->query();
$file=$db->loadRow();

// check user is banned or disable
$db->setQuery("select id from users where (status = -1 or revenue_program = -1) and id = '{$file["uid"]}' limit 1");
$db->query();

# processing
if ($db->getNumRows()) {
	$template->assign_var('delete_disable', 1);
}
elseif(!$file)
{
    $template->assign_var('nofile_deleted', 1);
}
elseif(isset($input[done]))
{
	// update web server database file record
    $db->setQuery("update files set deleted=1 where id='$file[id]'");
    $db->query();
    

    
	$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='$file[server_id]' limit 1");
	$db->query();
	$server_sql = $db->loadRow();
	// make connection to server
	$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
	if ($file_server_db) {
		// connect to database
		if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
			// insert the same sql to file server
			mysql_query("update files set deleted=1 where id='$file[id]'",$file_server_db);
		}
		else {
			@mysql_close($file_server_db);
			$this->setError("SQL Open Failed!");
		}
	}
	else {
		@mysql_close($file_server_db);
		$this->setError("SQL Open Failed!");
	}

    $template->assign_var('file_deleted', 1);
}
else
{
	if (!$file['downloads']) {
		$file['downloads'] = 0;
	}

	
	$file['time'] = date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}", intval($file['time']));
    $file[size]=convertsize($file[size]);
    $file[delete_id]=$file[upload_id].$file[delete_id];
    $file[delete_id]=$input[id];
	$file[name]=base64_decode($file[name]);
	$file[lastdl] = $file[lastdl] ? date("Y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}", $file[lastdl]) : $LANG["NoDL"];
	
	$file[downloadurl] = $baseWeb."/file/".strtolower($file['upload_id'])."/".$file['name'].".html";
	
    $template->assign_block_vars('delete_form',$file);
}

# output template
$otherPage=1;
$template->assign_var('deletepage',$otherPage);
require_once("header.php");
$template->set_filenames(array(
	'body' => 'delete.html')
	);
$template->pparse('body');
include "footer.php";
?>
