<?php
/*************************************/
/*                                   */
/*  MFHS Version : 1.2               */
/*  Dezended & Nulled by: LepeNU     */
/*  Release Date: 1/12/2007          */
/*                                   */
/*************************************/
function showlistform( )
{
    global $baseUrl;
    global $input;
    global $db;
    $per_num = 10;
    $input[s] = intval( $input[s] );
    $pid = intval( $input[id] );
    $db->setquery( "select * from groups where subscr_fee=0" );
    $db->query( );
    $nums = $db->getnumrows( );
    $db->setquery( "select g.id,g.name,g.guest,count(u.id) as num from groups as g left join users as u on g.id=u.gid group by g.id" );
    $db->query( );
    $rows = $db->loadrowlist( );
    showgroupth( $data );
    foreach ( $rows as $row )
    {
        $data = $row;
        $data[name] = "<a href={$baseUrl}&admin=user&search=1&user_gid={$data['id']} title='click to view users'>{$row['name']}</a>";
        /*
        if ( $row[guest] == 1 )
        {
            //$db->setquery( "select sum(f.bandwidth) as bandwidth,sum(f.size) as webspace from files as f where f.uid=0" );
            $db->setquery("select sum(fs.bandwidth) as bandwidth from filestats as fs, files as f where fs.upload_id=f.upload_id and f.uid=0");
            $db->query();
            $guestrow = $db->loadrow( );
            $row[tbandwidth] = $guestrow[bandwidth];
            
            $db->setquery("select sum(f.size) as webspace from files as f where f.uid=0");
            $db->query();
            $guestrow = $db->loadrow( );
            $row[twebspace] = $guestrow[webspace];
        }
        else {
        	$db->setquery("select sum(fs.bandwidth) as bandwidth from filestats as fs, files as f where fs.upload_id=f.upload_id and f.uid<>0");
            $db->query();
            $memberrow = $db->loadrow( );
        	$row[tbandwidth] = $memberrow[bandwidth];
        	
			$db->setquery("select sum(f.size) as webspace from files as f where f.uid<>0");
            $db->query();
            $memberrow = $db->loadrow( );
        	$row[twebspace] = $memberrow[webspace];
        }
        */
        
        $data[bandwidth] = convertsize( $row[tbandwidth], 0 );
        $data[webspace] = convertsize( $row[twebspace], 0 );
        $data[isguest] = $row[guest] == 1 ? "Yes" : "No";
        $data[del] = "<a href={$baseUrl}&admin=group&act=guest&id={$row['id']}>Guest Package?</a>";
        if ( $data[num] == 0 )
        {
            $data[del] .= "::<a href={$baseUrl}&admin=group&act=del&id={$row['id']} onclick=\"return confirm('Are You Sure Delete This Package?')\">Delete</a>";
        }
        $data[del] .= "::<a href={$baseUrl}&admin=group&act=edit&id={$row['id']}>Edit</a>";
        showgrouprow( $data );
    }
    showgrouptt( );
}

