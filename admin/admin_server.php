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
    global $picSet;
    global $db;
    $per_num = 10;
    $input[s] = intval( $input[s] );
    $db->setquery( "select * from server" );
    $db->query( );
    $rows = $db->loadrowlist( );
    showserverth( $data );
    foreach ( $rows as $row )
    {
        $data[id] = $row[server_id];
        $data[name] = "<a href='{$baseUrl}&admin=images&search=1&img_server={$data['id']}' title='click to view files'>{$row['name']}</a>";
        $data[hosted] = $row[hosted];
        $data[webspace] = convertsize( $row[webspace] );
        $data[max_webspace] = $row[max_webspace] ? convertsize( $row[max_webspace] ) : "Unlimited";
        $data[warn_webspace] = $row[warn_webspace] ? convertsize( $row[warn_webspace] ) : "Unlimited";
        if ( $row[enabled] == 1 )
        {
            $status = 0;
            $statustext = "Enabled";
        }
        else
        {
            $status = 1;
            $statustext = "Disabled";
        }
        $data[status] = "<a href={$baseUrl}?&admin=server&act=status&code={$status}&id={$row['server_id']} onclick=\"return confirm('Before you enable this server,please click [Test server] to check the possible errors!')\">{$statustext}</a>";
		
        $data[del] = "<a href={$baseUrl}&admin=server&act=edit&id={$row['server_id']}>Edit</a>";
        //$data[del] .= "::<a href={$baseUrl}&admin=server&act=test&id={$row['server_id']}>Test server</a>";
		$data[del] .= "::<a href=sql_connection_test.php?sql_host={$row['sql_host']}&sql_port={$row['sql_port']}&sql_user={$row['sql_username']}&sql_pass={$row['sql_password']}&sql_db={$row['sql_db']} target=blank>WS SQL</a>"; // new add
		$data[del] .= "::<a href='{$row["http"]}{$row["domain"]}/admin/test_fs_db_connection.php' target=blank>FS SQL</a>"; // new add
		
		// Offline 0 = Currently Online, Offline 1 = Currently Offline
		if ($row[offline]) {
			$data[del] .= "::<a href={$baseUrl}&admin=server&act=offline&id={$row['server_id']}&current_status=0>On-line</a>";
		}
		else {
			$data[del] .= "::<a href={$baseUrl}&admin=server&act=offline&id={$row['server_id']}&current_status=1>Set Off-line</a>";
		}
		
		
        if ( $data[hosted] == 0 )
        {
            $data[del] .= "::<a href={$baseUrl}&admin=server&act=del&id={$row['server_id']} onclick=\"return confirm('Are You Sure Delete This Server?')\">Delete</a>";
        }
        showserverrow( $data );
        unset( $data );
    }
    showservertt( $pageLinks );
}

function loginftp( $ftp, $user, $pass, $initdir )
{
    $ftpconn = @ftp_connect( @$ftp );
    $login_result = @ftp_login( @$ftpconn, @$user, @$pass );
    if ( !$ftpconn || !$login_result )
    {
        echo "<font color=red>FTP::can't login ftp {$ftp} with {$user}</font><br>";
        @ftp_close( @$this->ftpconn );
        return false;
    }
    else
    {
        echo "FTP::login ftp {$ftp} with {$user}<br>";
    }
    if ( $initdir == "" )
    {
        $$initdir = "/";
    }
    if ( @!ftp_chdir( @$ftpconn, @$initdir ) )
    {
        echo "<font color=red>FTP::Can't change to directory {$initdir}!</font><br>";
        ftp_close( $ftpconn );
        return false;
    }
    else
    {
        echo "FTP::change to directory {$initdir}<br>";
    }
    return $ftpconn;
}

