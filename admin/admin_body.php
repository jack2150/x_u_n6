<?php
if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
echo "<table class=\"adminlist1\" border=\"0\">\r\n<tr>\r\n\t<th>\r\n    <img src='images/cpanel.png' style='VERTICAL-ALIGN: middle'>Control Panel\r\n\t</th>\r\n</tr>\r\n</table>\r\n<table class=\"adminform\">\r\n<tr>\r\n\t<td width=\"70%\" valign=\"top\">\r\n<!--begin left content-->\r\n\t<table width=\"100%\" class=\"cpanel\">\r\n<tr>\r\n\t<td align=\"center\" style=\"height:100px\">\r\n \t<a href=\"?admin=set\" style=\"text-decoration:none;\">\r\n\t<img src=\"images/config.p";
echo "ng\" width=\"48\" height=\"48\" align=\"middle\" border=\"0\"/>\r\n\t<br />\r\n\tConfiguration\r\n\t</a>\r\n\t</td>\r\n\t<td align=\"center\" style=\"height:100px\">\r\n    <a href=\"?admin=user\" style=\"text-decoration:none;\">\r\n\t<img src=\"images/user.png\" width=\"48\" height=\"48\" align=\"middle\" border=\"0\"/>\r\n\t<br />\r\n\tUser Manager\r\n\t</a>\r\n\t</td>\r\n\t<td align=\"center\" style=\"height:100px\">\r\n    <a href=\"?admin=images\" style=\"text-decoration:n";
echo "one;\">\r\n\t<img src=\"images/mediamanager.png\" width=\"48\" height=\"48\" align=\"middle\" border=\"0\"/>\r\n\t<br />\r\n\tFiles Manager\r\n\t</a>\r\n\t</td>\r\n</tr>\r\n<tr>\r\n\t<td align=\"center\" style=\"height:100px\">\r\n\t<a href=\"?admin=report\" style=\"text-decoration:none;\">\r\n\t<img src=\"images/report.png\" width=\"48\" height=\"48\" align=\"middle\" border=\"0\"/>\r\n\t<br />\r\n\tReport Manager\r\n\t</a>\r\n\t</td>\r\n\t<td align=\"center\" style=\"height:100px\">";
echo "\r\n\t<a href=\"?admin=server\" style=\"text-decoration:none;\">\r\n\t<img src=\"images/mutipleserver.png\" width=\"48\" height=\"48\" align=\"middle\" border=\"0\"/>\r\n\t<br />\r\n    Setup Server\r\n\t</a>\r\n\t</td><td width='250'>&nbsp;</td></table>\r\n";
$GLOBALS = $GLOBALS2;
$day = !empty( $input['day'] ) ? $input['day'] : date( "j" );
$month = !empty( $input['month'] ) ? $input['month'] : date( "n" );
$year = !empty( $input['year'] ) ? $input['year'] : date( "Y" );
$search_start = mktime( 0, 0, 0, $month, "01", $year );
$search_end = mktime( 0, 0, 0, $month + 1, "01", $year );
$stats = array( );
$db->setquery( "SELECT DATE_FORMAT(FROM_UNIXTIME(time),'%d') as day,DATE_FORMAT(FROM_UNIXTIME(time),'%W, %M %D,%Y') as date,count(distinct id) as num FROM files WHERE time>{$search_start} and time<{$search_end} GROUP BY DATE_FORMAT(FROM_UNIXTIME(time),'%W, %M %D,%Y') ORDER BY time DESC" );
$db->query( );
$stats[files] = $db->loadrowlist( "day" );
$db->setquery( "SELECT DATE_FORMAT(FROM_UNIXTIME(regdate),'%d') as day,DATE_FORMAT(FROM_UNIXTIME(regdate),'%W, %M %D,%Y') as date,count(distinct id) as num\r\n               FROM users\r\n               WHERE regdate>{$search_start} and regdate<{$search_end}\r\n               GROUP BY DATE_FORMAT(FROM_UNIXTIME(regdate),'%W, %M %D,%Y')\r\n               ORDER BY regdate DESC" );
$db->query( );
$stats[users] = $db->loadrowlist( "day" );
$db->setquery( "SELECT DATE_FORMAT(FROM_UNIXTIME(date),'%d') as day,DATE_FORMAT(FROM_UNIXTIME(date),'%W, %M %D,%Y') as date,count(distinct report_id) as num\r\n               FROM reports\r\n               WHERE date>{$search_start} and date<{$search_end}\r\n               GROUP BY DATE_FORMAT(FROM_UNIXTIME(date),'%W, %M %D,%Y')\r\n               ORDER BY date DESC" );
$db->query( );
$stats[reports] = $db->loadrowlist( "day" );