function addeditgroup( )
{
    global $baseUrl;
    global $input;
    global $db;
    $id = $input[id];
    if ( $id )
    {
        $db->setquery( "select * from groups where id='{$id}' " );
        $db->query( );
        $row = $db->loadrow( );
    }
    else
    {
        $row[common] = $row[applet] = $row[url] = $row[thumb] = $row[flashgal] = $row[advancedaction] = 1;
        $row[payment_type] = "subscriptions";
    }
    $db->setquery( "select * from server where enabled=1" );
    $db->query( );
    $servers = $db->loadrowlist( );
    $serverlist = "<option value=0>----</option>";
    foreach ( $servers as $server )
    {
        if ( $row[server_id] == $server[server_id] )
        {
            $serverlist .= "<option value=".$server[server_id]." selected>".$server[name]."</option>";
        }
        else
        {
            $serverlist .= "<option value=".$server[server_id].">".$server[name]."</option>";
        }
    }
    $title = $id ? "Package {$row['name']}" : "Package information";
    $submit_btn = $id ? "Update Package" : "Add Package";
    $enabled = "<option value=1 selected>enabled</option><option value=0>disabled</option>";
    $disabled = "<option value=1 >enabled</option><option value=0 selected>disabled</option>";
    $row[dl_password] = $row[dl_password] == 1 ? $enabled : $disabled;
    $row[dl_resume] = $row[dl_resume] == 1 ? $enabled : $disabled;
    $row[dl_captcha] = $row[dl_captcha] == 1 ? $enabled : $disabled;
    $row[dl_direct] = $row[dl_direct] == 1 ? $enabled : $disabled;
    $row[dl_checkarea] = $row[dl_checkarea] == 1 ? $enabled : $disabled;
    $row[folder] = $row[folder] == 1 ? $enabled : $disabled;
    $row[show_site_ads] = $row[show_site_ads] == 1 ? $enabled : $disabled;
    $row[show_sponser_ads] = $row[show_sponser_ads] == 1 ? $enabled : $disabled;
    $option = array( );
    $option[1] = "<option value=1 selected>enabled</option><option value=0>disabled</option><option value=-1>hidden</option>";
    $option[0] = "<option value=1>enabled</option><option value=0 selected>disabled</option><option value=-1>hidden</option>";
    $option[-1] = "<option value=1>enabled</option><option value=0>disabled</option><option value=-1 selected>hidden</option>";
    $row[formupload] = $option[intval( $row[formupload] )];
    $row[urlupload] = $option[intval( $row[urlupload] )];
    $row[ftpupload] = $option[intval( $row[ftpupload] )];
    $row[flashupload] = $option[intval( $row[flashupload] )];
    $subscriptions = "<option value='subscriptions' selected>Subscriptions</option><option value='instant'>Common Payment</option>";
    $instant = "<option value='subscriptions'>Subscriptions</option><option value='instant' selected>Common Payment</option>";
    $row[payment_type] = $row[payment_type] == "subscriptions" ? $subscriptions : $instant;
    $typecheck = $row[allowed_filetype] ? "allow" : $row[disabled_filetype] ? "disable" : "all";
    $post_max_size = ini_get( "post_max_size" );
    //$upload_max_filesize = ini_get( "upload_max_filesize" );
    //$memory_size_limit = ini_get( "memory_size_limit" );
    echo "<div class=quote>\r\nWarning: Limitions of PHP config:\r\nPlease set php.ini on file server to upload_max_filesize <font style=\"color:red\">100M</font> and \r\npost_max_size <font style=\"color:red\">100M</font>\r\n when every time setup a new server!!</div>\r\n<form action=\"index.php?admin=group\" name=myform method=\"post\" onsubmit=\"return validateForm(this)\">\r\n<input type=\"hidden\" name=\"act\" value=\"{$input['act']}\">\r\n<input type=\"hidden\" name=\"tbl_id\" value=\"{$id}\">\r\n<table class=adminlist align=center>\r\n<tr><th align=\"center\" colspan=2><b>{$title}:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n<tr><td class=tdrow1>Name:</td><td class=tdrow2><input type=\"text\" name=\"tbl_name\" value=\"{$row['name']}\"></td></tr>\r\n<tr><th colspan=2 align=center><b>Upload Options:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n<tr><td class=tdrow1 width=40%>Upload server?<span class=note><a href=\"?admin=set\">Need Host package Based for Multiple server</a></span></td><td class=tdrow2><select name=\"tbl_server_id\">{$serverlist}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Size Limit:<br><span class=note>For single file</span></td><td class=tdrow2><input type=\"text\" name=\"tbl_sizelimit\" value=\"{$row['sizelimit']}\" onclick=\"calsize(this,'fsize');\" onchange=\"calsize(this,'fsize');\"><span class=note>Enter <b>2b/k/m/g</b> directly or the digital bytes 1024</span><div id=fsize></div></td></tr>\r\n<tr><td class=tdrow1 width=40%>Filetypes:</td><td class=tdrow2><input type=radio name=typecheck value='allow'><input type=\"text\" name=\"tbl_allowed_filetype\" value=\"{$row['allowed_filetype']}\"> allowed<br><input type=radio name=typecheck value='disable'><input type=\"text\" name=\"tbl_disabled_filetype\" value=\"{$row['disabled_filetype']}\"> disabled<br><input type=radio name=typecheck value='all'>All types</td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Uploads?</td><td class=tdrow2><input type=\"text\" name=\"tbl_max_uploads\" value=\"{$row['max_uploads']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Form Upload?</td><td class=tdrow2><select name=\"tbl_formupload\">{$row['formupload']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow URL Upload?</td><td class=tdrow2><select name=\"tbl_urlupload\">{$row['urlupload']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow FTP Upload?</td><td class=tdrow2><select name=\"tbl_ftpupload\">{$row['ftpupload']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Flash Upload?</td><td class=tdrow2><select name=\"tbl_flashupload\">{$row['flashupload']}</select></td></tr>\r\n<tr><th colspan=2 align=center><b>Download Options:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Resume Download?<br><span class=note>Support download-accelerators</span></td><td class=tdrow2><select name=\"tbl_dl_resume\">{$row['dl_resume']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Threads<br><span class=note>Max allowed threads to download by download-accelerators</span></a></td><td class=tdrow2><input type=\"text\" name=\"tbl_dl_threads\" value=\"{$row['dl_threads']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Speed<br><span class=note>Max download speed,setting 0 as unlimited</span></td><td class=tdrow2><input type=\"text\" name=\"tbl_dl_speed\" value=\"{$row['dl_speed']}\">KB/s</td></tr>\r\n<tr><td class=tdrow1 width=40%>Max bandwidth per hour from same IP<br><span class=note>Setting 0 as unlimited</span></td><td class=tdrow2><input type=\"text\" name=\"tbl_dl_sizebyhour\" value=\"{$row['dl_sizebyhour']}\" onclick=\"calsize(this,'dlsize');\" onchange=\"calsize(this,'dlsize');\"><span class=note>Enter <b>2b/k/m/g</b> directly or the digital bytes 1024</span><div id=dlsize></div></td></tr>\r\n<tr><td class=tdrow1 width=40%>Max Downloads IPs<br><span class=note>Max IPs to download at the same time,setting 0 as unlimited</span></td><td class=tdrow2><input type=\"text\" name=\"tbl_dl_ips\" value=\"{$row['dl_ips']}\"><span class=note></span></tr>\r\n<tr><td class=tdrow1 width=40%>Max Access Keys<br><span class=note>How many times for the same access key can be used, setting 0 as unlimited<br>This option is to forbid users to click 'download' button to get multiple download session for same file!</span></td><td class=tdrow2><input type=\"text\" name=\"tbl_dl_maxsbyip\" value=\"{$row['dl_maxsbyip']}\"></tr>\r\n<tr><td class=tdrow1 width=40%>Allow Direct Download?<br><span class=note>Enabled will ignore password checking, wait time and captcha checking</span></td><td class=tdrow2><select name=\"tbl_dl_direct\">{$row['dl_direct']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Enable captcha check before download?</td><td class=tdrow2><select name=\"tbl_dl_captcha\">{$row['dl_captcha']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Password Protection of Download?</td><td class=tdrow2><select name=\"tbl_dl_password\">{$row['dl_password']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Wait time for Download?<br><span class=note>Setting 0 to disable wait time</span></td><td class=tdrow2><input name=\"tbl_dl_waittime\" value=\"{$row['dl_waittime']}\">seconds</td></tr>\r\n<tr><td class=tdrow1 width=40%>Download link expire time<br><span class=note>Download link</span></td><td class=tdrow2><input name=\"tbl_dl_timeout\" value=\"{$row['dl_timeout']}\">seconds</td></tr>\r\n<tr><td class=tdrow1 width=40%>Download Location checking?</td><td class=tdrow2><select name=\"tbl_dl_checkarea\">{$row['dl_checkarea']}</select><a href=\"?admin=rule\">Create your download rules for package</a></td></tr>\r\n<tr><th colspan=2 align=center><b>Payment Information:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n<tr><td class=tdrow1 width=40%>Payment Type:</td><td class=tdrow2><select name=\"tbl_payment_type\">{$row['payment_type']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Subscription Unit:(M,D,Y)</td><td class=tdrow2><input type=\"text\" name=\"tbl_subscr_unit\" value=\"{$row['subscr_unit']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%>Subscription Period:<br><span class=note>Multiple period can be seprated by \",\",eg:<b>1,2</b></span></td><td class=tdrow2><input type=\"text\" name=\"tbl_subscr_period\" value=\"{$row['subscr_period']}\">(M,D,Y)</td></tr>\r\n<tr><td class=tdrow1 width=40%>Subscription Fee:<br><span class=note>Multiple fee corresponding to period can be seprated by \",\",eg:<b>5.00,10.00</b>,Leave blank to auto assign this package as free package</span></td><td class=tdrow2><input type=\"text\" name=\"tbl_subscr_fee\" value=\"{$row['subscr_fee']}\">/(M,D,Y)</td></tr>\r\n<tr><th colspan=2 align=center><b>Misc. Infomation:</b><input type=\"submit\" name=\"update\" value=\"{$submit_btn}\"></th></tr>\r\n<tr><td class=tdrow1 width=40%>Show site Ad?</td><td class=tdrow2><select name=\"tbl_show_site_ads\">{$row['show_site_ads']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Show sponser Ad?</td><td class=tdrow2><select name=\"tbl_show_sponser_ads\">{$row['show_sponser_ads']}</select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Auto validate files if thumbnails is submited?</td><td class=tdrow2><select name=\"tbl_validate_type\"><option value='auto'>Yes</option><option value='admin'>No,admin approve</option></select></td></tr>\r\n<tr><td class=tdrow1 width=40%>Can create folder?</td><td class=tdrow2><select name=\"tbl_folder\">{$row['folder']}</select></td></tr>\r\n</form>\r\n<script>\r\ns=document.myform.typecheck;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$typecheck}')\r\n   {\r\n     s[i].checked='true';\r\n     break;\r\n   }\r\n}\r\ns=document.myform.tbl_validate_type.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['validate_type']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\nfunction validateForm(obj)\r\n{\r\n    if(obj.tbl_name.value.length==0) {\r\n    alert(\"Name is empty!\");\r\n    obj.tbl_name.focus();\r\n    return false;\r\n    }\r\n    if(obj.tbl_sizelimit.value.length==0) {\r\n    alert(\"Size limit is not setted!\");\r\n    obj.tbl_sizelimit.focus();\r\n    return false;\r\n    }\r\n    if(obj.tbl_formupload.value==0&&obj.tbl_urlupload.value==0&&obj.tbl_ftpupload.value==0) {\r\n    alert(\"You must select a upload method!\");\r\n    obj.tbl_formupload.focus();\r\n    return false;\r\n    }\r\n    return true;\r\n}\r\n</script>";
}

