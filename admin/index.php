<?php

error_reporting (E_ERROR | E_WARNING | E_PARSE ); 
define('IN_ADMIN',1); 
$GLOBALS2 =$GLOBALS;
unset ($GLOBALS);
include_once('config.php');
$input[admin] = $_POST[admin]?$_POST[admin]:$_GET[admin]; 
$input[act] = $_POST[act]?$_POST[act]:$_GET[act];
unset($server_array); unset($architect);	include "../includes/inc2.php";  

				$baseUrl='index.php?'; $admin=$input[admin]; $act=$input[act]; 
				if($_SESSION[admin_logined]==0) { header('location:login.php?admin='.$admin); exit; } 
				else { $nowtime=time(); $timeout=3600; $timediff=$nowtime-$_SESSION['admin_last_click']; 
				$db->setQuery("update setting set admin_lastclick='$nowtime',admin_ip='$input[IP_CLIENT]'"); 
				$db->query(); if($timediff>$timeout) { $_SESSION[admin_logined]=0; header('location:login.php?admin='.$admin); exit; } 
				$_SESSION[admin_last_click]=time(); }	 
				if($input[admin]=='user'&&$input[act]=='login') { $db->setQuery("select * from users where id='$input[id]'"); 
				$db->query(); $row=$db->loadRow(); if($row) { include "../includes/user.class.php"; $user=new user(); 
				$status=$user->login($db->getEscaped($row[user]),$db->getEscaped($row[pass])); 
				if($status==4) { $output="Password error!"; } 
				elseif($status==0) { $output="Account is Not Activate"; } 
				elseif($status==-1) { $output="Account is Suspended"; } 
				else { redirect($baseWeb.'/members.php','Logined Successfully!'); } } } 
				
				if($input[admin]=='lang') { $lang_name=array('arabic','bulgarian', 'catalan','czech','danish','german', 'english', 'estonian','finnish','french','greek','spanish_argentina', 'spanish', 'gaelic','galego','gujarati','hebrew','hindi','croatian', 'hungarian','icelandic','indonesian','italian','japanese', 'korean', 'latvian','lithuanian','macedonian','dutch', 'norwegian','punjabi', 'polish','portuguese_brazil','portuguese','romanian','russian','slovenian', 'albanian','serbian','slovak','swedish','thai','turkish','ukranian','urdu', 'viatnamese', 'chinese_traditional_taiwan','chinese_simplified'); 
				$LANG_NAME= array_keys($LANG_TO_MATCH); 
				if(!in_array($input[loadlang],$LANG_NAME)) $input[loadlang] = 'english'; 
				$input[langfile]='../language/'.$input[loadlang].'/lang.php'; 
				$org_langfile='../language/'.$input[loadlang].'/lang.org.php';  unset($LANG); 
				require_once($input[langfile]); $LANGDATA=$LANG; unset($LANG);  
				require_once($org_langfile); $ORGLANGDATA=$LANG; foreach($LANGDATA as $k=>$v) $LANG[$k]=$LANGDATA[$k]; 
				define('CHARSET',$LANG[Charset]); } define('CHARSET',$SET[admin_charset]);  
				$admin_choices = array( 'set' => 'admin_set', 'server' => 'admin_server', 'group' => 'admin_group','user' => 'admin_user', 
				'images' => 'admin_img', 'report' => 'admin_report', 'menu' => 'admin_menus', 'body' => 'admin_body', 'pin' => 'admin_pin', 'premiums' => 'admin_premiums',
				'copyright' => 'delete_copyright', 'random_new' => 'random_news' ); 
				if($admin=='logout') { $_SESSION[admin_logined]=0; redirect('index.php','Logout Successfully!'); } 
				if(!in_array($admin,array_keys($admin_choices))) $admin = 'body';

?>
<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Administration</title>
<script LANGUAGE="JavaScript" SRC="js/prototype-1.6.0.2.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="js/admin_function.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="css/Theme/JSCookMenu.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="css/Theme/theme.js"></SCRIPT>
<script LANGUAGE="JavaScript" SRC="css/admin.js"></SCRIPT>
<LINK REL="stylesheet" HREF="css/Theme/theme.css" TYPE="text/css">
<link href="css/template_css.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="wrapper">
	<div id="header">
	   <div id="mfhs"><img src="images/header_text.gif" /></div>
	</div>
