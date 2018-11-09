<?php
function showlistform( )
{
    global $baseUrl;
    global $input;
    global $db;
    global $GROUPS;
    $per_num = 10;
    $input[s] = intval( $input[s] );
    $header = $header_ad = $header_img = array( );
    $db->setQuery( "select * from groups" );
    $db->query( );
    $GROUPS = $db->loadRowList( "id" );
    if ( $orderad != "ASC" && $orderad != "DESC" )
    {
        $orderad = "";
    }
    if ( isset( $input[search] ) )
    {
        $orderby = $_SESSION[user_orderby] = $input[user_orderby];
        $orderad = $_SESSION[user_AD] = $input[user_AD];
        $_SESSION[user_field] = $input[user_field];
        $_SESSION[user_func] = $input[user_func];
        $_SESSION[user_values] = $input[user_values];
        $_SESSION[user_field2] = $input[user_field2];
        $_SESSION[user_func2] = $input[user_func2];
        $_SESSION[user_values2] = $input[user_values2];
        $_SESSION[user_pages] = $input[user_pages];
        $_SESSION[user_status] = $input[user_status];
        $_SESSION[user_gid] = $input[user_gid];
    }
    $orderby = $_SESSION[user_orderby];
    $orderad = $_SESSION[user_AD];
    $per_num = intval( $_SESSION[user_pages] );
    if ( $per_num == 0 )
    {
        $_SESSION[user_pages] = $per_num = 10;
    }
    if ( $orderby )
    {
        $order = "ORDER BY $orderby $orderad ";
    }
    if ( ( $_SESSION[user_field] ) != "" && $_SESSION[user_func] != "" && $_SESSION[user_field] != "u.username" )
    {
        if ( $_SESSION[user_field] == "regdate" || $_SESSION[user_field] == "expire_date" )
        {
            $condition = "{$_SESSION['user_field']} {$_SESSION['user_func']} '".$db->getescaped( strtotime( $_SESSION[user_values]." 00:00:00" ) )."'";
        }
        else
        {
            $condition = "{$_SESSION['user_field']} {$_SESSION['user_func']} '".$db->getescaped( $_SESSION[user_values] )."'";
        }
    }
    if ( $_SESSION[user_field2] != "" && $_SESSION[user_func2] != "" && $_SESSION[user_field2] != "u.username" )
    {
        if ( $_SESSION[user_field2] == "regdate" || $_SESSION[user_field2] == "expire_date" )
        {
        $condition2 = "{$_SESSION['user_field2']} {$_SESSION['user_func2']} '".$db->getescaped( strtotime( $_SESSION[user_values2]." 00:00:00" ) )."'";
        }
        else
        {
            $condition2 = "{$_SESSION['user_field2']} {$_SESSION['user_func2']} '".$db->getescaped( $_SESSION[user_values2] )."'";   
        }
        $condition .= $condition ? " AND ".$condition2 : $condition2;
    }
    if ( is_numeric( $_SESSION[user_status] ) && $_SESSION[user_status] != 2 )
    {
        $condition .= $condition == "" ? " u.status='{$SESSION['user_status']}'" : " and u.status='{$SESSION['user_status']}'";
    }
    if ( is_numeric( $_SESSION[user_gid] ) && $_SESSION[user_gid] != 0 )
    {
        $condition .= $condition == "" ? " u.gid='{$_SESSION['user_gid']}'" : " and u.status='{$SESSION['user_gid']}'";
    }
    $condition .= $condition == "" ? "1" : "";
    $db->setQuery( "select count(*) as total from users as u where $condition" );
    $db->query( );
    $tmp = $db->loadRow( );
    $header[total] = $tmp[total];
    $db->setquery( "select u.id,u.user,u.custom,u.regdate,u.expire_date,u.status,u.gid, u.files,u.webspace,u.bandwidth,u.totaldownloads as downloads from users as u where {$condition} {$order} limit {$input['s']},{$per_num}" );
    $db->query( );
    $rows = $db->loadRowList( );
    $cur_page = $input[s] / $per_num;
    $info = array( "total"=>$tmp[total], "page"=>$per_num, "cur_page"=>$cur_page, "baseUrl"=>$baseUrl."&admin=user&$order_url" );
    $pageLinks = buildpagelinks( $info );
    showuserth( $header );
    foreach ( $rows as $row )
    {
        $GROUP = $GROUPS[$row[gid]];
        $row[name] = $GROUP[name];
        $row[subscr_fee] = $GROUP[subscr_fee];
        $data = $row;
        $data[id] = $row[id];
        $time = time( );
        $data[downloads] = intval( $row[downloads] );
        $data[regdate] = date( "M.d,Y", $row[regdate] );
        $data[expire_date] = ( "<font color=red>" ).date( "M.d,Y", $row[expire_date] ).( "</font>" );
        $data[group] = $row[name];
        $data[webspace] = convertsize( $row[webspace] );
        //$data[bandwidth] = convertsize( $row[bandwidth] );
        $data[bandwidth2] = convertsize( $row[bandwidth] );
        $data[status] = $row[status] == 1 ? "Activated" : "Unconfirmed";
        if ( $row[status] == -1 )
        {
            $data[status] = "Suspended";
        }
        $data[name] = "<a href=$baseUrl&admin=images&search=1&img_field=f.uid&img_func==&img_values={$data['id']} title='click to view files'>{$row['user']}</a>";
        if ( $data[files] == 0 )
        {
            $data[del] = "<a href=$baseUrl&admin=user&act=del&id={$row['id']} onclick=\"return confirm('Are You Sure Delete This Member?')\">Delete</a>";
        }
        else
        {
            $data[del] = "<a href=$baseUrl&admin=user&act=delimg&id={$row['id']} onclick=\"return confirm('Are You Sure Delete All files of This Member?')\">Empty</a>";
        }
        $data[del] .= "::<a href=$baseUrl&admin=user&act=edit&id={$row['id']}>Edit</a>";
        $data[del] .= "::<a href=$baseUrl&admin=user&act=login&id={$row['id']} target=blank>Login</a>";
        showuserrow( $data );
    }
    showusertt( $pageLinks );
}