function showgroupth( $data )
{
    global $baseUrl;
    echo "<form name=myform action=\"\" method=\"POST\">\r\n    <table class=adminlist border=0 align=center>\r\n    <tr>\r\n\t\t<th align=\"center\" colspan='6'>Host Package List</th>\r\n    </tr>\r\n    <tr>\r\n    <td align=left class='tdrow1' width=5% ><input type='checkbox' name=allbox onclick=checkAll()></td>\r\n    <td align=left class='tdrow1' width=30%><b>Package Name</b></td>\r\n    <td align=left class='tdrow1' width=15%><b>Members</b></td>\r\n    <td align=left class='tdrow1' width=20%><b>Total Bandwidth/Webspace</b></td>\r\n    <td align=left class='tdrow1' width=10%><b>Guest Package</b></td>\r\n    <td align=left class='tdrow1' width=25%><b>Action</b></td>\r\n    </tr>";
}

function showgrouprow( $data )
{
    global $baseUrl;
    echo "    <tr>\r\n    <td align=left class='tdrow1'><input type='checkbox' name=idList[{$data['id']}] {$disabled}></td>\r\n    <td align=left class='tdrow2'>{$data['name']}</a></td>\r\n    <td align=left class='tdrow1'>{$data['num']}</td>\r\n    <td align=left class='tdrow2'>{$data['bandwidth']}/{$data['webspace']}</td>\r\n    <td align=left class='tdrow1'>{$data['isguest']}</td>\r\n    <td align=left class='tdrow2'>{$data['del']}</td>\r\n    </tr>";
}