function makeconfig( $server )
{
    global $multi_server;
    $str = file_get_contents( $multi_server."/config.php" );
    $warning = "";
    if ( strpos( $str, "{SERVER_ID}" ) === false )
    {
        $warning .= "<tr><td class=tdrow1>\r\n<b>{SERVER_ID}</b>\r\n</td><td class=tdrow2><font color=red>Warning</font>:we can not find string '<b>{SERVER_ID}</b>' in file <b>fileserver/config.php</b>,\r\nyou may edit <b>config.php</b> on <b>{$server['domain']}</b> after installation  to make sure\r\n<font color=red>\$server_id='{$server['server_id']}';</font>\r\n</td></tr>";
    }
    if ( strpos( $str, "{CGI_TMP}" ) === false )
    {
        $warning .= "<tr><td class=tdrow1>\r\n<b>{CGI_TMP}</b>\r\n</td><td class=tdrow2><font color=red>Warning</font>:we can not find string '<b>{CGI_TMP}</b>' in file <b>fileserver/config.php</b>,\r\nyou may edit <b>config.php</b> on <b>{$server['domain']}</b> after installation  to make sure\r\n<font color=red>\$cgi_temp_dir='{$server['cgi_temp_dir']}';</font>\r\n</td></tr>";
    }
    if ( strpos( $str, "{PHP_TMP}" ) === false )
    {
        $warning .= "<tr><td class=tdrow1>\r\n<b>{PHP_TMP}</b>\r\n</td><td class=tdrow2><font color=red>Warning</font>:we can not find string '<b>{PHP_TMP}</b>' in file <b>fileserver/config.php</b>,\r\nyou may edit <b>config.php</b> on <b>{$server['domain']}</b> after installation to make sure\r\n<font color=red>\$php_temp_dir='{$server['php_temp_dir']}';</font>\r\n</td></tr>";
    }
    if ( strpos( $str, "{UPLOAD_DIR}" ) === false )
    {
        $warning .= "<tr><td class=tdrow1>\r\n<b>{UPLOAD_DIR}</b>\r\n</td><td class=tdrow2><font color=red>Warning</font>:we can not find string '<b>{UPLOAD_DIR}</b>' in file <b>fileserver/config.php</b>,\r\nyou may edit <b>config.php</b> on <b>{$server['domain']}</b> after installation  to make sure\r\n<font color=red>\$upload_dir='{$server['upload_dir']}';</font>\r\n</td></tr>";
    }
    if ( strlen( $warning ) )
    {
        echo "<table class=adminlist align=center>\r\n<tr><th align=\"center\" colspan=2>Warning</th></tr>\r\n{$warning}\r\n</table>";
    }
    $str = str_replace( "{SERVER_ID}", $server[server_id], $str );
    $str = str_replace( "{CGI_TMP}", $server[cgi_temp_dir], $str );
    $str = str_replace( "{PHP_TMP}", $server[php_temp_dir], $str );
    $str = str_replace( "{UPLOAD_DIR}", $server[upload_dir], $str );
    return $str;
}

function writeconfig( $str )
{
    $tmpfile = "../temp/".mytempname( "../temp", "config", ".php" );
    $fp = fopen( $tmpfile, "wb" );
    fputs( $fp, $str );
    fclose( $fp );
    return $tmpfile;
}