function addedituser( )
{
    global $baseUrl;
    global $input;
    global $db;
    $db->setQuery( "select * from groups" );
    $db->query( );
    $groups = $db->loadRowList( );
    $id = $input[id];
    $act = $input[act];
    $gid = 0;
    $cur_group = array( );
    if ( $input[act] == "edit" )
    {
        $db->setQuery( "select u.* from users as u where u.id='{$input['id']}'" );
        $db->query( );
        $row = $db->loadRow( );
        $id = $row[id];
        $gid = $row[gid];
        $regdate = date( "m/d/Y", $row[regdate] );
        $row[bandwidth2] = $row[bandwidth];
        $row[bandwidth] = convertsize( $row[bandwidth] );
    }
    $package_list = "";
    foreach ( $groups as $r )
    {
        if ( $r[id] == $gid )
        {
            $cur_group = $r;
            $expire_date = date( "m/d/Y", $row[expire_date] );
        }
        $package_list .= "<option value={$r['id']} ".( $r[id] == $gid ? "selected" : "" ).">{$r['name']}</option>";
    }
    $submit_btn = $id ? "Update User" : "Add User";
    $enabled = "<option value=1 selected>enabled</option><option value=0>disabled</option>";
    $disabled = "<option value=1 >enabled</option><option value=0 selected>disabled</option>";
    $option = array( );
    $option[1] = "<option value=1 selected>enabled</option><option value=0>disabled</option><option value=-1>hidden</option>";
    $option[0] = "<option value=1>enabled</option><option value=0 selected>disabled</option><option value=-1>hidden</option>";
    $option[-1] = "<option value=1>enabled</option><option value=0>disabled</option><option value=-1 selected>hidden</option>";
    $row[formupload] = $option[intval( $cur_group[formupload] )];
    $row[urlupload] = $option[intval( $cur_group[urlupload] )];
    $row[ftpupload] = $option[intval( $cur_group[ftpupload] )];
    $row[flashupload] = $option[intval( $cur_group[flashupload] )];
    if ( $row[custom] == "yes" )
    {
        $cur_group = unserialize( $row[data] );
    }
    $row[sizelimit] = $cur_group[sizelimit];
    $row[max_uploads] = $cur_group[max_uploads];
    $row[dl_speed] = $cur_group[dl_speed];
    $row[dl_threads] = $cur_group[dl_threads];
    $row[dl_waittime] = $cur_group[dl_waittime];
    $row[dl_timeout] = $cur_group[dl_timeout];
    $row[dl_ips] = $cur_group[dl_ips];
    $row[dl_direct] = $cur_group[dl_direct] == 1 ? $enabled : $disabled;
    $row[dl_checkarea] = $cur_group[dl_checkarea] == 1 ? $enabled : $disabled;
    $row[allowed_filetype] = $cur_group[allowed_filetype];
    $row[disabled_filetype] = $cur_group[disabled_filetype];
    $typecheck = $row[allowed_filetype] ? "allow" : $row[disabled_filetype] ? "disable" : "all";
    $row[dl_sizebyhour] = $cur_group[dl_sizebyhour];
    $row[dl_password] = $cur_group[dl_password] == 1 ? $enabled : $disabled;
    $row[dl_password] = $cur_group[dl_password] == 1 ? $enabled : $disabled;
    $row[dl_resume] = $cur_group[dl_resume] == 1 ? $enabled : $disabled;
    $row[dl_captcha] = $cur_group[dl_captcha] == 1 ? $enabled : $disabled;
    $row[folder] = $cur_group[folder] == 1 ? $enabled : $disabled;
    $row[show_site_ads] = $cur_group[show_site_ads] == 1 ? $enabled : $disabled;
    $row[show_sponser_ads] = $cur_group[show_sponser_ads] == 1 ? $enabled : $disabled;
    echo "<script type=\"text/javascript\" src=\"calendar/calendar.js\"></script>\r\n<script type=\"text/javascript\" src=\"calendar/lang/calendar-en.js\"></script>\r\n"
    	."<LINK REL=\"stylesheet\" HREF=\"calendar/calendar.css\" TYPE=\"text/css\">\r\n<form action=\"index.php?admin=user\" name=myform method=\"post\">\r\n"
    	."<input type=\"hidden\" name=\"admin\" value=\"user\">\r\n<input type=\"hidden\" name=\"act\" value=\"{$act}\">\r\n"
    	."<input type=\"hidden\" name=\"id\" value=\"{$id}\">\r\n<table class=adminlist align=center>\r\n"
    	."<tr><th align=\"center\" colspan=2><div class=\"cattitle\">User Info<input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></div></td></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Username</b>:</td><td class=tdrow2 align='left'><input type=text name=user value=\"{$row['user']}\"></td></tr>"
    	."\r\n<tr><td width=40% class=tdrow1 valign='middle'><b>Password</b>:</td><td class=tdrow2 align='left'><input type=password name=pass value=\"\">(Leave blank if you do not want to change the password)</td></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Email</b>:</td><td class=tdrow2 align='left'><input type=text name=email value=\"{$row['email']}\"></td></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Files</b>:</td><td class=tdrow2 align='left'>{$row['files']}</td></tr>\r\n"
    	."<tr><th colspan=2 align=center><b>Revenue Program:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Joined?</b>:</td><td class=tdrow2 align='left'><input type=text name=revenue_program value=\"{$row['revenue_program']}\"></td></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Payment Type</b>:</td><td class=tdrow2 align='left'><input type=text name=payment_method value=\"{$row['payment_method']}\"></td></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Payment Email</b>:</td><td class=tdrow2 align='left'><input type=text name=payment_email value=\"{$row['payment_email']}\"></td></tr>\r\n"
    	."<tr><th colspan=2 align=center><b>Premium Usage:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Donwloads</b>:</td><td class=tdrow2 align='left'><input type=text name=totaldownloads value=\"{$row['totaldownloads']}\"></td></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Bandwidths</b>:</td><td class=tdrow2 align='left'><input type=text name=bandwidth value=\"{$row['bandwidth2']}\">{$row['bandwidth']}</td></tr>\r\n"
    	."<tr><th colspan=2 align=center><b>Group Options:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n"
    	."<tr><td width=40% class=tdrow1 valign='middle'><b>Host Package</b>:</td><td class=tdrow2 align=left><select name=\"gid\" class='dropdown'>{$package_list}</select></td></tr>";
    if ( $id )
    {
        echo "<tr><td width=40% class=tdrow1 valign='middle'><b>Custom plan</b>:</td><td class=tdrow2 align=left><select name=\"custom\" class='dropdown'><option value='yes'>Yes</option><option value='no' selected>No</option></select><a href=\"javascript:toggle('customgroup')\">Custom!</a></td></tr>\r\n<tr><td colspan=2 width=80% class=tdrow1 valign='middle'>\r\n<table id=customgroup style='display:none' class=adminlist align=center style=\"width:50%\">\r\n<tr><th colspan=2 align=center><b>Upload Options:</b></th></tr>\r\n<tr><td class=tdrow1 width=40%>Size Limit:<br><span class=note>For single file</span></td><td class=tdrow2><input type=\"text\" name=\"tbl2_sizelimit\" value=\"{$row['sizelimit']}\" onclick=\"calsize(this,'fsize');\" onchange=\"calsize(this,'fsize');\"><span class=note>Enter <b>2b/k/m/g</b> directly or the digital bytes 1024</span><div id=fsize></div></td></tr>\r\n<tr><td class=tdrow1 width=40%>Filetypes:</td><td class=tdrow2><input type=radio name=typecheck value='allow'><input type=\"text\" name=\"tbl2_allowed_filetype\" value=\"{$row['allowed_filetype']}\"> allowed<br><input type=radio name=typecheck value='disable'><input type=\"text\" name=\"tbl2_disabled_filetype\" value=\"{$row['disabled_filetype']}\"> disabled<br><input type=radio name=typecheck value='all'>All types</td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Uploads?</td><td class=tdrow2><input type=\"text\" name=\"tbl2_max_uploads\" value=\"{$row['max_uploads']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Form Upload?</td><td class=tdrow2><select name=\"tbl2_formupload\">{$row['formupload']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow URL Upload?</td><td class=tdrow2><select name=\"tbl2_urlupload\">{$row['urlupload']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow FTP Upload?</td><td class=tdrow2><select name=\"tbl2_ftpupload\">{$row['ftpupload']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Flash Upload?</td><td class=tdrow2><select name=\"tbl2_flashupload\">{$row['flashupload']}</select></td></tr>\r\n<tr><th colspan=2 align=center><b>Download Options:</b></th></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Resume Download?<br><span class=note>Support download-accelerators</span></td><td class=tdrow2><select name=\"tbl2_dl_resume\">{$row['dl_resume']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Threads<br><span class=note>Max allowed threads to download by download-accelerators</span></a></td><td class=tdrow2><input type=\"text\" name=\"tbl2_dl_threads\" value=\"{$row['dl_threads']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Speed<br><span class=note>Max download speed,setting 0 as unlimited</span></td><td class=tdrow2><input type=\"text\" name=\"tbl2_dl_speed\" value=\"{$row['dl_speed']}\">KB/s</td></tr>\r\n<tr><td class=tdrow1 width=40%>Max bandwidth per hour from same IP<br><span class=note>Setting 0 as unlimited</span></td><td class=tdrow2><input type=\"text\" name=\"tbl2_dl_sizebyhour\" value=\"{$row['dl_sizebyhour']}\" onclick=\"calsize(this,'dlsize');\" onchange=\"calsize(this,'dlsize');\"><span class=note>Enter <b>2b/k/m/g</b> directly or the digital bytes 1024</span><div id=dlsize></div></td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Downloads IPs<br><span class=note>Max IPs to download at the same time,setting 0 as unlimited</span></td><td class=tdrow2><input type=\"text\" name=\"tbl2_dl_ips\" value=\"{$row['dl_ips']}\"></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Direct Download?<br><span class=note>Enabled will ignore password checking, wait time and captcha checking</span></td><td class=tdrow2><select name=\"tbl2_dl_direct\">{$row['dl_direct']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Enable captcha check before download?</td><td class=tdrow2><select name=\"tbl2_dl_captcha\">{$row['dl_captcha']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Password Protection of Download?</td><td class=tdrow2><select name=\"tbl2_dl_password\">{$row['dl_password']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Wait time for Download?<br><span class=note>Setting 0 to disable wait time</span></td><td class=tdrow2><input name=\"tbl2_dl_waittime\" value=\"{$row['dl_waittime']}\">seconds</td></tr>\r\n<tr><td class=tdrow1 width=40%>Download link expire time<br><span class=note>Download link</span></td><td class=tdrow2><input name=\"tbl2_dl_timeout\" value=\"{$row['dl_timeout']}\">seconds</td></tr>\r\n<tr><td class=tdrow1 width=40%>Download Location checking?</td><td class=tdrow2><select name=\"tbl2_dl_checkarea\">{$row['dl_checkarea']}</select><a href=\"?admin=rule\">Create your download rules for package</a></td></tr>\r\n<tr><th colspan=2 align=center><b>Misc. Infomation:</b></th></tr>\r\n<tr><td class=tdrow1 width=40%>Show site Ad?</td><td class=tdrow2><select name=\"tbl2_show_site_ads\">{$row['show_site_ads']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Show sponser Ad?</td><td class=tdrow2><select name=\"tbl2_show_sponser_ads\">{$row['show_sponser_ads']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Auto validate files if thumbnails is submited?</td><td class=tdrow2><select name=\"tbl2_validate_type\"><option value='auto'>Yes</option><option value='admin'>No,admin approve</option></select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Can create folder?</td><td class=tdrow2><select name=\"tbl2_folder\">{$row['folder']}</select></td></tr>\r\n</table>\r\n</td></tr>";
    }
    echo "<tr><td width=40% class=tdrow1 valign='middle'><b>Register date</b>:</td><td class=tdrow2 align='left'><input type=text name=regdate id=regdate value=\"{$regdate}\"><input name=\"reset\" type=\"reset\" class=\"button\" onClick=\"return showCalendar('regdate', 'y-mm-dd');\" value=\"...\">[m/d/y]</td></tr>\r\n<tr><td width=40% class=tdrow1 valign='middle'><b>Expire date</b>:</td><td class=tdrow2 align='left'><input type=text name=expire_date id=expire_date value=\"{$expire_date}\"><input name=\"reset\" type=\"reset\" class=\"button\" onClick=\"return showCalendar('expire_date', 'y-mm-dd');\" value=\"...\">[m/d/y]</td></tr>\r\n<tr><td width=40% class=tdrow1 valign='middle'><b>Status</b>:</td><td class=tdrow2 align='left'><select name=\"status\" class='dropdown'>\r\n<option value=0>Unconfirmed</option>\r\n<option value=1>Activted</option>\r\n<option value=-1>Suspended</option></select></td></tr>\r\n</table></form>\r\n<script>";
    if ( $id )
    {
        echo "s=document.myform.typecheck;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$typecheck}')\r\n   {\r\n     s[i].checked='true';\r\n     break;\r\n   }\r\n}\r\ns=document.myform.custom.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['custom']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}";
    }
    echo "s=document.myform.status.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['status']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n\r\n</script>\r\n</div>";

}