function showgrouptt( )
{
    global $baseUrl;
    global $input;
    global $db;
    global $SET;
    $yes = "<option value=1 selected'>yes</option><option value=0>no</option>";
    $no = "<option value=1 >yes</option><option value=0 selected>no</option>";
    $db->setquery( "select * from groups" );
    $db->query( );
    $groups = $db->loadrowlist( );
    $package_list = "";
    foreach ( $groups as $group )
    {
        $package_list .= "<option value=".$group[id].">".$group[name]."</option>";
    }
    echo "    <tr><td align='left' class='tdrow1' valign='middle' colspan=6>\r\n    <a name=\"add\" href={$baseUrl}&admin=group&act=add>Add a Host Package</a >\r\n    <td>\r\n    </tr>\r\n    </table>\r\n    </form>\r\n<form name=jump action=\"?admin=group\" method=POST>\r\n<input type=\"hidden\" name=\"admin\" value=\"group\">\r\n<input type=\"hidden\" name=\"act\" value=\"upgrade\">\r\n<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0' class=adminlist>\r\n<tr>\r\n<th align=\"center\" colspan='6'>Host Package Setting</th>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'>\r\n<b>Batch Upgrade/Downgrade</b>:<select name=\"old_gid\">{$package_list}</select>\r\n<b>to</b>:<select name=\"new_gid\">{$package_list}</select> <input type=submit name=update value=\"Upgrade/Downgrade\">\r\n</td>\r\n</tr>\r\n</table>\r\n</form>\r\n<form name=settingform action=\"?admin=group\" method=POST>\r\n<input type=\"hidden\" name=\"admin\" value=\"group\">\r\n<input type=\"hidden\" name=\"act\" value=\"setting\">\r\n</td>\r\n</tr>\r\n</table>\r\n</form>\r\n";
}

