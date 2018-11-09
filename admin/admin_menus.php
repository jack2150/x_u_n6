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
    global $baseWeb;
    global $input;
    global $db;
    global $CODE_TO_LANG;
    $per_num = 10;
    $input[s] = intval( $input[s] );
    $parent_id = intval( $input[pid] );
    if ( $parent_id )
    {
        $db->setquery( "select * from admin_menus where menu_id='{$parent_id}'" );
        $db->query( );
        $parent = $db->loadrow( );
    }
    else
    {
        $parent[name] = "ROOT";
    }
    $db->setquery( "select * from admin_menus where parent_id='{$parent_id}' order by orders" );
    $db->query( );
    $rows = $db->loadrowlist( );
    showmenuth( $data );
    $nowtime = time( );
    foreach ( $rows as $row )
    {
        $data = $row;
        $data[id] = $row[menu_id];
        $data[name] = "<a href={$baseUrl}&admin=menu&pid={$data['id']}>".$row[name]."</a>";
        $data[parent_name] = $parent[name];
        $data[del] = "<a href={$baseUrl}&admin=menu&act=del&id={$data['id']} onclick=\"return confirm('Are You Sure Delete This News?')\">Delete</a>";
        $data[del] .= "::<a href={$baseUrl}&admin=menu&act=edit&id={$data['id']}>Edit</a>";
        showmenurow( $data );
    }
    showmenutt( $pageLinks );
}

function addeditmenu( $id )
{
    global $baseUrl;
    global $input;
    global $db;
    global $LANG_TO_MATCH;
    global $TREE;
    $id = $input[id];
    if ( $input[act] == "edit" && $id )
    {
        $action = "Edit";
        $db->setquery( "select * from admin_menus where menu_id='{$id}' " );
        $db->query( );
        $row = $db->loadrow( );
    }
    else
    {
        $action = "Add";
        $row[parent_id] = $input[pid];
    }
    $TREE = build_menu_tree( );
    $level = 1;
    $menu_options = build_menu_options( $TREE[0], $level );
    $row[icon] = htmlspecialchars( $row[icon], ENT_QUOTES );
    $row[bigicon] = htmlspecialchars( $row[bigicon], ENT_QUOTES );
    echo "<form action=\"index.php?admin=menu\" name=myform method=\"post\">\r\n<input type=\"hidden\" name=\"act\" value=\"{$input['act']}\">\r\n<input type=\"hidden\" name=\"tbl_menu_id\" value=\"{$row['menu_id']}\">\r\n<table class=adminlist align=center>\r\n<tr><th colspan=2 align=center>Menu {$row['name']}:</th></tr>\r\n<tr><td class=tdrow1 width=40%><b>Parent Menu</b><br></td><td class=tdrow1 align=left>\r\n<select name=tbl_parent_id style=\"width:35%\">\r\n<option value=0>ROOT</option>\r\n{$menu_options}</select>\r\n</td></tr>\r\n<tr><td class=tdrow1 width=40%><b>Name:</b></td><td class=tdrow1 align=left><input type=\"text\" size=30 name=\"tbl_name\" value=\"{$row['name']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%><b>URL:</b></td><td align=left class=tdrow1><input type=\"text\" size=40 name=\"tbl_url\" value=\"{$row['url']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%><b>Hint:</b></td><td class=tdrow1 align=left><input type=\"text\" size=50 name=\"tbl_hint\" value=\"{$row['hint']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%><b>Icon</b>:<br></td><td align=left class=tdrow1><input type=\"text\" size=40 name=\"tbl_icon\" value=\"{$row['icon']}\"></td></tr>\r\n<tr><td class=tdrow1 width=40%><b>Big Icon</b>:<br></td><td align=left class=tdrow1><input type=\"text\" size=40 name=\"tbl_big_icon\" value=\"{$row['big_icon']}\"></td></tr>\r\n<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" name=\"done\" value=\"{$action} Menu\"></td></tr>\r\n</form>\r\n<script>\r\ns=document.myform.tbl_parent_id.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$row['parent_id']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script>";
}