function showuserrow( $data )
{
    global $baseUrl;
    echo "    <tr>\r\n    <td align=left class='tdrow1'><input type='checkbox' name=idList[{$data['id']}]></td>\r\n    <td align=left class='tdrow2'>{$data['name']}</td>\r\n    <td align=left class='tdrow1'><a href=\"?&admin=images&search=1&img_field=f.uid&img_func==&img_values={$data['id']}\" title='click to view files'>{$data['files']}</a>({$data['downloads']} downloads)</td>\r\n    <td align=left class='tdrow2'>{$data['webspace']}/{$data['bandwidth2']}</td>\r\n    <td align=left class='tdrow1'>{$data['status']}</td>\r\n    <td align=left class='tdrow2'>{$data['group']}</td>\r\n    <td align=left class='tdrow1'>{$data['custom']}</td>\r\n    <td align=left class='tdrow2'>{$data['expire_date']}</td>\r\n    <td align=left class='tdrow1'>{$data['regdate']}</td>\r\n    <td align=left class='tdrow2'>{$data['del']}</td>\r\n    </tr>";
}

function showuserth( $header )
{
    global $baseUrl;
    echo "<form name=myform action=\"?admin=user\" method=\"POST\">\r\n<input type=hidden name=act value='update'>\r\n    <table class=adminlist border=0 align=center>\r\n    <tr><th align=\"center\" class='pformstrip' colspan=12>Memebers List:<b>{$header['caption']}({$header['total']} members)</b></th></tr>\r\n    <tr>\r\n    <td align=left class='tdrow1' width=5% ><input type='checkbox' name=allbox onclick=checkAll()></td>\r\n    <td align=left class='tdrow1' width=10%><b>Username</b></td>\r\n    <td align=left class='tdrow1' width=12%><b>Files</b></td>\r\n    <td align=left class='tdrow1' width=12%><b>DiskSpace/Bandwidth</b></td>\r\n    <td align=left class='tdrow1' width=8%><b>Status</b></td>\r\n    <td align=left class='tdrow1' width=10%><b>Package</b></td>\r\n    <td align=left class='tdrow1' width=5%><b>Customised</b></td>\r\n    <td align=left class='tdrow1' width=10%><b>Expire Date</b></td>\r\n    <td align=left class='tdrow1' width=8%><b>Reg.Date</b></td>\r\n    <td align=left class='tdrow1'><b>Action</b></td>\r\n    </tr>";
}

