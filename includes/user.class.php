<?php
$DATASTORE       = array();
class user {
	/** basic infomation */
    var $uid         = 0;
    var $last_click  = 0;
    var $running_type= 'cookie';
    var $sess_id     = null;
    var $sess_timeout= 3600;
    var $username    = 'Guest';
    var $email       = '';
    var $reg_date    = 0;
    var $last_login  = 0;
    var $logined     = 0;
    var $ip     = "0.0.0.0";
	var $revenue_program = 0;
	var $dl_direct = 0;
	

    /**system setting*/
    var $setting=array();

    /**host package infomation*/
    var $package             = '';
    var $package_id          = 0;
    var $account_expired     = false;
    var $account_expire_date = 0;
    var $server_id           = 0;
    /**1:Active,0:Unconfirmed,-1:Suspended*/
    var $account_status      = 0;

    /**limition on user! */
    var $allowed_filetypes   = array();
    var $disabled_filetypes  = array();
    var $allowed_filesize    = 0;
    var $max_uploads         = 1;
    var $webspace            = 0;
    var $hosted_files        = 0;
    
    /**download options*/
    var $dl_resume       = 0;
    var $dl_speed        = 100;
    var $dl_threads      = 0;
    var $dl_password     = 0;
    var $dl_waittime     = 30;
    var $dl_timeout      = 0;
    var $dl_captcha      = 1;

    /**what user can do!*/
    var $upload_method   = array('can_formupload','can_ftpupload','can_urlupload','can_flashupload');
    var $can_formupload  = 1;
    var $can_urlupload   = 1;
    var $can_ftpupload   = 1;
    var $can_flashupload = 1;

    /**upload url*/
    var $php_upload_url  = '';
    var $cgi_upload_url  = '';
    var $swf_upload_url  = '';
    var $upload_mode     = 0;
    var $servers         = array();
    var $upload_servers  = array();

