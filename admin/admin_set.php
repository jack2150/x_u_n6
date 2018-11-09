<?php
/*************************************/
/*                                   */
/*  MFHS Version : 1.2               */
/*  Dezended & Nulled by: LepeNU     */
/*  Release Date: 1/12/2007          */
/*                                   */
/*************************************/

  function showsetform ()
  {
    global $baseUrl;
    global $input;
    global $output;
    global $db;
    global $Groups;
    global $SET;
    echo "<form name=myform action=\"?\" method=\"POST\">
    <input type=\"hidden\" name=\"act\" value=\"update\">
    <input type=\"hidden\" name=\"admin\" value=\"set\">
    {$output}
    <table width='80%' class=adminlist cellspacing='0' cellpadding='5' align='center' border='0'>
    <tr>
    <th align=\"center\" colspan='4'>Site Config&nbsp;<input type=\"submit\" name=\"update\" value=\"Quick Save\"></th>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Site Name<br><span class=note><a href=\"?admin=lang\">You can set sitename for different language</a></span></td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=\"text\" name=\"tbl_sitename\" value=\"{$SET['sitename']}\"></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Language</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_language\" style='width:180px'>{$SET['language']}</select></td>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Site Offline? </td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_site_offline\" style='width:180px'>{$SET['site_offline']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Admin Email </td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=\"text\" name=\"tbl_adminemail\" value=\"{$SET['adminemail']}\"></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Notify me when a report is submitted?</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_notifyreport\" style='width:180px'>{$SET['notifyreport']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Multiple Server</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_server\" style='width:180px'>{$SET['server']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Need validation on register?</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_validate\" style='width:180px'>{$SET['validate']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Show site stats on index page</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_sitestats\" style='width:180px'>{$SET['sitestats']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Site Skin</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_skin_dir\" style='width:180px'>{$SET['skin_dir']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Cache Template?</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_cached\" style='width:180px'>{$SET['cached']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Allow PHP in Template?</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_allow_php\" style='width:180px'>{$SET['allow_php']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Download Url?</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_static_url\" style='width:180px'><option value='dyn'>download.php?id=XXX</option><option value='short'>?d=XXX</option><option value='seo'>file/filename_id.html</option></select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Charset of Admin panel</td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=\"text\" name=\"tbl_admin_charset\" value=\"{$SET['admin_charset']}\"></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Enable Debug</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_debug\" style='width:180px'>{$SET['debug']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Show options of upload field</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_show_options\" style='width:180px'>{$SET['show_options']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Use flash progress bar</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_use_flash_progress\" style='width:180px'>{$SET['use_flash_progress']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Enable captcha for contact us form</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_contact_captcha\" style='width:180px'>{$SET['contact_captcha']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Enable captcha for report form</td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_report_captcha\" style='width:180px'>{$SET['report_captcha']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Static language page</td>
    <td class='tdrow2'  width='75%' colspan=3 valign='middle'><select name=\"tbl_lang_page\" style='width:180px'><option value='static'>Static</option><option value='dyn'>Dynamical</option></select><span class=note>'Static' will use http://yoursite.com/en/ for english site page, otherwise http://yoursite.com/?setlang=en will be used!</span></td>
    </tr>
    <tr>
    <th colspan=4 align=center><b>Server Monitor</b>&nbsp;<input type=\"submit\" name=\"update\" value=\"Quick Save\"></th>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%' valign='middle'>Server monitor emails<br><span class=note>multiple emails seprated by ','</span></td>
    <td class='tdrow2'  width='75%' colspan=3 valign='middle'><input type=text name='tbl_servermoniter_emails' value='{$SET['servermoniter_emails']}' size=60><br><a href='?admin=server'>Edit server setting for max space and warn space!</a></td>
    </tr>
    <tr>
    <th colspan=4 align=center><b>Cronjob Setting</b>&nbsp;<input type=\"submit\" name=\"update\" value=\"Quick Save\"></th>
    </tr>";
    $db->setquery( "select * from groups" );
    $db->query( );
    $Groups = $db->loadrowlist( );

    foreach ( $Groups as $group )
    {
        $group[cron_enabled] = $group[cron_enabled] == 1 ? "checked" : "";
        echo "<tr>
        <td class='tdrow1' valign='middle'><a href=\"?admin=group&act=edit&id={$group['id']}\">{$group['name']}</a></td>
        <td class='tdrow1' colspan=2>Delete file downloads less than<input type=\"text\" name=\"cron_views[{$group['id']}]\" size=8 value=\"{$group['cron_views']}\"> hits,last download at<input type=\"text\" name=\"cron_days[{$group['id']}]\" value=\"{$group['cron_days']}\" size=8> days ago</td>
        <td class='tdrow1'><input type=checkbox name=cron_enabled[{$group['id']}] value=\"1\" {$group['cron_enabled']}>Enabled?</td>
        </tr>";
    }
    echo "<tr>
    <th colspan=4 align=center><b>Download Setting</b>&nbsp;<input type=\"submit\" name=\"update\" value=\"Quick Save\"></th>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Download code<br><span class=note>A private key to encrypt download url</span></td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=text name=tbl_download_code value=\"{$SET['download_code']}\"></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Check download IP<br><span class=note>Validate IP of downloader</span></td>
    <td class='tdrow2'  width='25%'  valign='middle'><select name=\"tbl_check_download_ip\" style='width:180px'>{$SET['check_download_ip']}</select></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Allow download via proxy</td><td class='tdrow2'  width='25%'  valign='middle'><select name=tbl_allow_proxy style='width:180px'>{$SET['allow_proxy']}</select></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Prefix string of download file?<br><span class=note>The string will be added before download filename</span></td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=text name=tbl_download_prefix value=\"{$SET['download_prefix']}\"></td>
    </tr><!--
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Cache download sessions?</td>
    <td class='tdrow2'  width='75%' colspan=3 valign='middle'><select name=tbl_cache_sessions style='width:180px'>{$SET['cache_sessions']}</select>If 'no' is choosen, download stats will be updated instantly!</td>
    </tr>-->
    <tr>
    <th align=\"center\" colspan='4'>Page Infomation&nbsp;<input type=\"submit\" name=\"update\" value=\"Quick Save\"></th>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Site Keywords</td><td class='tdrow2'  width='75%'  valign='middle' colspan=3><textarea cols=60 rows=5 name=\"tbl_site_keyword\">{$SET['site_keyword']}</textarea><span class=note><br><a href=\"?admin=lang\">You can set site keywords for different language</a></span></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Site Description</td><td class='tdrow2'  width='75%'  valign='middle' colspan=3><textarea cols=60 rows=5 name=\"tbl_site_descr\">{$SET['site_descr']}</textarea><span class=note><br><a href=\"?admin=lang\">You can set site description for different language</a></span></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Email Signature</td><td class='tdrow2'  width='75%'  valign='middle' colspan=3><textarea cols=60 rows=5 name=\"tbl_emailsign\">{$SET['emailsign']}</textarea><span class=note><br><a href=\"?admin=lang\">You can set email signature for different language</a></span></td>
    </tr>
    <tr>
    <th class='tdrow1'  width='100%'  align='center' colspan=4><b>Banned IP</b> <input type=\"submit\" name=\"update\" value=\"Quick Save\"></th></tr><tr><td class='tdrow1'  width='25%'  valign='middle'>Banned IP<br>(use * denote any digit,127.*.0.1 will match 127.0.0.1,can not match 127.11.0.1 )</td><td class='tdrow2'  width='75%'  valign='middle' colspan=3><textarea cols=60 rows=5 name=\"tbl_banip\">{$SET['banip']}</textarea></td>
    </tr>
    </table>
    </form>
    <form name=myform2 action=\"?\" method=\"POST\">
    <input type=\"hidden\" name=\"act\" value=\"chpass\">
    <input type=\"hidden\" name=\"admin\" value=\"set\">
    <table class=adminlist cellspacing='0' cellpadding='5' align='center' border='0'>
    <tr>
    <th align=\"center\" colspan='4'>Login setting</th>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Admin username</td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=text name='admin_name' value=\"{$SET['admin']}\"></td>
    <td class='tdrow1'  width='25%'  valign='middle'>Max allowed failed login times</td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=text name='max_failtimes' value=\"{$SET['max_failtimes']}\"></td>
    </tr>
    <tr>
    <td class='tdrow1'  width='25%'  valign='middle'>Old Password</td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=password name=\"oldpass\"></td>
    <td class='tdrow1'  width='25%'  valign='middle'>New Password </td>
    <td class='tdrow2'  width='25%'  valign='middle'><input type=text name=\"newpass\"></td>
    </tr>
    <tr>
    <td class='tdrow1' align='center' valign='middle' colspan=4><input type=\"submit\" name=\"update\" value=\"Update\">
    </tr>
    </table>
    </form>
    <script>
	var s=document.myform.tbl_static_url.options;
	for(i=0;i<s.length;i++){   
		if(s[i].value=='{$SET['static_url']}') { 
			s[i].selected='true';     
			break;   
		}
	}

	var s=document.myform.tbl_lang_page.options;
	for(i=0;i<s.length;i++){
		if(s[i].value=='{$SET['lang_page']}') {     
			s[i].selected='true';     
			break;   
		}
	}</script>";
}