function showusertt( $pageLinks )
{
    global $baseUrl;
    global $input;
    global $db;
    global $GROUPS;
    if ( !is_array( $GROUPS ) )
    {
        $db->setQuery( "select * from groups" );
        $db->query( );
        $groups = $db->loadRowList( );
    }
    else
    {
        $groups = $GROUPS;
    }
    $package_list = "";
    foreach ( $groups as $group )
    {
        $package_list .= "<option value=".$group[id].">".$group[name]."</option>";
    }
    $status_option = "<option value='2'>all</option>\r\n<option value='1'>Activated</option>\r\n<option value='0'>Unconfirmed</option>\r\n<option value='-1'>Suspended</option>";
    $actions_option = "<option value='reset'>Reset bandwidth</option>\r\n<option value='resetdl'>Reset downloads</option>\r\n<option value='suspend'>Suspend</option>\r\n<option value='activate'>Activate</option>\r\n<option value='upgrade'>Upgrade</option>";
    echo "<tr><td class='tdrow1' align='left' valign='middle' colspan=12>\r\nActions:<select name=actions style=\"width:180px\">{$actions_option}</select>\r\n<input type=submit name=resetbd style=\"width:200px\" value=\"to selected members\" onclick=\"return opConfirm('Are You Sure Apply Actions to These Members?',1)\">\r\n<select name=newgid style=\"width:20%\">{$package_list}</select>\r\n<a href=\"?admin=user&act=add\">Add user</a><br>{$pageLinks}\r\n</form>\r\n<form name=jump action=\"?admin=user\" method=get>\r\n<input type=\"hidden\" name=\"admin\" value=\"user\">\r\n<table width='110%' cellspacing='0' cellpadding='5' align='center' border='0'>\r\n<tr><th colspan=3 align=center>Search a User</th></tr>\r\n<tr>\r\n<td class='row3' width='10%' align='center'><b>Field</b></td>\r\n<td class='row3' width='15%' align='right'><b>Operator</b></td>\r\n<td class='row3' width='60%' align='center'><b>Value</b></td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><select name=\"user_field\">\r\n<option value=\"\">Default</option>\r\n<option value=\"u.user\">Username</option>\r\n<option value=\"u.email\">Email</option>\r\n<option value=\"u.id\">User ID</option>\r\n<option value=\"files\">Files</option>\r\n<option value=\"webspace\">DiskSpace</option>\r\n<option value=\"regdate\">Reg.Date</option>\r\n<option value=\"expire_date\">Expire Date</option>\r\n</select></td>\r\n<td class='row2'  width='15%'  align='right'><select name=\"user_func\"><option value=\"=\">=</option><option value=\"&gt;\">&gt;</option><option value=\"&lt;\">&lt;</option><option value=\"&gt;=\">&gt;=</option><option value=\"&lt;=\">&lt;=</option><option value=\"!=\">!=</option><option value=\"LIKE\">LIKE</option><option value=\"NOT LIKE\">NOT LIKE</option></select></td>\r\n<td class='row1'  width='60%'  align='left'><input type='text' name='user_values' value='{$_SESSION['user_values']}' size='25' class='textinput'>(wildcard: \"%\" when \"like\" or \"not like\",register date:m/d/y)</td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><select name=\"user_field2\">\r\n<option value=\"\">AND</option>\r\n<option value=\"u.user\">Username</option>\r\n<option value=\"u.email\">Email</option>\r\n<option value=\"u.id\">User ID</option>\r\n<option value=\"files\">Files</option>\r\n<option value=\"webspace\">DiskSpace</option>\r\n<option value=\"regdate\">Reg.Date</option>\r\n<option value=\"expire_date\">Expire Date</option>\r\n</select></td>\r\n<td class='row2'  width='15%'  align='right'><select name=\"user_func2\"><option value=\"=\">=</option><option value=\"&gt;\">&gt;</option><option value=\"&lt;\">&lt;</option><option value=\"&gt;=\">&gt;=</option><option value=\"&lt;=\">&lt;=</option><option value=\"!=\">!=</option><option value=\"LIKE\">LIKE</option><option value=\"NOT LIKE\">NOT LIKE</option></select></td>\r\n<td class='row1'  width='60%'  align='left'><input type='text' name='user_values2' value='{$_SESSION['user_values2']}' size='25' class='textinput'>(wildcard: \"%\" when \"like\" or \"not like\",register date:m/d/y)</td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><b>Order By</b></td>\r\n<td class='row2'  width='15%'  align='left'><select name=\"user_orderby\">\r\n<option value=\"\">Default</option>\r\n<option value=\"user\">Username</option>\r\n<option value=\"files\">Files</option>\r\n<option value=\"bandwidth\">Bandwidth</option>\r\n<option value=\"webspace\">DiskSpace</option>\r\n<option value=\"u.gid\">Package</option>\r\n<option value=\"status\">Status</option>\r\n<option value=\"regdate\">Reg.Date</option>\r\n<option value=\"expire_date\">Expire Date</option>\r\n</select><select name=user_AD class=\"dropdown\"><option value=>Default</option><option value=ASC>ASC</option><option value=DESC>DESC</option></select></td>\r\n<td class='row1'  width='60%'  align='left'>\r\n<b>Members Status</b>:<select name=user_status>{$status_option}</select>\r\n<select name=user_gid style=\"width:20%\"><option>All Package</option>{$package_list}</select><b>Show</b>: <input type='text' name='user_pages' value='{$_SESSION['user_pages']}' size='8' class='textinput'>records per page\r\n</td>\r\n</tr>\r\n<tr>\r\n<td class='row1' colspan='3' align='left' class=''><input type='submit' name='search' value='Search' size='30' class='textinput'><input type='reset' name='reset' value='Reset' size='30' class='textinput'></td>\r\n</tr>\r\n<script>\r\ns=document.jump.user_orderby.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_orderby']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script>\r\n<script>\r\ns=document.jump.user_AD.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_AD']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script>\r\n<script>\r\ns=document.jump.user_func.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_func']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.user_func2.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_func2']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script>\r\n<script>\r\ns=document.jump.user_field.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_field']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.user_field2.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_field2']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.user_status.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_status']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.user_gid.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['user_gid']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script></table>\r\n</form>\r\n<form name=recountform action=\"?admin=user\" method=get>\r\n<input type=\"hidden\" name=\"admin\" value=\"user\">\r\n<input type=\"hidden\" name=\"act\" value=\"recount\">\r\n<table width='110%' cellspacing='0' cellpadding='5' align='center' border='0'>\r\n<tr><th colspan=3 align=center>Recount data for User</th></tr>\r\n<tr>\r\n<td class='row1'  width='20%'  align='left'><select name=\"package_id\">\r\n<option value=\"0\">All package</option>\r\n{$package_list}\r\n</select></td>\r\n<td class='row2'  width='20%'  align='right'>\r\n<select name=\"scope\">\r\n<option value=\"0\">Only recount files and webspace!</option>\r\n<option value=\"1\">Recount files,webspace,downloads and bandwidth!</option>\r\n<option value=\"2\">Recount files,webspace,all downloads and bandwidth!</option>\r\n</select></td>\r\n<td class='row1'  width='60%'  align='left'>process <input type='text' name='limit' value='100' size='8' class='textinput'> users per batch!</td>\r\n</tr>\r\n<tr>\r\n<td class='row1' colspan='3' align='left' class=''><input type='submit' name='recount' value='Recount' size='30' class='textinput'><input type='reset' name='reset' value='Reset' size='30' class='textinput'></td>\r\n</tr>\r\n</table>\r\n<div class=quote>\r\n1.\"<b>Recount files,webspace,downloads and bandwidth!</b>\" will sum up all downloads and bandwidth in current month!\r\n<br>2.\"<b>Recount files,webspace,all downloads and bandwidth!</b>\" will sum up all downloads in all time,but bandwidth in this month!\r\n<br>3.\"<b>Bandwidth</b>\" is the bandwidth that accurs in this month,and will be reset in next month!\r\n</div>\r\n</td></tr>\r\n</table>\r\n</form>";
}

