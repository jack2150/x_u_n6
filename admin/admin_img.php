<?php
function showlistform( )
{
    global $baseUrl;
    global $baseWeb;
    global $input;
    global $db;
    global $ThumbFile;
    global $SERVERS;
    global $ThumbUploadForm;
    $db->setQuery( "select * from setting limit 1 " );
    $db->query( );
    $SET = $db->loadRow( );
    $db->setQuery( "select * from server" );
    $db->query( );
    $SERVERS = $db->loadRowList( "server_id" );
    $mode = intval( $input[mode] );
    $input[s] = intval( $input[s] );
    $cols = 4;
    $header = array( );
    if ( $orderad != "ASC" && $orderad != "DESC" )
    {
        $orderad = "";
    }
    if ( isset( $input[search] ) )
    {
        $orderby = $_SESSION[img_orderby] = $input[img_orderby];
        $orderad = $_SESSION[img_AD] = $input[img_AD];
        $_SESSION[img_field] = $input[img_field];
        $_SESSION[img_func] = $input[img_func];
        $_SESSION[img_values] = $input[img_values];
        $_SESSION[img_field2] = $input[img_field2];
        $_SESSION[img_func2] = $input[img_func2];
        $_SESSION[img_values2] = $input[img_values2];
        $_SESSION[img_pages] = $input[img_pages];
        $_SESSION[img_mode] = $input[img_mode];
        $_SESSION[img_server] = $input[img_server];
        $_SESSION[img_filter] = $input[img_filter];
        $_SESSION[img_mode] = $input[img_mode];
        $_SESSION[img_thumb] = $input[img_thumb];
    }
    $orderby = $_SESSION[img_orderby];
    $orderad = $_SESSION[img_AD];
    $mode = intval( $_SESSION[img_mode] == 1 );
    $per_num = intval( $_SESSION[img_pages] );
    if ( $per_num == 0 )
    {
        $_SESSION[img_pages] = $per_num = 20;
    }
    if (!$input['s']) {
    	$input['s'] == 0;
    }
    $primary_filestats = 0;
    if ( $orderby )
    {
    	if ($orderby == "fs.dls") {
    		$primary_filestats = 1;
    	}
    	else {
    		$primary_filestats = 0;
        	$order = "ORDER BY $orderby $orderad ";
    	}
    }
    else {
    	$order = "ORDER BY f.time DESC ";
    }
    if ( ( $_SESSION[img_field] ) != "" && $_SESSION[img_func] != "" && $_SESSION[img_field] != "u.username" && $_SESSION[img_field] != "f.uid" )
    {
        if ( $_SESSION[img_field] == "f.time" )
        {
            $condition = "{$_SESSION['img_field']} {$_SESSION['img_func']}= '".$db->getescaped( strtotime( $_SESSION[img_values]." 00:00:00" ) )."'";
        }
        else
        {
            $condition = "{$_SESSION['img_field']} {$_SESSION['img_func']} '".$db->getescaped( $_SESSION[img_values] )."'";
        }
    }
    if ( $_SESSION[img_field2] != "" && $_SESSION[img_func2] != "" && $_SESSION[img_field2] != "u.username" && $_SESSION[img_field2] != "f.uid" )
    {
        if ( $_SESSION[img_field2] == "f.time" )
        {
            $condition2 = "{$_SESSION['img_field2']} {$_SESSION['img_func2']}= '".$db->getescaped( strtotime( $_SESSION[img_values2]." 00:00:00" ) )."'";
        }
        else
        {
            $condition2 = "{$_SESSION['img_field2']} {$_SESSION['img_func2']} '".$db->getescaped( $_SESSION[img_values2] )."'";
        }
        $condition .= $condition ? " AND ".$condition2 : $condition2;
    }
    if ( $_SESSION[img_field] == "u.username" )
    {
    $db->setquery( "select id,user from users where user='{$_SESSION['img_values']}'" );
        $db->query( );
        $caption = $db->loadRow( );
        $header[caption] = $caption[user];
        $condition .= $condition == "" ? " f.uid='{$caption['id']}'" : " and f.uid='{$caption['id']}'";
    }
    if ( $_SESSION[img_field] == "f.uid" )
    {
        $condition .= $condition == "" ? " f.uid='{$_SESSION['img_values']}'" : " and f.uid='{$_SESSION['img_values']}'";
        $db->setQuery( "select id,user from users where id='{$_SESSION['img_values']}'" );
        $db->query( );
        $caption = $db->loadRow( );
        $header[caption] = $caption[user];
    }
    if ( is_numeric( $_SESSION[img_server] ) && $_SESSION[img_server] != 0 )
    {
        $condition .= $condition == "" ? " f.server_id='{$_SESSION['img_server']}'" : " and f.server_id='{$_SESSION['img_server']}'";
    }
    $condition .= $condition == "" ? "1" : "";
    if ( $_SESSION[img_filter] == "deleted" )
    {
        if ( $_SESSION[img_filter] == "validated" ) {
        	$filter = "f.validate=1 and f.deleted>=1";
        }
        else if ( $_SESSION[img_filter] == "validate" ) {
        	$filter = "f.validate=0 and f.deleted>=1";
        }
        else {
        	$filter = "f.deleted>=1";
        }
    }
    else
    {
        if ( $_SESSION[img_filter] == "validated" ) {
        	$filter = "f.validate=1 and deleted=0";
        }
        elseif ( $_SESSION[img_filter] == "validate" ) {
        	$filter = "f.validate=0 and deleted=0";
        }
        elseif ( $_SESSION[img_filter] == "waiting" ) {
        	$filter = "f.validate=-1 and deleted=0";
        }
        else {
        	$filter = "1";
        }
    }
    if ( $_SESSION[img_thumb] == "0" )
    {
        $filter2 = "f.thumb=0";
    }
    else if ( $_SESSION[img_thumb] == 1 )
    {
        $filter2 = "f.thumb=1";
    }
    else
    {
        $filter2 = "1";
    }
    
    if ($condition == 1) {
	    if ($filter == 1) {
	    	if ($filter2 == 1) {
	    		$new_condition = "";
	    	}
	    	else {
	    		$new_condition = "where $filter2";
	    	}
	    }
	    else {
	    	if ($filter2 == 1) {
	    		$new_condition = "where $filter";
	    	}
	    	else {
	    		$new_condition = "where $filter and $filter2";
	    	}
	    }
    }
    else {
	    if ($filter == 1) {
	    	if ($filter2 == 1) {
	    		$new_condition = "where $condition";
	    	}
	    	else {
	    		$new_condition = "where $condition and $filter2";
	    	}
	    }
	    else {
	    	if ($filter2 == 1) {
	    		$new_condition = "where $condition and $filter";
	    	}
	    	else {
	    		$new_condition = "where $condition and $filter and $filter2";
	    	}
	    }
    }
    
    $db->setQuery( "select count(f.id) as total from files as f\r\n where $condition and $filter and $filter2" );
    $db->query( );
    $tmp = $db->loadRow( );
    $header[total] = $tmp[total];
    /*
    $db->setquery( "select f.*, fs.bandwidth as bandwidth, fs.dls as downloads, u.revenue_program from files as f, filestats as fs
    	LEFT JOIN users as u ON u.id = f.uid 
    	where {$condition} and {$filter} and {$filter2} {$order} 
    	limit {$input['s']},{$per_num}
    " );
    */
    //$db->setquery("select f.*,fs.dls as downloads, u.revenue_program from files as f, filestats as fs, users as u where u.id=f.uid and f.upload_id=fs.upload_id limit 0,30");
    /*
    $db->setquery("select f.*,fs.dls as downloads,fs.lastdownload as lastdownload,
    	fs.ips, fs.open, fs.done, fs.bandwidth, fs.dltimes
    	from files as f left join filestats as fs on f.upload_id=fs.upload_id 
    	$new_condition {$order}
    	limit {$input['s']},{$per_num}
    ");
    */
    if ($primary_filestats && $orderby == "fs.dls") {
    	$db->setquery("select f.* from files as f left join filestats as fs on f.upload_id = fs.upload_id where {$filter} order by fs.dls desc limit {$input['s']},{$per_num}");    	
    	$db->query();
    	$rows = $db->loadRowList( );
    	/*
    	$db->setquery("select fs.upload_id from filestats as fs order by fs.dls desc limit {$input['s']},{$per_num}");    	
    	$db->query();
    	$filestats = $db->loadRowList( );
    	$count = 0;
    	//echo $filter;
    	foreach ( $filestats as $filestat ) {
    		$db->setquery("select f.* from files as f where {$filter} and f.upload_id='{$filestat['upload_id']}' limit 1");
    		$db->query();
    		$temp_row = $db->loadRow( );
    		
    		$rows[$count] = $temp_row;
    		$count++;
    	}
    	*/
    }
    else {
    	$db->setquery("select f.* from files as f $new_condition {$order} limit {$input['s']},{$per_num}");
    	$db->query( );
    	$rows = $db->loadRowList( );
    }
    /*
    echo "select f.*,fs.dls as downloads,fs.lastdownload as lastdownload,
    	fs.ips, fs.open, fs.done, fs.bandwidth, fs.dltimes
    	from files as f left join filestats as fs on f.upload_id=fs.upload_id 
    	where {$condition} and {$filter} and {$filter2} {$order} 
    	limit {$input['s']},{$per_num}
    ";
    */
    

    $cur_page = $input[s] / $per_num;
    $info = array( "total"=>$tmp[total], "page"=>$per_num, "cur_page"=>$cur_page, "baseUrl"=>$baseUrl."&admin=images&$uid&$order2" );
    $pageLinks = buildpagelinks( $info );
    if ( $mode == 1 )
    {
        showimgth2( $header );
    }
    else
    {
        showimgth( $header );
    }
    $i = 0;
    $nums = count( $rows );
    foreach ( $rows as $row )
    {
    	$db->setquery("select fs.dls as downloads,fs.lastdownload as lastdownload,
    		fs.ips, fs.open, fs.done, fs.bandwidth, fs.dltimes
    		from filestats as fs where fs.upload_id='{$row['upload_id']}' limit 1
    	");
    	$db->query( );
    	$fs = $db->loadRow();
    	
    	if ($fs[downloads]) { $row[downloads] = $fs[downloads]; } else { $row[downloads] = 0; }
    	if ($fs[bandwidth]) { $row[bandwidth] = $fs[bandwidth]; } else { $row[bandwidth] = 0;	}
    	if ($fs[open]) { $row[open] = $fs[open]; } else { $row[open] = 0; }
    	if ($fs[done]) { $row[done] = $fs[done]; } else { $row[done] = 0; }
    	if ($fs[ips]) { $row[ips] = $fs[ips];} else { $row[ips] = 0; }
    	if ($fs[dltimes]) { $row[dltimes] = $fs[dltimes]; }	else { $row[dltimes] = 0; }
    	
        $SERVER = $SERVERS[$row[server_id]];
        $row[domain] = $SERVER[domain];
        $row[http] = $SERVER[http];
        $row[servername] = $SERVER[name];
        $row[mod_status] = $SERVER[mod_status];
        $data = $row;
        $sourceWeb = $row[http].$row[domain];
        $urls = geturls( $SERVER[upload_dir]."/".$row[file], $sourceWeb, $row[thumb] );
        extract( $urls );
        $data[thumburl] = $thumburl;
        $data[fileurl] = $fileurl;
        $urls = getdownloadurl( $row, "dyn" );
        extract( $urls );
        $data[downloadurl] = $downloadurl;
        $type = getext( $row[name], "_" );
        $data[style] = "".$row[validate]; // not working
		if ($row[deleted]) {
			$data[style] = "deleted";
		}
		else {
			if ($row[validate] == 1) {
				$data[style] = "validated";
			}
			elseif ($row[validate] == -1) {
				$data[style] = "waiting";
			}
			else {
				$data[style] = "validate";
			}
		}
		
    	$db->setquery("select revenue_program from users where id='{$row[uid]}' limit 1");
    	$db->query();
    	$user = $db->loadRow();
		if ($user['revenue_program']) {
			$data['revenue_program'] = "revenue";
		}
		else {
			$data['revenue_program'] = "normal";
		}
		
        if ( $_SESSION[img_field] == "u.username" )
        {
            $data[user] = $caption[user] ? $caption[user] : "GUEST";
        }
        else
        {
            $data[user] = $row[uid] ? "<a href='{$baseWeb}/admin/index.php?admin=images&img_field=f.uid&img_func=%3D&img_values={$row[uid]}&img_field2=&img_func2=%3D&img_values2=&img_orderby=&img_AD=&img_pages=20&img_server=All+servers&img_filter=&search=Search'>{$row[uid]}</a>" : "GUEST";
        }
        $data[time] = date( "M,d,y(H:i)", $row[time] );
        if ($fs[lastdownload]) { $data[lastdownload] = date( "M,d,Y", $fs[lastdownload] ); } else { $data[lastdownload] = "No Downloads"; }
        $data[bandwidth] = convertsize($data[bandwidth]);
        
        $data[expires] = date( "M.d,Y", $row[time] + $row[cron_days] * 24 * 60 * 60 ) ? date( "M.d,Y", $row[time] + $row[cron_days] * 24 * 60 * 60 ) : "Never";
        $data[size] = convertsize( $row[size] );
        
        $temp_name = base64_decode($row['name']);
        if (strlen($temp_name) > 60) {
        	$temp_name = substr($temp_name,0,40)."... .".strtolower(substr(strrchr($temp_name,'.'),1));
        }
        
        if ($data["dltimes"]) {
        	if ($data["dltimes"] >= (60*60*24)) {
        		$num_days = number_format(($data["dltimes"] / 86400),0,'','');
       			$num_hrs = ($data["dltimes"] / 3600) % 24;
        		$num_mins = ($data["dltimes"] / 60) % 60;
        		$num_secs = $data["dltimes"] % 60;
        		$data["dltimes"] = sprintf("[%02d]%02d:%02d:%02d",$num_days,$num_hrs,$num_mins,$num_secs);
        	}
        	else {
        		if ($data["dltimes"] >= (60*60)) {
        			$num_hrs = ($data["dltimes"] / 3600) % 24;
        			$num_mins = ($data["dltimes"] / 60) % 60;
        			$num_secs = $data["dltimes"] % 60;
        			$data["dltimes"] = sprintf("[%02d]%02d:%02d:%02d",0,$num_hrs,$num_mins,$num_secs);
        		}
        		else {
        			if ($data["dltimes"] >= (60)) {
        				$num_mins = ($data["dltimes"] / 60) % 60;
        				$num_secs = $data["dltimes"] % 60;
        				$data["dltimes"] = sprintf("[%02d]%02d:%02d:%02d",0,0,$num_mins,$num_secs);
        			}
        			else {
        				$data["dltimes"] = sprintf("[%02d]%02d:%02d:%02d",0,0,0,$data["dltimes"]);
        			}
        		}
        	}
        }
        else {
        	$data["dltimes"] = "---------------";
        }
        
        $file_ext = strtolower(substr(strrchr(base64_decode($row['name']),'.'),1));
        $download_filename = strtolower($row['upload_id']);
        
        // new download links $row[server_id]
		$year = date("Y",$row['time']);
		$month = date("m",$row['time']);
		$day = date("d",$row['time']);
		$hour = date("H",$row['time']);
        
        $dl_link = "{$row[http]}{$row[domain]}/admin/download.php?size={$row['size']}&f={$SERVER[upload_dir]}/{$year}/{$month}/{$day}/{$hour}/{$row['file']}/{$row['file']}&name={$download_filename}&ext={$file_ext}";
        
        $data[filelink] = "<a href='{$dl_link}' title='{$download_filename}.{$file_ext}'>".$temp_name." </a>(From <a href='{$baseWeb}/admin/index.php?admin=images&img_field=f.ip&img_func=%3D&img_values={$row['ip']}&img_field2=&img_func2=%3D&img_values2=&img_orderby=&img_AD=&img_pages=20&img_server=All+servers&img_filter=&search=Search' title='BAN {$row['ip']}'>{$row['ip']}</a>)";
        
        // backup link for mass download
        $list[] = $dl_link;
        
        
        $data[del] = "<a href='http://www.google.com.my/search?hl=en&q=".urlencode(base64_decode($row['name']))."&btnG=Search&meta=' target='_blank'>Search</a>";
        if ( $mode == 1 )
        {
            $data[del] .= "::<a href='{$baseUrl}&admin=images&act=thumbnail&s={$input['s']}&id={$row['id']}' onClick=\"return overlay(this, 'uploadthumb_div{$row['id']}')\">Thumbnail</a>";
        }
        else
        {
            $data[del] .= "::<a href='{$baseWeb}/file/".strtolower($row['upload_id'])."/".base64_decode($row['name']).".html' target=blank>Download</a>";
        }
        ++$i;
        $data[is_start] = $i % $cols == 1 || $cols == 1;
        $data[is_end] = $i % $cols == 0 || $cols == 1 || $nums == $i;
        if ( $mode == 1 )
        {
            $ThumbUploadForm .= showthumbuploadform( $data );
            showimgrow2( $data );
            continue;
        }
        else
        {
            showimgrow( $data );
        }
    }
    if ( $mode == 1 )
    {
        showimgtt2( $pageLinks );
    }
    else
    {
        showimgtt( $pageLinks );
    }
    
    showbatchlinks($list);
}

function showbatchlinks($linklist) {
	echo '<div class="batchlink">';
	echo '<input type="button" onclick="togglebatchlink()" value="Display Batch Links">';
	echo '<textarea id="batchlinks" name="batchlinks" cols="140" rows="8" onclick="this.focus(); this.select();" style="display:none">';
	if ($linklist) {
		foreach ($linklist as $link) {
			echo $link . "\n";
		}
	}
	echo '</textarea></div>';
}

function dels($delete_type)
{
    global $baseUrl;
    global $input;
    global $db;
    foreach ( $input[idList] as $key=>$value )
    {
        $key = intval( $key );
        $db->setQuery( "select file,uid,size,server_id from files where id='$key' and deleted=0" );
        $db->query( );
        $row = $db->loadRow( );
        if ( $row )
        {
            $db->setquery( "update users set files=files-1 where id='{$row['uid']}'" );
            $db->query( );
            $db->setquery( "update server set hosted=hosted-1, webspace=webspace-'{$row['size']}' where server_id='{$row['server_id']}'" );
            $db->query( );
        }
        $db->setQuery( "update files set deleted={$delete_type} where id='$key'" );
        $db->query( );
        
		/**
		 * [August 12 2008] Changelog
		 * update file server database file record
		 */
		$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='{$row[server_id]}' limit 1");
		$db->query();
		$server_sql = $db->loadRow();
		// make connection to server
		$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
		if ($file_server_db) {
			// connect to database
			if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
				// insert the same sql to file server
				mysql_query("update files set deleted={$delete_type} where id='{$key}'",$file_server_db);
			}
			else {
				@mysql_close($file_server_db);
			}
		}
		else {
			@mysql_close($file_server_db);
		}
		/**
		 * End Change
       	 */
    }
    redirect( "admin=images&s=".$input[s], "Deleted successfully!" );
}

