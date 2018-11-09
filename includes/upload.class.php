<?php
/*
+--------------------------------------------------------------------------
|   Mega File Hosting Script v1.2
|   ========================================
|   by Stephen Yabziz
|   (c) 2005-2006 YABSoft Services
|   http://www.yabsoft.com
|   ========================================
|   Web: http://www.yabsoft.com
|   Email: ywyhnchina@163.com
+--------------------------------------------------------------------------
|
|   > Script written by Stephen Yabziz
|   > Date started: 1th March 2006
+--------------------------------------------------------------------------
*/
/**
* @package MFHS
* Uploader class
*/
class uploader {
	/** basic infomation */
    var $upload_session = null;
    var $access_code    = '';
    var $upload_mode    = 1;
    var $upload_method  = array('can_formupload','can_ftpupload','can_urlupload','can_flashupload');
    var $server_id      = 0;
    var $upload_dir     = 'files';
    var $temp_dir       = 'temp';
    var $upload_time    = 0;
    var $uploaded_num   = 0;
    var $uploaded_size  = 0;
    var $upload_start   = 0;
    var $uploaded_files = array();
    var $uploader_id    = 0;
    
    var $timeout        = 0;
    var $max_uploads    = 10;
    
    var $upload_errors  = null;
	var $upload_error_type = 0;
    
    /**upload url*/
    var $upload_url='';
    var $applet_upload_url='';
    
    var $extras      = array();

    var $sizelimit   = 104857600;
    var $typelimit   = array();
    var $typelimit2  = array();
	var $total_file = 0;
    
    var $size_func   = array(2=>'getUrlFileSize',3=>'getFTPFileSize');
    var $get_func    = array(1=>'getLocalFile',2=>'getUrlFile',3=>'getFTPFile',4=>'getLocalFile');
    
    var $progbar     = 'total';//or 'single'
    var $progtype    = 'ajax';//or 'syn'
	
	// [March-05-2008] split file variable
	var $chunk_size  = 1048576;
	