if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
$act = $input[act];
switch ( $act )
{
    case "login" :
        redirect( "admin=user&", $output );
        break;
    case "edit" :
    if ( isset( $input[update] ) )
    {
        $user = new table( $db, "users", "id" );
        $user->id = $input[id];
        $user->user = $input[user];
        if ( $input[pass] )
        {
            $user->pass = $input[pass];
        }
        if ( $input[expire_date] )
        {
            $user->expire_date = intval( strtotime( $input[expire_date] ) );
        }
        if ( $input[regdate] )
        {
            $user->regdate = intval( strtotime( $input[regdate] ) );
        }
		
        $user->email = $input[email];
        $user->status = $input[status];
        $user->gid = $input[gid];
		
		$user->revenue_program = $input[revenue_program];
		$user->payment_method = $input[payment_method];
		$user->payment_email = $input[payment_email];
		
		$user->totaldownloads = $input[totaldownloads];
		$user->bandwidth = $input[bandwidth];
		
        $user->custom = $input[custom] == "yes" ? "yes" : "no";
        if ( $user->custom == "yes" )
        {
            $group_data = array( );
            $prefix = "tbl2_";
            foreach ( $input as $key=>$value )
            {
                if ( substr( $key, 0, strlen( $prefix ) ) == $prefix )
                {
                }
                else
                {
                    $var = substr( $key, strlen( $prefix ) );
                    $group_data[$var] = $value;
                }
            }
            if ( $input[typecheck] == "allow" && $group_data[allowed_filetype] )
            {
                $group_data[disabled_filetype] = "";
            }
            else if ( $input[typecheck] == "disable" && $group_data[disabled_filetype] )
            {
                $group_data[allowed_filetype] = "";
            }
            else
            {
                $group_data[disabled_filetype] = $group_data[disabled_filetype] = "";
            }
            $user->data = serialize( $group_data );
        }
        $user->update( );
        redirect( "admin=user", "Update Successfully!" );
    }
    addedituser( );
    break;
case "add" :
    if ( isset( $input[update] ) )
    {
        if ( $input[user] == "" || $input[email] == "" )
        {
            redirect( "admin=user&act=add", "User or email can't be empty!" );
        }
        $db->setQuery( "select * from users where user='{$input['user']}' or email='{$input['email']}'" );
        $db->query( );
        if ( $db->getNumRows( ) )
        {
            redirect( "admin=user&act=add", "User <b>{$input['user']}</b>, or email <b>{$input['email']}</b> has been choosed!" );
        }
        if ( !$input[regdate] )
        {
            $input[regdate] = time( );
        }
        if ( !$input[expire_date] )
        {
            $input[expire_date] = time( );
        }
        $user = new table( $db, "users", "id" );
        $user->id = $input[id];
        $user->user = $input[user];
        $user->pass = $input[pass];
        $user->email = $input[email];
        $user->status = $input[status];
        $user->gid = $input[gid];
        $user->expire_date = intval( strtotime( $input[expire_date] ) );
        $user->regdate = intval( strtotime( $input[regdate] ) );
        $user->insert( );
        redirect( "admin=user", "Added Successfully!" );
    }
    addedituser( );
    break;
case "update" :
    if ( $input[actions] == "reset" )
    {
        foreach ( $input[idList] as $id=>$on )
        {
            $db->setQuery( "update users set bandwidth=0 where id='$id'" );
            $db->query( );
        }
        redirect( "admin=user", "Updated Successfully!" );
    }
    if ( $input[actions] == "resetdl" )
    {
        foreach ( $input[idList] as $id=>$on )
        {
            $db->setQuery( "update users set totaldownloads=0 where id='$id'" );
            $db->query( );
        }
        redirect( "admin=user", "Updated Successfully!" );
    }
    if ( $input[actions] == "activate" )
    {
        foreach ( $input[idList] as $id=>$on )
        {
            $db->setQuery( "update users set status=1 where id='$id'" );
            $db->query( );
        }
        redirect( "admin=user", "Updated Successfully!" );
    }
    if ( $input[actions] == "suspend" )
    {
        foreach ( $input[idList] as $id=>$on )
        {
            $db->setQuery( "update users set status=-1 where id='$id'" );
            $db->query( );
        }
        redirect( "admin=user", "Updated Successfully!" );
    }
    if ( $input[actions] == "upgrade" )
    {
        foreach ( $input[idList] as $id=>$on )
        {
            $db->setQuery( "update users set gid='{$input['newgid']}' where id='$id'" );
            $db->query( );
        }
        redirect( "admin=user", "Updated Successfully!" );
    }
    break;
case "del" :
    $db->setQuery( "delete from users where id={$input['id']} " );
    $db->query( );
    redirect( "admin=user", "Delete Successfully!" );
    break;
case "delimg" :
    $db->setQuery( "update files set deleted=1 where uid='{$input['id']}'" );
    $db->query( );
    $db->setQuery( "select sum(if(f.deleted=1 or f.deleted is null,0,1)) as nums,sum(if(f.deleted=1,0,f.size)) as webspace from files as f where f.uid ='{$input['id']}'" );
    $db->query( );
    $row = $db->loadRow( );
    $total_files = $row[nums];
    $db->setQuery( "update users set files='{$total_files}' where id='{$input['id']}'" );
    $db->query( );
    redirect( "admin=user", "Delete Successfully!" );
    break;
case "recount" :
    $start = intval( $input[s] );
    $limit = intval( $input[limit] );
    $pid = intval( $input[package_id] );
    $scope = intval( $input[scope] );
    if ( !$limit )
    {
        $limit = 100;
    }
    if ( $pid )
    {
        $wherex = "and u.gid='$pid'";
    }
    if ( $scope == 1 )
    {
        $fields = "sum(f.bandwidth/size) as downloads";
    }
    if ( $scope == 2 )
    {
        $fields = "sum(f.downloads) as downloads";
    }
    if ( !$fields )
    {
        $fields = "1";
    }
    $db->setQuery( "select u.id,f.size,count(f.id) as files,sum(f.size) as webspace,$fields,sum(f.bandwidth) as bandwidth\r\n                       from users as u\r\n                       left join files as f on u.id=f.uid\r\n                       where f.deleted=0 $wherex\r\n                       group by u.id\r\n                       limit $start,$limit" );
    $db->query( );
    $rows = $db->loadRowList( );
    foreach ( $rows as $row )
    {
        if ( $scope == 2 || $scope == 1 )
        {
            $row[downloads] = intval( $row[downloads] );
            $db->setQuery( "update users set files='{$row['files']}', webspace='{$row['webspace']}', totaldownloads='{$row['downloads']}', bandwidth='{$row['bandwidth']}' where id='{$row['id']}'" );
        }
        else
        {
            $db->setQuery( "update users set files='{$row['files']}', webspace='{$row['webspace']}' where id='{$row['id']}'" );
        }
        $db->query( );
    }
    if ( count( $rows ) )
    {
        $start = $start + count( $rows );
        redirect( "admin=user&act=recount&limit=".$limit."&s=".$start."&package_id=".$pid."&scope=".$scope, "$limit users are updated successfully!" );
        }
        else
        {
            redirect( "admin=user", "All users are updated successfully!" );
        }
    default :
        showlistform( );
}
?>