    /**package list preload*/
    var $groups          = array();
    var $guest_group     = array();
    var $USER            = array();
  	/**
	* user object constructor
	*/
	function user($type='cookie')
    {
    global $db;
        $this->running_type=$type;
        #no valid sess_id stored,create new one for visitor
        if(strlen($this->sess_id = $this->getValue('sess_id'))!=32)
        {
            $this->newSession();
        }

        #load site setting
        $this->setting=$this->loadSetting();

        #checking the banned ips
        if($this->isBanedByIP())
        {
            die("baned by ip!");
        }

        #if logged
        if($this->getValue('logined')==1)
        {
            $nowtime=time();
            $timediff=$nowtime-$this->getValue('last_click');

            $this->uid=intval($this->getValue('uid'));
            if(($timediff>$this->sess_timeout||!$this->uid)&&$this->getValue('autologin')==0)
            {
                $this->uid     = 0;
                $this->logined = 0;
            }
            else
            {
                # last update or visit
                if($timediff>$this->sess_timeout)
                {
                    $db->setQuery("update users set last_login='$nowtime', login_ip='{$_SERVER['REMOTE_ADDR']}' where id='$this->uid' limit 1");
                    $db->query();
                }
                # check against the user table to confirm user exists
                if($this->uid)
                {
                    $db->setQuery("select * from users where id='$this->uid' limit 1");
                    $db->query();
                    $this->USER=$db->loadRow();
                }
                if(is_array($this->USER))
                {
                    $checked=1;
                    # is auto login?
                    if($this->getValue('autologin')==1)
                    {
                        # pass hash doesn't match!
                        if(md5($this->USER[pass])!=$this->getValue('passhash'))
                        {
                            $checked=0;
                            $this->uid       = 0;
                            $this->logined   = 0;
                            $this->autologin = 0;
                        }
                        else
                        {
                            $this->autologin = 1;
                        }
                    }
                    else
                    {
                        $this->autologin = 0;
                    }
                   
                    if($checked)
                    {
                        $this->logined   = 1;
                        $this->setValue('passhash',md5($this->USER[pass]));
                    }
                }
                else
                {
                    $this->uid     = 0;
                    $this->logined = 0;
                }
            }
        }
        else
        {
            $this->uid     = 0;
            $this->logined = 0;
        }
        # set value
        $this->setValue('logined',$this->logined);
        $this->setValue('uid',$this->uid);
        $this->setValue('last_click',time());
	}
    /**
	* create a new session id
    * @return void
	*/
    function newSession()
    {
        $seed = md5(microtime());
        $this->setValue('sess_id',$seed);
        $this->sess_id = $seed;
    }
    /**
	* init user:guest or member
    * @return void
	*/
	function initiate()
    {
        global $db,$input,$template,$baseWeb,$LANG,$DATASTORE,$download_pages;
        if(!is_array($DATASTORE[groups]))
        {
           # grab all groups for future use
            $db->setQuery("select * from groups");
            $db->query();
            $this->groups=$groups=$db->loadRowList('id');
           
            # store data
            $DATASTORE[groups]  = $groups;
        }
        else
        {
            $this->groups=$groups=$DATASTORE[groups];
        }
        
        # expired package for expired users
        $this->expired_group=$groups[$this->setting[expired_package]];
        
        # initiate a group for guest use
        $this->guest_group=$groups[1];
        foreach($groups as $row)
        {
            if($row[guest]==1) {$this->guest_group=$row;break;}
        }

        # logined?
        if($this->uid)
        {
            $USER=$this->USER;
            $old_group=$groups[$USER['gid']];
            $account_expired = $USER[expire_date]>time()||$old_group[subscr_fee]==''?false:true;
            $group = $account_expired ? $this->expired_group : $old_group;
            # custom group?overide the normal group
            $custom_group = $USER[custom]=='yes' ?unserialize($USER[data]):$group;
            if(is_array($custom_group))$group = array_merge($group,$custom_group);
        }
        else
        {
            $group=$this->guest_group;
            $USER=$this->USER=array('user'=>'Guest','email'=>'','regdate'=>time());
        }
        # format user data
        $this->username             = $USER[user];
        $this->email                = $USER[email];
        $this->reg_date             = $USER[regdate];
        $this->last_login           = $USER[last_login];
        
        
        $this->total_downloads      = $USER[totaldownloads];
        $this->total_points         = $USER[totalpoints];
		$this->revenue_program		= $USER[revenue_program];
		
		
		$this->login_ip        		= $USER[login_ip];
		
		$this->files				= $USER[files];
		$this->folders				= $USER[folders];
		$this->webspace				= $USER[webspace];
		$this->hosted_files         = $USER[hosted_files];
		$this->deleted_files 		= $USER[deleted_files];
		$this->deleted_space		= $USER[deleted_space];
		$this->hosted_files_stream 	= $USER[hosted_files_stream];
		$this->last_update          = $USER[last_update];
		
		$this->dl_direct		= $USER[dl_direct];
		
		
		
       
        # upload setting
        $this->allowed_filetypes    = $group[allowed_filetype]?split(',',$group[allowed_filetype]):array();
        $this->disabled_filetypes   = $group[disabled_filetype]?split(',',$group[disabled_filetype]):array();
        $this->allowed_filesize     = $group[sizelimit];
        $this->max_uploads          = $group[max_uploads];

        $this->validate_type        = $group[validate_type];
       
        # upload method setting
        $this->can_formupload       = $group[formupload];
        $this->can_urlupload        = $group[urlupload];
        $this->can_ftpupload        = $group[ftpupload];
        $this->can_flashupload      = $group[flashupload];
       
        # download setting
        $this->dl_resume            = $group[dl_resume];
        $this->dl_speed             = $group[dl_speed];
        
        $this->dl_threads           = $group[dl_threads];
        $this->dl_password          = $group[dl_password];
        $this->dl_waittime          = $group[dl_waittime];
        $this->dl_timeout           = $group[dl_timeout];
        $this->dl_captcha           = $group[dl_captcha];

        # cronjob setting
        $this->cron_enabled         = $group[cron_enabled];
        $this->cron_days            = $group[cron_days];
        $this->cron_views           = $group[cron_views];
       
        # ad setting
        $this->show_site_ads        = $group[show_site_ads];
        $this->show_sponser_ads     = $group[show_sponser_ads];
       
        $this->can_create_folder    = $group[folder];

        if(IN_MAIN_SERVER)
        {
            $template->assign_var('shakeTime',$this->setting[shaketime]);
            $template->assign_var('startShow',$this->setting[startshow]);
            $template->assign_var('AdTop',$this->setting[adtop]);
            $template->assign_var('AdLeft',$this->setting[adleft]);
            $template->assign_var('slideStep',$this->setting[slidestep]);
            $template->assign_var('slideInternal',$this->setting[slideinternal]);

            $template->assign_var('show_site_ads',$this->show_site_ads);
            $template->assign_var('show_sponser_ads',$this->show_sponser_ads);
        }
        
        # host package
        $this->package              = $old_group[name];
        $this->is_custom            = $USER[custom]=='yes';
        $this->package_id           = $group[id];
        $this->package_server_id    = $group[server_id];

        # include language
        if(IN_MAIN_SERVER)
        {
        	if (@$download_pages) {
        		require_once(ROOT.'/language/'.$this->setting[language].'/dl.php');
        	}
        	else {
            	require_once(ROOT.'/language/'.$this->setting[language].'/lang.php');
        	}
            # assign lang vars into template
            if(is_array($LANG))foreach($LANG as $text=>$translation) $template->assign_vars(array('L_'.$text=>$translation));
            # define time format
            $LANG[Units] = array('D'=>$LANG[Days],'M'=>$LANG[Months],'Y'=>$LANG[Years]);
            define('DateFormat', $LANG[DateFormat]);
            define('DateFormat2',$LANG[DateFormat2]);
            define('TimeStamp',  $LANG[TimeStamp]);
        }
       
        # account status
        $this->account_status       = $USER[status];
        $this->account_expired      = $account_expired;
        $this->account_expire_date  = $old_group[subscr_fee]=='' ? $LANG["NeverExpired"] : date(DateFormat,$USER[expire_date]);

        # select server based package,note a valid package id is required
        if($this->setting[server]==-2)
        {
            if($this->package_server_id) $this->server_id = $this->package_server_id;
            else $this->setting[server]=0;
        }
        # specify server by admin
        if($this->setting[server]>0)
        {
            $this->server_id = $this->setting[server];
        }
        # the last one is random server

        # below code moved function showUploadForm()
        # get upload server details
        # $this->getServer();
	}
    /**
    *get upload method:form upload,ftp upload,url upload
    */
    function getServer()
    {
    global $db,$input,$uploadURL;
        # check available servers
        if($this->setting[site_offline] == 1) return ;
        $this->checkServer();
        if($this->setting[site_offline] == 1) return ;
    
        $server = array();
        # sepcify by admin or get it at package based
        if($this->setting[server]>0 || $this->setting[server]==-2)
        {
            if(!is_array($this->upload_servers[$this->server_id]))
            {
                #switch to random selection
                $this->setting[server] = 0;
            }
            else
            {
                $server=$this->upload_servers[$this->server_id];
            }
        }
        #select a server randonly to upload
        if($this->setting[server]==0)
        {

            $server = $this->upload_servers[array_rand($this->upload_servers)];

        }
        #build a server list to select by uploader!
        elseif($this->setting[server]==-1)
        {

            $server = current($this->upload_server);
            $this->server_id = $server[server_id];
        }

        #$this->cgi_upload_url   = $server[http].$server[cgiurl].'/upload.cgi';
        /**
        $this->cgi_upload_url   = $uploadURL."/cgi-bin/upload.cgi";
       	$this->php_upload_url   = $uploadURL."/uploading.php";
       	$this->php_progress_url = $uploadURL."/progress.php";
       	*/
        if ($this->server_id >= 1 && $this->server_id <= 99) {
	        $this->cgi_upload_url   = "http://184.22.170.93/cgi-bin/upload.cgi";
	        $this->php_upload_url   = "http://184.22.170.93/uploading.php";
	        $this->php_progress_url = "http://184.22.170.93/progress.php";
        }
        else {
            $this->cgi_upload_url   = "http://184.22.170.93/cgi-bin/upload.cgi";
            $this->php_upload_url   = "http://184.22.170.93/uploading.php";
            $this->php_progress_url = "http://184.22.170.93/progress.php";
        }
        
        if ($this->uid == 6) {
	        $this->cgi_upload_url   = "http://184.22.170.93/cgi-bin/upload.cgi";
	        $this->php_upload_url   = "http://184.22.170.93/uploading.php";
	        $this->php_progress_url = "http://184.22.170.93/progress.php";
        }

        

        return $server;
    }
    /**
    *get upload method:form upload,ftp upload,url upload
    */
    function checkServer()
    {
    global $db,$input;

        if(count($this->servers)==0)
        {
            $this->sendNotify(0,'fatal');
            $this->setting[site_offline] = 1;
        }
        else
        foreach($this->servers as $server)
        {
            if($server[enabled]&&$server[max_webspace]&&$server[webspace]>$server[max_webspace])
            {
                $this->sendNotify(0,'fatal');
                $SET[site_offline] = 1;
                continue;
            }
            if($row[warn_webspace]&&$row[webspace]>$row[warn_webspace])
            {
                $this->sendNotify($row[server_id],'warn');
            }
            if($server[enabled]) $this->upload_servers[$server[server_id]]=$server;
        }
        
        if(count($this->upload_servers)==0)
        {
            $this->sendNotify(0,'fatal');
            $this->setting[site_offline] = 1;
        }
    }
    