function testserver( $server_id )
{
    global $db;
    include( "../includes/http.php" );
    $db->setquery( "select * from server where server_id='{$server_id}'" );
    $db->query( );
    $row = $db->loadrow( );
    echo "<table class=adminlist align=center>";
    $res = getres( "http://".$row[domain]."/" );
    echo "<tr><th align=\"center\">Check Server Url....</th></tr>";
    if ( substr( $res[code], 0, 1 ) == "2" )
    {
        echo "<tr><td class=tdrow1>";
        echo "        <b>Main Server url</b>:<a href='http://{$row['domain']}'>http://{$row['domain']}</a> exists!";
        if ( strpos( strtolower( $res[body] ), "<title>" ) === false )
        {
            $res[body] = htmlentities( $res[body] );
            echo "        <br><font color=red size=2><b>Warning!</b></font>But It seem this server does not install zend optimizer or some errors accurs\r\n        <br><b>Response content</b>:<br><textarea cols=60 rows=3>{$res['body']}</textarea>\r\n        <br><b>Solution:</b>\r\n        <div class=quote>\r\n        1.Check this server has Zend Optimizer installed,if not,you need ask your host comapny to install it for you!Zend Optimizer is free to get from zend.com\r\n        <br>2.If you are sure it is installed,please check you upload PHP scripts via Binary Mode by FTP,if not,you need upload all PHP scripts(*.php) via Binary Mode by FTP.\r\n        </div>";
        }
        echo "</td></tr>";
    }
    if ( substr( $res[code], 0, 1 ) == "3" )
    {
        echo "<tr><td class=tdrow1>";
        echo "        <br><b>Remote server url<b>:<a href='http://{$row['domain']}'>http://{$row['domain']}</a> Exists!\r\n        <br>But because this is remote server,it will redirect to the main server <a href='{$res['loc']}'>{$res['loc']}</a>";
        echo "</td></tr>";
    }
    $res = getres( "http://".$row[domain]."/captcha.php" );
    if ( substr( $res[code], 0, 1 ) == "2" || substr( $res[code], 0, 1 ) == "3" )
    {
        echo "<tr><th align=\"center\">Check captcha Url....</th></tr>";
        echo "<tr><td class=tdrow1>";
        echo "        <b>Captcha Image url</b>:<a href='http://{$row['domain']}/captcha.php'>http://{$row['domain']}/captcha.php</a> Exists!";
        if ( strpos( strtolower( $res[body] ), "captcha.php" ) !== false )
        {
            $res[body] = strip_tags( $res[body] );
            echo "        <br><font color=red size=2><b>Warning!</b></font>But It seem this page has some error,the possible reason is that GD Module is not installed!\r\n        <br><b>Response content</b>:<br>\r\n        <textarea cols=60 rows=3>{$res['body']}</textarea>";
        }
        echo "</td></tr>";
    }
    echo "<tr><th align=\"center\">Check CGI-BIN Requirement Url:cgienv.cgi....</th></tr>";
    $res = getres( "http://".$row[cgiurl]."/cgienv.cgi" );
    if ( substr( $res[code], 0, 1 ) == "2" || substr( $res[code], 0, 1 ) == "3" )
    {
        echo "<tr><td class=tdrow1>";
        echo "        <br><b>CGI script</b>:<a href=\"http://{$row['cgiurl']}/cgienv.cgi\">http://{$row['cgiurl']}/cgienv.cgi</a> works fine!\r\n        <br><b>CGI-BIN Requirement Detecting:</b>\r\n        <div class=quote>\r\n        {$res['body']}\r\n        </div>";
        echo "</td></tr>";
    }
    if ( substr( $res[code], 0, 1 ) == "5" || substr( $res[code], 0, 1 ) == "4" )
    {
        $res[body] = strip_tags( $res[body] );
        echo "<tr><td class=tdrow1>";
        echo "        <br><font color=red size=2><b>Warning!</b></font><b>CGI script</b>:<a href='http://{$row['cgiurl']}/cgienv.cgi'>http://{$row['cgiurl']}/cgienv.cgi</a> show internal server errors!\r\n        <br><b>Response content</b>:<br><textarea cols=60 rows=3>{$res['body']}</textarea>\r\n        <br><b>Solution:</b>\r\n        <div class=quote>\r\n        Reupload this CGI file to this server,please note 2 things:\r\n        <br>1.Upload it via ASCII Mode by FTP\r\n        <br>2.Chmod the permission of this file to 0755 by FTP\r\n        </div>";
        echo "</td></tr>";
    }
    echo "<tr><th align=\"center\">Check CGI-BIN Upload Url:upload.cgi....</th></tr>";
    $res = getres( "http://".$row[cgiurl]."/upload.cgi" );
    if ( substr( $res[code], 0, 1 ) == "3" || substr( $res[code], 0, 1 ) == "2" )
    {
        echo "<tr><td class=tdrow1>";
        echo "        <br><b>CGI script</b>:<a href=\"http://{$row['cgiurl']}/upload.cgi\">http://{$row['cgiurl']}/upload.cgi</a> works fine!";
        echo "</td></tr>";
    }
    if ( substr( $res[code], 0, 1 ) == "5" || substr( $res[code], 0, 1 ) == "4" )
    {
        $res[body] = strip_tags( $res[body] );
        echo "<tr><td class=tdrow1>";
        echo "        <br><font color=red size=2><b>Warning!</b></font><b>CGI script</b>:<a href='http://{$row['cgiurl']}/upload.cgi'>http://{$row['cgiurl']}/upload.cgi</a> show internal server errors!\r\n        <br><b>Response content</b>:<br><textarea cols=60 rows=3>{$res['body']}</textarea>\r\n        <br><b>Solution:</b>\r\n        <div class=quote>\r\n        Reupload this CGI file to this server,please note 2 things:\r\n        <br>1.Upload it via ASCII Mode by FTP\r\n        <br>2.Chmod the permission of this file to 0755 by FTP\r\n        </div>";
        echo "</td></tr>";
    }
    echo "</table>";
}