function undels( )
{
    global $baseUrl;
    global $input;
    global $db;
    foreach ( $input[idList] as $key=>$value )
    {
        $key = intval( $key );
        $db->setQuery( "select file,uid,size,server_id from files where id='$key' and deleted=1" );
        $db->query( );
        $row = $db->loadRow( );
        if ( $row )
        {
            $db->setquery( "update users set files=files+1 where id='{$row['uid']}'" );
            $db->query( );
            $db->setquery( "update server set hosted=hosted+1, webspace=webspace+'{$row['size']}' where server_id='{$row['server_id']}'" );
            $db->query( );
        }
        $db->setQuery( "update files set deleted=0 where id='$key'" );
        $db->query( );
        
        
		/**
		 * [August 12 2008] Changelog
		 * update file server database file record
		 */
		$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='{$row[server_id]}' limit 1");
		$db->query();
		$server_sql = $db->loadRow();
		// make connection to server
		$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
		if ($file_server_db) {
			// connect to database
			if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
				// insert the same sql to file server
				mysql_query("update files set deleted=0 where id='{$key}'",$file_server_db);
			}
			else {
				@mysql_close($file_server_db);
			}
		}
		else {
			@mysql_close($file_server_db);
		}
		/**
		 * End Change
       	 */
    }
    redirect( "admin=images&s=".$input[s], "Undeleted successfully!" );
}