    function sendNotify($server_id=0,$type='warn')
    {
    global $db,$email;
        # load the corssponding template
        $email->template->set_filenames(array(
	         'email' => $type=='warn' ? 'server_warning.html':'server_out.html'
        ));
        # assign global vars
        $email->template->assign_vars(array(
            'SITENAME'=>$this->setting[sitename],
            'PASSWORD'=>$result[pass],
            'EMAIL_SIG'=>$user->setting[emailsign],
        ));
        # build server list
        foreach($this->servers as $row)
        {
            $email->template->assign_block_vars('servers', array(
            'name'=>$row[name],
            'server_id'=>$row[server_id],
            'domain'=>$row[domain],
            'webspace'=>convertsize($row[webspace]),
            'warn_webspace'=>convertsize($row[warn_webspace]),
            'max_webspace'=>convertsize($row[max_webspace]),
            'enabled'=>$row[enabled]==1?'Yes':'No',
            ));
            
            if($server_id == $row[server_id])
            {
                $email->template->assign_var('server_name',$row['name']);
            }
        }
        # get emails list to send
        $emails   = split(',',$this->setting[servermoniter_emails]);
        $emails[] = $this->setting[adminemail];

        # sending emails
        foreach($emails as $name)
        {
            $email->to($name,$this->setting[sitename]);
        }
        $email->from($this->setting[adminemail]);
        $email->send('email');
    }
    /**
	* check this user is baned by IP
    * @return bool
	*/
	function isBanedByIP()
    {
        global $input;
        $this->setting[banip]=str_replace('*','[0-9]{1}',$this->setting[banip]);
        $banip=split(',',$this->setting[banip]);
        foreach($banip as $ip)
        {
           $ip=trim($ip);
           if($ip=='') continue;
           $ban=preg_match("'$ip'isx",$input[IP_CLIENT]);

           if($ban) return true;
        }
        return false;
	}