function getres( $uri )
{
    $http = new http_class( );
    $http->follow_redirect = 0;
    $error = $http->getrequestarguments( $uri, $arguments );
    $arguments['Headers']['Pragma'] = "nocache";
    $error = $http->open( $arguments );
    if ( $error == "" )
    {
        $error = $http->sendrequest( $arguments );
        if ( $error == "" )
        {
            $headers = array( );
            $error = $http->readreplyheaders( $headers );
            if ( $error == "" )
            {
                $red_location = $headers['location'];
                do
                {
                    $error = $http->readreplybody( $body, 4096 );
                    if ( strlen( $body ) == 0 )
                    {
                        break;
                    }
                    $content .= $body;
                } while ( 1 );
            }
        }
    }
    $http->close( );
    return array( "code" => $http->response_status, "loc" => $red_location, "body" => $content );
}

function editaddserver( )
{
    global $baseUrl;
    global $input;
    global $db;
    $id = intval( $input[id] );
    if ( $id )
    {
        $db->setquery( "select * from server where server_id='{$id}'" );
        $db->query( );
        $row = $db->loadrow( );
        $input[act] = "edit";
        $mod_status = split( ",", $row[mod_status] );
        foreach ( $mod_status as $k => $status )
        {
            $mod_installed[$k] = $status == 1 ? " checked" : "";
        }
    }
    else
    {
        $input[act] = "add";
        $row[upload_dir] = "files";
        $row[cgi_temp_dir] = "temp";
        $row[php_temp_dir] = "temp";
        $row[url_prog_mode] = "ajax";
        $row[ftp_prog_mode] = "ajax";
    }
    $submit = ucfirst( $input[act] == "edit" ? "Update" : "Add" );
    $enabled = "<option value=1 selected>enabled</option><option value=0>disabled</option>";
    $disabled = "<option value=1 >enabled</option><option value=0 selected>disabled</option>";
    $row[keepext] = $row[keepext] == "yes" ? $enabled : $disabled;
    echo "<form action=\"index.php?admin=server\" name=myform method=\"post\">\r\n<input type=\"hidden\" name=\"act\" value=\"{$input['act']}\">\r\n<input type=\"hidden\" name=\"tbl_server_id\" value=\"{$row['server_id']}\">\r\n<table class=adminlist align=center>\r\n<tr><th align=\"center\" colspan=2>Server Info</th></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>ID:</b></td><td class=tdrow2><input type=\"text\" size=4 name=\"tbl_server_id\" value=\"{$row['server_id']}\"></td></tr>\r\n<tr><tr><td class=tdrow1 align=right width=40%><b>Name:</b></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_name\" value=\"{$row['name']}\"></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>HTTP Protocol:</b></td><td class=tdrow2><select name=\"tbl_http\"><option value='http://'>http://</option><option value='https://'>https://</option></select></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Domain:</b></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_domain\" value=\"{$row['domain']}\"><br>For example:<br><font color=red>newserver.com</font>,<br><font color=red>newserver.com/subdir</font> <br>Note:no \"http://\" and no \"/\" at the end of url</td></tr>\r\n
	
	
	<tr><td class=tdrow1 align=right width=40%><b>Direct/CDN Response Domain:</b></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_cdn\" value=\"{$row['cdn']}\"><br />
	<font color=red>s001pp.xun6.com/s001cdn.xun6.com</font></td></tr>\r\n
	
	<tr><td class=tdrow1 align=right width=40%><b>CGI Url:</b></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_cgiurl\" value=\"{$row['cgiurl']}\"><br>For example:<br><font color=red>newserver.com/cgi-bin</font>,<br><font color=red>newserver.com/cgi-bin/subdir</font> <br>Note:no \"http://\" and no \"/\" at the end of url</td></tr>\r\n<tr><th align=\"center\" colspan=2>SQL Setting</th></tr>"
	
	."<tr><td class=tdrow1 align=right width=40%><b>SQL host</b><br><span class=note>domain name or ip address</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_sql_host\" value=\"{$row['sql_host']}\"></td></tr>"
	
	."<tr><td class=tdrow1 align=right width=40%><b>SQL port</b><br><span class=note>port that used</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_sql_port\" value=\"{$row['sql_port']}\"></td></tr>"
	
	."<tr><td class=tdrow1 align=right width=40%><b>SQL username</b><br><span class=note>username that used for login</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_sql_username\" value=\"{$row['sql_username']}\"></td></tr>"
	
	."<tr><td class=tdrow1 align=right width=40%><b>SQL password</b><br><span class=note>password that used for login</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_sql_password\" value=\"{$row['sql_password']}\"></td></tr>"
	."<tr><td class=tdrow1 align=right width=40%><b>SQL database</b><br><span class=note>database name</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_sql_db\" value=\"{$row['sql_db']}\"></td></tr>"
	."<tr><th align=\"center\" colspan=2>Premium Setting</th></tr>"
	."<tr><td class=tdrow1 align=right width=40%><b>Is Premium Server?</b><br><span class=note>select yes or no!</span></td><td class=tdrow2><input type=\"text\" size=3 maxlength=1 name=\"tbl_is_premium\" value=\"{$row['is_premium']}\"></td></tr>"
	."<tr><td class=tdrow1 align=right width=40%><b>Private Port IP</b><br><span class=note>very important, use to transfer files!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_private_ip\" value=\"{$row['private_ip']}\"></td></tr>"
	."<tr><td class=tdrow1 align=right width=40%><b>FTP Username</b><br><span class=note>required!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_ftp_username\" value=\"{$row['ftp_username']}\"></td></tr>"
	."<tr><td class=tdrow1 align=right width=40%><b>FTP Password</b><br><span class=note>required!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_ftp_password\" value=\"{$row['ftp_password']}\"></td></tr>"
	."<tr><th align=\"center\" colspan=2>Upload setting</th></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Upload directory</b><br><span class=note>Path relative to installation!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_upload_dir\" value=\"{$row['upload_dir']}\"></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Keep extenstion</b><br><span class=note>Keep extenstion of uploaded files, recommend to disable the option!</span></td><td class=tdrow2><select name=\"tbl_keepext\">{$row['keepext']}</select></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Temporary directory of CGI upload</b><br><span class=note>Absolute path or path relative to installation!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_cgi_temp_dir\" value=\"{$row['cgi_temp_dir']}\"></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Temporary directory of PHP upload</b><br><span class=note>Absolute path or path relative to installation!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_php_temp_dir\" value=\"{$row['php_temp_dir']}\"></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Progress mode of URL upload</b><br><span class=note></span></td><td class=tdrow2><select name=\"tbl_url_prog_mode\"><option value='syn'>Synchronal Mode</option><option value='ajax'>AJAX Mode</option></select></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Progress mode of FTP upload</b><br><span class=note></span></td><td class=tdrow2><select name=\"tbl_ftp_prog_mode\"><option value='syn'>Synchronal Mode</option><option value='ajax'>AJAX Mode</option></select></td></tr>\r\n<tr><th align=\"center\" colspan=2>Server Moniter</th></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Max webspace allowed:</b><br><span class=note>Leave 0 will disable this check! Once the used webspace get this value,your site will be forced to be offline!</span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_max_webspace\" value=\"{$row['max_webspace']}\" onclick=\"calsize(this,'maxsize');\" onchange=\"calsize(this,'maxsize');\"><span class=note>Enter <b>2b/k/m/g</b> directly or the digital bytes 1024</span><div id=maxsize></div></td></tr>\r\n<tr><td class=tdrow1 align=right width=40%><b>Warn level of webspace:</b><br><span class=note>Leave 0 will disable this check! Once the used webspace get this value,you will be emailed about it </span></td><td class=tdrow2><input type=\"text\" size=30 name=\"tbl_warn_webspace\" value=\"{$row['warn_webspace']}\" onclick=\"calsize(this,'warnsize');\" onchange=\"calsize(this,'warnsize');\"><span class=note>Enter <b>2b/k/m/g</b> directly or the digital bytes 1024</span><div id=warnsize></div></td></tr>\r\n<tr><td align=\"center\" class=\"tdrow1\" colspan=\"2\"><input type=\"submit\" name=\"done\" value=\"{$submit} Server\"></td></tr>\r\n</table></form>\r\n<script>\r\ns=document.myform.tbl_http.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['http']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.myform.tbl_url_prog_mode.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['url_prog_mode']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.myform.tbl_ftp_prog_mode.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['ftp_prog_mode']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script>";   
}

