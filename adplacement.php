<?
define("IN_PAGE",'adplacementPage');
define("PAGE_TITLE",'adplacementPage');

include "includes/inc.php";

$adplacementPage = 1;
$template->assign_var('adplacementPage', $adplacementPage);

require_once("header.php");

$template->set_filenames(array(
	'body' => 'adplacement.html')
	);
$template->pparse('body');

include "footer.php";

?>
