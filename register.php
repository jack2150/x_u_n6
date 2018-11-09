<?
//header("location: index.php");

define("IN_PAGE",'REGISTER');
define("PAGE_TITLE",'SITEREGISTER');

include "includes/inc.php";

$ok=0;
$nowtime=time();

if($input[act]=='reg'&&IS_POST)
{
	// check register field missing!
    if(trim($input[username])==''||trim($input[email])==''||trim($input[password])=='')
    {
        $information[] = $LANG["RegisterFieldMissing"];
    }
	// new add, username must be more than 6 character and less than 60 character and specific character
	if (!eregi("([_A-Za-z0-9-]{6,60})",$input[username])) {
		$information[] = $LANG["InvalidUsernameFormat"];
	}
	else {
		$pos = strpos($input[username], "'");
		if ($pos !== false) {
			$information[] = $LANG["InvalidUsernameFormat"];
		}
	}
	
	// new add, check password must be more than 6 character and less than 60 character and specific character
	if (!eregi("([A-Za-z0-9|-|_|@|.|#|%|!|^|&|*]{6,60})",$input[password])) {
		$information[] = $LANG["InvalidPasswordFormat"];
	}
	else {
		$pos = strpos($input[password], "'");
		if ($pos !== false) {
			$information[] = $LANG["InvalidPasswordFormat"];
		}
	}
	
	// check email 
	if (!ereg("^([A-Za-z0-9\.|-|_]{1,60})([@])([A-Za-z0-9\.|-|_]{1,40})(\.)([A-Za-z]{2,3})$",$input[email])) {
		$information[] = $LANG["InvalidEmailFormat"] ;
	}
    elseif($input[password]!=$input[password2])
    {
        $input[password2] = "";
        $information[] = $LANG["PasswordNotMatch"] ;
    }
    
    elseif(!$user->checkAvailable(mysql_escape_string($input[username]),mysql_escape_string($input[email])))
    {
        $information[] = str_replace('{username}',$input[username],str_replace('{email}',$input[email],$LANG["DuplicateUsername"]));
    }
    else
    {
		$one_hour_before_now = time() - 60;
		$db->setQuery("select * from users where ip='".$_SERVER['REMOTE_ADDR']."' and regdate > ".$one_hour_before_now." LIMIT 1");
		$db->query();
		
		if ($db->getNumRows()) {
			$information[] = $LANG['RegisterIPLimit'];
		}
		else {
			/**
			* prepare vars
			*/
			//$info=split('-',$input[plan_id]);
			$group_id=2;
	
			/**
			* load package details
			*/
			
			$db->setQuery("select * from groups where id='$group_id'");
			$db->query();
			
		
			$pos = strpos($input[email], "'");
			if ($pos !== false) {
				$information[] = $LANG["InvalidEmailFormat"];
			}
		}
    }
    /**
    *  process
    */
    if($information) {
    	$information = implode($information,"<br>");
    }
    else {
        $row=$db->loadRow();
        $periods=split(',',$row[subscr_period]);
        $fees=split(',',$row[subscr_fee]);
        $expire_date=$nowtime;
        $gid=$row[id];
        
        /**
        * prepare user data
        */
        $adduser = new TABLE($db,'users','id');
        $adduser->user = $input[username];
        $adduser->pass = $input[password];
        $adduser->email = $input[email];
        $adduser->gid = $gid;
        $adduser->regdate = $nowtime;
        $adduser->expire_date = $expire_date;
		$adduser->ip = $_SERVER['REMOTE_ADDR'];
		
		$adduser->last_login = $nowtime;
		$adduser->login_ip = $_SERVER['REMOTE_ADDR'];
		
		
        /**
        * send welcome email or validate email
        */
        if($user->setting[validate]==1)
        {
            $email->template->set_filenames(array(
	        'email' => 'activate_account.html')
	        );
            $adduser->status = 0;
            $adduser->validate_code = md5(uniqid(microtime()));
        }
        else
        {
            $email->template->set_filenames(array(
	        'email' => 'welcome_email.html')
	        );
            $adduser->status = 1;
            $adduser->validate_code = md5(uniqid(microtime()));
        }
        $adduser->insert();
        $uid=$adduser->insertid();

        /*
        * do the login now!
        */
        $status=$user->login($db->getEscaped($input[username]),$db->getEscaped($input[password]));
		
        // register success
        //header('location:'.$baseWeb.'/redirect.php?error=1&code=RegistrationCompleted');
        //do_redirect("{$baseWeb}/premium.php", $LANG[RegistrationCompleted]);
        
        // cross domain login
        $login_session_id = md5($input[username].microtime());
        $auto_login = $input[autologin] == 1 ? 1 : 0;
        $db->setQuery("insert into session_cookies values ('{$login_session_id}','{$input["username"]}','{$input["password"]}', '1')");
    	$db->query();
    	
		if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
			header("location: http://www.xun6.net/login.php?session_id={$login_session_id}&register=1");
			exit;
		}
	    else {
			header("location: http://www.xun6.com/login.php?session_id={$login_session_id}");
			exit;
		}
        
        
    	//header('location:'.$baseWeb.'/members.php');
    }
}

if ($user->logined==0) {
	if($input[act]!='reg'||!IS_POST||strlen($information))
	{
		# show register form
		$template->assign_block_vars('RegsiterForm', array());
	}
$registerPage=1;
$template->assign_var('registerpage',$registerPage);

$template->set_filenames(array(
	'body' => 'register.html')
	);
$template->assign_vars(array(
    'user'=>$input[username],
    'email'=>$input[email],
    'password'=>$input[password],
    'password2'=>$input[password2],
    'L_RegOk'=>str_replace('{email}',$input[email],$LANG[RegOk]),
    'information'=>$information,
    ));
    
require_once("header.php");
$template->pparse('body');
include "footer.php";
}
else {
	header('location:'.$baseWeb.'/members.php');
}


?>
