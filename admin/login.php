<?php
/*************************************/
/*                                   */
/*  MFHS Version : 1.2               */
/*  Dezended & Nulled by: LepeNU     */
/*  Release Date: 1/12/2007          */
/*                                   */
/*************************************/

define( "IN_ADMIN", 1 );
include( "../includes/inc2.php" );
$db->setquery( "select set_id,adminemail,admin,pass,pass_salt,reset_date,fail_times,max_failtimes,last_fail,last_login from setting where set_id=1" );
$db->query( );
$row = $db->loadrow( );
if ( $input[act] == "login" && IS_POST )
{
    $_SESSION['admin_last_click'] = time( );
    if ( $row[max_failtimes] <= $row[fail_times] && time( ) - 900 < $row[last_fail] )
    {
        $input[act] = "";
        $output = "<br><font color=red>Wrong username or password. You have used up your failed login quota! Please wait 15 minutes before trying again. <br>Don't forget that the password is case sensitive. Try to 'Email your password' bu using the second form!</font>";
        mail( $row[adminemail], "**Hack access to your admin panel!**", "Access IP:{$input['IP_CLIENT']}\n", "FROM:{$row['adminemail']}" );
    }
    else
    {
        if ( $row[set_id] == 1 && $row[admin] == $input[user] && $row[pass] == md5( md5( $input[pass] ).$row[pass_salt] ) )
        {
            $_SESSION[admin_logined] = 1;
            $_SESSION[last_login] = $row[last_login];
            $db->setquery( "update setting set fail_times=0,admin_lastclick='{$_SESSION['admin_last_click']}',admin_ip='{$input['IP_CLIENT']}',last_login='".time( )."'" );
            $db->query( );
            $last_login = intval( $_SESSION[last_login] );
            $db->setquery( "select count(*) as nums from files where time>{$last_login}" );
            $db->query( );
            $stats = $db->loadrow( );
            $db->setquery( "update setting set last_stats='{$stats['nums']}' where set_id=1" );
            $db->query( );
            header( "location:index.php?admin=".$input[admin] );
            exit( );
        }
        else
        {
            if ( time( ) - 900 < $row[last_fail] )
            {
                $row[fail_times] = intval( $row[fail_times] );
                $db->setquery( "update setting set fail_times={$row['fail_times']}+1,admin_ip='{$input['IP_CLIENT']}',last_fail='".time( )."'" );
            }
            else
            {
                $db->setquery( "update setting set fail_times=1,admin_ip='{$input['IP_CLIENT']}',last_fail='".time( )."'" );
            }
            $db->query( );
            $output = "<br><font color=red>You have used ".( $row[fail_times] + 1 )." out of {$row['max_failtimes']} login attempts. After all {$row['max_failtimes']} have been used, you will be unable to login for 15 minutes.</font>";
        }
    }
}
if ( $input[act] == "emailpassword" && IS_POST )
{
    $_SESSION['admin_last_click'] = time( );
    if ( $row[admin] == $input[user] )
    {
        $reset_date = time( );
        $reset_hash = md5( md5( $reset_date ).$row[pass_salt] );
        $db->setquery( "update setting set reset_date='{$reset_date}' where set_id=1" );
        $db->query( );
        $reset_url = "{$baseWeb}/admin/login.php?act=reset&reset_hash={$reset_hash}";
        mail( $row[adminemail], "*Reset Admin Password!*", "Reset Password:{$reset_url}\n", "FROM:{$row['adminemail']}" );
        $output2 = "<br><font color=red>Emailed!</font>";
    }
    else
    {
        $output2 = "<br><font color=red>Error Username!</font>";
    }
}
if ( $input[act] == "reset" && IS_POST )
{
    $_SESSION['admin_last_click'] = time( );
    if ( time( ) - $row[reset_date] < 3600 && md5( md5( $row[reset_date] ).$row[pass_salt] ) == $input[reset_hash] )
    {
        $newpass = md5( md5( $input[reset_pass] ).$row[pass_salt] );
        $db->setquery( "update setting set pass='{$newpass}' where set_id=1" );
        $db->query( );
        $output = "<br><font color=red>Password reset!</font>";
        $input[act] = "login";
    }
    else
    {
        $output3 = "<br><font color=red>Error reset hash or expired!</font>";
    }
}
echo "<html><title>XUN6 Free File Hosting Administration V0.2</title><body>\r\n<style>\r\n  TABLE, TR, TD                   { font-family: Verdana,Arial; font-size: 12px;  }\r\n  BODY                            { font: 10px Verdana; background-color: #FCFCFC; padding: 0; margin: 0 }\r\n\r\n  a:link, a:visited, a:active     { color: #000055 }\r\n  a:hover                         { color: #333377; text-decoration: underline }\r\n  FORM                            { padding: 0; margin: 0 }\r\n  #textinput { background-color: #EEEEEE; color:#000000; font-family:Verdana, Arial; font-size:10px; width:80% }\r\n\r\n  .textbox                        { border: 1px solid black; padding: 1px; width: 100% }\r\n  .headertable                    { background-color: #FFFFFF; border: 1px solid black; padding: 2px }\r\n  .title                          { font-size: 10px; font-weight: bold; line-height: 150%; color: #FFFFFF; height: 26px; background-image: url(./tile_back.gif) }\r\n  .tablewrap {background-color:#EEF2F7;\r\n            border-bottom:1px solid #D1DCEB;\r\n            border-right:1px solid #D1DCEB ;\r\n\t\t    border-top:1px solid #FFF;\r\n\t\t\tborder-left:1px solid #FFF; }\r\n  .tdrow1 { background-color:#EEF2F7;\r\n\t\t\t\t\t          border-bottom:1px solid #D1DCEB;\r\n\t\t\t\t\t          border-right:1px solid #D1DCEB ;\r\n\t\t\t\t\t          border-top:1px solid #FFF;\r\n\t\t\t\t\t          border-left:1px solid #FFF;\r\n\t\t\t\t\t        }\r\n  .tdrow2 { background-color:#F5F9FD;\r\n\t\t\t\t\t\t\t  border-bottom:1px solid #D1DCEB;\r\n\t\t\t\t\t          border-right:1px solid #D1DCEB;\r\n\t\t\t\t\t          border-top:1px solid #FFF;\r\n\t\t\t\t\t          border-left:1px solid #FFF;\r\n  .quote {\r\n\tmargin: 4px;\r\n\tborder: 1px solid #cccccc;\r\n\tbackground-color: #E9ECEF;\r\n\tpadding: 10px;\r\n\tfont-size: 12px;\r\n\tcolor: #254D78;\r\n}\r\n</style>\r\n<br><br> <br><br>\r\n<div class=quote>";
if ( $input[act] != "reset" )
{
    echo "<form action=\"login.php?act=login\" method=\"post\" name='loginform'>\r\n  <table width=40% align=center cellspacing=\"1\" class=tdrow2>\r\n  <tr>\r\n    <td colspan=2 align=center><h3>XUN6 Free File Hosting Administration V0.2</h3></td>\r\n  </tr>\r\n  <tr>\r\n    <td width=40% align=right>Username</td>\r\n    <td><input type='text' name='user' id='textinput' size='20' /></td>\r\n  </tr>\r\n  <tr>\r\n    <td align=right>Password</td>\r\n    <td><input type='password' name='pass' id='textinput' size='20' /></td>\r\n  </tr>\r\n  <tr>\r\n    <td align=right>Start In</td>\r\n    <td><select name='admin' id='textinput'>\r\n    <option value=''>Index stats</option>\r\n    <option value=set>Site config</option>\r\n    <option value=images>Unvalidated Files</option>\r\n    <option value=server>Server config</option>\r\n    <option value=groups>Host package</option>\r\n    <option value=users>User list</option>\r\n    <option value=report>File Reports</option>\r\n    <option value=gateway>Payment Gateway</option>\r\n    <option value=payment>Payment records</option>\r\n    </select>\r\n    </td>\r\n  </tr>\r\n  <tr>\r\n    <td colspan=2 align=center><input type=\"submit\" name='submit' value=\"Log me in\" class='forminput' /></td>\r\n  </tr>\r\n  </table>\r\n  <div align=center class=quote width=60%>\r\n  {$output}</div>\r\n</form>\r\n<script>\r\nvar s=document.loginform.admin.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$input['admin']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script>\r\n<br>\r\n<form action=\"login.php?act=emailpassword\" method=\"post\" name='emailform'>\r\n<input type=hidden name=admin value={$input['admin']}>\r\n  <center>{$output2}</center>\r\n  <table width=40% align=center cellspacing=\"1\" class=tdrow2>\r\n  <tr>\r\n    <td colspan=2 align=center>Email your password:</td>\r\n  </tr>\r\n  <tr>\r\n    <td width=40% align=right>Admin Account</td>\r\n    <td><input type='text' name='user' id='textinput' size='20' /></td>\r\n  </tr>\r\n  <tr>\r\n    <td colspan=2 align=center><input type=\"submit\" name='submit' value=\"Get password\" class='forminput' /></td>\r\n  </tr>\r\n  </table>\r\n</form>\r\n<br>";
}
else
{
    echo "<form action=\"login.php?act=reset\" method=\"post\" name='resetform'>\r\n<input type=hidden name=admin value={$input['admin']}>\r\n  <center>{$output3}</center>\r\n  <table width=40% align=center cellspacing=\"1\" class=tdrow2>\r\n  <tr>\r\n    <td colspan=2 align=center>Email your password:</td>\r\n  </tr>\r\n  <tr>\r\n    <td width=40% align=right>Reset Hash</td>\r\n    <td><input type='text' name='reset_hash' id='textinput' size='20' value='{$input['reset_hash']}'/></td>\r\n  </tr>\r\n  <tr>\r\n    <td width=40% align=right>New password</td>\r\n    <td><input type='text' name='reset_pass' id='textinput' size='20' /></td>\r\n  </tr>\r\n  <tr>\r\n    <td colspan=2 align=center><input type=\"submit\" name='submit' value=\"Reset password\" class='forminput' /></td>\r\n  </tr>\r\n  </table>\r\n</form>";
}
echo "</div>\r\n</body>\r\n</html>";
?>
