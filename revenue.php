<?php
/**
 * Revenues script contain 3 sections
 * 1. show daily or monlty earnings report
 * 2. show payments history and payment details
 * 3. edit payment and payee information
 */

define("IN_PAGE",'REVENUES');

include "includes/inc.php";

define("PAGE_TITLE","Revenue_Program");

$template->assign_var('revenuepromotepage',1);

// output template
require_once("header.php");
$template->set_filenames
(array("body" => "revenue_detail.html"));
$template->pparse('body');
include "footer.php";
?>