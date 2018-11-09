<?
define("IN_PAGE",'REPORT');
define("PAGE_TITLE",'SITEREPORT');

include "includes/inc.php";

$baseUrl='report.php?';

$template->assign_var('report_captcha',$user->setting[report_captcha]);

# loading file
$upload_id = preg_replace('/[^A-Z0-9]/','',strtoupper($input[id]));
$db->setQuery("select * from files where upload_id='$upload_id' limit 1");
$db->query();
$filerow = $db->loadRow();

# check file exists
if(!$filerow)
{
    $input[report_error] = $LANG[ReportFileNoFound];
}

# processing contact captcha checking
$fail_captcha=1;
if(IS_POST&&isset($input[done])&&$filerow)
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
        $input[report_error] = $LANG[CaptchaError];
        $template->assign_vars($input);
    }
}
# processing request
if(IS_POST&&isset($input[done])&&!$fail_captcha)
{
	if(!$input[tbl_fullname]||!$input[tbl_email]||!$input[tbl_problems]||!$input[tbl_details]||!$input[id]) {
		$input[report_error] = $LANG[ReportError];
        $template->assign_vars($input);
	}
	else {
		$report = new TABLE($db,'reports','report_id');
		$report->inputData();
		$report->file_id=$filerow[id];
		$report->upload_id=$filerow[upload_id];
		$report->IP=$input[IP_CLIENT];
		$report->date=time();
		$report->insert();
	
		/**
		* send a email to notify admin
		*/
		if($user->setting[notifyreport])
		{
			$email->template->set_filenames(array(
				'email' => 'report_notify.html')
			);
			
			$email->template->assign_vars(array(
				'REPORTER'=>$report->fullname,
				'FILEURL'=>$baseWeb.'/file/'.strtolower($input['id']).'/'.base64_decode($filerow['name']).".html",
				'DETAILS'=>$report->details,
				'IP'=>$report->IP,
				'EMAIL_SIG'=>$user->setting[emailsign],
			));
			
			$email->subject($report->problems);
			$email->to($user->setting[adminemail],$LANG["CustomerSupport"]);
			$email->from($report->email);
			$email->replyto($report->email);
	
			$email->send('email');
		}
		//$template->assign_var('report_sent',1);
		do_redirect($baseWeb."/file/".$input["id"]."/",$LANG["ReportReceived"]);
	}
}
else
{
	$template->assign_var('report_sent',0);
	$template->assign_vars($input);
}
$ReportExplanation=str_replace(array('{baseWeb}','{SITENAME}'),array($baseWeb,$user->setting[sitename]),$LANG[ReportExplanation]);
$template->assign_var('L_ReportExplanation',$ReportExplanation);

$reportPage=1;
$template->assign_var('reportpage',$reportPage);

# output template
require_once("header.php");
$template->set_filenames(array(
	'body' => 'report.html')
	);
$template->pparse('body');
include "footer.php";
?>