    /**
	* update stats for user:uploaded files,used spaces,
	*/
	function updateStats($uid=0) {
    global $db,$input;
        $uid = intval($uid);
        if($uid==0) $uid=$this->uid;
        if($uid==0) return '';
        
        $db->setQuery("select count(f.id) as nums,sum(f.size) as webspace from files as f where f.uid ='$uid' and f.deleted=0");
        $db->query();
        $row=$db->loadRow();
        $db->setQuery("update users set
                       webspace='$row[webspace]',
                       files='$row[nums]',
                       last_login ='".time()."',
                       login_ip='{$_SERVER['REMOTE_ADDR']}'
                       where id='$uid' ");
        $db->query();
        
        return '';

        /*
        $db->setQuery("select sum(f.downloads) as downloads,count(f.id) as nums,sum(f.size) as webspace,sum(f.bandwidth) as bandwidth from files as f where f.uid ='$uid' and f.deleted=0");
        $db->query();
        $row=$db->loadRow();
        $db->setQuery("update users set last_login='".time()."',
                       webspace='$row[webspace]',
                       bandwidth='$row[bandwidth]',
                       totaldownloads='$row[downloads]',
                       files='$row[nums]',
                       login_ip='{$_SERVER['REMOTE_ADDR']}'
                       where id='$uid' ");
        $db->query();
        */
	}

    /**
	* load setting from system
    * @return bool
	*/
	function loadSetting()
    {
        global $db,$input,$match_lang,$root_dir,$DATASTORE,$CODE_TO_LANG;

        ## load site setting!
        if(!is_array($DATASTORE[setting]))
        {
            # loading setting table
            $db->setQuery("select * from setting where set_id=1");
            $db->query();
            $SET=$db->loadRow();
        
            # store data
            $DATASTORE[setting]  = $SET;
        }
        else
        {
            $SET=$DATASTORE[setting];
        }
        
        ## load all servers!
        if(!is_array($DATASTORE[servers]))
        {
            # loading server table
            $db->setQuery("SELECT * FROM server");
            $db->query();
            $this->servers=$db->loadRowList('server_id');
            # store data
            $DATASTORE[servers]  = $this->servers;
        }
        else
        {
            $this->servers=$DATASTORE[servers];
        }
       
        ## choose a skin
        $oldskin=$this->getValue('myskin');
        # request from url
        if(isset($input[setskin])&&eregi("[a-z0-9]+",$input[setskin])&&is_dir(ROOT.'/skin/'.$input[setskin]))
        {
            if($this->setValue('myskin',$input[setskin]));
            $SET['skin_dir']=$input[setskin];
        }
        # old skin is valid?
        elseif(eregi("[a-z0-9]+",$oldskin)&&is_dir(ROOT.'/skin/'.$oldskin))
        {
            $SET['skin_dir']=$oldskin;
        }
        ## choose a language
        $oldlang=$this->getValue('mylang');
        if(in_array($input[setlang],array_keys($CODE_TO_LANG))&&is_dir(ROOT.'/language/'.$CODE_TO_LANG[$input[setlang]]))
        {
            $SET[language] = $CODE_TO_LANG[$input[setlang]];
            # record the selected lang
            $this->setValue('mylang',$input[setlang]);
        }
        elseif(in_array($oldlang,array_keys($CODE_TO_LANG))&&is_dir(ROOT.'/language/'.$CODE_TO_LANG[$oldlang]))
        {
            $SET[language] = $CODE_TO_LANG[$oldlang];
        }
        
        ## choose by browser
        if($SET[language]==-1)
        {
            $SET[language] = $this->guess_lang();
            # record the selected lang
            $this->setValue('mylang',array_search($SET[language],$CODE_TO_LANG));
        }

        # if the language is not available, exit ?
        if(!file_exists(ROOT.'/language/'.$SET[language].'/lang.php'))
        {
            $SET[language]='english';
            $this->setValue('mylang','en');
        }
        $this->langcode = array_search($SET[language],$CODE_TO_LANG);
        return $SET;
	}
    function guess_lang()
	{
	global $HTTP_SERVER_VARS,$COUNTRY_CODE_TO_MATCH_LANG;

		// The order here _is_ important, at least for major_minor
		// matches. Don't go moving these around without checking with
		// me first - psoTFX

		if (isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']))
		{
			$accept_lang_ary = explode(',', $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']);
			for ($i = 0; $i < sizeof($accept_lang_ary); $i++)
			{
				@reset($COUNTRY_CODE_TO_MATCH_LANG);
				while (list($lang, $match) = each($COUNTRY_CODE_TO_MATCH_LANG))
				{
					if (preg_match('#' . $match . '#i', trim($accept_lang_ary[$i])))
					{
						if (is_file('language/' . $lang . '/lang.php'))
						{
							return $lang;
						}
					}
				}
			}
		}
		return 'english';
    }
    function guess_lang2()
	{
	global $HTTP_SERVER_VARS,$match_lang;

		// The order here _is_ important, at least for major_minor
		// matches. Don't go moving these around without checking with
		// me first - psoTFX

		if (isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']))
		{
			$accept_lang_ary = explode(',', $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']);
			for ($i = 0; $i < sizeof($accept_lang_ary); $i++)
			{
				@reset($match_lang);
				while (list($lang, $match) = each($match_lang))
				{
					if (preg_match('#' . $match . '#i', trim($accept_lang_ary[$i])))
					{
						if (is_file('language/' . $lang . '/lang.php'))
						{
							return $lang;
						}
					}
				}
			}
		}
		return 'english.php';
    }
    /**
	* set value for user class:cookie or session
	*/
	function setValue($name,$value)
    {
        $this->$name = $value;
        if($this->running_type=='cookie')
        {
            return setCookies($name,$value);
        }
        else
        {
            $_SESSION[$name]=$value;
        }
	}

    /**
	* get value for user class:cookie or session
	*/
	function getValue($name)
    {
        if($this->running_type=='cookie')
        return getCookies($name);
        else
        return $_SESSION[$name];
	}

    /**
	* user login method
    * @return status of user:1:active,0:unconfirmed,-1:suspended,4:not found
	*/
	function login($username,$password,$autologin=1)
    {
    global $db,$input;

       $db->setQuery("select * from users where user='".$db->getEscaped($username)."' and pass='".$db->getEscaped($password)."'");
       $db->query();
       if($db->getNumRows()==1)
       {
           $row=$db->loadRow();
           if($row[status]==1)
           {
               $this->setValue('logined',1);
               $this->setValue('uid',$row[id]);
               $this->setValue('passhash',md5($password));
               $this->setValue('last_click',time());
               $this->setValue('autologin',$autologin);
           }
           return $row[status];
       }
       else
       {
           $row=$db->loadRowList();
           return 4;
       }
	}
    /**
	* user login method
    * @return status of user:1:active,0:unconfirmed,-1:suspended,4:not found
	*/
	function logout()
    {
		global $db,$input;

         $this->setValue('logined',0);
         $this->setValue('uid',0);
         $this->setValue('passhash','');
         $this->setValue('last_click',time());
         $this->setValue('autologin',0);
         
         
         
	}
    /**
    *check this user with such email available
    */
    function checkAvailable($username,$useremail)
    {
    global $db;
        if($username==''||$useremail=='') return false;
        $db->setQuery("select id from users where user='{$username}' or email='{$useremail}' limit 1");
        $db->query();
        if($db->getNumRows())
        return false;
        else
        return true;
    }

    /**
    *check password for this user
    */
    function getPassword($username,$useremail)
    {
    global $db;
       if(!empty($username) and !empty($useremail))
       $db->setQuery("select * from users where user='$username' and email='$useremail'");
       else return -1;
       $db->query();
       if($db->getNumRows()==0)
       return -1;
       $row=$db->loadRow();
       return $row;
    }
}
?>
