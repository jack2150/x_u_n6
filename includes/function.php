<?
// include("includes/filelink.inc.php");

/**
* load downlaod rules based on $group_id,$country_code
*/
function loadDownloadRules($group_id,$country_code)
{
global $input,$db,$user;
    # clean input
    $group_id = intval($group_id);
    $country_code = strtoupper($country_code);
    $default_rule = array(
        'rule_id'=>0,
        'dl_gid'=>0,
        'dl_area'=>'NA',
        'dl_points'=>0,
        'dl_maxbyday'=>0,
        'dl_donebytes'=>0,
        'dl_maxbytes'=>0,
    );

    # load rules by host package
    $db->setQuery("select * from downloadrules where dl_gid='$group_id'");
    $db->query();
    $rows=$db->loadRowList();

    # load rules
    foreach($rows as $row)
    {
        $codes = split(',', strtoupper($row[dl_area]));
        # if a NA rule loaded, set it as default rule!
        if($row[area]=='UN')$default_rule = $row;
        # if a match rule is found, ignore the default rule
        if(in_array($country_code,$codes)) return $row;
    }

    return $default_rule;
}
##################
// limit http and ftp upload function (created: Jan 24 2008)
function checkUploadAllow($uploadTypeCheck,$hourCheck,$maxUploadFiles,$maxUploadFileSize) {
	global $db;
	$timeCheck=time()-(3600*$hourCheck);
	
	/*
	$db->setQuery("select count(id) as total_files, sum(size) as total_size from files where time>=$timeCheck and upload_type=$uploadTypeCheck");
	$db->query();
	$fileRows=$db->loadRow();
	*/
	$db->setQuery("select count(up_id) as total_files from upsessions where start_time >= $timeCheck and upload_type = $uploadTypeCheck");
	$db->query();
	$fileRows=$db->loadRow();
	

	if ($fileRows["total_files"]>=$maxUploadFiles) {
		return false;
	}
	else {
		/*
		$tempUploadFileSize=$maxUploadFileSize*1024*1024;
		if($fileRows["total_size"]>=$tempUploadFileSize) {
			return false;
		}
		else {
			return true;
		}
		*/
		return true;
	}
}
##################
/**
* Show uplaod form
*/
function showUploadForm()
{
global $input, $user, $template, $server_id, $baseWeb, $_SERVER;
    
    # get upload server details
    $server = $user->getServer();

    # initiate the uploader class and create the upload session
    $uploader = new uploader($server_id);
    $uploader->create_session($user->uid);

    $uploadmode = getCookies('uploadmode')?getCookies('uploadmode'):$input[uploadmode];
    if(!in_array($uploadmode,array(1,2,3,4))) $uploadmode=1;

    # cross domain access:Javscript and Ajax Use!
    $localhost=$_SERVER[HTTP_HOST];
    $parts=parse_url($baseWeb);
    
    if ($input[folder_id]) {
    	$user->php_upload_url = $user->php_upload_url . "?folder_id=" . $input[folder_id];
    }
    else {
    	$user->php_upload_url = $user->php_upload_url . "?folder_id=0";
    }

    # assign vars
    $template->assign_vars(array(
    'allowed_filesize'   => $user->allowed_filesize,
    'allowed_types'      => implode(',',$user->allowed_filetypes),
    'disabled_types'     => implode(',',$user->disabled_filetypes),
    'format_filesize'    => convertsize($user->allowed_filesize),
    'max_uploads'        => $user->max_uploads,
    'use_flash_progress' => $user->setting[use_flash_progress],
    'upload_session'     => $uploader->upload_session,
    'AccessKey'          => $uploader->access_code,
    'ServerID'           => $server[server_id],
    'uploadmode'         => $uploadmode,
    'show_options'       => $user->setting[show_options]==1?'block':'none',
    'password_disabled'  => $user->dl_password==1?'':' onclick="RegisterNow()"',
    # set php uploader script as action url for url and ftp upload if the progress bar is ajax
    'cgi_upload_url'     => $user->cgi_upload_url,
    'url_upload_url'     => $server[url_prog_mode]=='ajax'?$user->php_upload_url:$baseWeb.'/cross.php',
    'ftp_upload_url'     => $server[ftp_prog_mode]=='ajax'?$user->php_upload_url:$baseWeb.'/cross.php',
    'php_upload_url'     => $user->php_upload_url,
    'php_progress_url'   => $user->php_progress_url,
    'flash_progress_url' => $baseWeb.'/skin/'.$user->setting[skin_dir].'/images/progress_bar.swf',
    # url on main site:
    'swf_upload_url'     => $baseWeb.'/includes/uploader.swf',//$user->swf_upload_url,
    'return_url'         => $baseWeb.'/cross.php',
    ));
	
	#####################################
	// limit http and ftp upload function
	// http check
	if (!checkUploadAllow(2,HOURCHECK,MAXUPLOADFILES,MAXUPLOADFILESIZE)) {	$user->can_urlupload=0; }
	// ftp check
	if (!checkUploadAllow(3,HOURCHECK,MAXUPLOADFILES,MAXUPLOADFILESIZE)) { $user->can_ftpupload=0; }
	#######################################
    
    # select the upload method
    $template->assign_var('file_selected', $uploadmode==1&&$user->can_formupload!=-1?'checked':' ');
    $template->assign_var('url_selected',  $uploadmode==2&&$user->can_urlupload!=-1?'checked':' ');
    $template->assign_var('ftp_selected',  $uploadmode==3&&$user->can_ftpupload!=-1?'checked':' ');
    $template->assign_var('flash_selected',$uploadmode==4&&$user->can_flashupload!=-1?'checked':' ');

    # site offline
    if($user->setting[site_offline]==1)
    {
        $template->assign_var('IS_siteoffline',1);
        $template->assign_block_vars('siteoffline', array());
    }
    else
    {
        # show or not
        $template->assign_var('show_formupload',  $user->can_formupload!=-1);
        $template->assign_var('show_urlupload',   $user->can_urlupload!=-1);
        $template->assign_var('show_ftpupload',   $user->can_ftpupload!=-1);
        $template->assign_var('show_flashupload', $user->can_flashupload!=-1);

        # enabled or not
        $template->assign_var('can_formupload',  $user->can_formupload);
        $template->assign_var('can_urlupload',   $user->can_urlupload);
        $template->assign_var('can_ftpupload',   $user->can_ftpupload);
        $template->assign_var('can_flashupload', $user->can_flashupload);
        
        # enabled or not
        $template->assign_var('cgi_prog_mode',  $server[cgi_prog_mode]);
        $template->assign_var('url_prog_mode',  $server[url_prog_mode]);
        $template->assign_var('ftp_prog_mode',  $server[ftp_prog_mode]);
        
        # set upload interface status
        $template->assign_var('file_block',  $uploadmode==1&&$user->can_formupload!=-1?'block':'none');
        $template->assign_var('url_block',   $uploadmode==2&&$user->can_urlupload!=-1?'block':'none');
        $template->assign_var('ftp_block',   $uploadmode==3&&$user->can_ftpupload!=-1?'block':'none');
        $template->assign_var('flash_block', $uploadmode==4&&$user->can_flashupload!=-1?'block':'none');
    }
}
function PHPunescape ($source)
{
    $decodedStr = "";
    $pos = 0;
    $len = strlen ($source);
    while ($pos < $len) {
        $charAt = substr ($source, $pos, 1);
        if ($charAt == '%') {
            $pos++;
            $charAt = substr ($source, $pos, 1);
            if ($charAt == 'u') {
                // we got a unicode character
                $pos++;
                $unicodeHexVal = substr ($source, $pos, 4);
                $unicode = hexdec ($unicodeHexVal);
                $entity = "&#". $unicode . ';';
                $decodedStr .= utf8_encode ($entity);
                $pos += 4;
            }
            else {
                // we have an escaped ascii character
                $hexVal = substr ($source, $pos, 2);
                $decodedStr .= chr (hexdec ($hexVal));
                $pos += 2;
            }
        } else {
            $decodedStr .= $charAt;
            $pos++;
        }
    }
    return $decodedStr;
}
/**
* Show links for files
*/
/*
function showFileLinks()
{
	global $input,$baseUrl,$baseWeb,$db,$user,$template;
    $per_num=20;
	
    $input[s]=intval($input[s]);
	if ($input[s]<0) {
		$input[s]=0;
	}
	if (($input[s]%20)!=0) {
		$input[s] = @ceil($input[s]/20)*20;
	}

    $db->setQuery("select count(*) as nums from files as f where f.uid='$user->uid' and f.deleted=0");
    $db->query();
	
    $tmp=$db->loadRow();

    $db->setQuery("select f.*,sum(fs.dls) as downloads from files as f LEFT JOIN filestats as fs ON fs.upload_id=f.upload_id where f.uid='$user->uid' and f.deleted=0 group by f.upload_id order by f.time DESC limit $input[s],$per_num");
    $db->query();
    $rows=$db->loadRowList();
	
	if ($tmp[nums] > 0) {
		 $template->assign_var('gotFiles', 1);
	}

    $cur_page=@($input[s]/$per_num);
    $info=array('total'      =>$tmp[nums],
                'page'       =>$per_num,
                'cur_page'   =>$cur_page,
                'baseUrl'    =>$baseUrl."&showlinks=1"
                );
	
	

    $pageLinks=buildPageLinksNew($info);
    $template->assign_vars(array('pageLinks'=>$pageLinks,'filenums'=>$tmp[nums],'pages'=>$per_num));

    buildLinksCode($rows);
}
*/
/*
function showFileLinks()
{
	global $input,$baseUrl,$baseWeb,$db,$user,$template;
	
	// Set the number of links as default 20
    $number_link_per_page = 20;
    
    // if links number is less than 0 or 
    $input["s"] = intval($input["s"]);
	if ($input["s"] < 0) {
		$input["s"] = 0;
	}
	
	if (($input["s"] % 20) != 0) {
		$input["s"] = @ceil($input["s"] / 20) * 20;
	}
	
	// Count total files for user
    $db->setQuery("select count(*) as total_files from files as f where f.uid='{$user->uid}' and f.deleted=0");
    $db->query();
    $stats = $db->loadRow();
    
    // Select result from files
    $db->setQuery("select f.*, fs.dls from files as f LEFT JOIN filestats as fs ON f.upload_id=fs.upload_id "
    	. "where f.uid='{$user->uid}' and f.deleted=0 order by f.time DESC limit {$input["s"]}, {$number_link_per_page}");
    $db->query();
    $files = $db->loadRowList();
    
    // Set templete show file links or not and set total files or later use
	if ($stats["total_files"] > 0) {
		$template->assign_var('gotFiles', 1);
		$total_files = $stats["total_files"];
	}
	else {
		$total_files = 0;
	}

	// Set current pages	
    $current_page = @($input["s"] / $number_link_per_page);

    // Build links by using function   
    $pageLinks=buildPageLinksNew(array('total'=>$total_files,'page'=>$number_link_per_page,
    	'cur_page'=>$current_page,'baseUrl'=>$baseUrl."&showlinks=1"));
    
    // Assign all row and variable to template 	
    $template->assign_vars(array('pageLinks'=>$pageLinks,'filenums'=>$total_files,'pages'=>$number_link_per_page));

    // Start building links for user
    buildLinksCode($files);	
}
*/