if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
$act = $input[act];
switch ( $act )
{
    case "chpass" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        if ( isset( $input[update] ) )
        {
            $db->setquery( "select * from setting where set_id=1" );
            $db->query( );
            $SET = $db->loadrow( );
            if ( strlen( $input[oldpass] ) || strlen( $input[newpass] ) )
            {
                if ( md5( md5( $input[oldpass] ).$SET[pass_salt] ) == $SET[pass] )
                {
                    $newpass = md5( md5( $input[newpass] ).$SET[pass_salt] );
                    $db->setquery( "update setting set pass='{$newpass}' where set_id=1" );
                    $db->query( );
                    $output = "Update Successfully!";
                }
                else
                {
                    $output = "Password is not correct!";
                }
            }
            $db->setquery( "update setting set admin='".$db->getescaped( $input[admin_name] )."',max_failtimes='".$db->getescaped( $input[max_failtimes] )."' where set_id=1" );
            $db->query( );
            if ( !isset( $output ) )
            {
                $output = "Update Successfully!";
            }
            redirect( "admin=set", $output );
        }
        break;
    case "update" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        if ( isset( $input[update] ) && $_POST )
        {
            foreach ( $input[cron_days] as $gid => $days )
            {
                $editgroup = new table( $db, "groups", "id" );
                $editgroup->id = $gid;
                $editgroup->cron_days = $days;
                $editgroup->cron_views = $input[cron_views][$gid];
                $editgroup->cron_enabled = intval( $input[cron_enabled][$gid] );
                $editgroup->update( );
            }
            $db->setquery( "update setting set set_id=1 limit 1" );
            $db->query( );
            $editset = new table( $db, "setting", "set_id" );
            $editset->inputdata( );
            $editset->set_id = 1;
            $editset->banip = remove_blank( $editset->banip );
            $editset->update( );
            $output = "Update Successfully!";
            redirect( "admin=set", $output );
        }
        break;
    case "banip" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        if ( isset( $input[ip] ) )
        {
            $db->setquery( "select banip from setting" );
            $db->query( );
            $row = $db->loadrow( );
            $banip = remove_blank( $row[banip].",{$input['ip']}" );
            $db->setquery( "update setting set banip='{$banip}'" );
            $db->query( );
            $output = "Update Successfully!";
        }
        break;

    default:
      $db->setQuery ('select * from setting limit 1 ');
      $db->query ();
      $SET = $db->loadRow ();
      $enabled = '<option value=1 selected>enabled</option><option value=0>disabled</option>';
      $disabled = '<option value=1 >enabled</option><option value=0 selected>disabled</option>';
      $yes = '<option value=1 selected\'>yes</option><option value=0>no</option>';
      $no = '<option value=1 >yes</option><option value=0 selected>no</option>';
      $SET[notifyreport] = ($SET[notifyreport] == 1 ? $enabled : $disabled);
      $SET[validate] = ($SET[validate] == 1 ? $enabled : $disabled);
        $SET[sitestats] = $SET[sitestats] == 1 ? $enabled : $disabled;
      $SET[cached] = ($SET[cached] == 1 ? $yes : $no);
      $SET[delete_guest_files_checked] = ($SET[delete_guest_files] == 1 ? ' checked' : '');
      $SET[site_offline] = ($SET[site_offline] == 1 ? $yes : $no);
        $SET[allow_php] = $SET[allow_php] == 1 ? $yes : $no;
        $SET[debug] = $SET[debug] == "yes" ? $yes : $no;
        $SET[allow_proxy] = $SET[allow_proxy] == 1 ? $yes : $no;
        $SET[check_download_ip] = $SET[check_download_ip] == 1 ? $yes : $no;
        $SET[show_featured_files] = $SET[show_featured_files] == 1 ? $yes : $no;
        $SET[show_options] = $SET[show_options] == "1" ? $yes : $no;
        $SET[report_captcha] = $SET[report_captcha] == "1" ? $yes : $no;
        $SET[contact_captcha] = $SET[contact_captcha] == "1" ? $yes : $no;
        $SET[use_flash_progress] = $SET[use_flash_progress] == "1" ? $yes : $no;
        $SET[cache_sessions] = $SET[cache_sessions] == "1" ? $yes : $no;
      $lang = $SET[language];
      $skin = $SET[skin_dir];
      $dir = opendir ('../language');
        $SET[language] = "<option value=-1>--Auto Detect--</option>";
        while ( $file = readdir( $dir ) )
        {
            if ( $file != "." && $file != ".." && is_dir( "../language/".$file ) )
            {
                $SET[language] .= "<option value=".$file.( $lang == $file ? " selected" : "" ).">".$file."</option>";
            }
        }
        $dir = opendir( "../skin" );
        $SET[skin_dir] = "";
        while ( $file = readdir( $dir ) )
        {
            if ( $file != "." && $file != ".." && is_dir( "../skin/".$file ) )
            {
                $SET[skin_dir] .= "<option value=".$file.( $skin == $file ? " selected" : "" ).">".$file."</option>";
            }
        }
        $db->setquery( "select * from server where enabled=1" );
        $db->query( );
        $servers = $db->loadrowlist( );
        $serverlist = "<option value=0 ".( $SET[server] == 0 ? " selected" : "" ).">--Random Select--</option>";
        $serverlist .= "<option value=-2 ".( $SET[server] == -2 ? " selected" : "" ).">--Host package Based--</option>";
        foreach ( $servers as $server )
        {
            if ( $SET[server] == $server[server_id] )
            {
                $serverlist .= "<option value=".$server[server_id]." selected>".$server[name]."</option>";
            }
            else
            {
                $serverlist .= "<option value=".$server[server_id].">".$server[name]."</option>";
            }
        }
        $SET[server] = $serverlist;
        showsetform( );
}
?>