function showimgth( $header )
{
    global $baseUrl;
    global $input;
    echo "<form name=myform action=\"?admin=images\" method=\"POST\">\r\n
    <input type=\"hidden\" name=\"act\" value=\"dels\">\r\n
    <input type=\"hidden\" name=\"s\" value=\"{$input['s']}\">\r\n
    <input type=\"hidden\" name=\"admin\" value=\"images\">\r\n
    <table width=80% border=0 align=center class=adminlist>
    <tr><th align=\"center\" colspan=14>\r\n
    Files List:<b>{$header['caption']}({$header['total']} files)</b>\r\n</th></tr>\r\n
    <tr class='filerow'><td align=left class='tdrow1' width=7% >
    <input type='checkbox' name=allbox onclick=checkAll()></td>
    <td align=left class='tdrow1' width=25%><b>FileName</b></td>
    <td align=left class='tdrow1' width=10%><b>Size</b></td>
    <td align=left class='tdrow1' width=10%><b>Uploaded</b></td>
    <td align=left class='tdrow1' width=5%><b>Downloads</b></td>
    <td align=left class='tdrow1' width=10%><b>Last DL</b></td>
    <td align=left class='tdrow1' width=10%><b>Bandwidth</b></td>
    <td align=left class='tdrow1' width=5%><b>Open</b></td>
    <td align=left class='tdrow1' width=5%><b>Done</b></td>
    <td align=left class='tdrow1' width=5%><b>Total IP</b></td>
    <td align=left class='tdrow1' width=5%><b>Used Times</b></td>
    <td align=left class='tdrow1' width=8%><b>User ID</b></td>
    <td align=left class='tdrow1' width=10%><b>Server</b></td>
    <td align=left class='tdrow1'><b>Action<b></td></tr>";
}

