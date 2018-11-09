<?php
include "includes/inc.php";

switch ($input["act"]) {
	case "folderdelete":
		
		break;
	case "":
		break;
	case "":
		break;
	case "":
		break;
	case "":
		break;
	case "":
		break;
	default:
		break;
}

$otherPage=1;
$template->assign_var("otherpage",$otherPage);

require_once("header.php");
$template->set_filenames(array(
	'body' => 'confirm.html')
	);
$template->pparse('body');
include "footer.php";
?>