/**
*  generate links code
*/
function buildLinksCode($images)
{
	global $LANG,$input,$baseWeb,$db,$user,$template,$ThumbFile;

    // make a new folder_id array
    foreach ($images as $file) { $temp_fid[] = $file["upload_id"]; }
    
    if ($temp_fid) {
	    $fid_list = "'".implode($temp_fid,"','")."'";
	    $db->setQuery("select upload_id, dls from filestats where upload_id in ({$fid_list})");
	    $db->query();
	    $filestats = $db->loadRowList();
	    foreach ($filestats as $fs) {
	    	$dls[$fs["upload_id"]] = $fs["dls"];
	    }
	    unset($filestats);
	    unset($fs);
	    unset($fid_list);
    }

    $i=0;
	$start_no = $input[s] + 1;
    foreach($images as $image)
    {
        $i++;
        $image[name] = base64_decode($image[name]);
        # retrieve the urls
        $urls=getDownloadUrl($image);
        extract($urls);
        
        if ($dls[$image["upload_id"]]) {
        	$image[dls] = $dls[$image["upload_id"]];
        }
        else {
        	$image[dls] = 0;
		}

		if (strlen($image[name]) > 34) {
			$image[name] = substr($image[name], 0, 34)."...";
		}
		
		// file type and file icon
		$image[file_icon] = file_icon(getExt($image[name],'.'));
		$image[file_type] = file_type(getExt($image[name],'.'));

		/*
		if ($image[password]=="") {
			$image[password]=$LANG['NoPass'];
		}
		else {
			$image[password]=$LANG['GotPass'];
		}
		*/
		
        //$image[showthumb] = $image['thumb'];
       
        $image[remoteWeb] = $image[http].$image['domain'];
        $image[accessKey] = base64_encode(encryptStr("$user->uid",$input[IP_CLIENT]));

        $image[size]=convertsize($image[size]);
        $image[s]=$input[s];
		
		/*
		if ($image[validate] == 1) { $image[validate] = $LANG['FileValidatePass']; }
		elseif ($image[validate] == -1) { $image[validate] = $LANG['WaitingValidate']; }
		else { $image[validate] = $LANG['FileValidateNon']; }
		*/
		
		/**
		 * March 29 2009 - Modify Folder Style
		 * modify display files status
		 */
		// file validate images display
		switch ($image[validate]) {
			case -1:
				$image[validate] = "bullet_orange.gif";
				$image[validate_tips] = $LANG["WaitingValidate"];
				break;
			case 0:
				$image[validate] = "bullet_red.gif";
				$image[validate_tips] = $LANG["FileValidateNon"];
				break;
			case 1:
				$image[validate] = "bullet_green.gif";
				$image[validate_tips] = $LANG["FileValidatePass"];
				break;
		}
		
		// file password images display
		if ($image[password] == "") {
			$image[password] = "bullet_none.gif";
			$image[password_tips] = $LANG[No_Pass];
		}
		else {
			$image[password] = "bullet_key.gif";
			$image[password_tips] = $LANG[Use_Pass];
		}
		
		// new upload date new display
		$image[upload_date] = date("y{$LANG[Years]}m{$LANG[Months]}d{$LANG[Days]}",$image['time']);

		       
        // $image[thumb_url]    = $thumburl;
        $image[delete_url]   = $deleteurl;
        $image[download_url] = $downloadurl;
        //$image[upload_date]  = date(TimeStamp,$image['time']);
		$image[no] = $i;

        /*$image[hasdescr]=strlen($image[descr])!=0;
        $image[descr]=$image[descr]==''?$LANG[ClickEditDescr]:htmlspecialchars($image[descr]);

        $image[haspw]=strlen($image[password])!=0;
        $image[password]=$image[password]==''?$LANG[ClickEditPW]:$image[password];
        */
        $template->assign_block_vars('row', $image);
    }
    
    $template->assign_var('total_files',$i ? $i : 0);
}
/**
 * March 31 2009: Function Select Icon
 */
function file_icon($ext) {
	if ($ext == "rar" || $ext == "zip" || $ext == "gz" || $ext == "gzip" || $ext == "b2z" || $ext == "tar.gz" || $ext == "tgz") {
		return "compress.gif";
	}
	elseif ($ext == "wmv" || $ext == "rm" || $ext == "mpg" || $ext == "mpeg" || $ext == "avi" || $ext == "flv" || $ext == "rmvb" || $ext == "mkv" || $ext == "mp4") {
		return "video.gif";
	}
	elseif ($ext == "mp3" || $ext == "wma" || $ext == "snd" || $ext == "wav" || $ext == "aud") {
		return "audio.gif";
	}
	elseif ($ext == "gif" || $ext == "bmp" || $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "pic" || $ext == "img" || $ext == "dwg" || $ext == "tiff") {
		return "image.gif";
	}
	elseif ($ext == "exe" || $ext == "msi" || $ext == "com" || $ext == "bat" || $ext == "app" || $ext == "jar" || $ext == "cmd") {
		return "program.gif";
	}
	elseif ($ext == "txt" || $ext == "rtf" || $ext == "pdf" || $ext == "html" || $ext == "htm" || $ext == "inc" || $ext == "ini" || $ext == "xml") {
		return "text.gif";
	}
	elseif ($ext == "doc" || $ext == "docx" || $ext == "xls" || $ext == "xlsx" || $ext == "ppt" || $ext == "pptx" || $ext == "pub" || $ext == "xsf") {
		return "office.gif";
	}
	else {
		return "other.gif";
	}
}