	/**
	* uploader object constructor
	*/
	function uploader($server_id=1,$type=0)
    {
    global $db, $DATASTORE;
        $this->server_id     = $server_id;
        
        # new added
        $this->upload_server = $DATASTORE[servers][$server_id];

        # used in ver 1.1
        if($type==1&&!$this->upload_server)
        {
            $db->setQuery("select * from server where server_id='$server_id'");
            $db->query();
            $server = $db->loadRow();
            # for flash upload use!
            $this->server_domain = $server[domain];
            
            #
            $this->upload_server = $server;
        }
        
        #
        $this->upload_dir = $this->upload_server[upload_dir];
        $this->keep_ext   = $this->upload_server[keepext]=='yes';
	}
    function setUploadMode($upload_mode)
    {
        $this->upload_mode = $upload_mode;

        if($upload_mode == 1) $this->progtype    = $this->upload_server[cgi_prog_mode];
        if($upload_mode == 2) $this->progtype    = $this->upload_server[url_prog_mode];
        if($upload_mode == 3) $this->progtype    = $this->upload_server[ftp_prog_mode];

        if($upload_mode == 1)
        {
            $this->temp_dir = $this->upload_server[cgi_temp_dir];
        }
        else
        {
            $this->temp_dir = $this->upload_server[php_temp_dir];
        }
    }
    function create_session($user_id=0)
    {
        $session = md5(microtime());
        $this->access_code    = base64_encode(encryptStr("$user_id",$session));
        $this->upload_session = $session;
	}
    function validate_session(&$user,$access_code,$upload_session)
    {
        $this->access_code    = $access_code;
        $this->upload_session = preg_replace('/[^a-z0-9]/','',$upload_session);

        $uid = decryptStr(base64_decode($this->access_code),$this->upload_session);

        if(is_numeric($uid))
        {
            $this->uploader_id = $uid;

            # assign var from script vitually!
            $_SESSION['uid']=$uid;
            $_SESSION['logined']=1;
            $_SESSION['last_click']=time();
            $user = new user('session');

            $user->initiate();
            
            $this->sizelimit   = $user->allowed_filesize;
            $this->typelimit   = $user->allowed_filetypes;
            $this->typelimit2  = $user->disabled_filetypes;
            $this->max_uploads = $user->max_uploads;
            
            return true;
        }

        $this->setError('invalid upload session!');
        return false;
	}
    function accept($files)
    {
    global $db,$user,$input;
        # empty the vars
        $this->uploaded_files = array();
        $this->uploaded_num   = 0;
        $this->upload_time    = time();
        
        # slice the files and keep the key unchanged
        $s = 0;$sliced_files=array();
        foreach($files as $k=>$v)
        {
            $s++;
            if($s>$this->max_uploads) break;
            $sliced_files[$k]=$v;;
        }
        $files = $sliced_files;
        
        if(count($files)==0) $this->setError('No upload files found!');
        
        # get total size for url upload and ftp upload
        if($this->upload_mode!=1&&$this->upload_mode!=4)
        {
            if($this->progbar=='total')
            {
                $this->upload_start = time();
                $files = $this->getTotalSize($files);
            }
            # syn progress
            if($this->progtype=='syn')
            {
                flush();
                # avoid cache in IE browser!
                echo str_repeat(' ',256);
            }
            # ajax progress
            if($this->progtype=='ajax'&&$this->progbar=='total')
            {
                $monitor_file = $this->upload_session.'_flength';
                $fp2=fopen($this->temp_dir.'/'.$monitor_file,'wb');
                fwrite($fp2,$this->total_size);
                fclose($fp2);
            }
        }
        flush();
		
		// validate URL/FTP upload
		if ($this->total_size >= $this->sizelimit) {
			$this->setError($file[errinfo]);
			$this->upload_error_type = 1;
		}
		else {
			//generate value
			$max_upload = 8; // File Concurrent
			// $max_bandwidth = 104857600; // 100MB
			$max_bandwidth = 1048576000; // 1000MB or 1G a time
			$expired_after = 30*60;
			
			$current_time = time();
			
			// get ip
			$upload_ip = $input[uploadmode]!=1?$input[IP_CLIENT]:$input[IP_CGI];
			$upload_ip = empty($input[IP_CGI])?$input[IP_CLIENT]:$input[IP_CGI];
			// check using ip limitation
			$db->setQuery("SELECT SUM(up_num) as up_num_total,SUM(up_size) as up_size_total FROM upsessions WHERE ip = '$upload_ip' and expired_time > $current_time");
			$db->query();
			
			$b_validate_http_ftp = 0;
			if ($db->getNumRows()) {
				$rows=$db->loadRow();

				// get total uploads and bandwidth used
				$temp_upload_num = $rows['up_num_total'] + $this->total_file;
				$temp_bandwidth = $rows['up_size_total'] + $this->total_size;
				
				// if not more then maximum then run upload
				if ($max_upload >= $temp_upload_num and $max_bandwidth >= $temp_bandwidth) {
					$b_validate_http_ftp = 1;
				}
				else {
					$b_validate_http_ftp = 0;
					$this->upload_error_type = 2;
				}
			}
			else {
				$b_validate_http_ftp = 1;
			}

			if ($b_validate_http_ftp) {

				// insert into table before upload to make sure concurrent upload
				$upsessiontable = new TABLE($db,'upsessions','up_id');
				$upsessiontable->ip = $upload_ip;
				$upsessiontable->start_time = $current_time;
				$upsessiontable->expired_time = $current_time + $expired_after;
				$upsessiontable->up_num = $this->total_file;
				$upsessiontable->up_size = $this->total_size;
				$upsessiontable->upload_type = $input[uploadmode];
				$upsessiontable->insert();
					
				# process every file
				foreach($files as $id=>$tmpfile)
				{
					# get html id to identify the description and password for form upload
					if($this->upload_mode==4) $id = 0;
		
					# assign start time
					if($this->progbar=='single') $this->upload_start = time();
		
					# get file
					$function = $this->get_func[$this->upload_mode];
					$file     = $this->$function($tmpfile);
		
					if($file[error]) {$this->setError($file[errinfo]);continue;}
					
					# [march 05 2008] v0.2 changelogs - split file after uploaded
					# move the temporay file to upload_dir
					/* ************************************************** */
					
					if ($mainFileHandle = @fopen($file[tmp_name],'rb')) {
						$i=0;
						while(!feof($mainFileHandle)) {
							$contents = fread($mainFileHandle,$this->chunk_size);
							if ($chunkFileHandle = @fopen(($this->upload_dir.'/'.$file[filename].sprintf('.%03d',$i++)),'wb')) {
								fwrite($chunkFileHandle, $contents);
								fclose($chunkFileHandle);
								unset($chunkFileHandle);
							}
							else {
								echo "error";
							}
						}
						fclose($mainFileHandle);
						unset($mainFileHandle);
					}
					else {
						$this->setError('copy error:Max Upload Size or Upload directory can not be writen in');
						continue;
					}
					
					/* ************************************************** */
					/* ************************************************** */
					
					# original code
					/*
					if(!copy($file[tmp_name], $this->upload_dir.'/'.$file[filename]))
					{
						$this->setError('copy error:Max Upload Size or Upload directory can not be writen in');
						continue;
					}
					*/

					

					# get extras information
					$this->temps=$extras   = array('descr'=>$this->extras['descr'][$id],'password'=>$this->extras['password'][$id]);
					
					// fixed base64 encoding problem for http and ftp upload
					if ($this->upload_mode!=1&&$this->upload_mode!=4) {
						$file['name'] = base64_encode($file['name']);
					}
					
					# record into db
					$fileobj  = $this->insertDB($file,$extras);
		
					# check insert...
					$insertid = $fileobj->id;
					if($insertid==0) break;
		
					#delete the temporay files
					@unlink($file[tmp_name]);
		
					#uploaded...
					$this->uploaded_files[$insertid] = $fileobj;
					$this->uploaded_num++;
					$this->uploaded_size=$this->uploaded_size+$file[size];
				}
			}
			else {
				$this->setError($file[errinfo]);
			}
		}
        
        # ajax progress
        if($this->upload_mode!=1 && $this->progtype=='ajax' && $this->progbar=='total')
        {
            $signal_file  = $this->upload_session.'_signal';
            $fp2=fopen($this->temp_dir.'/'.$signal_file,'wb');
            fclose($fp2);
        }

        # upload is finished,do some updates on user's stats and redirect...
        $this->uploadStats();
    }
    function getTotalSize($files)
    {
        $returns = array();
        $this->total_size = 0;
        # process every file
		$debug_file=fopen("debug/filesize.txt", "w");
		
		//$sum_filesize=0;
        foreach($files as $key=>$tmpfile)
        {
            if(strlen($tmpfile)==0) continue;

            # get file size
            $function = $this->size_func[$this->upload_mode];
            $file     = $this->$function($tmpfile);
			
			// debug
			//fwrite($debug_file,$file['size']."\n\r ");

            if($file[error]) {$this->setError($file[errinfo]);continue;}
            
            $returns[$key] = $tmpfile;
            $this->total_size=$this->total_size+$file[size];
			
			$this->total_file++;
        }
		
		// debug
		//fclose($debug_file);
		
		//if ($this->total_size >= $this->sizelimit) {}
		
        return $returns;
    }
    function uploadStats()
    {
    global $db;

         # update the users stats
         if($this->uploader_id!=0&&$this->uploaded_num)
         {
             $db->setQuery("update users set files=files+$this->uploaded_num where id='$this->uploader_id'");
             $db->query();
         }
         # update the server stats
         if($this->uploaded_num)
         {
             $db->setQuery("update server set hosted=hosted+$this->uploaded_num,webspace=webspace+$this->uploaded_size where server_id='$this->server_id'");
             $db->query();
         }
    }
    function getUploadIDs()
    {
    global $db;
        $db->setQuery("select id,name,size,descr,password,delete_id,upload_id from files where upload_session='$this->upload_session'");
        $db->query();
        return $db->loadRowList();
    }
    function checkFile(&$file)
    {
        $filesize = $file[size];
        $filetype = getExt($file[name]);
        if($filesize>$this->sizelimit
           ||($this->typelimit&&!in_array($filetype,$this->typelimit))
           ||($this->typelimit2&&in_array($filetype,$this->typelimit2)))
        {
            $file[error] = 1;
            $file['errinfo']="Size $filesize ($this->sizelimit)  or type $filetype error";
        }
        else
        {
            $file[error] = 0;
        }
        
        return !$file[error];
    }
    function getUrlFile($uri)
    {
        $filename=basename(strtok($uri,'?'));
        $filetype=getExt($filename);
        
        # system used file
        $data_file     = $this->upload_session.'_postdata';
        $progress_file = $this->upload_session.'_progress';
        $monitor_file  = $this->upload_session.'_flength';
        $signal_file   = $this->upload_session.'_signal';
        
        # construct the tmpfile
        $tmpfile=array('name'=>$filename);

        $http = new http_class();
        
        $http->timeout = $this->timeout;
        $http->data_timeout = $this->timeout;

        $error=$http->GetRequestArguments($uri,$arguments);
        $arguments["Headers"]["Pragma"]="nocache";
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
                    # get file size
                    foreach($headers as  $header_name => $header_value)
			        {
			    		if($header_name=='content-length')
			    		{
                            $filesize=$header_value;
                            $tmpfile['size']     = $filesize;

                            if(!$this->checkFile($tmpfile)) return $tmpfile;
                            break;
			    		}
			    	}

                    # set progress bar info
                    $dtstart = $this->upload_start;
                    $iTotal  = $this->progbar=='single' ? $filesize : $this->total_size;
                    $iRead   = $this->progbar=='single' ? 0 : $this->uploaded_size;

                    # record filesize into $flength_name
                    if($this->progtype=='ajax'&&$this->progbar=='single')
                    {
                        $fp2=fopen($this->temp_dir.'/'.$monitor_file,'wb');
                        fwrite($fp2,$iTotal);
                        fclose($fp2);
                    }
                    
                    # create temporary file
                    $fp=fopen($this->temp_dir.'/'.$data_file,'wb');
                    $tmpfile['tmp_name'] = $this->temp_dir.'/'.$data_file;
                    # and downloading file
                    for($i=0;;)
  		            {
                        # read response body
                        $error=$http->ReadReplyBody($body,4096);
                        if($error!="")
                        {
                            $http->Close();
                            fclose($fp);
                            $tmpfile['error']    = 1;
                            $tmpfile['errinfo']  = 'Remote file error';
                            return $tmpfile;
                        }
                        
                        # read size
                        $readlength=strlen($body);
                        $iRead +=$readlength;
                        
                        # progress bar
                        # if($this->progtype=='syn'&&$i%100==0) sleep(1);
                        if($this->progtype=='syn'&&$readlength==0)
                        {
                            //showProgStatus($iTotal,$iRead,$dtstart,&$dtRemainingf,&$dtelapsedf,&$bSpeedf,&$percent);
                            showStatus($iTotal,$iRead,$dtstart);
                            break;
                        }
                        elseif($i%5==0)
                        {
                            $this->writeContents($this->temp_dir.'/'.$progress_file, $iRead);
                        }
                        
                        if($readlength==0) break;

                        # write response contents to temporary file
                        fwrite($fp,$body);

                        # progress bar
                        if($this->progtype=='syn'&&$i%5==0)
                        {
                            showStatus($iTotal,$iRead,$dtstart);
                        }
                        
                        # record loops
                        $i++;
		            }
                    fclose($fp);

                    # create signal file
                    if($this->progtype=='ajax'&&$this->progbar=='single')
                    {
                        $fp2=fopen($this->temp_dir.'/'.$signal_file,'wb');
                        fclose($fp2);
                    }

                    $tmpfile['error']=0;
                }
                else
                {
                    $tmpfile['error']=1;
                }
	      	}
            else
            {
                $tmpfile['error']=1;
            }
        }
        else
        {
            $tmpfile['error']=1;
        }
	    $http->Close();

        if($tmpfile['error']==0) $tmpfile['filename'] = $this->tempname($filetype);

        return $tmpfile;
    }
    function getUrlFileSize($uri)
    {
        $filename=basename($uri);
        $filetype=getExt($filename);

        # construct the tmpfile
        $tmpfile=array('name'=>$filename);

        $http = new http_class();

        $error=$http->GetRequestArguments($uri,$arguments);
        $arguments["Headers"]["Pragma"]="nocache";
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
                    #get file size
                    foreach($headers as  $header_name => $header_value)
			        {
			    		if($header_name=='content-length')
			    		{
                            $filesize=$header_value;
                            $tmpfile['size']     = $filesize;

                            if(!$this->checkFile($tmpfile)) return $tmpfile;
                            break;
			    		}
			    	}
                }
                else
                {
                    $tmpfile['error']=1;
                }
	      	}
            else
            {
                $tmpfile['error']=1;
            }
        }
        else
        {
            $tmpfile['error']=1;
        }
	    $http->Close();
        if($tmpfile['error']==1&&$error) $tmpfile['errinfo']=$error;
        return $tmpfile;
    }
    function getFTPFile($uri)
    {
        # system used file
        $data_file     = $this->upload_session.'_postdata';
        $progress_file = $this->upload_session.'_progress';
        $monitor_file  = $this->upload_session.'_flength';
        $signal_file   = $this->upload_session.'_signal';
        
        $ftp = new ftp();
        $ftp->debug = TRUE;

        # parse ftp url
        $parts=parse_url($uri);
        $ftp_host=$parts[host];
        $ftp_port=$parts[port]?$parts[port]:'21';
        $ftp_user=$parts[user]?$parts[user]:'anonymous';
        $ftp_pass=$parts[pass]?$parts[pass]:'anonymous';
        $remote_filename=$parts[path];

        $filename=basename(strtok($uri,'?'));
        $filetype=getExt($filename);

        # construct the tmpfile
        $tmpfile=array('name'=>$filename);
        
        # connect to ftp host
        if (!$ftp->ftp_connect($ftp_host,$ftp_port))
        {
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Cannot connect';
            return $tmpfile;
        }

        # login with username and password
        if (!$ftp->ftp_login($ftp_user, $ftp_pass)) {
    	    $ftp->ftp_quit();
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Login failed';
            return $tmpfile;
        }

        # get file size
        $filesize = $ftp->ftp_size($remote_filename);
        if($filesize == -1)
        {
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Could not get file size';
            return $tmpfile;
        }
        else
        {
            $tmpfile['size']=$filesize;
        }

        # check file by us
        if(!$this->checkFile($tmpfile)) return $tmpfile;

        # create temporary file
        # $stored_name=mytempname($this->temp_dir, '', $filetype);
        $tmpfile['tmp_name'] = $this->temp_dir.'/'.$data_file;
        $fp=fopen($tmpfile['tmp_name'],'wb');
        
        $iTotal  = $filesize;
        $iTotal  = $this->progbar=='single' ? $filesize : $this->total_size;

        # assign var to ftp calss
        $ftp->total_size    = $iTotal;
        $ftp->progtype      = $this->progtype;
        $ftp->progress_file = $this->temp_dir.'/'.$progress_file;
        
        # record filesize into $flength_name
        if($this->progtype=='ajax')
        {
            $fp2=fopen($this->temp_dir.'/'.$monitor_file,'wb');
            fwrite($fp2,$iTotal);
            fclose($fp2);
        }

        # downloading the file
        if (!$ftp->ftp_get($tmpfile['tmp_name'],$remote_filename))
        {
    	    $ftp->ftp_quit();
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Download error';

            return $tmpfile;
        }
        
        # create signal file
        if($this->progtype=='ajax'&&$this->progbar=='single')
        {
            $fp2=fopen($this->temp_dir.'/'.$signal_file,'wb');
            fclose($fp2);
        }
                    
        $ftp->ftp_quit();

        if($tmpfile['error']==0) $tmpfile['filename'] = $this->tempname($filetype);

        return $tmpfile;
    }
    function getFTPFileSize($uri)
    {
        $ftp = new ftp();
        $ftp->debug = TRUE;

        # parse ftp url
        $parts=parse_url($uri);
        $ftp_host=$parts[host];
        $ftp_port=$parts[port]?$parts[port]:'21';
        $ftp_user=$parts[user]?$parts[user]:'anonymous';
        $ftp_pass=$parts[pass]?$parts[pass]:'anonymous';
        $remote_filename=$parts[path];

        $filename=basename($uri);
        $filetype=getExt($filename);

        # construct the tmpfile
        $tmpfile=array('name'=>$filename);

        # connect to ftp host
        if (!$ftp->ftp_connect($ftp_host,$ftp_port))
        {
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Cannot connect';
            return $tmpfile;
        }

        # login with username and password
        if (!$ftp->ftp_login($ftp_user, $ftp_pass)) {
    	    $ftp->ftp_quit();
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Login failed';
            return $tmpfile;
        }

        # get file size
        $filesize = $ftp->ftp_size($remote_filename);
        if($filesize == -1)
        {
            $tmpfile['error']=1;
            $tmpfile['errinfo']='Could not get file size';
            return $tmpfile;
        }
        else
        {
            $tmpfile['size']=$filesize;
        }

        # check file by us
        //if(!$this->checkFile($tmpfile)) return $tmpfile;
        
        return $tmpfile;
    }
    function getLocalFile($file)
    {
        $filetype=getExt($file['name']);

        # check file by us
        $this->checkFile($file);
        $file['filename'] = $this->tempname($filetype);
        return $file;
    }
    function tempname($filetype)
    {
        # format: 7 chars name + server id + ext
        # return mytempname($this->upload_dir, '', $this->server_id) . (!$this->keep_ext?'_':'.') . $filetype;
        # format: 8 chars name + .server id + ext
        return mytempname($this->upload_dir) . '.' . $this->server_id . (!$this->keep_ext?'_':'.') . $filetype;
        # format: unqiue server id + 7 chars name +  ext
        return mytempname($this->upload_dir, $this->server_id) . (!$this->keep_ext?'_':'.') . $filetype;
        # format: server id + 7 chars name +  ext
        return mytempname($this->upload_dir, $this->server_id, (!$this->keep_ext?'_':'.').$filetype);
        # format: 8 chars name +  ext
        return mytempname($this->upload_dir, '', (!$this->keep_ext?'_':'.').$filetype);
    }
    function writeContents($filename, $contents)
    {
        $fwriteHandle = @fopen($filename, "w");
        if (!is_resource($fwriteHandle))
        {
            return false;
        }

        fwrite($fwriteHandle, $contents);
        fclose($fwriteHandle);

        return true;
    }
    /**
    *5:store infomation in db:make the uplaod time is unique by "fake" uploadtime
    */
    function InsertDB($file,$extras=array())
    {
    global $db,$user,$input;
        # get upload ip
        $upload_ip = $input[uploadmode]!=1?$input[IP_CLIENT]:$input[IP_CGI];
        $upload_ip = empty($input[IP_CGI])?$input[IP_CLIENT]:$input[IP_CGI];

        # generate unique upload id and delete id
        $upload_id = strtoupper($this->getUploadID($file[filename]));
        $delete_id = strtoupper(substr(md5(microtime()), 0, 4));

        # prepare data
        $filetable = new TABLE($db,'files','id');
        $filetable->time        = $this->upload_time;
        $filetable->server_id   = $this->server_id;
        $filetable->upload_session  = $this->upload_session;
        $filetable->uid         = $this->uploader_id;
        $filetable->password    = $user->dl_password==1?$extras[password]:'';
        $filetable->descr       = $extras[descr];
        $filetable->validate	= 0; // set default as validate
        $filetable->ip          = $upload_ip;
        $filetable->file        = $file[filename];
        $filetable->size        = $file[size];
        $filetable->name        = $file[name];
        
        $filetable->upload_id   = $upload_id;
        $filetable->delete_id   = $delete_id;
		$filetable->upload_type   = $this->upload_mode;
        
        # inserting
        $filetable->insert();

        # increase upload time to avoid repeat one!
        $this->upload_time++;

        $filetable->id = $filetable->insertid();
        return $filetable;
    }
    function getUploadID($filename)
    {
    	global $db;
    	
        // Create the upload id
	    do {
	    	if ($this->server_id < 10) { // 1 digit + 8 chars
	    		//$upload_id = substr($filename, 0, 7).$this->server_id;
	    		$upload_id = substr(md5(microtime().$this->base64_name.mt_rand(0,9999)), 0, 8).$this->server_id;
	    	}
	    	elseif ($this->server_id < 100) { // 2 digits + 7 chars
	    		$upload_id = substr(md5(microtime().$this->base64_name.mt_rand(0,9999)), 0, 7).$this->server_id;
	    	}
	    	else { // 3 digits + 6 chars
	    		$upload_id = substr(md5(microtime().$this->base64_name.mt_rand(0,9999)), 0, 6).$this->server_id;
	    	}
	    	
            $db->setQuery("select * from files where upload_id='$upload_id' limit 1");
            $db->query();
            
            $exists = $db->getNumRows();
	    } while ($exists);

	    return $upload_id;
    }
    function getUploadID2($filename)
    {
    global $db;
        return substr($filename,0,8).$this->server_id;
        // Create the upload id
	    do {
		    $upload_id = substr(md5(microtime()), 0, 8);
            $db->setQuery("select * from files where upload_id='$upload_id' limit 1");
            $db->query();
            $exists = $db->getNumRows();
	    } while ($exists);

	    return $upload_id;
    }
    function setError($err)
    {
        echo $err;
        $this->upload_errors .= $err."\n";
    }
}
?>
