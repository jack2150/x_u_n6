<?
/*
+--------------------------------------------------------------------------
|   Mega File Hosting Script v1.2
|   ========================================
|   by Stephen Yabziz
|   (c) 2005-2006 YABSoft Services
|   http://www.yabsoft.com
|   ========================================
|   Web: http://www.yabsoft.com
|   Email: ywyhnchina@163.com
+--------------------------------------------------------------------------
|
|   > Script written by Stephen Yabziz
|   > Date started: 1th March 2006
+--------------------------------------------------------------------------
*/
@session_start();
error_reporting  (E_ERROR | E_WARNING | E_PARSE );
$included_files = get_included_files();
$root_dir = dirname(dirname(end($included_files)));
define('ROOT',$root_dir);

#####################################
// limit http and ftp upload function
define("HOURCHECK","24");
define("MAXUPLOADFILES","100");
define("MAXUPLOADFILESIZE","2000"); // in MB
#####################################

include_once(ROOT.'/config.php');
include_once(ROOT.'/includes/sysvars.php');
include_once(ROOT.'/includes/database.php');
include_once(ROOT.'/includes/function.php');

# initiate db connection
$db=new database($sql_host,$sql_user,$sql_pass,$sql_database,"");
# parse input
$input=parse_incoming();
# load front end
if(!defined('IN_ADMIN')&&IN_PAGE!='GETFILE')
{
   include_once(ROOT.'/includes/upload.class.php');
   include_once(ROOT.'/includes/user.class.php');

   $user=new user();
   if(IN_MAIN_SERVER==1)
   {
       include_once(ROOT.'/includes/template.php');
       include_once(ROOT.'/includes/functions_template.php');
       include_once(ROOT.'/includes/email.class.php');
       
       $template = new Template( ROOT.'/skin/'.$user->setting['skin_dir'] );
   
       $email = new Email(ROOT.'/language/'.$user->setting[language].'/emails');
       $email->admin_email =$user->setting[adminemail];
   
       $template->cache=$user->setting[cached];
       $template->allow_php=$user->setting[allow_php]=='yes';
       
       /**
        * [CHANGELOG] Aug 06 2009
        * Switch advertisement between 3 accounts randomly
        * Foong, Yang, Nfdia
        */
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 100000));
		$random_account = mt_rand(0,9);
		if ($random_account < 2) {
			// Yang - 0,1
			$template->assign_var("yang_account",1);		
		}
		elseif ($random_account > 1 && $random_account < 6) {
			// Nfdia - 2,3,4,5
			$template->assign_var("nfdia_account",1);	
		}
		else {
			// Foong - 6,7,8,9
			$template->assign_var("foong_account",1);
		}

		//$template->assign_var("use_adsense_account",0);
   }
   $user->initiate();
}

if(IN_MAIN_SERVER==1)
{
    $parts=parse_url($baseWeb);
    $parts[host] = $_SERVER[HTTP_HOST];
    $baseWeb = "$parts[scheme]://$parts[host]$parts[path]";
}
# load language setting
$langWeb = $baseWeb.($user->setting[lang_page]=='static'?'/'.$user->langcode:'');

$DEMOMODE=0;
$DEMOTEXT='Demo mode disabled some function!';
$debug = $user->setting[debug]=='yes';

# fixing no keywords
// $template->assign_var("keyword",$LANG["SiteKeyword"]);
?>