function showmenuth( $data )
{
    global $baseUrl;
    global $input;
    echo "<form name=myform action=\"?admin=menu\" method=\"POST\">\r\n<input type=\"hidden\" name=\"act\" value=\"orders\">\r\n<input type=\"hidden\" name=\"admin\" value=\"menu\">\r\n<input type=\"hidden\" name=\"parent_id\" value=\"{$input['pid']}\">\r\n    <table class=adminlist border=0 align=center width=800px>\r\n    <tr>\r\n\t\t<th align=\"center\" colspan='7'>Admin Menus</th>\r\n    </tr>\r\n    <tr>\r\n    <td align=left class='tdrow1' width=5%><input type='checkbox' name=allbox onclick=checkAll()></td>\r\n    <td align=left class='tdrow1' width=25%><b>Name</b></td>\r\n    <td align=left class='tdrow1' width=15%><b>URL</b></td>\r\n    <td align=left class='tdrow1' width=25%><b>Parent</b></td>\r\n    <td align=left class='tdrow1' width=15%><b>Hint</b></td>\r\n    <td align=left class='tdrow1' width=15%><b>Order</b></td>\r\n    <td align=left class='tdrow1' width=20%><b>Action</b></td>\r\n    </tr>";
}

function showmenurow( $data )
{
    global $baseUrl;
    echo "    <tr>\r\n    <td align=left class='tdrow1'><input type='checkbox' name=idList[{$data['id']}]></td>\r\n    <td align=left class='tdrow2'>{$data['icon']}{$data['name']}</td>\r\n    <td align=left class='tdrow1'>{$data['url']}</td>\r\n    <td align=left class='tdrow2'>{$data['parent_name']}</td>\r\n    <td align=left class='tdrow1'>{$data['hint']}</td>\r\n    <td align=left class='tdrow1'><input type=text name=orders[{$data['id']}] size=5 value=\"{$data['orders']}\"></td>\r\n    <td align=left class='tdrow2'>{$data['del']}</td>\r\n    </tr>";
}

function showmenutt( $pageLinks )
{
    global $baseUrl;
    global $input;
    $pid = intval( $input[id] );
    echo "    <tr>\r\n    <td align='left' class='tdrow1' valign='middle' colspan=7>\r\n    <input type=\"submit\" name=\"update\" value=\"Update orders\">\r\n    <a name=\"add\" href={$baseUrl}&admin=menu&pid={$input['pid']}&act=add>  Add  </a >\r\n    {$pageLinks}\r\n    <td>\r\n    </tr>\r\n    </table>\r\n</form>";
}

function build_jsmenu( $menu )
{
    global $TREE;
    global $num;
    $menu_jscode = "";
    ++$num;
    foreach ( $menu as $menu_id => $node )
    {
        $node[icon] = $node[icon] ? "'".$node[icon]."'" : "null";
        if ( is_array( $TREE[$node[id]] ) )
        {
            $menu_jscode .= "[{$node['icon']},'{$node['name']}','{$node['url']}',null,'{$node['hint']}',\n";
            $menu_jscode .= build_jsmenu( $TREE[$node[id]] )."],\n";
        }
        else
        {
            $menu_jscode .= "[{$node['icon']},'{$node['name']}','{$node['url']}',null,'{$node['hint']}'],\n";
        }
        if ( $node['split'] )
        {
            $menu_jscode .= "_cmSplit,\n";
        }
    }
    return $menu_jscode;
}

function build_menu_options( $menu, &$level )
{
    global $TREE;
    $menu_option = "";
    $prefix = str_repeat( "&nbsp;&nbsp;", $level );
    foreach ( $menu as $menu_id => $node )
    {
        if ( is_array( $TREE[$node[id]] ) )
        {
            $menu_option .= "<option value={$menu_id}>{$prefix}{$node['name']}</option>";
            ++$level;
            $menu_option .= build_menu_options( $TREE[$node[id]], $level );
        }
        else
        {
            $menu_option .= "<option value={$menu_id}>{$prefix}{$node['name']}</option>";
        }
    }
    --$level;
    return $menu_option;
}

function build_menu_tree( )
{
    global $db;
    $db->setquery( "select * from admin_menus order by orders" );
    $db->query( );
    $rows = $db->loadrowlist( );
    foreach ( $rows as $row )
    {
        $TREE[$row['parent_id']][$row['menu_id']] = array( "id" => $row['menu_id'], "icon" => $row['icon'], "name" => $row['name'], "url" => $row['url'], "hint" => $row['hint'], "split" => $row['split'] );
    }
    return $TREE;
}

