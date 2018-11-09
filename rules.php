<?
define("IN_PAGE",'RULES');
define("PAGE_TITLE",'SITERULES');

include "includes/inc.php";

$rulePage=1;
$template->assign_var('rulepage',$rulePage);

require_once("header.php");

$template->set_filenames(array(
	'body' => 'rules.html')
	);
$template->pparse('body');

include "footer.php";

?>
