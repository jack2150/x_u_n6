<?
$template->set_filenames(array(
	'header' => 'header.html')
	);
$page_title = (IN_PAGE=='INDEX'?$user->setting[sitename]:'').$LANG[PAGE_TITLE];

# assign some global vars
$template->assign_vars(array(
    # global var
    'baseWeb'          => $baseWeb,
    'langWeb'          => $langWeb,
    'PAGETILE'         => $page_title,
    'SITENAME'         => $LANG[SiteName] ? $LANG[SiteName] : $user->setting[sitename],
    'SKIN_DIR'         => $baseWeb.'/skin/'.$user->setting[skin_dir],
	'cdnWeb'           => $cdnWeb,
    'SITE_KEYWORD'     => $LANG[SiteKeyword] ? $LANG[SiteKeyword] : $user->setting[site_keyword],
    'SITE_DESCR'       => $LANG[SiteDescr] ? $LANG[SiteDescr] : $user->setting[site_descr],
    'username'         => $user->username,
    'debug'            => $debug ? 100 : 0,
    # logined status
    'not_logined'      => $user->logined!=1,
    'logined'          => $user->logined==1,
    # load js script
    'load_prototype'   => IN_PAGE=='INDEX'||IN_PAGE=='MEMBERS'||IN_PAGE=='BATCH',
    'load_uploadjs'    => $load_uploadjs,
	'loginfix' => substr(md5(rand(9999,99999)),0,6) ,
    ));

if ($dh = opendir("language/")) {
   while (($file = readdir($dh)) !== false)
   {
      if ($file != '..' && $file != '.'&& strlen($LANG_TO_CODE[$file]) && is_dir("language/".$file))
      {
          $template->assign_block_vars('flag_icon',array('code'=>$LANG_TO_CODE[$file],'url'=>$user->setting[lang_page]=='static'?$baseWeb.'/'.$LANG_TO_CODE[$file]:'?setlang='.$LANG_TO_CODE[$file]));
      }
   }
   closedir($dh);
}
if($AJAX!=1) $template->pparse('header');
?>