</div>
<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
  <tr>
	<td class="menubackgr">
		<div id="myMenuID"></div>
		<script language="JavaScript" type="text/javascript">
		var myMenu =
		[
			[null,'Home','index.php',null,'Control Panel'],
			_cmSplit,
			[null,'Site',null,null,'Site Management',
				['<img src="css/Theme/config.png" />','Configuration','?admin=set',null,'Configuration'],
			],
			_cmSplit,
				[null,'Waiting','?admin=images&img_filter=waiting&search=1',null,'Waiting Files'],
			_cmSplit,
				[null,'Top Download','?admin=images&img_filter=waiting&img_orderby=fs.dls&img_AD=DESC&search=1',null,'Top Download Waiting'],
			_cmSplit,
				[null,'Sort Size','?admin=images&img_filter=waiting&img_orderby=f.size&img_AD=DESC&search=1',null,'Top Download Waiting'],
			_cmSplit,
			  [null,'Files',null,null,'Files Management',
				  ['<img src="css/Theme/media.png" />','Files','?admin=images&search=1',null,'List/Search/Delete Files',
					['<img src="css/Theme/media.png" />','Waiting Files','?admin=images&img_filter=waiting&search=1',null,'List/Search/Waiting Files'],
					['<img src="css/Theme/media.png" />','Validated Files','?admin=images&img_filter=validated&search=1',null,'List/Search/Validated Files'],
					['<img src="css/Theme/media.png" />','Unvalidate Files','?admin=images&img_filter=validate&search=1',null,'List/Search/Unvalidated Files'],
					['<img src="css/Theme/media.png" />','Deleted Files','?admin=images&img_filter=deleted&search=1',null,'List/Search/Delete Files'],
					['<img src="css/Theme/media.png" />','Top Downloads Files','?admin=images&img_orderby=fs.dls&img_AD=DESC&search=1',null,'List/Search/Delete Files'],
				],
				['<img src="css/Theme/report.png" />','Reports','?admin=report',null,'Process Report'],
			  ],
			_cmSplit,
			  [null,'Package',null,null,'Host Package',
				['<img src="css/Theme/package.png" />', 'List Package', '?admin=group', null,'Add/Edit/Delete Package'],
			],
			_cmSplit,
			  [null,'Members',null,null,'Members Management',
				['<img src="css/Theme/users.png" />', 'List Members', '?admin=user', null,'Search/Edit/Add Members'],
				['<img src="css/Theme/users_add.png" />', 'Add Members', '?admin=user&act=add', null,'Suspended Members'],
			],
			_cmSplit,
			  [null,'Servers','?admin=server',null,'Servers Management'],
			_cmSplit,
			  [null,'Premiums','?admin=premium',null,'Servers Management'],
			_cmSplit,
			  [null,'PIN','?admin=pin','','Servers Management',
			  ['', 'Show PIN', '?admin=pin', null,'Suspended Members'],
			  ['', 'Generate PIN', '?admin=pin&act=generate_pin_form', null,'Search/Edit/Add Members'],
			  
			],
			_cmSplit,
			  [null,'Revenues','?admin=revenues',null,'Servers Management'],
			_cmSplit,
				[null,'Delete','?admin=copyright',null,'Del Copyright'],
			_cmSplit,
				[null,'News','?admin=random_new',null,'Random News'],
		];
		cmDraw ('myMenuID', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');
		</script>
</td>
<td class="menubackgr" align="right">
<? $last_login = intval($_SESSION[last_login]); $last_logined=$last_login==0?"Never":date("m/d/Y H:i",$last_login); ?>
<div>(<b>Last Logined: <?=$last_logined?></b>) <a href="<?=$baseWeb?>">Homepage</a> <a href="?admin=logout"><img src="images/users.png" style="VERTICAL-ALIGN:middle" alt="Logout" border=0/>Logout</a> </div>

</td>
</tr>
</table>
<br />

<div align="center">
<div class="main">
<table width="100%" border="0">
  <tr>
	<td valign="middle" width=100% align="left">

<!--begin the content-->
<? include $admin_choices[$admin].'.php'; ?>
<!--end the content-->
</td>
  </tr>
</table>
<? if($db->allSql1){ ?>
<div align=left class="quote"><?=str_replace("\n",'<br>',$db->allSql)?></div>
<? } ?>
</div>
<div id="footer" align=center>[ XUN6 V0.2 CONTROL PANEL ]
</div>

</div>
</body>
</html>
<?php 
?>