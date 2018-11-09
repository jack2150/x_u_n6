<?
define("IN_PAGE",'CONTACTUS');
define("PAGE_TITLE",'SITECONTACTUS');
include "includes/inc.php";

# show contact us form
$template->assign_var('email_sent',0);
$template->assign_var('contact_captcha',$user->setting[contact_captcha]);

# processing contact captcha checking
$fail_captcha=1;
if(IS_POST&&isset($input[done]))
{
    if($user->setting[contact_captcha])
    {
        $fail_captcha = ($_SESSION['authkey_expire']-time()>5*60||!$input[captchacode]||$input[captchacode]!=$_SESSION[authkey]);
    }
    else
    {
        $fail_captcha = 0;
    }
    # fails to check, refill vars for users
    if($fail_captcha)
    {
        $input[contact_error] = $LANG[CaptchaError];
        $template->assign_vars($input);
    }
}
# processing request
if(IS_POST&&isset($input[done])&&!$fail_captcha)
{
	if (!$input[subject] || !$input[text] || !$input[name] || !$input[email]) {
		$input[contact_error] = $LANG[ContactUsError];
		$template->assign_vars($input);
	}
	else {
		# prepare email template
		$email->template->set_filenames(array(
			'email' => 'contactus.html')
		);
		$email->template->assign_vars(array(
			'Message'=>$input[text],
			'SenderName'=>$input[name],
			'SenderIP'=>$input[IP_CLIENT],
			'EMAIL_SIG'=>$user->setting[emailsign],
		));

		$email->subject($input[subject]);
		$email->to($user->setting[adminemail],$LANG["CustomerSupport"]);
		$email->from($input[email]);
		$email->replyto($input[email]);
	
		# sending email
		$email->send('email');
	
		//$template->assign_var('email_sent',1);
		do_redirect($baseWeb,$LANG["ReportReceived"]);
	}
}

# output template
$contactPage=1;
$template->assign_var('contactpage',$contactPage);

require_once("header.php");
$template->set_filenames(array(
	'body' => 'contact.html')
	);
$template->pparse('body');
include "footer.php";
?>