$lang = "en";
$country = "en";
include( "kalender/lang/conf.php" );
include( "kalender/lang/locallang.php" );
if ( $country )
{
    if ( file_exists( "kalender/country/extcountry_".$country.".php" ) )
    {
        include( "kalender/country/extcountry_".$country.".php" );
    }
    else
    {
        include( "kalender/country/extcountry_de.php" );
    }
}

include( "kalender/class/class.kalendar.php" );
include( "kalender/class/class.template.php" );
$cal = new calendar( $day, $month, $year );
$cal->showtemplate( $GLOBALS['INI']['skin'], $stats );
unset( $GLOBALS );
unset( $GLOBALS2 );
echo "<!--end left content-->\r\n</td>\r\n";
$db->setquery( "select count(*) as members,count(if(status=1,1,null)) as amembers,count(if(status=0,1,null)) as unmembers,count(if(status=-1,1,null)) as smembers from users" );
$db->query( );
$tmp = $db->loadrow( );
$stats[amembers] = $tmp[amembers];
$stats[unmembers] = $tmp[unmembers];
$stats[smembers] = $tmp[smembers];
$stats[members] = $tmp[members];
$db->setquery( "select count(if(deleted=1,1,null)) as dels1, count(if(uid=0,1,null)) as guestfiles,count(if(uid!=0,1,null)) as userfiles from files" );
$db->query( );
$tmp = $db->loadrow( );
$stats[guestfiles] = $tmp[guestfiles];
$stats[userfiles] = $tmp[userfiles];
$stats[dels1] = $tmp[dels];
$stats[dels] = $tmp[dels1] + $tmp[dels2];
$week_date = urlencode( date( "m/d/y", time( ) + 604800 ) );
$last_week_date = urlencode( date( "m/d/y", time( ) - 604800 ) );
echo "<td width=\"30%\" valign=\"top\">\r\n<!--begin right content-->\r\n<!--Shorcuts -->\r\n<div style=\"width:100%;\">\r\n<form action=\"index2.php\" method=\"post\" name=\"adminForm\">\r\n<link id=\"luna-tab-style-sheet\" type=\"text/css\" rel=\"stylesheet\" href=\"css/tabs/tabpane.css\" />\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"css/tabs/tabpane.js\"></script>\r\n\r\n<div class=\"tab-page\" id=\"modules-cpanel\">\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\n   var tabPane1 = new WebFXTabPane( document.getElementById( \"modules-cpanel\" ), 1 )\r\n</script>\r\n\r\n<div class=\"tab-page\" id=\"module33\"><h2 class=\"tab\"><b>Stats</b></h2>\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\n  tabPane1.addTabPage( document.getElementById( \"module33\" ) );\r\n</script>\r\n<table width=100%>\r\n<tr><td>\r\nUncomfirmed Members:</td><td><a href=\"?admin=user&search=1&user_status=0\">";
echo $stats[unmembers];
echo "</a>\r\n</td></tr>\r\n<tr><td>\r\nSuspended Members:</td><td><a href=\"?admin=user&search=1&user_status=-1\">";
echo $stats[smembers];
echo "</a>\r\n</td></tr>\r\n<tr><td>\r\nTotal Members:</td><td>";
echo $stats[members];
echo "</td></tr>\r\n<tr><td>\r\nGuest Files:</td><td>";
echo $stats[guestfiles];
echo "</td></tr>\r\n<tr><td>\r\nMembers Files:</td><td>";
echo $stats[userfiles];
echo "</td></tr>\r\n<tr><td>\r\nDeleted Files:</td><td>";
echo $stats[dels];
echo "</td></tr>\r\n<tr><td colspan=2>\r\n<a href=\"../crontabs/crondelete.php\" target=_blank>Delete these files now!</a>\r\n</td></tr>\r\n</table>\r\n</div>\r\n\r\n<div class=\"tab-page\" id=\"module19\"><h2 class=\"tab\"><b>Quick Links</b></h2>\r\n";
echo "<s";
echo "cript type=\"text/javascript\">\r\n  tabPane1.addTabPage( document.getElementById( \"module19\" ) );\r\n</script>\r\n<table width=100%>\r\n<tr><td>\r\n<a href=\"?admin=images&img_field=&img_func=%3D&img_values=&img_orderby=f.time&img_AD=DESC&img_mode=0&img_pages=50&search=Search\">Lastest Uploaded Files</a>\r\n</td></tr>\r\n<tr><td>\r\n<a href=\"?admin=images&img_field=&img_func=%3D&img_values=&img_orderby=f.downloads&img_AD=DESC&";
echo "img_mode=0&img_pages=50&search=Search\">Most Pouplar Files</a>\r\n</td></tr>\r\n<tr><td>\r\n<a href=\"?admin=user&user_field=&user_func=%3D&user_values=&user_orderby=regdate&user_AD=DESC&user_status=2&user_pages=50&search=Search\">New Registered Members</a>\r\n</td></tr>\r\n<tr><td>\r\n<a href=\"?admin=payment&pay_field=payment_date&pay_func=%3E%3D&pay_values=";
echo $last_week_date;
echo "&pay_orderby=r.payment_date&pay_AD=DESC&pay_mode=&pay_pages=50&search=Search\">Payment in this week</a>\r\n</td></tr>\r\n<tr><td>\r\n<a href=\"?admin=user&user_field=expire_date&user_func=%3C%3D&user_values=";
echo $week_date;
echo "&user_orderby=expire_date&user_AD=DESC&user_status=2&user_pages=10&search=Search\">Membership Expired in this week</a>\r\n</td></tr>\r\n</table>\r\n</div>\r\n</form>\r\n<!--End of Shorcuts -->\r\n\t</div>\r\n<!--end right content-->\r\n\t</td>\r\n</tr>\r\n</table>\r\n<div ID=\"remotenews\"></div>\r\n";
echo "<s";
echo "cript>\r\nfunction createNewsTable()\r\n{\r\n    var x = xmlDoc.getElementsByTagName('item');\r\n    if(x.length==0) return '';\r\n\tvar newEl = document.createElement('TABLE');\r\n    newEl.className = 'adminlist';\r\n\tvar tmp = document.createElement('TBODY');\r\n\r\n\tfor (i=0;i<x.length;i++)\r\n\t{\r\n        var vars = new Array();\r\n        for (j=0;j<x[0].childNodes.length;j++)\r\n\t    {\r\n\t\t    if (x[0].childNodes[j].no";
echo "deType != 1) continue;\r\n            var node = x[i].childNodes;\r\n            var varname= node[j].nodeName;\r\n            var varvalue= node[j].firstChild.nodeValue;\r\n            vars[varname] = varvalue;\r\n\t    }\r\n\r\n        var row = document.createElement('TR');\r\n\t    var container = document.createElement('TH');\r\n        container.innerHTML=vars['title'];\r\n        if(vars['link'])\r\n        {\r\n   ";
echo "         var link = document.createElement('A');\r\n            link.href = vars['link'];\r\n            link.target  = '_blank';\r\n            link.innerHTML=\"[More]\";\r\n            container.appendChild(link);\r\n        }\r\n\t    row.appendChild(container);\r\n        tmp.appendChild(row);\r\n\r\n        var row = document.createElement('TR');\r\n\t    var container = document.createElement('TD');\r\n        contai";
echo "ner.className = 'tdrow1';\r\n        container.innerHTML=vars['description'];\r\n\t    row.appendChild(container);\r\n        tmp.appendChild(row);\r\n\r\n\t}\r\n    newEl.appendChild(tmp);\r\n\tdocument.getElementById('remotenews').appendChild(newEl);\r\n}\r\nfunction importNewsXML(xmlUrl)\r\n{\r\n\tif (document.implementation && document.implementation.createDocument)\r\n\t{\r\n\t\txmlDoc = document.implementation.createDocumen";
echo "t(\"\", \"\", null);\r\n\t\txmlDoc.onload = createNewsTable;\r\n        xmlDoc.load(xmlUrl);\r\n\t}\r\n\telse if (window.ActiveXObject)\r\n\t{\r\n\t\txmlDoc = new ActiveXObject(\"Microsoft.XMLDOM\");\r\n\t\txmlDoc.onreadystatechange = function () {\r\n\t\t\tif (xmlDoc.readyState == 4) createNewsTable()\r\n\t\t};\r\n        xmlDoc.load(xmlUrl);\r\n \t}\r\n\telse\r\n\t{\r\n\t\t//alert('Your browser can\\'t handle this script');\r\n\t\treturn;\r\n\t}\r\n}\r\n";
echo "\r\n</script>\r\n";
?>