$TREE = build_menu_tree( );
$menu_jscode = "var myMenu = [\n".build_jsmenu( $TREE[0] )."];";
echo "        <div id=\"myMenuID2\"></div>\r\n\t\t";
echo "<s";
echo "cript language=\"JavaScript\" type=\"text/javascript\">\r\n\t\tvar myMenu =\r\n\t\t[\r\n\t\t\t[null,'Home','index.php',null,'Control Panel'],\r\n\t\t\t_cmSplit,\r\n\t\t\t[null,'Site',null,null,'Site Management',\r\n\t\t\t\t['<img src=\"css/Theme/controlpanel.png\" />','Configuration','?admin=set','wwwwwwww','Configuration'],\r\n         \t\t['<img src=\"css/Theme/template.png\" />','Site Ads','?admin=siteads',null,'Change/Edit Site Ads'],\r";
echo "\n                ['<img src=\"css/Theme/publish.png\" />','News System','?admin=news',null,'Manage news'],\r\n\t\t\t\t['<img src=\"css/Theme/mass_email.png\" />','Newsletter','?admin=newsletter',null,'Manage news'],\r\n                ['<img src=\"css/Theme/package.png\" />','FAQ Manager','?admin=faqs',null,'Manage FAQS'],\r\n                ['<img src=\"css/Theme/config.png\" />','License Manager','?admin=license',nul";
echo "l,'Manage licenses'],\r\n            ],\r\n\t\t\t_cmSplit,\r\n  \t\t\t[null,'Files',null,null,'Files Management',\r\n  \t\t\t\t['<img src=\"css/Theme/media.png\" />','Files','?admin=images&search=1',null,'List/Search/Delete Files',\r\n                    ['<img src=\"css/Theme/media.png\" />','Top Downloads Files','?admin=images&search=1&orderby=DESC',null,'List/Search/Delete Files'],\r\n          ";
echo "          ['<img src=\"css/Theme/media.png\" />','Validated Files','?admin=images&img_filter=validated&search=1',null,'List/Search/Delete Files'],\r\n                    ['<img src=\"css/Theme/media.png\" />','Deleted Files','?admin=images&img_filter=deleted&search=1',null,'List/Search/Delete Files'],\r\n                ],\r\n                ['<img src=\"css/Theme/package.png\" />','Download','',null,'File Downl";
echo "oad',\r\n                    ['<img src=\"css/Theme/package.png\" />','Sessions','?admin=dlsessions',null,'List download sessions'],\r\n                    ['<img src=\"css/Theme/save.png\" />','History','?admin=dlhistory',null,'List download history'],\r\n                ],\r\n                ['<img src=\"css/Theme/checkin.png\" />','Reports','?admin=report',null,'Process Report'],\r\n  \t\t\t\t['<img src=\"css/Theme/tra";
echo "sh.png\" />','Prune','?admin=imgprune',null,'Prune files'],\r\n  \t\t\t],\r\n            _cmSplit,\r\n  \t\t\t[null,'Host Package',null,null,'Host Package',\r\n\t\t\t\t['<img src=\"css/Theme/categories.png\" />', 'List Package', '?admin=group', null,'Add/Edit/Delete Package'],\r\n                ['<img src=\"css/Theme/package.png\" />', 'Promotions', '?admin=promotion', null,'Add/Edit/Delete Promotions'],\r\n                [";
echo "'<img src=\"css/Theme/checkin.png\" />', 'Download Rules', '?admin=rule', null,'Add/Edit/Delete Rules'],\r\n                ['<img src=\"css/Theme/template.png\" />','Package Template','?admin=showtemplate',null,'Add/Edit/Delete Show Template for the package']\r\n\t\t\t],\r\n            _cmSplit,\r\n  \t\t\t[null,'Members',null,null,'Members Management',\r\n\t\t\t\t['<img src=\"css/Theme/users.png\" />', 'List Members', '?adm";
echo "in=user', null,'Search/Edit/Add Members'],\r\n                ['<img src=\"css/Theme/users_add.png\" />', 'Add Members', '?admin=user&act=add', null,'Suspended Members'],\r\n                ['<img src=\"css/Theme/save.png\" />', 'Points History', '?admin=points', null,'List,Search points history'],\r\n\t\t\t],\r\n            _cmSplit,\r\n  \t\t\t[null,'Servers','?admin=server',null,'Servers Management'],\r\n            _";
echo "cmSplit,\r\n            [null,'Crontabs',null,null,'Manage Crontabs',\r\n                ['<img src=\"css/Theme/package.png\" />','Crontabs','?admin=crontab',null,'Crontab setting'],\r\n                ['<img src=\"css/Theme/save.png\" />','Crontab Logs','?admin=cronlog',null,'View crontabs logs'],\r\n            ],\r\n            _cmSplit,\r\n  \t\t\t[null,'Billing',null,null,'Billing',\r\n\t\t\t\t['<img src=\"css/Theme/tran";
echo "sactions.png\" />', 'Transactions', '?admin=payment', null,'View Transaction'],\r\n                ['<img src=\"css/Theme/config.png\" />', 'Gateway Setting', '?admin=gateway', null,'Setup Payment Gateway'],\r\n\t\t\t],\r\n            _cmSplit,";
echo "\n                ['<img src=\"css/Theme/template.png\" />','Language Manager','?admin=lang',null,'Edit Language'],\r\n                ['<img src=\"css/Theme/template.png\" />','Email Template','?admin=emails',null,'Change/Edit Template'],\r\n\r\n\t\t\t],\r\n            _cmSplit,\r\n  \t\t\t[null,'Tools',null,null,'Site tools',\r\n                ['<img src=\"css/Theme/save.png\" />','Backup Database','?admin=backup',null,'B";
echo "ackup Database'],\r\n                ['<img src=\"css/Theme/checkin.png\" />','Database Restore','?admin=restore',null,'Restore Backup'],\r\n                ['<img src=\"css/Theme/config.png\" />','Maintain Database','?admin=maintain',null,'Maitain Database'],\r\n\r\n\t\t\t],\r\n\t\t\t_cmSplit,\r\n            [null,'Help',null,null,'Site Help',\r\n                ['<img src=\"css/Theme/users.png\" />','Support forum','";
echo "#',null,'Support'],\r\n\r\n\t\t\t],\r\n\t\t];\r\n        ";
echo $menu_jscode;
echo "\t\tcmDraw ('myMenuID2', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');\r\n\t\t</script>\r\n</td>\r\n";
if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
$act = $input[act];
switch ( $act )
{
    case "add" :
        if ( isset( $input[done] ) )
        {
            if ( $DEMOMODE )
            {
                redirect( "admin=set", $DEMOTEXT );
            }
            $menu = new table( $db, "admin_menus", "menu_id" );
            $menu->inputdata( );
            $menu->menu_id = 0;
            $menu->insert( );
            redirect( "admin=menu&pid=".$menu->parent_id, "Add Successfully!" );
        }
        addeditmenu( 0 );
        break;
    case "edit" :
        if ( isset( $input[done] ) )
        {
            if ( $DEMOMODE )
            {
                redirect( "admin=set", $DEMOTEXT );
            }
            $menu = new table( $db, "admin_menus", "menu_id" );
            $menu->inputdata( );
            $menu->update( );
            redirect( "admin=menu&pid=".$menu->parent_id, "Update Successfully!" );
        }
        addeditmenu( $input[id] );
        break;
    case "orders" :
        if ( isset( $input[update] ) )
        {
            if ( $DEMOMODE )
            {
                redirect( "admin=set", $DEMOTEXT );
            }
            foreach ( $input[orders] as $id => $val )
            {
                $val = intval( $val );
                $db->setquery( "update admin_menus set orders='{$val}' where menu_id='{$id}'" );
                $db->query( );
            }
            redirect( "admin=menu&pid=".$input[parent_id], "Update Successfully!" );
        }
        break;
    case "del" :
        if ( $DEMOMODE )
        {
            redirect( "admin=set", $DEMOTEXT );
        }
        $db->setquery( "delete from admin_menus where menu_id='{$input['id']}'" );
        $db->query( );
        redirect( "admin=menu", "Delete Successfully!" );
        break;
    default :
        showlistform( );
}
?>