function showimgrow( $data )
{
    global $baseUrl;
    
    echo "<tr id='row_{$data['id']}' class='filerow'><td align=left class='filerow_field' width='100'>
    <input type='checkbox' name='idList[{$data['id']}]' onclick='highlight_filerow({$data['id']});' />
    <span class='{$data['style']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='{$data['revenue_program']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
    <td align=left class='filerow_field'>{$data['filelink']}</td>
    <td align=left class='filerow_field'>{$data['size']}</td>
    <td align=left class='filerow_field'>{$data['time']}</td>
    <td align=left class='filerow_field'>{$data['downloads']}</td>
    <td align=left class='filerow_field'>{$data['lastdownload']}</td>
    <td align=left class='filerow_field'>{$data['bandwidth']}</td>
    <td align=left class='filerow_field'>{$data['open']}</td>
    <td align=left class='filerow_field'>{$data['done']}</td>
    <td align=left class='filerow_field'>{$data['ips']}</td>
    <td align=left class='filerow_field'>{$data['dltimes']}</td>
    <td align=left class='filerow_field'>{$data['user']} </td>
    <td align=left class='filerow_field'>{$data['servername']}</td>
    <td align=left class='filerow_field'>{$data['del']}</td></tr>";
}

function showimgtt( $pageLinks )
{
    global $baseUrl;
    global $input;
    global $total;
    global $db;
    global $SERVERS;
    if ( !is_array( $SERVERS ) )
    {
        $db->setQuery( "select * from server" );
        $db->query( );
        $servers = $db->loadRowList( );
    }
    else
    {
        $servers = $SERVERS;
    }
    $server_list = "";
    foreach ( $servers as $server )
    {
        $server_list .= "<option value=".$server[server_id].">".$server[name]."</option>";
    }
    echo "<tr><td class='tdrow1' align='left' valign='middle' colspan=14>\r\n<input type=\"submit\" name=\"delete\" value=\"Delete\"><input type=\"submit\" name=\"undelete\" value=\"Undelete\">\r\n<input type=\"submit\" name=\"validate\" value=\"Validate\" >\r\n<input type=\"submit\" name=\"unvalidate\" value=\"Unvalidate\" ><span class=deleted>&nbsp;Deleted Files&nbsp;</span>\r\n<span class=validated>&nbsp;Validated Files&nbsp;</span>\r\n<span class=validate>&nbsp;UnValidated Files&nbsp;</span>\r\n<span class=waiting>&nbsp;Waiting Files&nbsp;</span>\r\n<span class=revenue>&nbsp;Revenue Program User&nbsp;</span>\r\n<span class=normal>&nbsp;Normal User&nbsp;</span><input type=\"submit\" name=\"d_copyrighted\" value=\"D:Copyrighted\"><input type=\"submit\" name=\"d_virusfound\" value=\"D:VirusFound\"><br>$pageLinks\r\n</form>";
    imgsearchform( $server_list );
}

function showimgth2( $header )
{
    global $baseUrl;
    global $input;
    echo "<form name=myform action=\"?admin=images\" method=\"POST\">\r\n<input type=\"hidden\" name=\"act\" value=\"dels\">\r\n<input type=\"hidden\" name=\"s\" value=\"{$input['s']}\">\r\n<input type=\"hidden\" name=\"admin\" value=\"images\">\r\n    <table width=80% border=0 align=center class=adminlist>\r\n    <tr>\r\n    <th align=\"center\" colspan=14>\r\n    Files List:<b>{$header['caption']}({$header['total']} files)</b>\r\n    </th></tr>\r\n    <tr>\r\n    <td align=left class='tdrow1' width=5% colspan=8>\r\n    <input type='checkbox' name=allbox onclick=checkAll()>\r\n    </td>\r\n    </tr>";
}

function showimgrow2( $data )
{
    global $baseUrl;
    global $ThumbUploadForm;
    if ( $data[is_start] == 1 )
    {
        echo "    <tr class=tdrow1>";
    }
    echo "   <td valign=top class='tdrow1'>\r\n      <a href=\"{$data['fileurl']}\"><span id='link_{$data['id']}'><img src=\"{$data['thumburl']}\" border=0></span></a>\r\n      <br>\r\n      {$data['filelink']}<br>\r\n      <input type='checkbox' name=idList[{$data['id']}]><span class='{$data['style']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>\r\n      {$data['del']}\r\n      <br>\r\n  </td>";
    if ( $data[is_end] == 1 )
    {
        echo "    </tr>";
    }
}

function showthumbuploadform( $row )
{
    global $baseUrl;
    global $input;
    global $db;
    global $debug;
    global $ImageFile;
    global $ImageFile2;
    global $VideoFile;
    $ext_sep = keepext( $row[file], "_" ) == "" ? "." : "_";
    $ext_sep = $row[keepext] == "yes" ? "." : "_";
    $filetype = getext( $row[file], $ext_sep );
    $ffmpeg = split( ",", $row[mod_status] == 2 );
    $mplayer = split( ",", $row[mod_status] == 1 );
    $im = split( ",", $row[mod_status] == 0 );
    $can_makethumb = in_array( $filetype, $ImageFile ) || $im && in_array( $filetype, $ImageFile2 ) || ( $mplayer || $ffmpeg ) && in_array( $filetype, $VideoFile );
    $return .= "<DIV id=\"uploadthumb_div{$row['id']}\" class=tableborder style=\"position:absolute; border: 1px solid orange; background-color: white; width: 300px; padding: 1px; display:none\">";
    if ( $can_makethumb )
    {
        $return .= "<A href=\"#{$row['id']}\" onclick=\"showUploadFrame('{$row['id']}');document.getElementById('uploadframe{$row['id']}').contentWindow.location='{$row['http']}{$row['domain']}/uploadthumb.php?&act=make&id={$row['id']}';\"><B>MakeThumb</B></A>\r\nOr:";
    }
    $return .= "<form method=\"post\" action=\"{$row['http']}{$row['domain']}/uploadthumb.php?\" id=\"form{$row['id']}\" name=\"form{$row['id']}\" target=uploadframe{$row['id']} encType=multipart/form-data>\r\n<input type=hidden name=id value='{$row['id']}'>\r\n<input type=\"hidden\" name=\"act\" value=\"upload\">\r\n<input type=file name=thumbfile>\r\n<input type=submit name=Upload value=\"Upload\" onclick=\"showUploadFrame('{$row['id']}')\">\r\n</form>\r\n<div align=\"right\"><a href=\"#\" onClick=\"overlayclose('uploadthumb_div{$row['id']}'); return false\">Close</a></div>\r\n<div id='wraperuploadframe{$row['id']}' style='display:none'><IFRAME width=0 height=0 style='display:block' name=uploadframe{$row['id']} id=uploadframe{$row['id']} src=\"\" frameBorder=0></IFRAME>\r\n</div>\r\n</DIV>";
    return $return;
}

function showimgtt2( $pageLinks )
{
    global $baseUrl;
    global $input;
    global $total;
    global $db;
    global $SERVERS;
    global $ThumbUploadForm;
    if ( !is_array( $SERVERS ) )
    {
        $db->setQuery( "select * from server" );
        $db->query( );
        $servers = $db->loadRowList( );
    }
    else
    {
        $servers = $SERVERS;
    }
    $server_list = "";
    foreach ( $servers as $server )
    {
        $server_list .= "<option value=".$server[server_id].">".$server[name]."</option>";
    }
    echo "<tr><td class='tdrow1' align='left' valign='middle' colspan=13>\r\n<input type=\"submit\" name=\"delete\" value=\"Delete\" >\r\n<input type=\"submit\" name=\"undelete\" value=\"Undelete\" >\r\n<input type=\"submit\" name=\"validate\" value=\"Validate\" >\r\n<input type=\"submit\" name=\"unvalidate\" value=\"Unvalidate\"  >\r\n<input type=\"submit\" name=\"thumb\" value=\"Thumb\"  onclick=\"return opConfirm('This will set selected files thumbnails status,but you have to upload thumb files manualy!',1)\">\r\n<input type=\"submit\" name=\"unthumb\" value=\"Unthumb\"  onclick=\"return opConfirm('This will set selected files no thumbnails,and will disable the file listing on top pages!',1)\">\r\n<span class=deleted>Deleted Files</span>\r\n<span class=validated>Validated Files</span>\r\n<span class=validate>UnValidated Files</span>\r\n<br>$pageLinks\r\n</form>";
    echo $ThumbUploadForm;
    imgsearchform( $server_list );
    echo "<script>\r\nfunction showUploadFrame(id)\r\n{\r\n    document.getElementById('wraperuploadframe'+id).style.display = '';\r\n    document.getElementById('uploadframe'+id).style.width=300;\r\n    document.getElementById('uploadframe'+id).style.height=100;\r\n}\r\n</script>";
}

function imgsearchform( $server_list )
{
    echo "<form name=jump action=\"?admin=images\" method=get>\r\n<input type=\"hidden\" name=\"admin\" value=\"images\">\r\n<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'><tr>\r\n<tr><th colspan=3 align=center>Search a file</th></tr>\r\n<td class='row3' width='10%' align='center'><b>Field</b></td>\r\n<td class='row3' width='15%' align='right'><b>Operator</b></td>\r\n<td class='row3' width='60%' align='center'><b>Value</b></td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><select name=\"img_field\">\r\n<option value=\"\">Default</option>\r\n<option value=\"f.id\">ID</option>\r\n<option value=\"f.upload_id\">Upload ID</option>\r\n<option value=\"f.name\">Name</option>\r\n<option value=\"f.file\">Filename</option>\r\n<option value=\"f.size\">File Size</option>\r\n<option value=\"u.username\">Username</option>\r\n<option value=\"f.uid\">User ID</option>\r\n<option value=\"f.ip\">Uploaded IP</option>\r\n<option value=\"f.time\">Uploaded Date</option>\r\n</select></td>\r\n<td class='row2'  width='15%'  align='right'><select name=\"img_func\"><option value=\"=\">=</option><option value=\"&gt;\">&gt;</option><option value=\"&lt;\">&lt;</option><option value=\"&gt;=\">&gt;=</option><option value=\"&lt;=\">&lt;=</option><option value=\"!=\">!=</option><option value=\"LIKE\">LIKE</option><option value=\"NOT LIKE\">NOT LIKE</option></select></td>\r\n<td class='row1'  width='60%'  align='left'><input type='text' name='img_values' value='{$_SESSION['img_values']}' size='25' class='textinput'>(wildcard: \"%\" when \"like\" or \"not like\")</td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><select name=\"img_field2\">\r\n<option value=\"\">AND</option>\r\n<option value=\"f.id\">ID</option>\r\n<option value=\"f.upload_id\">Upload ID</option>\r\n<option value=\"f.name\">Name</option>\r\n<option value=\"f.size\">File Size</option>\r\n<option value=\"f.ip\">Uploaded IP</option>\r\n<option value=\"f.time\">Uploaded Date</option>\r\n</select></td>\r\n<td class='row2'  width='15%'  align='right'><select name=\"img_func2\"><option value=\"=\">=</option><option value=\"&gt;\">&gt;</option><option value=\"&lt;\">&lt;</option><option value=\"&gt;=\">&gt;=</option><option value=\"&lt;=\">&lt;=</option><option value=\"!=\">!=</option><option value=\"LIKE\">LIKE</option><option value=\"NOT LIKE\">NOT LIKE</option></select></td>\r\n<td class='row1'  width='60%'  align='left'><input type='text' name='img_values2' value='{$_SESSION['img_values2']}' size='25' class='textinput'>(wildcard: \"%\" when \"like\" or \"not like\")</td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><b>Order By</b></td>\r\n<td class='row2'  width='15%'  align='left'><select name=\"img_orderby\">\r\n<option value=\"\">Default</option>\r\n<option value=\"f.name\">Name</option>\r\n<option value=\"f.size\">File Size</option>\r\n<option value=\"u.username\">Username</option>\r\n<option value=\"f.time\">Uploaded date</option>\r\n</select><select name=img_AD class=\"dropdown\"><option value=>Default</option><option value=ASC>ASC</option><option value=DESC>DESC</option></select></td>\r\n<td class='row1'  width='60%'  align='left'>\r\n<b>Show</b>: <input type='text' name='img_pages' value='{$_SESSION['img_pages']}' size='8' class='textinput'>records per page\r\n</td>\r\n</tr>\r\n<tr>\r\n<td class='row1'  width='10%'  align='left'><b>Filter By</b></td>\r\n<td class='row1'  width='60%' colspan=2 align='left'>\r\n<select name=img_server><option>All servers</option>{$server_list}</select>\r\n<select name=\"img_filter\">\r\n<option value=\"\" selected=\"selected\">All files</option>\r\n<option value=\"validate\">Unvalidated</option>\r\n<option value=\"validated\">Validated</option>\r\n<option value=\"deleted\">Deleted</option>\r\n</select>\r\n<input type='submit' name='search' value='Search' size='30' class='textinput'><input type='reset' name='reset' value='Reset' size='30' class='textinput'></td>\r\n</tr>\r\n<script>\r\ns=document.jump.img_filter.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_filter']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_thumb']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_mode']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_orderby.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_orderby']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_AD.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_AD']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_func.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_func']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_field.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_field']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_func2.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_func2']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_field2.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_field2']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\ns=document.jump.img_server.options;\r\nfor(i=0;i<s.length;i++)\r\n{\r\n   if(s[i].value=='{$_SESSION['img_server']}')\r\n   {\r\n     s[i].selected='true';\r\n     break;\r\n   }\r\n}\r\n</script></table>\r\n</td></tr>\r\n</table>\r\n</form>";
}

if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}
$act = $input[act];
switch ( $act )
{
    case "dels" :
        if ( isset( $input[delete] ) )
        {
            dels(1);
        }
        if ( isset( $input[d_copyrighted] ) )
        {
            dels(2);
        }
        if ( isset( $input[d_virusfound] ) )
        {
            dels(3);
        }
        if ( isset( $input[undelete] ) )
        {
            undels( );
        }
        if ( isset( $input[validate] ) )
        {
            $ids = implode( ",", array_keys( $input[idList] ) );
            if ( $ids )
            {
                $db->setquery( "update files set validate=1 where id in ({$ids})" );
                $db->query( );

                /**
				 * [August 12 2008] Changelog
				 * update file server database file record
				 */
                /*
                foreach ($input[idList] as $key=>$value) {

					$db->setQuery("select s.sql_host as sql_host,s.sql_port as sql_port,s.sql_username as sql_username,s.sql_password as sql_password,s.sql_db as sql_db"
						." from server as s,files as f where f.id='$key' and s.server_id=f.server_id");
					//$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='{$row[server_id]}' limit 1");
					$db->query();
					$server_sql = $db->loadRow();
					// make connection to server
					$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
					if ($file_server_db) {
						// connect to database
						if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
							// insert the same sql to file server
							mysql_query("update files set validate=1 where id in ({$ids})",$file_server_db);
						}
						else {
							@mysql_close($file_server_db);
						}
					}
					else {
						@mysql_close($file_server_db);
					}
                }
                */
				/**
				 * End Change
			     */
            }
            redirect( "admin=images&s=".$input[s], "Validated successfully!" );
        }
        if ( isset( $input[unvalidate] ) )
        {
            $ids = implode( ",", array_keys( $input[idList] ) );
            if ( $ids )
            {
                $db->setquery( "update files set validate=0 where id in ({$ids})" );
                $db->query( );
                
                /**
				 * [August 12 2008] Changelog
				 * update file server database file record
				 */
                /*
                foreach ($input[idList] as $key=>$value) {

					$db->setQuery("select s.sql_host as sql_host,s.sql_port as sql_port,s.sql_username as sql_username,s.sql_password as sql_password,s.sql_db as sql_db"
						." from server as s,files as f where f.id='$key' and s.server_id=f.server_id");
					//$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='{$row[server_id]}' limit 1");
					$db->query();
					$server_sql = $db->loadRow();
					// make connection to server
					$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
					if ($file_server_db) {
						// connect to database
						if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
							// insert the same sql to file server
							mysql_query("update files set validate=0 where id in ({$ids})",$file_server_db);
						}
						else {
							@mysql_close($file_server_db);
						}
					}
					else {
						@mysql_close($file_server_db);
					}
                }
                */
				/**
				 * End Change
			     */
            }
            redirect( "admin=images&s=".$input[s], "UnValidated successfully!" );
        }
        if ( isset( $input[thumb] ) )
        {
            $ids = implode( ",", array_keys( $input[idList] ) );
            if ( $ids )
            {
                $db->setquery( "update files set thumb=1 where id in ({$ids})" );
                $db->query( );
            }
            redirect( "admin=images&s=".$input[s], "Set as thumbnails successfully!" );
        }
        if ( isset( $input[unthumb] ) )
        {
            $ids = implode( ",", array_keys( $input[idList] ) );
            if ( $ids )
            {
                $db->setquery( "update files set thumb=0 where id in ({$ids})" );
                $db->query( );
            }
            redirect( "admin=images&s=".$input[s], "Set as no thumbnails successfully!" );
        }
        break;
    case "del" :
        $db->setquery( "select file,uid,size,server_id from files where id={$input['id']}" );
        $db->query( );
        $row = $db->loadrow( );
        if ( $row )
        {
            $db->setquery( "update server set hosted=hosted-1, webspace=webspace-'{$row['size']}' where server_id='{$row['server_id']}'" );
            $db->query( );
            if ( $row[uid] )
            {
                $db->setquery( "update users set files=files-1 where id='{$row['uid']}'" );
                $db->query( );                
            }
            $db->setquery( "update files set deleted=1 where id='{$input['id']}'" );
            $db->query( );
            
            
            /**
			 * [August 12 2008] Changelog
			 * update file server database file record
			 */
			$db->setQuery("select sql_host,sql_port,sql_username,sql_password,sql_db from server where server_id='{$row[server_id]}' limit 1");
			$db->query();
			$server_sql = $db->loadRow();
			// make connection to server
			$file_server_db = @mysql_connect($server_sql[sql_host], $server_sql[sql_username], $server_sql[sql_password]);
			if ($file_server_db) {
				// connect to database
				if (@mysql_select_db($server_sql[sql_db],$file_server_db)) {
					// insert the same sql to file server
					mysql_query("update files set deleted=1 where id='{$input[id]}'",$file_server_db);
				}
				else {
					@mysql_close($file_server_db);
				}
			}
			else {
				@mysql_close($file_server_db);
			}
		    /**
		     * End Change
       		*/
        }
        redirect( "admin=images&s=".$input[s], "Delete Successfully!" );
        break;
    case "upload" :
        $remoteScript = "http://".$file[domain]."/uploadthumb.php?&act=make&id=".$row[id];
        $fp = @fopen( @$remoteScript, "r" );
        redirect( "admin=images&s=".$input[s], "Delete Successfully!" );
        break;
    case "thumbnail" :
        showthumbuploadform( );
        break;
    default :
        showlistform( );
}
?>