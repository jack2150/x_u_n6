<?
define("IN_PAGE",'LINKTO');
define("PAGE_TITLE",'LINKTOUS');

include "includes/inc.php";

$otherPage=1;
$template->assign_var('otherpage',$otherPage);

require_once("header.php");

$template->set_filenames(array(
	'body' => 'links.html')
	);
$template->pparse('body');

include "footer.php";
?>
