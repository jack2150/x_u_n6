<?
//echo "temporary shutdown! fixing largest ddos attack ever happen in xun6.com!";
//exit;
//header("location: index.php");

define("IN_PAGE",'MEMBERS');
define("PAGE_TITLE",'SITEMEMBERS');

include "includes/inc.php";
include "includes/filelink.inc.php";
include "includes/folder.inc.php";

$baseUrl='members.php?';

define("MIN_DL_JOIN_REVENUE", 200);

# checking if logined
if($user->logined==0) header('location:login.php');

if ($user->account_status == -1) { $user->logout(); header('location:'.$baseWeb.'/redirect.php?error=1&code=LoginErrSuspended'); }

if($input[act]=='savefile'&&IS_POST&&$input['file_id']){

	$db->setQuery("select name,password,descr from files where upload_id='".$input['file_id']."' limit 0,1");
	$db->query();
	if ($db->getNumRows()) {
		if (trim($input['filename']) == "" && strlen($input['filename']) < 256) {
			//do_redirect('editfile=1&file_id='.$input['file_id'].'&s='.$input['s'],$LANG[FileNameInvalid]);
			$information=$LANG[FileNameInvalid];
		}
		else {
			if (strlen($input['filepass']) < 126) {
			$db->setQuery("update files set name='".base64_encode(trim($input['filename']))."',password='".trim($input['filepass'])
				."',descr='".substr(trim($input['filedescr']),0,512)."' where upload_id='".$input['file_id']."' and uid='{$user->uid}' limit 1");
			$db->query();
			
			
			
			/**
			 * [August 12 2008] Changelog
			 * update file server database file record
			 */
			$db->setQuery("select * from files where upload_id='{$input[file_id]}' limit 1");
        	$db->query();
        	$file=$db->loadRow();
        	
			$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='$file[server_id]' limit 1");
			$db->query();
			$server_sql = $db->loadRow();
			// make connection to server
			$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
			if ($file_server_db) {
				// connect to database
				if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
					// insert the same sql to file server
					mysql_query("update files set name='".base64_encode(trim($input['filename']))."',password='".trim($input['filepass'])
						."',descr='".substr(trim($input['filedescr']),0,512)."' where upload_id='".$input['file_id']."' and uid='{$user->uid}' limit 1",$file_server_db);
				}
				else {
					@mysql_close($file_server_db);
					$information=$LANG[ErrorUpdate];
				}
			}
			else {
				@mysql_close($file_server_db);
				$information=$LANG[ErrorUpdate];
			}
	        /**
	         * End Change
	         */
			
			do_redirect("{$baseWeb}/members.php?showfile=1&s={$input['s']}",$LANG[EditSaved]);
			}
			else {
				//do_redirect('editfile=1&file_id='.$input['file_id'].'&s='.$input['s'],$LANG[FilePassInvalid]);
				$information=$LANG[FilePassInvalid];
			}
		}
	}
	else {
		//do_redirect('',$LANG[DL_FileNotFound]);
		$information=$LANG[DL_FileNotFound];
	}
	if ($information) {
		$db->setQuery("select name,password,descr from files where upload_id='".$input['file_id']."' and uid='{$user->uid}' limit 0,1");
		$db->query();
		if ($db->getNumRows()) {
			$file=$db->loadRow();
	
			$template->assign_var('filename',base64_decode($file['name']));
			$template->assign_var('filepass',$file['password']);
			$template->assign_var('filedescr',$file['descr']);
			$template->assign_var('upload_id',$input[file_id]);
			$template->assign_var('s',$input['s']);
			
			$template->assign_block_vars('EditFile',array());
		
			$editFile=1;
			$template->assign_var('editfilepage',$editFile);
		}
	}
}
elseif($input[editfile]==1&&$input[file_id]){
	$db->setQuery("select name,password,descr from files where upload_id='".$input['file_id']."' and uid='{$user->uid}' limit 0,1");
	$db->query();
	if ($db->getNumRows()) {
		$file=$db->loadRow();

		$template->assign_var('filename',base64_decode($file['name']));
		$template->assign_var('filepass',$file['password']);
		$template->assign_var('filedescr',$file['descr']);
		$template->assign_var('upload_id',$input[file_id]);
		$template->assign_var('s',$input['s']);
		
		$template->assign_block_vars('EditFile',array());
	
		$editFile=1;
		$template->assign_var('editfilepage',$editFile);
	}
	else {
		 //do_redirect('',$LANG[DL_FileNotFound]);
		 $information=$LANG[DL_FileNotFound];
		 do_redirect("{$baseWeb}/members.php?showfile=1&s={$input['s']}",$LANG['DL_FileNotFound']);
	}
}
elseif($input[act]=='editrevenue'&&IS_POST) {
	if (trim($input['payment_method']) == "paypal" or trim($input['payment_method']) == "alipay") {
		if (check_email_address(trim($input['payment_email']))) {
			if (is_numeric(trim($input['payment_minimum'])) and trim($input['payment_minimum'])>=10) {
				$db->setQuery("update users set payment_method='".$input['payment_method']."', payment_email='".$input['payment_email']."', payment_minimum='".$input['payment_minimum']."' where id='".$user->getValue('uid')."'");
				$db->query();
				do_redirect('',$LANG['RevenueEdited']);
			}
			else {
				$information=$LANG['PaymentMinimumInvalid'];
			}
		}
		else {
			$information=$LANG['PaymentEmailInvalid'];
		}
	}
	else {
		$information=$LANG['PaymentDetailMissing'];
	}
	if ($information) {
		$db->setQuery("select payment_method from users where id='".$user->getValue('uid')."' and revenue_program=1");
		$db->query();
		if ($db->getNumRows()) {
			$payment_info=$db->loadRow();
			
			if ($payment_info['payment_method'] == "paypal") {
				$template->assign_var('payment_paypal','checked="checked"');
			}
			elseif ($payment_info['payment_method'] == "alipay") {
				$template->assign_var('payment_alipay','checked="checked"');
			}
			$template->assign_var('payment_email',$input['payment_email']);
			$template->assign_var('payment_minimum',$input['payment_minimum']);
			
			$template->assign_block_vars('EditRevenue',array());
			$editRevenue=1;
			$template->assign_var('small_revenuepage',$editRevenue);
		}
		else {
			do_redirect('',$LANG['HaventJoinRevenue']);
		}
	}
}
elseif($input[editrevenue]==1) {
	// redirect to new pages
	header("location: {$baseWeb}/revenues.php?action=editpayments");
}
elseif($input[joinrevenue]==1) {
	// redirect to new pages
	header("location: {$baseWeb}/revenues.php?action=join");
}
// show earning
elseif($input['showearning']==1) {
	// redirect to new pages
	header("location: {$baseWeb}/revenues.php?action=earnings");
}
elseif($input['showpayment']==1) {
	// redirect to new pages
	header("location: {$baseWeb}/revenues.php?action=payments");
}
//
//
//
# default page information
else
{
	if($user->revenue_program == 1) {
		$template->assign_var('showRevenue',1);
	}
	else {
		/*
		$db->setQuery("select sum(fs.dls) as total from filestats as fs, files as f where fs.upload_id=f.upload_id and f.uid='".$user->getValue('uid')."'");
		$db->query();
		
		$total_downloads=$db->loadRow();
		*/
		if ($user->revenue_program == -1) {
			$information=$LANG['NotAllowJoinRevenue'];
		}
		else {
			$information=$LANG['CanJoinRevenueNow'];			
		}
		
		//$information=$LANG['CanJoinRevenueNow'];
		$template->assign_var('showRevenue',0);
	}

	/*
    if(is_numeric($input[del]))
    {
        $db->setQuery("select f.*,s.domain from files as f
                      left join server as s on s.server_id=f.server_id
                      where f.id='$input[del]' and f.uid='$user->uid'
                      ");
        $db->query();
        $file=$db->loadRow();

        $db->setQuery("update files set deleted=1 where id='{$file[id]}' and uid='{$user->uid}' limit 1");
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
				mysql_query("update files set deleted=1 where id='{$file[id]}' and uid='{$user->uid}'",$file_server_db);
			}
			else {
				@mysql_close($file_server_db);
				$information=$LANG[ErrorUpdate];
			}
		}
		else {
			@mysql_close($file_server_db);
			$information=$LANG[ErrorUpdate];
		}
        

        $user->updateStats();
    }
    */
	
	// show files list
    showFileLinks();
    
    /**
     * Account Type
     */
    switch ($user->package_id) {
    	case 2:
    		$template->assign_var("account_type","<b>".$LANG["Member"]."</b>");
    		break;
    	case 3:
    		$template->assign_var("account_type","<b class='p'>".$LANG["Premium"]."</b>");
    		break;
    	case 4:
    		$template->assign_var("account_type","<b class='p'>".$LANG["Premium"]."</b>");
    		break;
    	case 10:
    		$template->assign_var("account_type","<b class='t'>".$LANG["Tester"]."</b>");
    		break;
    	default:
    		$template->assign_var("account_type","<b>".$LANG["Member"]."</b>");
    		break;
    }
    
    $template->assign_var("groups_id",$user->package_id);

    $template->assign_block_vars('FileList',array());
	
	$memberPage=1;
	$template->assign_var('memberpage',$memberPage);
	$template->assign_var('load_prototype',1);
	
	/**
	 * Show Folder URL
	 */
	$db->setQuery("select name, mode from folders where folder_id = '{$input["folder_id"]}' and uid = '{$user->uid}' limit 1");
	$db->query();
	$temp_folder = $db->loadRow();
	if ($temp_folder["mode"]) {
		$template->assign_var("show_folder_url", 1);
		$template->assign_var("folder_url", "{$baseWeb}/folder/{$input["folder_id"]}/1/{$temp_folder["name"]}.html");
	}
}

$template->assign_var('information',$information);

$load_prototype = 1;

require_once("header.php");
$template->set_filenames(array(
	'body' => 'members.html')
	);
$template->pparse('body');
include "footer.php";


?>