function file_type($ext) {
	global $LANG;
	
	if ($ext == "rar" || $ext == "zip" || $ext == "gz" || $ext == "gzip" || $ext == "b2z" || $ext == "tar.gz" || $ext == "tgz") {
		return $LANG[FileType][Compress];
	}
	elseif ($ext == "wmv" || $ext == "rm" || $ext == "mpg" || $ext == "mpeg" || $ext == "avi" || $ext == "flv" || $ext == "rmvb" || $ext == "mkv" || $ext == "mp4") {
		return $LANG[FileType][Video];
	}
	elseif ($ext == "mp3" || $ext == "wma" || $ext == "snd" || $ext == "wav" || $ext == "aud") {
		return $LANG[FileType][Audio];
	}
	elseif ($ext == "gif" || $ext == "bmp" || $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "pic" || $ext == "img" || $ext == "dwg" || $ext == "tiff") {
		return $LANG[FileType][Image];
	}
	elseif ($ext == "exe" || $ext == "msi" || $ext == "com" || $ext == "bat" || $ext == "app" || $ext == "jar" || $ext == "cmd") {
		return $LANG[FileType][Program];
	}
	elseif ($ext == "txt" || $ext == "rtf" || $ext == "pdf" || $ext == "html" || $ext == "htm" || $ext == "inc" || $ext == "ini" || $ext == "xml") {
		return $LANG[FileType][Text];
	}
	elseif ($ext == "doc" || $ext == "docx" || $ext == "xls" || $ext == "xlsx" || $ext == "ppt" || $ext == "pptx" || $ext == "pub" || $ext == "xsf") {
		return $LANG[FileType][Office];
	}
	else {
		return $LANG[FileType][Other];
	}
}


