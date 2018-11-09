<?
$template->set_filenames(array(
	'footer' => 'footer.html')
	);
if($AJAX!=1) $template->pparse('footer');
@$db->close_db();
?>