if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
$act = $input[act];
switch ( $act )
{
    case "add" :
        if ( isset( $input[update] ) )
        {
            if ( $DEMOMODE )
            {
                redirect( "admin=set", $DEMOTEXT );
            }
            $group = new table( $db, "groups", "id" );
            $group->inputdata( );
            $group->allowed_filetype = remove_blank( $group->allowed_filetype );
            $group->disabled_filetype = remove_blank( $group->disabled_filetype );
            if ( $input[typecheck] == "allow" && $group->allowed_filetype )
            {
                $group->disabled_filetype = "";
            }
            else if ( $input[typecheck] == "disable" && $group->disabled_filetype )
            {
                $group->allowed_filetype = "";
            }
            else
            {
                $group->disabled_filetype = $group->disabled_filetype = "";
            }
            $group->insert( );
            redirect( "admin=group", "Add Successfully!" );
        }
        addeditgroup( );
        break;
    case "edit" :
        if ( isset( $input[update] ) )
        {
            if ( $DEMOMODE )
            {
                redirect( "admin=set", $DEMOTEXT );
            }
            $group = new table( $db, "groups", "id" );
            $group->inputdata( );
            $group->allowed_filetype = remove_blank( $group->allowed_filetype );
            $group->disabled_filetype = remove_blank( $group->disabled_filetype );
            if ( $input[typecheck] == "allow" && $group->allowed_filetype )
            {
                $group->disabled_filetype = "";
            }
            else if ( $input[typecheck] == "disable" && $group->disabled_filetype )
            {
                $group->allowed_filetype = "";
            }
            else
            {
                $group->disabled_filetype = $group->disabled_filetype = "";
            }
            $group->update( );
            redirect( "admin=group", "Update Successfully!" );
        }
        addeditgroup( );
        break;
    case "guest" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        $db->setquery( "update groups set guest=1 where id='{$input['id']}'" );
        $db->query( );
        $db->setquery( "update groups set guest=0 where id!='{$input['id']}'" );
        $db->query( );
        redirect( "admin=group", "Update Successfully!" );
        break;
    case "upgrade" :
        if ( isset( $input[update] ) )
        {
            if ( $DEMOMODE )
            {
                redirect( "admin=set", $DEMOTEXT );
            }
            $input[old_gid] = intval( $input[old_gid] );
            $input[new_gid] = intval( $input[new_gid] );
            $db->setquery( "select * from groups where id='{$input['new_gid']}'" );
            $db->query( );
            if ( $db->getnumrows( ) )
            {
                $db->setquery( "update users set gid='{$input['new_gid']}' where gid='{$input['old_gid']}'" );
                $db->query( );
                redirect( "admin=group", "Update Successfully!" );
            }
        }
        break;
    case "del" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        $db->setquery( "delete from groups where id={$input['id']} " );
        $db->query( );
        redirect( "admin=group", "Delete Successfully!" );
        break;
    default :
        showlistform( );
}
?>