/**
* build nice package information for users and output
*/
function showPackageInfo()
{
global $user,$baseUrl,$db,$LANG,$template;

    $template->assign_vars(array(
    'format_sizelimit'=> convertsize($user->allowed_filesize),
    'sizelimit'=>$user->allowed_filesize,
    'format_userwebspace'=> convertsize($user->webspace),
    'format_webspace'=>convertsize($user->allowed_max_webspace),
    'hosted_files'=>$user->hosted_files,
    'total_downloads'=>$user->total_downloads,
    'total_points'=>$user->total_points,
    'last_update'=>date(DateFormat,$user->last_update),
    'account_reg_date'=>date(DateFormat,$user->reg_date),
    'package_name'=>$user->package,
    'is_custom'=>$user->is_custom,
    'account_expire_date'=>$user->account_expire_date,
    'account_expired'=>$user->account_expired,
    'username'=>$user->username,
    'max_uploads'=>$user->max_uploads,
    ));
}
/**
* show all package information
*/
function ListHostPackages($group_id=0)
{
global $db,$template,$user,$LANG,$baseWeb,$DATASTORE;

    # load package list
    if(!is_array($DATASTORE[groups]))
    {
        $db->setQuery("select * from groups ".($group_id? " where id = '$group_id'": ''));
        $db->query();
        $rows=$db->loadRowList();
    }
    else
    {
        $rows=$group_id?array($DATASTORE[groups][$group_id]):$DATASTORE[groups];
    }

    # image template
    $tick_img = '<img src="'.$baseWeb.'/skin/'.$user->setting[skin_dir].'/images/tick.png">';
    $x_img = '<img src="'.$baseWeb.'/skin/'.$user->setting[skin_dir].'/images/x.png">';

    # assign vars of host package to template
    foreach($rows as $row)
    {        
        # build subscription info
        $ps=split(',',$row[subscr_period]);
        $fs=split(',',$row[subscr_fee]);
        $price_list='';
        if($row[subscr_fee]=='')
        {
            $price_list=$LANG['Free'];
        }
        else
        {
            for($i=0;$i<count($ps);$i++)
            {
                $price_list.=$LANG[CurrencySymbol].($fs[$i]).'/'.$ps[$i].' '.$LANG[Units][$row[subscr_unit]].'<br>';
            }
        }
        # upload limitions
        $template->assign_block_vars('name',array('var'=>$row[name]));
        $template->assign_block_vars('sizelimit',array('var'=>convertsize($row[sizelimit])));
        $template->assign_block_vars('max_uploads',array('var'=>$row[max_uploads]));

        $filetype = $row[allowed_filetype]?$LANG[AllowedFiletypes].':'.$row[allowed_filetype]:($row[disabled_filetype]?$LANG[DisabledFiletypes].':'.$row[disabled_filetype]:$LANG[AllFiletypeAllowed]);
        
        $template->assign_block_vars('filetype',array('var'=>$filetype));
        $template->assign_block_vars('allow_filetype',array('var'=>$row[allow_filetype]));
        $template->assign_block_vars('disabled_filetype',array('var'=>$row[disabled_filetype]));

        # download options
        $template->assign_block_vars('dl_speed',array('var'=>$row[dl_speed]==0?$LANG['Unlimited']:$row[dl_speed].'kb/s'));
        $template->assign_block_vars('dl_ips',array('var'=>$row[dl_ips]==0?$LANG['Unlimited']:$row[dl_ips]));
        $template->assign_block_vars('dl_resume',array('var'=>$row[dl_resume]==1?$tick_img:$x_img));
        $template->assign_block_vars('dl_captcha',array('var'=>$row[dl_captcha]==0?$tick_img:$x_img));

        # upload method
        $template->assign_block_vars('formupload',array('var'=>$row[formupload]==1?$tick_img:$x_img));
        $template->assign_block_vars('urlupload',array('var'=>$row[urlupload]==1?$tick_img:$x_img));
        $template->assign_block_vars('ftpupload',array('var'=>$row[ftpupload]==1?$tick_img:$x_img));
        $template->assign_block_vars('flashupload',array('var'=>$row[flashupload]==1?$tick_img:$x_img));

        # ads setting
        $template->assign_block_vars('show_sponser_ads',array('var'=>$row[show_sponser_ads]==1?$tick_img:$x_img));
        $template->assign_block_vars('show_site_ads',array('var'=>$row[show_site_ads]==1?$tick_img:$x_img));
        $template->assign_block_vars('folder',array('var'=>$row[folder]==1?$tick_img:$x_img));

        # delete days
        $template->assign_block_vars('cron_days',array('var'=>$row[cron_enabled]?$row[cron_days].$LANG[NoDownloads]:$LANG['NeverDelete']));

        # order
        $template->assign_block_vars('price',array('var'=>$price_list));
        $template->assign_block_vars('order',array('group_id'=>$row[id]));
    }
}
/**
* list files by condition
*/
function ListFiles($orderby='f.time',$ads_nums=1,$limit=5,$cols=1,$start=0)
{
global $user,$db,$template;

    $nowtime=time();
    $condiftion = ($user->setting[featured_type]?"f.validate=1 AND" : "") . " f.thumb=1";
    
    $db->setQuery("select f.*,s.http,s.domain from files as f
              left join server as s on s.server_id=f.server_id
              where $condiftion and f.deleted=0
              order by $orderby DESC
              limit $start,$limit");
    $db->query();
    $rows=$db->loadRowList();

    if(!$rows) return 0;
    $nums = count($rows);
    
    # mixed the ads unit!
    for($i=0;$i<$ads_nums;$i++)
    array_splice($rows, rand(1, $nums), 0,array(array("is_ads"=>1)));

    $nums = count($rows);

    $i=0;
    foreach($rows as $row)
    {
        $i++;
        $urls=getDownloadUrl($row);
        extract($urls);

        $start = ($i%$cols==1)||($cols==1);
        $end = ($i%$cols==0)||($cols==1)||($nums==$i);
        
        # mixed in ads!
        if($row[is_ads]==1)
        $template->assign_block_vars('row',array(
                'is_end'      => $end,
                'is_start'    => $start,
                'is_ads'      => 1
                ));
        else
        $template->assign_block_vars('row', array(
	 			'downloadurl' => $downloadurl,
                'thumburl'    => $thumburl,
                'size'        => convertsize($row[size]),
                'descr'       => $row[descr],
                'name'        => $row[name],
                'add_date'    => date('M d,Y H:i',$row['time']),
                'add_days'    => intval((time()-$row['time'])/(24*3600)),
                'is_end'      => $end,
                'is_start'    => $start,
                'is_ads'      => 0,
				));
    }
}
/**
* get total files to be listed
*/
function GetFileNums()
{
global $db,$user;

    $condiftion = ($user->setting[featured_type]?"f.validate=1 AND" : "") . " f.thumb=1";
    $nowtime=time();
    $db->setQuery("SELECT count(f.id) as num
                   FROM files as f
                   WHERE $condiftion AND f.deleted=0");
    $db->query();
    $row=$db->loadRow();
    return $row[num];
}
/**
*Build url for download files,short, seo url
* @info =array('id'=>,'file'=>,'upload_id'=>,'delete_id'=>,'thumb'=>,'http'=>,'domain'=>,)
*/
function getDownloadUrl($info, $urltype='')
{
global $langWeb,$baseWeb,$user;

    # get thumbnail url
    $ImageFile= array('jpg','jpeg','gif','png');
    $filetype = getExt($info[file],'_')?getExt($info[file],'_'):getExt($info[file]);
    $thumbfilename = !in_array($filetype,$ImageFile) ? substr($info[file],0,-(strlen($filetype)+1)).'.jpg':str_replace('_','.',$info[file]);

    # get thumbnail url
    if($info[thumb]==1)
    {
        $return[thumburl] = $info[http].$info[domain].'/thumb/thumb_'.$thumbfilename;
    }
    elseif($info[thumb]==0)
    {
        $return[thumburl] = $baseWeb.'/skin/'.$user->setting[skin_dir].'/thumb/soon.jpg';
    }
    else
    {
        $return[thumburl] = $baseWeb.'/skin/'.$user->setting[skin_dir].'/thumb/thumb_'.$filetype.'.jpg';
    }
    
    $urltype = $urltype ? $urltype : $user->setting[static_url];
    
    # get download url for different modes
    if($urltype=='short')
    {
        $return[downloadurl]=$baseWeb.'/?d='.$info[upload_id];
        $return[deleteurl]=$baseWeb.'/?d='.$info[upload_id].$info[delete_id];
    }
    elseif($urltype=='seo')
    {
        //$return[downloadurl]=$langWeb.'/file/'.strtolower($info[upload_id]).'/'.urlencode($info[name]).'.html';
        //$return[deleteurl]=$langWeb.'/delete/'.strtolower($info[upload_id].$info[delete_id]).'/'.urlencode($info[name]).'.html';
        $return[downloadurl]=$langWeb.'/file/'.strtolower($info[upload_id]).'/'.$info[id].'.html';
        $return[deleteurl]=$langWeb.'/delete/'.strtolower($info[upload_id].$info[delete_id]).'/'.$info[id].'.html';
    }
    else
    {
        $return[downloadurl]=$langWeb.'/download.php?id='.$info[upload_id];
        $return[deleteurl]=$langWeb.'/delete.php?id='.$info[upload_id].$info[delete_id];
    }

    return $return;
}
/**
*Build url for download files,short, seo url
*/
function validateDownloadUrl($urls)
{
global $baseWeb,$db;

    # extract nice info
    $parts = parse_url($baseWeb);
    $domain = strtolower(substr($parts[host],0,4))=='www.' ? substr($parts[host],4):$parts[host];
    $path = str_replace('/','([/]?)',$parts[path]);

    # build reg patern
    $match=array();
    $match[dyn] = "'$parts[scheme]://([www\.]{0,4})$domain$path([/]?)([a-z]{0,2})([/]?)download.php\?id=([A-Z0-9]+)'";
    $match[short] = "'$parts[scheme]://([www\.]{0,4})$domain$path([/]+)\?d=([A-Z0-9]+)'";
    $match[seo] = "'$parts[scheme]://([www\.]{0,4})$domain$path([/]?)([a-z]{0,2})([/]?)file([/]+)([0-9]+)/(.*)'";

    # process the
    $upload_id=$file_id=array();
    if($urls)
    foreach($urls as $url)
    {
        if(preg_match($match[dyn], $url, $matchesL))
        {
            $upload_id[] = "'".$matchesL[count($matchesL)-1]."'";
        }
        elseif(preg_match($match[seo], $url, $matchesL))
        {
            $file_id[] = $matchesL[count($matchesL)-2];
        }
        elseif(preg_match($match[short], $url, $matchesL))
        {
            $upload_id[] = "'".$matchesL[count($matchesL)-1]."'";
        }
    }
    else
    {
        return 0;
    }
    # build query
    $upload_ids=implode(',',$upload_id);
    $file_ids=implode(',',$file_id);
    
    $upload_ids = $upload_ids ? "upload_id in ($upload_ids) or " : '';
    $file_ids   = $file_ids ? "id in ($file_ids)" : '0';
    
    if(!$upload_ids&&!$file_ids) return 0;
    
    # check against db
    $db->setQuery("select id from files where ($upload_ids $file_ids) and deleted=0");
    $db->query();
    $rows = $db->loadRowList('id');
    
    if(!count($rows)) return 0;
    return array_keys($rows);
}
/**
* get the urls for admin panel!
*/
function getUrls($filename,$sourceWeb,$exists=0)
{
global $baseWeb,$SET;
    $filetype = getExt($filename,'_')?getExt($filename,'_'):getExt($filename);
    $SET[skin_dir]='default';
    $ImageFile= array('jpg','jpeg','gif','png');

    if(!in_array($filetype,$ImageFile))
    {
        $thumbfilename = basename(substr($filename,0,-(strlen($filetype)+1)).'.jpg');
    }
    else
    {
        $thumbfilename = basename(str_replace('_','.',$filename));
    }
    $fileurl=$sourceWeb.'/admin/download.php?f='.$filename;
    if($exists==1)
    {
        $thumburl=$sourceWeb.'/thumb/thumb_'.($thumbfilename);
    }
    elseif($exists==0)
    {
        $thumburl=$baseWeb.'/skin/'.$SET[skin_dir].'/thumb/cominigsoon.gif';
    }
    else
    {
        $thumburl=$baseWeb.'/skin/'.$SET[skin_dir].'/thumb/thumb_'.$filetype.'.jpg';
    }
    return array('thumburl'=>$thumburl,'fileurl'=>$fileurl);
}
/**
* get the urls for fodlers!
*/
function getFolderUrl($info,$isadmin=0)
{
global $baseWeb,$SET,$langWeb;

    $folderurl = $langWeb.'/folders/'.$info[fid].'/'.makeurls($info[name]).'.html';
    return $folderurl;
}

/**
* auth the remote access to upload thumb!
*/
function verify_admin_access()
{
global $db,$input;
    $timeout = time() - 3600;
    $db->setQuery("select * from setting where admin_lastclick > $timeout and admin_ip= '$input[IP_CLIENT]'");
    $db->query();
    return $db->getNumRows();
}
/**
* build lang list!
*/
function build_lang_list($mylang='',$type='url')
{
global $LANG_TO_MATCH, $CODE_TO_LANG;
    $lang_list = '';
    foreach($LANG_TO_MATCH as $lang=>$matchcode)
    {
        $lang_code  = array_search($lang,$CODE_TO_LANG);
        $lang_list .= '<option value="'.$lang_code.'" '.((is_array($mylang)?in_array($lang_code,$mylang):$lang_code==$mylang)?' selected':'').'>'.$lang.($type=='url'?'[?setlang='.$lang_code.'])':'').'</option>';
    }
    return $lang_list;
}
/**
* build lang list!
*/
function build_country_list($mycountry='')
{
global $GEOIP_COUNTRY_NAMES, $GEOIP_COUNTRY_CODES;
    $country_list = '';
    foreach($GEOIP_COUNTRY_NAMES as $id=>$country)
    {
        if($country)
        $country_list .= '<option value="'.$GEOIP_COUNTRY_CODES[$id].'" '.((is_array($mycountry)?in_array($GEOIP_COUNTRY_CODES[$id],$mycountry):$GEOIP_COUNTRY_CODES[$id]==$mycountry)?' selected':'').'>'.$country.'</option>';
    }
    return $country_list;
}
/**
* site stats:today/yesterday uploads
*/
function siteStats()
{
global $db,$user,$template;
    # check sitestats is enabled
    if($user->setting[sitestats]!=1) return 0;
    
    # build date filter
    $today=date('d');
    $month=date('m');
    $year=date('y');
    $today_time=mktime(0,0,0,$month,$today,$year);
    $yestoday_time=mktime(0,0,0,$month,$today-1,$year);
    $thisweek_time=mktime(0,0,0,$month,$today-6,$year);

    # get stats from db
    $db->setQuery("select count(*) as nums,count(if(time>$today_time,1,NULL)) as todays,count(if($today_time>time and time>$yestoday_time,1,NULL)) as yesterdays from files where deleted=0");
    $db->query();
    $stats=$db->loadRow();
    $totalfiles=$stats[nums];
    $todayfiles=$stats[todays];
    $yesterdayfiles=$stats[yesterdays];

    $db->setQuery("select count(*) as num from users");
    $db->query();
    $stats=$db->loadRow();
    $totalusers=$stats[num];

    # template assigning
    $template->assign_var('show_site_stats',$user->setting[sitestats]);
    $template->assign_vars(array(
    'todayfiles'=>$todayfiles,
    'yesterdayfiles'=>$yesterdayfiles,
    'totalfiles'=>$totalfiles,
    'totalusers'=>$totalusers,
    ));
}
/**
* show news
*/
function showNews($uploadTimes)
{
	global $db;

    $db->setQuery("select title,body from news where start_date<$uploadTimes and end_date>=$uploadTimes limit 0,1");
    $db->query();
    $row=$db->loadRow();
    if(!$row) return "";
	else return "<b>".$row['title'].":</b> ".$row['body'];
}
/*
* build nav pages links
* need fix bug
*/
function buildPageLinksNew($info,$static=0)
{
	global $input,$baseUrl,$LANG,$template;
	
	if ($input["folder_id"]) {
		$folder_parameter = "&folder_id=".$input["folder_id"];
	}
	else {
		$folder_parameter = "";
	}
	
	if ($input[s] >= 0 and $input[s] <= $info[total]) {
		$current_page=$info[cur_page];
		$total_pages=@ceil($info[total]/$info[page]);
		$link_number=$info[page];
		$current_page_number=@ceil($current_page*$link_number);
		
		if (@$info[total] > 20) {
			$template->assign_var('showPageLink',1);
		}
		
		if ($total_pages==1||$total_pages==0) {
			return;
		}
		else {
			$first_pages = 0;
			
			
			if ($info[total] % 20 == 0) {
				$last_pages = $info[total] - 20;
				
			}
			else {
				$last_pages = @floor($info[total]/$info[page])*$link_number;
			}
			
			if ($first_pages != $current_page) {
				$first_pages_href = '<a href="'.$info[baseUrl].'&s='.$first_pages.$folder_parameter.'">'.$LANG[FirstPage].'</a>';
			}
			
			if ($last_pages != $current_page*$link_number) {
				$last_pages_href = '<a href="'.$info[baseUrl].'&s='.$last_pages.$folder_parameter.'">'.$LANG[LastPage].'</a>';
			}
			$previous_pages = ($current_page-1)*$link_number;
			$next_pages = ($current_page+1)*$link_number;
			
			if ($previous_pages >= 0) {
				$previous_pages_href = '<a href="'.$info[baseUrl].'&s='.$previous_pages.$folder_parameter.'">'.$LANG[PreviousPage].'</a>';
			}
			if ($next_pages < $info[total]) {
				$next_pages_href = '<a href="'.$info[baseUrl].'&s='.$next_pages.$folder_parameter.'">'.$LANG[NextPage].'</a>';
			}
		}
		
		$link_page = 10;
		
		$current_start = $current_page - ($current_page%$link_page);
		$current_end = $current_page + $link_page - ($current_page%$link_page);
		
		if ($current_start >= $link_page) {
			$current_temp = ($current_start - $link_page) * $link_number;
			$current_start_href = '<a href="'.$info[baseUrl].'&s='.$current_temp.$folder_parameter.'">'.$LANG[PreviousNumberPage].'</a>';
		}
		
		if ($current_end < $total_pages) {
			$current_temp = $current_end * $link_number;
			$current_end_href = '<a href="'.$info[baseUrl].'&s='.$current_temp.$folder_parameter.'">'.$LANG[NextNumberPage].'</a>';
		}
				
		for($temp_link=$current_start;$temp_link<$current_end;$temp_link++) {
			if ($temp_link <= ($total_pages-1)) {
				$temp_link_href = $temp_link + 1;
				if ($temp_link == $current_page) {	
					$page_links .= "&nbsp;<b>" . $temp_link_href . "</b>&nbsp;";
				}
				else {
					$temp_s = $temp_link*$link_number;
					$page_links .= '&nbsp;<a href="'.$info[baseUrl].'&s='.$temp_s.$folder_parameter.'">'.$temp_link_href.'</a>&nbsp;';
				}
			}
			else {
				break;
			}
		}
	}
	else {
		header("location: $info[baseUrl]&s=0");
	}
	
	
	
	return @$first_pages_href  . " " . $current_start_href . " " . @$previous_pages_href . " " . $page_links . " " .  @$next_pages_href . " " .  @$current_end_href . " " . @$last_pages_href;
}

function buildpagelinks($info,$static=0)
{
global $input,$baseUrl,$LANG;

    if(!is_array($LANG))
    {
        $LANG[Pages]='Pages';
        $LANG[LastPage]='Last page';
        $LANG[NextPage]='Next page';
    }

    $pages=  @ceil($info[total]/$info[page]);
    if($pages==1 || $pages== 0) return;
    $span=10;
    $s=intval(($info[cur_page]/$span))*$span;
    for($i=$s;$i<$s+$span; $i++)
    {
       if($i>=$pages) break;
       $start=$i*$info[page];
       $tag=$i+1;
       if($tag==$info[cur_page]+1)
       {
           $html.="&nbsp;<b>$tag</b>";
       }
       else
       {
           if($static)
           $html.=" &nbsp;<a href='$info[baseUrl]/{$tag}.html' class=pagenav>$tag</a>";
           else
           $html.=" &nbsp;<a href='$info[baseUrl]&s=$start' class=pagenav>$tag</a>";
       }
    }
    $start+=$info[page];
    if($start>$info[total])  $start-=$info[page];
    $start2=$s*($info[page]-1);
    $endpage=($pages-1)*$info[page];
    
    if($static)
    return "<a href='$info[baseUrl]/".($start2/$info[page]?$start2/$info[page]:'index.html')."' class=pagenav>$LANG[FirstPage]</a>&nbsp;<a href='$info[baseUrl]/".($start2/$info[page]+1).".html'><<</a>".$html."&nbsp;<a href='$info[baseUrl]/".($start/$info[page]+1).".html'>>></a> <a href='$info[baseUrl]/".($endpage/$info[page]+1).".html'>$LANG[LastPage]</a>($pages $LANG[Pages])";
    else
    return "<a href='$info[baseUrl]&s=$start2' class=pagenav>$LANG[FirstPage]</a>&nbsp;<a href='$info[baseUrl]&s=$start2'><<</a>".$html."&nbsp;<a href='$info[baseUrl]&s=$start'>>></a> <a href='$info[baseUrl]&s=$endpage'>$LANG[LastPage]</a> ($pages $LANG[Pages])";

}
/**
* get extention by the specified separater
*/
function getExt($name,$sep='.')
{
    $ext = substr(strtolower(strrchr($name,$sep)),1);
    #if($ext==''&&strpos($name,$sep)!==false) return ' ';
    return $ext;
}
/**
* keep extention or not by the specified separater
*/
function keepExt($name,$sep='.')
{
    $ext = substr(strtolower(strrchr($name,$sep)),1);
    if($ext==''&&strpos($name,$sep)!==false) return ' ';
    return $ext;
}
/**
* calculate the progress bar info
*/
function showStatus($iTotal,$iRead,$dtstart)
{
global $dtRemainingf,$dtelapsedf,$bSpeedf,$percent;
    ##
    # Elapsed time
    # Calculate elapsed time and format for display
    ##
    $dtnow=time();
    $dtelapsed = $dtnow - $dtstart;
    $dtelapsed_sec = ($dtelapsed % 60); # gets number of seconds
    $dtelapsed_min = ((($dtelapsed - $dtelapsed_sec) % 3600) / 60); # gets number of minutes
    $dtelapsed_hours = (((($dtelapsed - $dtelapsed_sec) - ($dtelapsed_min * 60)) % 86400) / 3600);
    # gets number of hours; assuming that we won't be going into days!
    if ($dtelapsed_sec < 10) { $dtelapsed_sec = "0$dtelapsed_sec"; } # append leading zero
    if ($dtelapsed_min < 10) { $dtelapsed_min = "0$dtelapsed_min"; } # append leading zero
    if ($dtelapsed_hours < 10) { $dtelapsed_hours = "0$dtelapsed_hours"; } # append leading zero
    $dtelapsedf = "$dtelapsed_hours:$dtelapsed_min:$dtelapsed_sec"; # display as 00:00:00

    ##
    # Upload speed
    ##
    $bSpeed = 0; # if not yet determined
    if ($dtelapsed > 0) # avoid divide by zero errors
    {
    	$bSpeed = $iRead / $dtelapsed; # Bytes uploaded / Seconds elapsed = Bytes/Second speed
    	$bitSpeed = $bSpeed * 8; # bps
    	$kbitSpeed = $bitSpeed / 1000; # Kbps
    }
    else
    {
    	$kbitSpeed = $bSpeed; # just pass the zero value
    }
    $bSpeedf = sprintf("%d",$kbitSpeed); # remove decimals


    ##
    # Est remaining time
    # Calculate remaining time based on upload speed so far
    ##

    $bRemaining = $iTotal - $iRead; # Total size - amount uploaded = amount remaining
    $dtRemaining = 0;
    if ($bSpeed > 0) {
    	# Bytes remaining / Bytes/Second = Seconds
    	$dtRemaining = $bRemaining / $bSpeed;
    }
    $dtRemaining = sprintf("%d",$dtRemaining); # remove decimals
    $dtRemaining_sec = ($dtRemaining % 60); # gets number of seconds
    $dtRemaining_min = ((($dtRemaining - $dtRemaining_sec) % 3600) / 60); # gets number of minutes
    $dtRemaining_hours = (((($dtRemaining - $dtRemaining_sec) - ($dtRemaining_min * 60)) % 86400) / 3600); # gets number of hours; assuming that we won't be going into days!
    if ($dtRemaining_sec < 10)
    {
     	# append leading zero
    	$dtRemaining_sec = "0$dtRemaining_sec";
    }
    if ($dtRemaining_min < 10)
    {
    	# append leading zero
    	$dtRemaining_min = "0$dtRemaining_min";
    }
    if ($dtRemaining_hours < 10)
    {
     	# append leading zero
    	$dtRemaining_hours = "0$dtRemaining_hours";
    }
    $dtRemainingf = "$dtRemaining_hours:$dtRemaining_min:$dtRemaining_sec"; # display as 00:00:00
    $percent = @($iRead * 100 / $iTotal);
    $percent = sprintf("%d",$percent ); # remove decimals
    flush();
    echo "<script>parent.showProgress('$iRead,$iTotal,$dtRemainingf,$dtelapsedf,$bSpeedf,$percent')</script>";

}
/**
* validate path, remove the relative path...
*/
function valid_path($path)
{
    $path = str_replace(base64_decode('XA=='),'/',$path);
    $absolute=0;
    if(substr($path,0,1)=='/')
    {
        $absolute=1;
        $path = substr($path,1);
    }
    $parts=split('/',$path);

    $valid_path=$absolute?'/':'';
    foreach($parts as $part)
    {
        $part = str_replace('..','',$part);
        if(!$part) continue;
        $valid_path.=$valid_path?$part.'/':$part.'/';
    }
    return $valid_path;
}
/**
* validate file, call valid_path
*/
function valid_file($file)
{
    $path = valid_path($file);
    
    $path = str_replace('/','',$path);

    return $path;
}
function blank($var)
{
    return ($var != '');
}
function remove_blank($string,$sep=',')
{
    return implode($sep,array_filter(explode($sep,$string),'blank'));
}

/**
* encrypt and decrypt a string
*/
// --------------
// This function takes a clear-text password and returns an encrypted password
// Written by Jakub
// --------------
function encryptStr($str,$encryption_string=null) {

// XOR with random string (which is set in settings.inc.php)

	$str_encrypted = "";
	$encryption_string = $encryption_string==null?'~!@#$%^&*()_+|':$encryption_string;
	if ($encryption_string % 2 == 1) { // we need even number of characters
		$encryption_string .= $encryption_string{0};
	}
	for ($i=0; $i < strlen($str); $i++) { // encrypts one character - two bytes at once
		$str_encrypted .= sprintf("%02X", hexdec(substr($encryption_string, 2*$i % strlen($encryption_string), 2)) ^ ord($str{$i}));
	}

	return $str_encrypted;

} // End function encryptPassword
function decryptStr($str_encrypted,$encryption_string=null) {

// --------------
// This function takes an encrypted password and returns the clear-text password
// Written by Jakub
// --------------
// XOR with random string (which is set in settings.inc.php)
	$password = "";
    $encryption_string = $encryption_string==null?'~!@#$%^&*()_+|':$encryption_string;
	if ($encryption_string % 2 == 1) { // we need even number of characters
		$encryption_string .= $encryption_string{0};
	}
	for ($i=0; $i < strlen($str_encrypted); $i += 2) { // decrypts two bytes - one character at once
		$password .= chr(hexdec(substr($encryption_string, $i % strlen($encryption_string), 2)) ^ hexdec(substr($str_encrypted, $i, 2)));
	}
	return $password;

} // End function decryptPassword
/**
* create a temp file with $prefix and $postfix
*/
function mytempname($dir, $prefix='', $postfix='')
{
	if ($dir[strlen($dir) - 1] == '/') { $trailing_slash = ""; }
	else { $trailing_slash = "/"; }

    // Check if the $dir is a directory
	if (!is_dir(realpath($dir)) || filetype(realpath($dir)) != "dir") { return false; }

    // Check if the directory is writeable
	if (!is_writable($dir)){ return false; }

    // Create the temporary filename
	do {
		$seed = substr(md5(microtime()), 0, 8);
		$filename = $dir . $trailing_slash . $prefix . $seed . $postfix;
	} while (file_exists($filename));
    $filename = $prefix . $seed . $postfix;
	return $filename;

} // end mytempnam
/**
* convert special character to "-"
*/
function makeurls($name)
{
    //first convert spanish charter
    $specs=array(''=>'a',''=>'e',''=>'i','' =>'o',''=>'u','?' =>'n');
    foreach($specs as $k => $v)
    {
        $name=str_replace($k,$k,$name);
    }
    $name=str_replace(chr($k),'-',$name);
    for ($k = 1; $k <= 47; $k++)
    {
        $name=str_replace(chr($k),'-',$name);
    }
    for ($k = 58; $k <= 64; $k++)
    {
        $name=str_replace(chr($k),'-',$name);
    }
    for ($k = 91; $k <= 96; $k++)
    {
        $name=str_replace(chr($k),'-',$name);
    }
    for ($k = 123; $k <= 255; $k++)
    {
        $name=str_replace(chr($k),'-',$name);
    }
    return $name;
}
function buildGETQuery($input)
{
    if(is_array($input))
    foreach($input as $key=> $value)
    {
        if(is_array($value))
        {
            foreach($value as $k=> $v)
            {
                if(!is_array($v))
                $myquery.=$key.'%5B'.$k."%5D=".urlencode(clean_value($v))."&";
            }
        }
        else
        $myquery.=$key."=".urlencode(clean_value($value))."&";
    }
    return $myquery;
}
/**
* parse request info and build in one array $input
*/
function parse_incoming()
{
  	global $_GET, $_POST, $HTTP_CLIENT_IP, $REQUEST_METHOD, $REMOTE_ADDR, $HTTP_PROXY_USER, $HTTP_X_FORWARDED_FOR;
   	$return = array();

	if( is_array($_GET) )
	{
		while( list($k, $v) = each($_GET) )
		{
			if( is_array($_GET[$k]) )
			{
				while( list($k2, $v2) = each($_GET[$k]) )
				{
					$return[$k][ clean_key($k2) ] =clean_value($v2);
				}
	     	}
			else
			{
				$return[ clean_key($k) ] =clean_value($v);
			}
		}
	}

	// Overwrite GET data with post data

	if( is_array($_POST) )
	{
		while( list($k, $v) = each($_POST) )
        {
    		if ( is_array($_POST[$k]) )
			{
				while( list($k2, $v2) = each($_POST[$k]) )
				{
					$return[$k][ clean_key($k2) ] =clean_value($v2);
				}
			}
			else
			{
				$return[ clean_key($k) ]  =clean_value($v);
			}
		}
	}
    $return['REQUEST_METHOD']=$_SERVER['REQUEST_METHOD'];
    $return['IP_ADDRESS']=$_SERVER['SERVER_ADDR'];
    $return['IP_CLIENT']=$_SERVER['REMOTE_ADDR'];
    $return['IP_CLIENT'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER[REMOTE_ADDR];
    define('IS_POST',$return['REQUEST_METHOD']=='POST');
    
	return $return;
}
/**
* called in parse_incoming
*/
function clean_key($key)
{
  	return $key;
}
/**
* called in parse_incoming
*/
function clean_value($val)
{
    if ( get_magic_quotes_gpc()!=0 )
    {
        $val = stripslashes($val);
   	}
  	return $val;
}
/**
* set cookie with a nice interface
* $name: string
* $value: string
*/
function setCookies($name,$value,$sticky=1)
{
    if ($sticky == 1)
    {
       	$expires = time() + 60*60*24*365;
    }
    if($_SERVER[HTTP_HOST]!='127.0.0.1'&&$_SERVER[HTTP_HOST]!='localhost')
    {
        if(strtolower(substr($_SERVER[HTTP_HOST],0,4))=='www.')
        $cookie_domain = substr($_SERVER[HTTP_HOST],3);
        else
        $cookie_domain = '.'.$_SERVER[HTTP_HOST];
    }
    else
    {
        $cookie_domain = "";
    }
    $cookie_path   = "/";

    $name = $name;
    setcookie($name, $value, $expires, $cookie_path, $cookie_domain);
}
/**
* get cookie with a nice interface
* $name: string
*/
function getCookies($name)
{
global $_COOKIE;

    if (isset($_COOKIE[$name]))
    {
    	return urldecode($_COOKIE[$name]);
   	}
   	else
   	{
   		return FALSE;
   	}
}
/**
* get cookie with a nice interface
* $name: string
*/
function getCookieFrom($cookiename,$source)
{
    $cookiename = $cookiename;
    $cookiestring=$source;
    $index1=strpos($cookiestring,$cookiename);
    if ($index1===false || $cookiename=="") return "";
    $index2=strpos($cookiestring,';',$index1);
    if ($index2===false) $index2=strlen($cookiestring);
    return PHPunescape(substr($cookiestring,$index1+strlen($cookiename)+1,$index2-$index1-strlen($cookiename)-1));

}
/**
* record input into the cookie of client side
* used in admin panle to remember the search info on user PC
* $input: array, the source vars
* $prefix: string, a filter on vars
*/
function input2cookie($input,$prefix='')
{
    $parts=split(',',$prefix);
    foreach($input as $name=>$value)
    {
        if(!is_array($value))
        {
            if($parts)
            foreach($parts as $part)
            {
                if(substr($name,0,strlen($part))==$part)
                {
                    setCookies($name,$value);
                    break;
                }
            }
            else
            {
                setCookies($name,$value);
            }
        }
    }
}
/**
* record input into the session of users
* used in admin panle to remember the search info
* $input: array, the source vars
* $prefix: string, a filter on vars
*/
function input2session($input,$prefix='')
{
    $parts=split(',',$prefix);
    foreach($input as $name=>$value)
    {
        if(!is_array($value))
        {
            if($parts)
            foreach($parts as $part)
            {
                if(substr($name,0,strlen($part))==$part)
                {
                    $_SESSION[$name]=$value;
                    break;
                }
            }
            else
            {
                $_SESSION[$name]=$value;
            }
        }
    }
}
/**
* record cookie into the session
* $prefix: string, a filter on vars
*/
function cookie2session($prefix='')
{
global $_COOKIE;
    $parts=split(',',$prefix);
    foreach($_COOKIE as $name=>$value)
    {
        $name=substr($name,4);
        if($parts)
        foreach($parts as $part)
        {
            if(substr($name,0,strlen($part))==$part)
            {
                $_SESSION[$name]=$value;
                break;
            }
        }
        else
        {
            $_SESSION[$name]=$value;
        }
    }
}
/**
* record session into the cookie
* $prefix: string, a filter on vars
*/
function session2cookie($prefix='')
{
global $_COOKIE;
    $parts=split(',',$prefix);
    foreach($_SESSION as $name=>$value)
    {
        if(!is_array($value))
        {
            if($parts)
            foreach($parts as $part)
            {
                if(substr($name,0,strlen($part))==$part)
                {
                    setCookies($name,$value);
                    break;
                }
            }
            else
            {
                setCookies($name,$value);
            }
        }
    }
}
/**
* build nice format for bytes
* $size: digital
* $mode: set to 1 will remove the numbers after the first dot
*/
function convertsize($size,$mode=0)
{
   $times = 0;
   $comma = '.';
   while ($size>1024){
      $times++;
      $size = $size/1024;
   }
   $size2 = floor($size);
   $rest = $size - $size2;
   $rest = $rest * 100;
   $decimal = floor($rest);

   $addsize = $decimal;
   if ($decimal<10) {$addsize .= '0';};

   if ($times == 0){$addsize=$size2;} else
   {$addsize=$size2.$comma.substr($addsize,0,2);}

   switch ($times) {
      case 0 : $mega = " Byte"; break;
      case 1 : $mega = " KB"; break;
      case 2 : $mega = " MB"; break;
      case 3 : $mega = " GB"; break;
      case 4 : $mega = ' TB'; break;
   }
   if($mode==1&&(($pos=strrpos($addsize,'.')))!==false)$addsize=substr($addsize,0,$pos);
   $addsize .= $mega;
   return $addsize;
}
/**
* clean input data
* @ $input is a array or object
* @ $filters is a array
*/
function clean_input(&$input,$filters=array())
{
    if(!is_array($input)&&!is_object($input)) return '';
    $is_obj = is_object($input);
    foreach($filters as $varname=>$vartype)
    {
        if((!$is_obj&&!isset($input[$varname]))||($is_obj&&!isset($input->$varname))) continue;
        $varvalue = $is_obj ? $input->$varname:$input[$varname];
        
        if(is_array($vartype)) {$varfunc = $vartype[1];$vartype = $vartype[0];}
        switch ($vartype)
 	    {
            case INT:      $is_obj ? $input->$varname = intval($varvalue)
                                   : $input[$varname] = intval($varvalue);
                           break;
			case UINT:
                           $is_obj ? $input->$varname = ($varvalue = intval($varvalue)) < 0 ? 0 : $varvalue
                                   : $input[$varname] = ($varvalue = intval($varvalue)) < 0 ? 0 : $varvalue;
                           break;
            case NUM:
                           $is_obj ? $input->$varname = strval($varvalue) + 0
                                   : $input[$varname] = strval($varvalue) + 0;
                           break;
			case UNUM:
                           $is_obj ? $input->$varname = ($varvalue = (strval($varvalue)) < 0) ? 0 : $varvalue
                                   : $input[$varname] = ($varvalue = (strval($varvalue)) < 0) ? 0 : $varvalue;
                           break;
			case STR:      $is_obj ? $input->$varname = strval($varvalue)
                                   : $input[$varname] = strval($varvalue);
                           break;
            case BRSTR:    $is_obj ? $input->$varname = nl2br(strval($varvalue))
                                   : $input[$varname] = nl2br(strval($varvalue));
                           break;
			case NOHTML:   $is_obj ? $input->$varname = htmlspecialchars(trim(strval($varvalue)))
                                   : $input[$varname] = htmlspecialchars(trim(strval($varvalue)));
                           break;
			case BOOL:     $is_obj ? $input->$varname = intval(!empty($varvalue))
                                   : $input[$varname] = intval(!empty($varvalue));
                           break;
			case ARR:      $is_obj ? $input->$varname = (is_array($varvalue)) ? $varvalue : array()
                                   : $input[$varname] = (is_array($varvalue)) ? $varvalue : array();
                           break;
            case XSS:      $is_obj ? $input->$varname = preg_replace(array('#javascript#i', '#vbscript#i'),array('java script',   'vb script',$varvalue))
                                   : $input[$varname] = preg_replace(array('#javascript#i', '#vbscript#i'),array('java script',   'vb script',$varvalue));
            case CNT:      $is_obj ? $input->$varname = $varfunc
                                   : $input[$varname] = $varfunc;
                           break;
            case DATE:     $is_obj ? $input->$varname = ($varvalue) ? strtotime(($varvalue)) : time()
                                   : $input[$varname] = ($varvalue) ? strtotime(($varvalue)) : time();
                           break;
            case CSM:
                           if(!function_exists($varfunc)) break;
                           $is_obj ? $input->$varname = $varfunc($input->$varname)
                                   : $input[$varname] = $varfunc($input[$varname]);
                           break;
            case ENUM:     $is_obj ? $input->$varname = in_array($input->$varname,$varfunc) ? $input->$varname : current($varfunc)
                                   : $input[$varname] = in_array($input[$varname],$varfunc) ? $input[$varname] : current($varfunc);
                           break;
        }
    }
}
/**
* clean single input data
* @ $var is a array or object
* @ $vartype is a var type to convert
* @ $varfunc is a custom function to apply the $var
*/
function clean_var(&$var,$vartype=INT,$varfunc=null)
{
    if(is_array($input)||is_object($input)) return '';
    $varvalue = $var;
    switch ($vartype)
 	{
        case INT:   $var = intval($varvalue);   break;
		case UINT:  $var = ($varvalue = intval($varvalue)) < 0 ? 0 : $varvalue;  break;
        case NUM:   $var = strval($varvalue) + 0;  break;
		case UNUM:  $var = ($varvalue = (strval($varvalue)) < 0) ? 0 : $varvalue; break;
		case STR:   $var = strval($varvalue); break;
        case BRSTR: $var = nl2br(strval($varvalue)); break;
        case ESC:   $var = mysql_escape_string($varvalue); break;
		case NOHTML:$var = htmlspecialchars(trim(strval($varvalue))); break;
		case BOOL:  $var = !empty($varvalue); break;
        case XSS:   $var = preg_replace(array('#javascript#i', '#vbscript#i'),array('java script',   'vb script',$varvalue));
        case CNT:   $var = $varfunc; break;
        case DATE:  $var = ($varvalue) ? strtotime(($varvalue)) : time(); break;
        case CSM:   if(!function_exists($varfunc)) break;
                    $var = $varfunc($var); break;
        case ENUM:  $var = in_array($var,$varfunc) ? $var : current($varfunc); break;
    }
}
function grabRemoteContent($url)
{
    $content = '';
    if(function_exists('curl_init'))
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        $content = curl_exec($ch);
        curl_close($ch);
    }
    elseif(function_exists('fsockopen'))
    {
        require_once(ROOT.'/includes/http.php');
        $http = new http_class();
        $http->follow_redirect=0;

        $error=$http->GetRequestArguments($url,$arguments);
        $arguments["Headers"]["Pragma"]="nocache";
        $arguments["Referer"]=$url;
        $error=$http->Open($arguments);
        if($error=="")
        {
           $error=$http->SendRequest($arguments);
           if($error=="")
           {
               $headers=array();
               $error=$http->ReadReplyHeaders($headers);
               if($error=="")
               {
                   $red_location = $headers['location'];
                   for(;;)
  		           {
                       $error=$http->ReadReplyBody($body,4096);
                       if(strlen($body)==0) break;
                       $content .= $body;
		           }
               }
    	    }
	    }
	    $http->Close();
        if(!empty($error))
        {
            $content = $error;
        }
    }
    elseif(ini_get('allow_url_fopen'))
    {
        $content = file_get_contents($url);
    }
    else
    {
        $content = ('could not get contents from $url');
    }
    return $content;
}
/**
* debug function
*/
if(!function_exists('trace'))
{
function trace()
{
    for ($i=0; $i<func_num_args(); $i=$i+2)
    {
        $title = func_get_arg($i);
        $vars  = func_get_arg($i+1);
        trace_echo($title, $vars);
    }
}
}
if(!function_exists('trace_echo'))
{
function trace_echo($title,$vars,$direct=1)
{
    echo "<pre><b>$title</b>:\r\r".(!$direct?'<textarea cols=60>':'');
	print_r($vars);
	echo (!$direct?'</textarea>':'')."\r\r</pre>";
}
}
function startTimer() {
global $starttime;
    $mtime = microtime ();
    $mtime = explode (' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;
}
function endTimer() {
global $starttime;
    $mtime = microtime ();
    $mtime = explode (' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = round (($endtime - $starttime), 5);
    return $totaltime;
}
/**
* redirect to a new page at front end
* @ $url is the url to redirect
*/
function do_redirect($url,$printinfo,$type='standby')
{
global $baseUrl,$db,$baseWeb,$langWeb,$user,$LANG,$input,$template,$otherPage;

    $redirect_url = strtolower(substr($url,0,7))=='http://' ? "$url" : "$baseWeb/$baseUrl$url";
	
	$otherPage=1;
	$template->assign_var('otherpage',$otherPage);
    
    require_once("header.php");

    $template->assign_vars(array(
        'redirect_url'=>$redirect_url,
        'error_text'  =>$printinfo,
        'PAGETILE'    =>$LANG[SITEERRORS],
    ));
    $template->set_filenames(array(
    	'body' => 'siteerrors.html')
    	);
    $template->pparse('body');
    require_once("footer.php");
    @$db->close_db();
    exit();
}
/* Added April 12 2008 */
function check_email_address($email) {
  // First, we check that there's one @ symbol, 
  // and that the lengths are right.
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters 
    // in one section or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if
(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
$local_array[$i])) {
      return false;
    }
  }
  // Check if domain is IP. If not, 
  // it should be valid domain name
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if
(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$",
$domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}
/**
* redirect to a new page in admin panel
* @ $url is the url to redirect
*/
function redirect($url,$html)
{
global $baseUrl,$baseWeb,$input;

    if(strtolower(substr($url,0,7))=='http://')
    $redirect_url="$url";
    else
    $redirect_url=$baseWeb.(defined('IN_ADMIN')?'/admin':'').'/'.$baseUrl.$url;

    echo <<<EOF
<style>
TABLE, TR, TD                   { font-family: Verdana,Arial; font-size: 12px;  }
BODY                            { font: 10px Verdana; background-color: #FCFCFC; padding: 0; margin: 0 }
.tablewrap {background-color:#EEF2F7;
            border-bottom:1px solid #D1DCEB;
            border-right:1px solid #D1DCEB ;
		    border-top:1px solid #FFF;
			border-left:1px solid #FFF; }
</style>
<br><br>
<script>
window.setTimeout("location.href='$redirect_url';",100);
</script>
<table width=70% align=center class=tablewrap><tr><td>

          $html<br /><br />
		  Please wait while we transfer you...<br /><br />
	      (<a href='$redirect_url'>Or click here if you do not wish to wait</a>)
</td></tr></table>
EOF;
    exit();
}
?>
