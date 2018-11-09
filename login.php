<?
//header("location: index.php");

define("IN_PAGE",'LOGIN');
define("PAGE_TITLE",'SITELOGIN');

include "includes/inc.php";

$baseUrl='login.php?';

// if user already login
if($user->uid) header('location:index.php');


// if redirect include session_id
if ($_GET["session_id"]) {
	$db->setQuery("select * from session_cookies where session_id = '{$_GET["session_id"]}' limit 1");
    $db->query();
    
    if ($db->getNumRows()) {
    	$temp_login = $db->loadRow();
    	$user->login($temp_login["username"],$temp_login["password"],$temp_login["auto_login"]);
    	
    	// remove session cookies
    	$db->setQuery("delete from session_cookies where session_id = '{$_GET["session_id"]}' limit 1");
    	$db->query();
    	
    	unset($temp_login);
    	
    	// redirect to original member page
        if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
        	if ($input["register"]) {
        		header("location: http://www.xun6.net/premium.php");
        	}
        	else {
				header("location: http://www.xun6.net/members.php");
        	}
		}
		else {
			if (preg_match("/\bxun6.net\b/i", $_SERVER["SERVER_NAME"])) {
				if ($input["register"]) {
	        		header("location: http://www.xun6.com/premium.php");
	        	}
	        	else {
					header("location: http://www.xun6.com/members.php");
	        	}
			}
			else {
				header("location: http://www.xun6.com/members.php");
			}
		}
    	exit;
    }
}

/**
* process the login action
*/
if($input[act]=='login'&&IS_POST)
{
	// trim
	$input[user] = trim($input[user]);
	$input[pass] = trim($input[pass]);

    $status=$user->login($input[user],$input[pass],$input[autologin]==1);
    if($status==4)
    {
        $error=$LANG[LoginErrUP];
    }
    elseif($status==0)
    {
        $error=$LANG[LoginErrNotActivate];
    }
    elseif($status==-1)
    {
        $error=$LANG[LoginErrSuspended];
    }
    else
    {
    	// success login, set cross domain cookie
    	// 1. redirect to .net or .com using header location
    	// 2. submit user data using post method
        $user->updateStats();
        
        // insert session cookies
        $login_session_id = md5($input[user].microtime());
        $auto_login = $input[autologin] == 1 ? 1 : 0;
        $db->setQuery("insert into session_cookies values ('{$login_session_id}','{$input["user"]}','{$input["pass"]}', '{$auto_login}')");
    	$db->query();
        
    	// check is .com or .net
    	if (preg_match("/\bxun6.com\b/i", $_SERVER["SERVER_NAME"])) {
			header("location: http://www.xun6.net/login.php?session_id={$login_session_id}");
		}
	    else {
			header("location: http://www.xun6.com/login.php?session_id={$login_session_id}");
		}
		
        //header('location:'.$baseWeb.'/members.php');
        exit;
    }

    $template->assign_var('error',$error);
    $template->assign_var('login_form',1);
}
/**
* process the validate action
*/
elseif($input[act]=='validate')
{
    $db->setQuery("select * from users where id='".$db->getEscaped($input[uid])."' and validate_code='".$db->getEscaped($input[code])."'");
    $db->query();
    if($db->getNumRows()==0)
    {
        $error = $LANG['NoValidateCodeFound'];
    }
    else
    {
        $row = $db->loadRow();
        $db->setQuery("update users set status =1 where id='".$db->getEscaped($input[uid])."'");
        $db->query();

        //do the login now!
        $status=$user->login($db->getEscaped($row[user]),$db->getEscaped($row[pass]));

        do_redirect($baseWeb.'/members.php',$LANG['ValidateIsOK']);
    }
    do_redirect($baseWeb.'/login.php?act=resend',$error);
}
/**
* process the get password action
*/
elseif($input[act]=='getpass')
{
    if(IS_POST)
    {
        $result=$user->getPassword($db->getEscaped($input[user]),$db->getEscaped($input[email]));
        if(is_array($result))
        {
            $email->template->set_filenames(array(
	        'email' => 'getlostpassword.html')
	        );
            $email->template->assign_vars(array(
            'baseWeb' =>$baseWeb,
            'SITENAME'=>$user->setting[sitename],
            'USERNAME'=>$result[user],
            'PASSWORD'=>$result[pass],
            'EMAIL_SIG'=>$user->setting[emailsign],
            ));

            $email->to($result[email],$result[user]);
            $email->from($user->setting[adminemail]);

            $email->send('email');

            $information2=$LANG[PasswordSent];
            //show login page
            $template->assign_var('login_form',0);
        }
        else
        {
            $template->assign_var('login_form',0);
            $template->assign_var('resend_form',0);
            $template->assign_var('error',$LANG[UserEmailError]);
        }
    }
    else
    {
        $template->assign_var('login_form',0);
        $template->assign_var('resend_form',0);
    }
}
/**
* process the get password action
*/
elseif($input[act]=='resend')
{
    if(IS_POST)
    {
        $result=$user->getPassword($db->getEscaped($input[user]),$db->getEscaped($input[email]));
        if(is_array($result))
        {
            $email->template->set_filenames(array(
	        'email' => 'send_code.html')
	        );
            $email->template->assign_vars(array(
            'baseWeb' =>$baseWeb,
            'SITENAME'=>$user->setting[sitename],
            'USERNAME'=>$result[user],
            'ValidateCode'=>$result[validate_code],
            'ACTIVATE_URL'=>"$baseWeb/login.php?&uid=$result[id]&act=validate&code=".$result[validate_code],
            'EMAIL_SIG'=>$user->setting[emailsign],
            ));

            $email->to($result[email],$result[user]);
            $email->from($user->setting[adminemail]);

            $email->send('email');

            $information=$LANG[ValidateCodeSent];
            //show login page
            $template->assign_var('login_form',1);
        }
        else
        {
            $template->assign_var('error',$LANG[UserEmailError]);
            $template->assign_var('login_form',0);
            $template->assign_var('resend_form',1);
        }
    }
    else
    {
        $template->assign_var('login_form',0);
        $template->assign_var('resend_form',1);
    }
}
/**
* show login form
*/
else
{
    $template->assign_var('login_form',1);
}

$loginPage=1;
$template->assign_var('loginpage',$loginPage);

require_once("header.php");
$template->set_filenames(array(
	'body' => 'login.html')
	);
$template->assign_vars(array(
'user'=>$input[user],
'email'=>$input[email],
'information'=>$information,
'information2'=>$information2
));
$template->pparse('body');
include "footer.php";
?>
