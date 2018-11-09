<?

include "includes/inc.php";


$otherPage=1;
$template->assign_var('otherpage',$otherPage);
if($_GET[error]==1)
{
    if(substr($input[code],0,3)=='DL_')
    {
    	define("PAGE_TITLE",'SEC_SITEERROR');
        if($input[code]=='DL_FileNotFound')
        {
            do_redirect($baseWeb,isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
        }
        else
        {
        	if ($_GET["fid"]) {
        		if ($_GET["name"]) {
            		do_redirect($baseWeb.'/file/'.$_GET["fid"].'/'.$_GET["name"],isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
        		}
        		else {
        			do_redirect($baseWeb.'/file/'.$_GET["fid"].'/',isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
        		}
        	}
        	else {
        		do_redirect($baseWeb,isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
        	}
        }
    }
    elseif ($input[code]=='LogoutMessage') {
    	define("PAGE_TITLE",'MLogout');
		do_redirect($baseWeb,isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
    }
    elseif ($input[code]=='RegistrationCompleted') {
    	define("PAGE_TITLE",'RegistrationCompleted');
    	do_redirect($baseWeb."/members.php",isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
    }
    elseif ($input[code]) {
    	define("PAGE_TITLE",$input[code]);
    	do_redirect($baseWeb."/members.php",isset($LANG[$input[code]])?$LANG[$input[code]]:$input[code]);
    }
    
    @$db->close_db();
    exit;
}
elseif ($_GET[edit]==1) {
	do_redirect($baseWeb."/".$input[page].".php", $LANG[$input[code]]);
}
else {
	header("location:".$baseWeb);
}
?>