<?
define("IN_EMAILING",1);
include "includes/inc.php";

$uploader = new uploader($server_id);
$uploader->validate_session($user,$input[AccessKey],$input[UploadSession]);

if(1)
{
    $files = $uploader->getUploadIDs();
    $submits = $input[submitnums];
    if($debug)
    {
        print_r($input); print_r($uploader);
    }
    if(count($files)==0 && $submits<10)
    {
        $submits = $submits+1;
		echo "<script>parent.document.emailform.submitnums.value='$submits'; setTimeout('parent.document.emailform.submit();', 2000);</script>";		
		exit();
    }
}

/**
* show download links
*/
$count_files = 1;
foreach($files as $fileobj)
{
    $urls=getDownloadUrl(array('id'=>$fileobj[id],'name'=>base64_decode(strip_tags($fileobj[name])),'upload_id'=>strtolower($fileobj[upload_id]),'delete_id'=>$fileobj[delete_id]));

    $template->assign_block_vars('links', array(
    'downloadurl' => $urls[downloadurl],
    'deleteurl'   => $urls[deleteurl],
    'filename'    => base64_decode(strip_tags($fileobj[name])),
    'show_seperator'=> count($files) == $count_files ? "" : "<br />", 
    ));
    $count_files++;
}



/**
* email download links
*/
$email->template->set_filenames(array(
    'email'  => 'filedelivered.html',
    'email2' => 'filedelivery.html'
    ));

$uploaded_size = 0;
foreach($files as $file)
{
    $urls=getDownloadUrl(array('id'=>$file[id],'name'=>base64_decode(strip_tags($file[name])),'upload_id'=>$file[upload_id],'delete_id'=>$file[delete_id]));
    extract($urls);
    
    $uploaded_size += $file[size];
	
    $email->template->assign_block_vars('links', array(
        'filename'=>base64_decode(strip_tags($file[name])),
        'filesize'=>$file[size],
        'file_password'=>$file[password],
        'downloadurl'=>$downloadurl,
        'deleteurl'=>$deleteurl,
        'file_descr'=>$file[descr],
    ));
	if ($input[fromemail]) {
		$db->setQuery("UPDATE files SET from_email='$input[fromemail]' WHERE id='$file[id]'");
		$db->query();
	}
	if ($input[toemail]) {
		$db->setQuery("UPDATE files SET to_email='$input[toemail]' WHERE id='$file[id]'");
		$db->query();
	}
}

/**
* sending email
*/
$subject='=?UTF-8?B?'.base64_encode($LANG[FromEmailSubject1]." ".count($files)." ".$LANG[FromEmailSubject2]." (".$uploaded_size."Bytes)").'?=';
$email->template->assign_var("subject",$subject);
$email->template->assign_vars(array(
    'SITENAME'=>$user->setting[sitename],
    'uploaded_num'=>count($files),
    'uploaded_size'=>$uploaded_size,
    'EMAIL_SIG'=>$user->setting[emailsign],
    'from_email'=>$input[fromemail],
    ));

if(strlen($input[fromemail]))
{
	$email->to($input[fromemail],$input[fromemail]);
	$email->from($user->setting[adminemail]);
	$email->send('email');

	$subject='=?UTF-8?B?'.base64_encode($LANG[ToEmailSubject1]." ".count($files)." ".$LANG[ToEmailSubject2]." (".$uploaded_size."Bytes)").'?=';
	$email->template->assign_var("subject",$subject);
	
	$to_emails=split(',',$input[toemail]);
    foreach($to_emails as $to_email)
    {
        if($debug) echo  $to_email.'<br>';
        $email->to($to_email,$to_email);
        $email->from($input[fromemail]);
        $email->send('email2');
    }
}
/**
* output the results
*/
$template->assign_vars(array(
    'uploaded_num'   => count($files),
    'uploaded_size'  => $uploaded_size,
    ));
$template->set_filenames(array(
	'body'     => 'uploading.html',
));

$template->pparse('body');
if($debug)
{
    print_r($email);
    print_r($files);
    print_r($uploader);
}
?>