function showserverth( $data )
{
    global $baseUrl;
    echo "<form name=myform action=\"?\" method=\"POST\">\r\n    <table class=adminlist border=0 align=center>\r\n    <tr>\r\n\t<th align=\"center\" colspan='7'>Server List</td>\r\n    </tr>\r\n    <tr>\r\n    <td align=left class='tdrow1' width=5% ><input type='checkbox' name=allbox onclick=checkAll()></td>\r\n    <td align=left class='tdrow1' width=10%><b>Server ID</b></td>\r\n    <td align=left class='tdrow1' width=20%><b>Name</b></td>\r\n    <td align=left class='tdrow1' width=10%><b>Hosted Files</b></td>\r\n    <td align=left class='tdrow1' width=25%><b>Used/Warn/Max Space</b></td>\r\n    <td align=left class='tdrow1' width=10%><b>Status</b></td>\r\n    <td align=left class='tdrow1' width=25%><b>Action</b></td>\r\n    </tr>";
}

function showserverrow( $data )
{
    global $baseUrl;
    echo "    <tr>\r\n    <td align=left class='tdrow1'><input type='checkbox' name=idList[{$data['id']}] {$disabled}></td>\r\n    <td align=left class='tdrow2'>{$data['id']}</a></td>\r\n    <td align=left class='tdrow1'>{$data['name']}</a></td>\r\n    <td align=left class='tdrow2'>{$data['hosted']}</td>\r\n    <td align=left class='tdrow1'>{$data['webspace']}/{$data['warn_webspace']}/{$data['max_webspace']}</td>\r\n    <td align=left class='tdrow2'>{$data['status']}</td>\r\n    <td align=left class='tdrow1'>{$data['del']}</td>\r\n    </tr>";
}

