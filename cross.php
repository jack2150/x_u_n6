<?
error_reporting(0);
@set_time_limit(0);
require_once('includes/function.php');
# parse input
$input=parse_incoming();

if($input[redir]==1)
{
    $upload_errors = $input[error];
	$upload_error_type = $input[error_type];
    echo "<html><head><meta http-equiv='content-type' content='text/html; charset=UTF-8'><title>Uploading....</title></head><body><div id=upload_errors>$upload_errors</div><script>";
    # show error
    if($input[error])
    {
        echo "parent.showDownloadErrors('$upload_error_type');";
    }
    else 
    {
        echo "parent.submitEmailForm();";
    }
    echo "</script></body></html>";
	exit;
}

if(isset($input[url])) $url=$input[url];
if(isset($input[phpuploadscript])) $url=$input[phpuploadscript];

$myquery = buildGETQuery($input);

flush();
if(function_exists('curl_init'))
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.'?'.$myquery.'IP_CGI='.$_SERVER['REMOTE_ADDR']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_exec($ch);
    curl_close($ch);
}
elseif(ini_get('allow_url_fopen'))
{
    echo file_get_contents( $url.'?'.$myquery.'IP_CGI='.$_SERVER['REMOTE_ADDR'] );
}
?>