function showservertt( $pageLinks )
{
    global $baseUrl;
    global $input;
    $pid = intval( $input[id] );

    echo "    <tr><td align='left' class='tdrow1' valign='middle' colspan=7>\r\n    <a name=\"add\" href={$baseUrl}&admin=server&act=add>  Add new server </a>&nbsp; <a name=\"recount\" href={$baseUrl}&admin=server&act=recount>  Recount Hosted files </a>\r\n    <br>   <br>\r\n    <div class=quote>\r\n    Note:\r\n    <br>1.<b>Server ID</b> will be used in config.php on the main server and remote server!\r\n     <br>.Before you enable this server,please click [<b>Test server</b>] to check the possible errors of the server!\r\n    <br>3.<b>[Test server]</b> function will detect the possible errors of the server!\r\n    <br>4.<b>[Delete server]</b> function will work only if you have 0 hosted files on that server. If you want to remove one server, please make sure you delete the hosted files first!\r\n    <br>5.A server with the <b>[Disabled]</b> status can still serve previously uploaded files, but will not allow any new files to be uploaded!\r\n    </div>\r\n    <td></tr>\r\n    </table>\r\n    </form>";
}

if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
@set_time_limit( 0 );
flush( );
$act = $input[act];
$files = array( "flashvars.php", "progress.php", "servertimer.php", "uploading.php", "index.php", "getfile.php", "crossdomain.xml" );
$emptydirs = array( "files", "temp", "thumb" );
$dirs = array( "includes", "cgi-bin", "admin" );
$multi_server = "../fileserver";
switch ( $act )
{
    case "add" :
        if ( isset( $input[done] ) )
        {

            $server = new table( $db, "server", "server_id" );
            $server->inputdata( );
            $server->mod_status = intval( $input[mod_status][0] ).",".intval( $input[mod_status][1] ).",".intval( $input[mod_status][2] );
            $server->insert( );
            redirect( "admin=server", "Add Successfully!" );
        }
        editaddserver( );
        break;
    case "edit" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        if ( isset( $input[done] ) )
        {

            $server = new table( $db, "server", "server_id" );
            $server->inputdata( );
            $server->mod_status = intval( $input[mod_status][0] ).",".intval( $input[mod_status][1] ).",".intval( $input[mod_status][2] );
            $server->update( );
            redirect( "admin=server", "Update Successfully!" );
        }
        editaddserver( );
        break;
    case "del" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        $db->setquery( "delete from server where webspace=0 and server_id='{$input['id']}'" );
        $db->query( );
        redirect( "admin=server", "Delete Successfully!" );
        break;
    case "test" :
        if ( !intval( $input[id] ) )
        {
            redirect( "admin=server", "No server to Test!" );
        }
        testserver( intval( $input[id] ) );
        break;
    case "setup" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        setupserver( intval( $input[id] ) );
        break;
    case "download" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        break;
    case "recount" :
        $db->setquery( "update server set hosted='0',webspace='0'" );
        $db->query( );
        $db->setquery( "select server_id,count(id) as hosted,sum(size) as webspace from files where deleted=0 group by server_id " );
        $db->query( );
        $rows = $db->loadrowlist( );
        foreach ( $rows as $row )
        {
            $db->setquery( "update server set hosted='{$row['hosted']}',webspace='{$row['webspace']}' where server_id='{$row['server_id']}' " );
            $db->query( );
        }
        redirect( "admin=server", "Recount Files Done..." );
        break;
    case "status" :

        $db->setquery( "update server set enabled='{$input['code']}' where server_id='{$input['id']}' " );
        $db->query( );
        redirect( "admin=server", "Server Updated..." );
        break;
	case "offline" :
		if ($input['current_status']) {
        	$db->setquery("update server set offline='1' where server_id={$input['id']}");
        	$db->query( );
        	 redirect( "admin=server", "Server Set Online Successfully!" );
		}
		else {
			$db->setquery("update server set offline='0' where server_id={$input['id']}");
        	$db->query( );
        	 redirect( "admin=server", "Server Set Offline Successfully!" );
		}
		break;
	
    default :
        showlistform( );
        break;
}
?>