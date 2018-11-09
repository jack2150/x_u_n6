<?
/*
* File hander class
* @package YABInstaller
* @author stephen yabziz
*
*/
class FileMan {
    var $dirnums=0;
    var $filenums=0;
    var $totalsize=0;
    var $files=array();
    var $_error='';
    var $_all_error='';

	var $sDir='.';
    var $dDir='';
    var $action='';
    var $ext='all';
    var $permsValue='';
    
    var $ftpmode=FTP_ASCII;
    var $debug=false;

	function FileMan() {

	}
    function SetSourceDir($dir='.')
    {
        if($dir==null||!file_exists($dir))
        {
            $this->SetError("Source directory [$dir] can't be found!");
            return false;
        }
        $this->sDir=$dir;
        return true;
    }
    function SetDestDir($dir='.')
    {
        //if($dir==null||!file_exists($dir)) die("Dest directory [$dir] can't be found!");
        $this->dDir=$dir;
    }
    function Compare($sDir,$dDir,$cDir=flase)
    {
        $new_main = $new_comp = $unchanged_files = $changed_files = $new_files = array();

        if(!$this->SetSourceDir($sDir)) return false;;
        $main=$this->View();
        foreach($main as $sfile)
        {
            if($sfile[name]!=$this->sDir)
            $new_main[substr($sfile[name],strlen($this->sDir)+1)]=$sfile[folder];
        }

        if(!$this->SetSourceDir($dDir)) return false;;
        $comp=$this->View();
        foreach($comp as $sfile)
        {
            if($sfile[name]!=$this->sDir)
            $new_comp[substr($sfile[name],strlen($this->sDir)+1)]=$sfile[folder];
        }
        $new_files      = array_diff(array_keys($new_main),array_keys($new_comp));

        foreach($new_main as $file => $dir)
        {
            if(in_array($file,$new_files)) continue;

            if($dir) {/*@mkdir($cDir.'/'.$file); */ continue;}

            $ok=md5_file($sDir.'/'.$file) == md5_file($dDir.'/'.$file);
            if($ok)
            {
                $unchanged_files[]=$sDir.'/'.$file;
                $results .= "<font color=blue>".$file."</font> unchanged\n";//.md5_file('install-1.0/' .$file).':'. md5_file('install-1.1/'.$file)."\n";
            }
            else
            {
                if($cDir)
                {
                    $this->preparecopy($cDir.'/'.$file);
                    if(!is_dir($sDir.'/'.$file))copy($sDir.'/'.$file,$cDir.'/'.$file);
                }
                $changed_files[]=$sDir.'/'.$file;
                $results .= "<font color=red>".$file."</font> changed\n";//.md5_file('install-1.0/' .$file).':'. md5_file('install-1.1/'.$file)."\n";
            }
        }
        if($cDir)
        foreach($new_files as $file)
        {
            if(is_dir($sDir.'/'.$file)) continue;
            $this->preparecopy($cDir.'/'.$file);
            if(!is_dir($sDir.'/'.$file))copy($sDir.'/'.$file,$cDir.'/'.$file);
        }
        return array('new'=>$new_files,'changed'=>$changed_files,'unchanged'=>$unchanged_files);

    }
    function preparecopy($dfile)
    {
        $dirs=split('/',$dfile);
        array_pop($dirs);

        $basedir='';
        foreach($dirs as $dir)
        {
           if(!is_dir($basedir.$dir)){mkdir($basedir.'/'.$dir);}
           $basedir .= $dir.'/';
        }
    }
    function SetError($error)
    {
        $this->_all_error.=$this->_error="<font color=red>".$error."</font><br>";
    }
    function GetLastError()
    {
        return  $this->_error;
    }
    function GetError()
    {
        return  $this->_all_error;
    }
    function debug($msg)
    {
        if($this->debug)
        {
           flush();
           echo $msg."<br>";
        }
    }
    function FTPGet($lfile,$rfile)
    {
        if(@ftp_get($this->ftpconn,$lfile,$rfile,$this->ftpAsciiBinary($sfile)))
        {
           $this->debug("ftp get file:".$rfile." to ".$lfile);
           return true;
        }
        else
        {
           $this->SetError("ftp get file:".$rfile." to ".$lfile);
           return false;
        }
    }
    function FTPPut($rfile,$lfile)
    {
        if(@ftp_put($this->ftpconn,$rfile,$lfile,$this->ftpAsciiBinary($sfile)))
        {
           $this->debug("ftp put file:".$lfile." to ".$rfile);
           return true;
        }
        else
        {
           $this->SetError("ftp put file:".$lfile." to ".$rfile);
           return false;
        }
    }
    function FTPDelete($file)
    {
        if(@ftp_delete($this->ftpconn,$file))
        {
           $this->debug("ftp delete file:".$file);
           return true;
        }
        else
        {
           $this->SetError("ftp delete file:".$file);
           return false;
        }
    }
    function FTPSite($cmd)
    {
        if(@ftp_site($this->ftpconn,$cmd))
        {
           $this->debug("ftp site commond:".$cmd);
           return true;
        }
        else
        {
           $this->SetError("ftp site commond:".$cmd);
           return false;
        }
    }
    function FTPMkDir($newdir)
    {
        if(@ftp_mkdir($this->ftpconn,$newdir))
        {
            $this->debug("ftp get file:".$sfile." to ".$dfile);
            return true;
        }
        else
        {
            $this->SetError("ftp get file:".$sfile." to ".$dfile);
            return false;
        }
    }
    function GetContent($getUrl)
    {
        $fp=@fopen($getUrl,'r');
        while ($t=@fread($fp,1024) ) {
        $content.=$t;
        }
        return $content;
    }
    /**

    */
    function View()
    {
        $this->action='view';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        return $this->_LocalProcessor();
    }
    /**

    */
    function GetFiles()
    {
        $this->action='getfiles';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        return $this->_LocalProcessor();
    }
    /**
    
    */
    function Copy()
    {
        $this->action='copy';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_LocalProcessor();
    }
    /**

    */
    function Copy2FTP()
    {
        $this->action='remoteupload';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_LocalProcessor();
    }
    /**

    */
    function DelFiles()
    {
        $this->action='delfile';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_LocalProcessor();
    }
    /**

    */
    function Delete()
    {
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='delfile';
        $this->_LocalProcessor();
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='deldir';
        $this->_LocalProcessor();
    }
    /**

    */
    function Rename()
    {
        if(!$this->newext)
        {
           $this->debug("New extenstion is not defined!");
           return 0;
        }
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='rename';
        $this->_LocalProcessor();
    }
    /**

    */
    function ChmodDir()
    {
        if(!$this->permsValue)
        {
           $this->debug("Perms value is not defined!");
           return 0;
        }
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='chmoddir';
        $this->_LocalProcessor();
    }
    /**

    */
    function ChmodFiles()
    {
        if(!$this->permsValue)
        {
           $this->debug("Perms value is not defined!");
           return 0;
        }
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='chmodfiles';
        $this->_LocalProcessor();
    }
    /**

    */
    function Chmod()
    {
        $this->action='chmod';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_LocalProcessor();
    }
    /**

    */
    function FTPView()
    {
        $this->action='view';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPGetFiles()
    {
        $this->action='getfiles';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        return $this->_FTPProcessor();
    }
    /**

    */
    function FTPRename()
    {
        if(!$this->newext)
        {
           $this->debug("New extenstion is not defined!");
           return 0;
        }
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='rename';
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPDownload()
    {
        $this->action='download';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPCopy2FTP()
    {
        $this->action='remoteupload';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_LocalProcessor();
    }
    /**

    */
    function FTPDelFiles()
    {
        $this->action='delfile';
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPDeletes()
    {
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='delfile';
        $this->_FTPProcessor();
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='deldir';
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPChmodDir()
    {
        if(!$this->permsValue)
        {
           $this->debug("Perms value is not defined!");
           return 0;
        }
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='chmoddir';
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPChmodFiles()
    {
        if(!$this->permsValue)
        {
           $this->debug("Perms value is not defined!");
           return 0;
        }
        $this->dirnums=$this->filenums=$this->totalsize=0;
        $this->action='chmodfiles';
        $this->_FTPProcessor();
    }
    /**

    */
    function FTPChmod()
    {
        if(!$this->permsValue)
        {
           $this->debug("Perms value is not defined!");
           return 0;
        }
        $this->action='chmod';
        $this->_FTPProcessor();
    }
    function echoBar($size,$files,$dirs)
    {
    global $totalsizes,$totalfiles,$totaldirs;
       flush();
       $sizeP=$size/$totalsizes;
       $sizeP=sprintf("%01.2f",$sizeP);
       $sizeP=100*$sizeP;
       if($sizeP<=99)
       {
       echo <<<EOF
<script>advanceProgBarProgress('progBar', 'progBarInner', '$sizeP', '$sizeP%($size/$totalsizes bytes)');</script>
EOF;
       }
       else
       {
       echo <<<EOF
<script>advanceProgBarProgress('progBar', 'progBarInner', '$sizeP', 'Copy Completed!');</script>
EOF;
       }
    }
    /**
    $action
    -----------
    copy to another dir
    delete all files in all dirs
    delete all empty dirs
    chmod special files,such as .php,.pl,.cgi
    copy special files,such as .php,.pl,.cgi
    delete special files,such as .php,.pl,.cgi
    caculate file and subdir numbers in dir
    -----------
    $action
    */
    function _LocalProcessor()
    {
    //init...
    $info=array();
    $info[1][0]=$this->sDir;
    $info[1][1]="?/none";
    $info[1]['index']=1;
    $nextdir=$this->sDir;
    $level=1; $i=0;
    
    //$basedir=dirname($this->sDir);
    $basedir=($this->sDir);

    /*if((!$dir=opendir($this->dDir))&&($this->action=="copy"))
    {
        //$this->SetError("目标文件夹 ".$this->dDir." 不存在!");return 0;
    }*/
    
    while($level!=0){
      $curdir=$nextdir;
      if(!$dir=@opendir($nextdir))
      {
          $this->SetError("can not open directory：$nextdir!!");
          return 0;
      }
      
      $this->debug("Open dir:$nextdir");

      if($basedir!="/")
      {
          $realpath=substr($nextdir,strlen($basedir)+1);
      }
	  else
      {
          $realpath=$nextdir;
      }
      if($this->dDir)
      {
          $destdir=$this->dDir."/".$realpath;
      }
      else
      {
          $destdir=$realpath;
      }
      //=======action:begin========
      if($this->action=='view')
      {
          $list[$i]['name']=$nextdir;
          $list[$i]['folder']=1;
      }
      if($this->action=="copy")
      {
          if(@mkdir($destdir,0777))
          {
              $this->debug("$this->action::$destdir");
          }
          else
          {
              $this->SetError("$this->action::can not create $destdir,continue...");
          }
      }
	  if($this->action=="chmod"||$this->action=="chmoddir")
      {
          if(@chmod($nextdir,$this->permsValue))
          {
              $this->debug("$this->action:$nextdir=>$this->permsValue");
          }
          else
          {
              $this->SetError("Error of $this->action:$nextdir=>$this->permsValue") ;
          }
      }
      if($this->action=="remoteupload")
      {
          if(@ftp_mkdir($this->ftpconn,$destdir))
          {
              $this->debug("$this->action :: create->$destdir");
          }
          else
          {
              $this->SetError("Error of $this->action: create $destdir,continue...");
          }
      }
      //========action:end=========
      $hasdir=0;
      $dirnum=0;

      while($file=readdir($dir)){

         if (($file != ".") && ($file != "..")){
            if(is_dir($nextdir."/".$file)){
              $hasdir=1;
              $info[$level+1][$dirnum]=$nextdir."/".$file;
              $info[$level+1]['index']=0;
              $dirnum++;
              $this->dirnums++;
           }
           if(is_file($nextdir."/".$file)){

              $this->filenums++;
              $this->totalsize=$this->totalsize+filesize($nextdir."/".$file);
              //============action::begin=================
              $i++;
              if($this->action=='view'){
                  $list[$i]['name']="$nextdir/$file";
                  $list[$i]['folder']=0;
              }
              if($this->action=='getfiles'){
                  $this->files[$i]['name']="$nextdir/$file";
                  $this->files[$i]['folder']=0;
              }
              if($this->action=="copy"){
                  if($this->ext=="all"||@in_array($this->fileext($file),$this->ext)){
                      if(@copy($nextdir."/".$file,$destdir."/".$file)){
                          $this->debug("copy $nextdir$file => $destdir$file ");
                      }else{
                          $this->SetError("Err:copy $nextdir$file => $destdir$file ");
                      }
                  }
              }
              if($this->action=="rename"){
                  if($this->ext=="all"||@in_array($this->fileext($file),$this->ext)){
                      $newfile=substr($file,0,strlen($file)-strlen($this->fileext($file))).$this->newext;
                      if(@rename($nextdir."/".$file,$nextdir."/".$newfile)){
                          $this->debug("rename $nextdir$file => $nextdir$newfile ");
                      }
                      else{
                          $this->SetError("Error->rename $nextdir$file => $nextdir$newfile ");
                      }
                  }
              }
              if($this->action=="delfile"){
                  if($this->ext=="all"||@in_array($this->fileext($file),$this->ext)){
                      if(unlink($nextdir."/".$file))
                           $this->debug("delete file:".$nextdir.$file);
                      else
                           $this->SetError("Err:delete file:".$nextdir.$file);
                  }
              }
              if($this->action=="remoteupload"){
                  if(@in_array($this->fileext($file),$this->ext)||$this->ext=='all'){
                    if(@ftp_put($this->ftpconn,$destdir."/".$file,$nextdir."/".$file,$this->ftpAsciiBinary($file)))
                    {
                       $this->debug("ftp put files:".$nextdir."/".$file." to ".$destdir."".$file);
                       $this->echoBar($this->totalsize,$this->filenums,$this->dirnums);
                    }
                    else
                       $this->SetError("Error of put files:".$nextdir."/".$file." to ".$destdir.$file);
                  }
              }
			   if($this->action=="chmod"||$this->action=="chmodfile"){
                   if(@in_array($this->fileext($file),$this->ext)||$exinfo[ext]=='all'){
                      if(@chmod($nextdir."/". $file,$this->permsValue))
                         $this->debug("chmod:".$nextdir."/".$file."=>".$this->permsValue);
                      else
                         $this->SetError("Error->chmod:".$nextdir."/".$file."=>".$this->permsValue);
                   }
              }
              //===============action:end=====================
           }
         }

      }//end read dir while
      $i++;
      closedir($dir);
      $noindex=$info[$level+1]['index']+1;
      $info[$level+1][$dirnum]="?/none";

      if($hasdir==1){//level+1下一层次，即下一个兄弟接点是否存在
          $curindex=$info[$level+1]['index'];
          $nextdir= $info[$level+1][$curindex];
          $info[$level+1]["index"]++;
          $level++;
      }
      else{
          if($this->action=="deldir"){
             if(@rmdir($nextdir)) $this->debug( "delete dir:".$nextdir );
             else  $this->SetError( "Error->delete dir:".$nextdir );
          }
          if($this->action=='getfiles'){
             $this->files[$i]['name']="$nextdir";
             $this->files[$i]['folder']=1;
          }
          $tempflag=0;
          while($level!=0 && $tempflag==0){

               $curindex=$info[$level]['index'];
               if($info[$level][$curindex]!="?/none") {
                    $nextdir=$info[$level][$curindex];
                    $info[$level]['index']++;
                    $tempflag=1;
               }
               else{
                    $level--;
                    if($this->action=="deldir"){
                        $curindex= $info[$level]["index"];
                        $deldir= $info[$level][$curindex-1];
                        if($deldir){
                           if(@rmdir($deldir)) $this->debug("delete dir:".$deldir);
                           else $this->SetError("Err:delete dir:".$deldir);
                        }
                    }
                    if($this->action=='getfiles'){
                        $curindex= $info[$level]["index"];
                        $deldir= $info[$level][$curindex-1];
                        $this->files[$i]['name']=$deldir;
                        $this->files[$i]['folder']=1;
                    }
                    //echo "--->level:".$level."<br>";
               } //end if
          }//end goback while
      }//end if

    }//end level while

    $this->dirnums++;

    $this->debug("文件夹: $this->dirnums 个;文件: $this->filenums 个.Size:$this->totalsize bytes");

    if($this->action=='view') return $list;

    }//end func
    /**
    $action
    copy to another dir
    delete all files in all dirs
    delete all empty dirs
    chmod special files,such as .php,.pl,.cgi
    copy special files,such as .php,.pl,.cgi
    delete special files,such as .php,.pl,.cgi
    caculate file and subdir numbers in dir
    $action
    */
    function _FTPProcessor()
    {
    //init...
    $info=array();
    $info[1][0]=$this->sDir;
    $info[1][1]="?/none";
    $info[1]['index']=1;
    $nextdir=$info[1][0];
    $level=1;$deldirs=$i=0;
    
    $basedir=dirname($this->sDir);
    $basedir=($this->sDir);

       
    if($this->action=="download")
    {
        if(!$dir=opendir($this->dDir))
        {
           $this->SetError("target directory  ".$targetdir["dest"]." doesn't exist!");
           return 0;
        }
    }

    while($level!=0){

      if($this->action=="view"){
          $list[$i][name]= $nextdir;
          $list[$i][folder]=1;
      }
      //$files = ftp_nlist($this->ftpconn,$nextdir);
      $files = $this->ftp_getlist($this->ftpconn,$nextdir);

      $this->debug("Get list from $nextdir");
      $hasdir=0;
      $dirnum=0;

	  if($basedir=="")  $realpath=$nextdir;
	  else $realpath=substr($nextdir,strlen($basedir)+1);
      $destdir=$this->dDir."/".$realpath;

      //=====action::begin=============
      if($this->action=="download"){
          if(@mkdir($destdir,0755))
          $this->debug("$this->action-> mkdir:".$destdir);
          else
          $this->SetError("error of $this->action mkdir:".$destdir);
      }
      if($this->action=="chmod"||$this->action=="chmoddir"){
          if(function_exists('ftp_chmod')&&ftp_chmod($this->ftpconn, $this->permsValue, $nextdir))
          $this->debug("chmod: $nextdir => $this->permsValue");
          elseif(function_exists('ftp_site')&&ftp_site($this->ftpconn, "CHMOD $this->permsValue $nextdir"))
          $this->debug("chmod: $nextdir => $this->permsValue");
          else
          $this->SetError("fail to chmod: $nextdir => $this->permsValue");
      }
      //======action:end============

      if($files)
      foreach ($files as $key=>$fileinfo) {
          $isdir=$fileinfo[dirorfile];
          $file=$fileinfo[dirfilename];
          if (($file != ".") && ($file != "..")){
          if($isdir == "d") { //Is a directory
             $hasdir=1;
             $info[$level+1][$dirnum]=$nextdir.'/'.$file;
             $info[$level+1]['index']=0;
             $dirnum++;
             $this->dirnums++;
          }
          if($isdir == "-") { //Is a file
             $this->filenums++;
             $i++;
             //=====action::begin================
             if($this->action=="view"){
                $list[$i][name]= $nextdir.'/'.$file;
                $list[$i][folder]=0;
             }
             if($this->action=='getfiles'){
                  $this->files[$i]['name']="$nextdir/$file";
                  $this->files[$i]['folder']=0;
             }
             if($this->action=="delfile"){
                 if(@in_array($this->fileext($file),$this->ext)||$this->ext=='all'){
                  if(ftp_delete($this->ftpconn, $nextdir.'/'.$file))  $this->debug("delete: $file");
                  else   $this->SetError("Error->delete: $file");
                 }
             }
             if($this->action=="rename"){
                 if(@in_array($this->fileext($file),$this->ext)||$this->ext=='all'){
                 // if(ftp_delete($this->ftpconn, $nextdir.'/'.$file))  $this->debug("delete: $file");
                 // else   $this->SetError("Error->delete: $file");
                 //}
                 $newfile=substr($file,0,strlen($file)-strlen($this->fileext($file))).$this->newext;
                 if(@ftp_rename($this->ftpconn,$nextdir."/".$file,$nextdir."/".$newfile)){
                    $this->debug("rename $nextdir$file => $destdir$newfile ");
                 }
                 else{
                    $this->SetError("Error->rename $nextdir$file => $destdir$newfile ");
                 }
                 }
             }
		     if($this->action=="chmod"||$this->action=="chmodfiles"){
                 if(@in_array($this->fileext($file),$this->ext)||$this->ext=='all'){
                     if(function_exists('ftp_chmod')&&ftp_chmod($this->ftpconn, $this->permsValue, $nextdir.'/'.$file))
                        $this->debug("chmod: $nextdir/$file => $this->permsValue");
                     elseif(function_exists('ftp_site')&&ftp_site($this->ftpconn, "CHMOD $this->permsValue $nextdir/$file"))
                        $this->debug("chmod: $nextdir/$file=>$this->permsValue");
                     else
                        $this->SetError("Error->chmod: $nextdir/$file=>$this->permsValue");
                 }
             }
             if($this->action=="download"){
                 if(@in_array($this->fileext($file),$this->ext)||$this->ext=='all'){
                     if(ftp_get($this->ftpconn,$destdir."/".$file,$nextdir.'/'.$file,$this->ftpAsciiBinary($file)) )
                        $this->debug("download files:".$nextdir.'/'.$file." -> ".$destdir."/".$file);
                     else
                        $this->SetError("error of download files:".$nextdir.'/'.$file." -> ".$destdir."/".$file);
                 }
             }
             //=======action:end================
          }
          $this->totalsize=$this->totalsize+$fileinfo[size];
        }
      }
      $i++;
      $info[$level+1][$dirnum]="?/none";

      if($hasdir==1){//level+1下一层次，即下一个兄弟接点是否存在
          $curindex=$info[$level+1]['index'];
          $nextdir= $info[$level+1][$curindex];
          $info[$level+1]["index"]++;
          $level++;
      }
      else{
          if($action=="deldir"){
             if(@ftp_rmdir($this->ftpconn,$nextdir))
                 $this->debug( "delete dir::".$nextdir);
             else
                 $this->SetError( "error of delete dir::".$nextdir);
             $deldirs++;
             if($this->action=='getfiles'){
                 $this->files[$i]['name']=$nextdir;
                 $this->files[$i]['folder']=1;
             }
          }
          $tempflag=0;
          while($level!=0 && $tempflag==0){
               //echo "curdir:".$curdir."<br>";
               $curindex=$info[$level]['index'];
               //echo "level:".$level."  curindex=:".$curindex."<br>";
               if($info[$level][$curindex]!="?/none") {
                    $nextdir=$info[$level][$curindex];
                    $info[$level]['index']++;
                    $tempflag=1;
                    //goto 1:
               }
               else{
                    $level--;
                    if($this->action=="deldir"){
                        $curindex= $info[$level]["index"];
                        $deldir= $info[$level][$curindex-1];
                        if(@ftp_rmdir($this->ftpconn,$deldir))
                             $this->debug("delete dir::".$deldir);
                        else
                             $this->SetError("Error of delete dir::".$deldir);
                        if($deldir) $deldirs++;
                    }
                    if($this->action=='getfiles'){
                        $curindex= $info[$level]["index"];
                        $deldir= $info[$level][$curindex-1];
                        $this->files[$i]['name']=$deldir;
                        $this->files[$i]['folder']=1;
                    }
                    //echo "--->level:".$level."<br>";
               } //end if
               //echo "tempflag:".$tempflag."<br>";
          }//end while
      }//end if

    }//end while
    $dirnums++;

    $this->debug( "文件夹: $this->dirnums 个;文件: $this->filenums 个,size:$this->totalsize bytes");
    if($this->action=='view') return $list;

    }//end func
    function fileext($file)
    {
       $p = pathinfo($file);
       return strtolower($p['extension']);
    }
    function convertsize($size){

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
       case 0 : $mega = ' bytes'; break;
       case 1 : $mega = ' KB'; break;
       case 2 : $mega = ' MB'; break;
       case 3 : $mega = ' GB'; break;
       case 4 : $mega = ' TB'; break;}

    $addsize .= $mega;
    return $addsize;
    }
    function mytempname($dir, $prefix, $postfix) {
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
    $filename = $trailing_slash . $prefix . $seed . $postfix;
	return $filename;

    } // end mytempnam
    function LoginFtp($ftp,$user,$pass,$initdir)
    {
       $this->ftpconn = @ftp_connect($ftp);

       $login_result = @ftp_login($this->ftpconn, $user, $pass);

       if ((!$this->ftpconn) || (!$login_result))
       {
          $this->SetError("Ftp::can't login ftp $ftp with $user");
          @ftp_close($this->ftpconn);
          return false;
       }
       else
       {
          $this->debug("Ftp::login ftp $ftp with $user");
       }

       if($initdir=="") $$initdir="/";

       if(!@ftp_chdir($this->ftpconn,$initdir))
       {
          //ftp_chdir($conn_id,"/");
          $this->SetError("Ftp::Can't change to directory $initdir,change to / instead!");
          ftp_close($this->ftpconn);
          return false;
       }
       else
       {
          $this->debug("Ftp::change to directory $initdir");
          ftp_chdir($this->ftpconn,'/');//reset
       }
       return 1;
    }
    function ftpAsciiBinary($filename) {

   // --------------
   // Checks the first character of a file and its extension to see if it should be
   // transferred in ASCII or Binary mode
   //
   //	Default: FTP_BINARY
   //	Exceptions: FTP_ASCII (files which start with a dot, and a list of exceptions)
   //	A file with more than 1 dot: the last extension is taken into account
   //
   // --------------

   // -------------------------------------------------------------------------
   // If the first character is a dot, return FTP_ASCII
   // -------------------------------------------------------------------------
	$firstcharacter = substr($filename, 0, 1);

	if ($firstcharacter == ".") {
		$ftpmode = FTP_ASCII;
		return $ftpmode;
	}

    // -------------------------------------------------------------------------
    // If the first character is not a dot, check the extension
    // -------------------------------------------------------------------------
	//$last = get_filename_extension($filename);
    $last=strtolower(strrchr($filename,'.'));
    $last=substr($last,1);
	if (
		$last == "asp"  		||
		$last == "bas"  		||
		$last == "bat"  		||
		$last == "c"  		||
		$last == "cfg"  		||
		$last == "cfm"  		||
		$last == "cgi"  		||
		$last == "conf"  		||
		$last == "cpp"  		||
		$last == "css"  		||
		$last == "dhtml"		||
		$last == "diz"		||
		$last == "default"	||
		$last == "file"  		||
		$last == "h"  		||
		$last == "hpp"  		||
		$last == "htaccess"	||
		$last == "htpasswd"	||
		$last == "htm"  		||
		$last == "html"  		||
		$last == "inc"  		||
		$last == "ini"  		||
		$last == "js"  		||
		$last == "jsp"  		||
		$last == "mak" 		||
		$last == "msg" 		||
		$last == "nfo" 		||
		$last == "old" 		||
		$last == "pas" 		||
		$last == "patch" 		||
		$last == "perl" 		||
		$last == "php" 		||
		$last == "php3" 		||
		$last == "phps" 		||
		$last == "phtml" 		||
		$last == "pinerc"		||
		$last == "pl" 		||
		$last == "pm" 		||
		$last == "qmail" 		||
		$last == "readme"		||
		$last == "setup" 		||
		$last == "sh" 		||
		$last == "shtml" 		||
		$last == "sql" 		||
		$last == "style" 		||
		$last == "tcl" 		||
		$last == "tex"		||
		$last == "threads"	||
		$last == "tmpl"  		||
		$last == "tpl"  		||
		$last == "txt"  		||
		$last == "ubb"  		||
		$last == "vbs"  		||
		$last == "xml"  		||
		$last == "conf"		||
		strstr($last, "htm")
							)	{ $ftpmode = FTP_ASCII; }
	else 							{ $ftpmode = FTP_BINARY; }
    //echo "<font color=green>".$filename.  ",$last,$ftpmode</font>";
	return $ftpmode;

    } // end ftpAsciiBinary
    function ftp_getlist($conn_id, $directory) {


    // -------------------------------------------------------------------------
    // Replace \' by \\' to be able to delete directories with names containing \'
    // -------------------------------------------------------------------------
	if (strlen($directory) > 1) { $directory1 = str_replace("\'", "\\\'", $directory); }
	else                        { $directory1 = $directory; }

    // -------------------------------------------------------------------------
    // Step 1 - Chdir to the directory
    // This is to check if the directory exists, but also because otherwise
    // the ftp_rawlist does not work on some FTP servers.
    // -------------------------------------------------------------------------
	$result1 = @ftp_chdir($conn_id, $directory1);


    // -------------------------------------------------------------------------
    // Step 2 - Get list of directories and files
    // The -a option is used to show the hidden files as well on some FTP servers
    // Some servers do not return anything when using -a, so in that case try again without the -a option
    // -------------------------------------------------------------------------
	$rawlist = ftp_rawlist($conn_id, "-a");
	if (sizeof($rawlist) <= 1) { $rawlist = ftp_rawlist($conn_id, ""); }


    // -------------------------------------------------------------------------
    // Step 3 - Parse the raw list to get an array
    // -------------------------------------------------------------------------
	for($i=0; $i<count($rawlist); $i++) {
		$templist[$i] = $this->ftp_scanline($rawlist[$i]);
	} // End for

    // -------------------------------------------------------------------------
    // Step 4 - Move the rows so that
    //   1. the array would contain elements from 1 to n
    //   2. the list would be sorted directories first, then files, then symlinks, then unrecognized
    // -------------------------------------------------------------------------
	$i = 0; // $i is the index of templist and could go from 0 to n+3
	$j = 1; // $j is the index of list and should go from 1 to n  (n being the nr of valid rows)
	$list_directories = array();
	$list_files = array();
	$list_symlinks = array();
	$list_unrecognized = array();

	for ($i=0; $i<count($templist); $i=$i+1) {
		if (is_array($templist[$i]) == true) {
			if     ($templist[$i]['dirorfile'] == "d") { array_push($list_directories, $templist[$i]); }
			elseif ($templist[$i]['dirorfile'] == "-") { array_push($list_files, $templist[$i]); }
			elseif ($templist[$i]['dirorfile'] == "l") { array_push($list_symlinks, $templist[$i]); }
			elseif ($templist[$i]['dirorfile'] == "u") { array_push($list_unrecognized, $templist[$i]); }
		}
	}
	for ($i=0; $i<count($list_directories); $i=$i+1)  { $list[$j] = $list_directories[$i]; $j=$j+1; }
	for ($i=0; $i<count($list_files); $i=$i+1)        { $list[$j] = $list_files[$i];$j=$j+1; }
	//for ($i=0; $i<count($list_symlinks); $i=$i+1)     { $list[$j] = $list_symlinks[$i]; $j=$j+1; }
	//for ($i=0; $i<count($list_unrecognized); $i=$i+1) { $list[$j] = $list_unrecognized[$i]; $j=$j+1; }

    // -------------------------------------------------------------------------
    // Step 5 - Return the result
    // -------------------------------------------------------------------------
 	$list_warnings_directory[1] = $list;
	$list_warnings_directory[2] = $warnings;
	$list_warnings_directory[3] = $directory;
    //print_r($list);
    $result1 = @ftp_chdir($conn_id, '/');
    return $list;




    } // End function ftp_getlist


    function ftp_scanline($rawlistline) {

    // --------------
    // This function scans an ftp_rawlist line string and returns its parts (directory/file, name, size,...) using ereg()
    //
    //  !!! Documentation about ereg and FTP server's outputs are now at the end of the function !!!
    // --------------

    // -------------------------------------------------------------------------
    // Scanning:
    //   1. first scan with strict rules
    //   2. if that does not match, scan with less strict rules
    //   3. if that does not match, scan with rules for specific FTP servers (AS400)
    //   4. and if that does not match, return the raw line
    // -------------------------------------------------------------------------


    // ----------------------------------------------
    // 1. Strict rules
    // ----------------------------------------------
	    if (ereg("([-dl])([rwxst-]{9})[ ]+([0-9]+)[ ]+([^ ]+)[ ]+(.+)[ ]+([0-9]+)[ ]+([a-zA-Z]+[ ]+[0-9]+)[ ]+([0-9:]+)[ ]+(.*)", $rawlistline, $regs) == true) {
    //                  permissions             number      owner      group     size         month        day        year/hour    filename
		$listline['scanrule']    = "rule-1";
		$listline['dirorfile']   = "$regs[1]";		// Directory ==> d, File ==> -
		$listline['dirfilename'] = "$regs[9]";		// Filename
		$listline['size']        = "$regs[6]";		// Size
		$listline['owner']       = "$regs[4]";		// Owner
		$listline['group']       = "$regs[5]";		// Group
		$listline['permissions'] = "$regs[2]";		// Permissions
		$listline['mtime']       = "$regs[7] $regs[8]";	// Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
	}

    // ----------------------------------------------
    // 2. Less strict rules
    // ----------------------------------------------
	elseif (ereg("([-dl])([rwxst-]{9})[ ]+(.*)[ ]+([a-zA-Z0-9 ]+)[ ]+([0-9:]+)[ ]+(.*)", $rawlistline, $regs) == true) {
    //                  permissions             number/owner/group/size
    //                                                  month-day          year/hour    filename
		$listline['scanrule']    = "rule-2";
		$listline['dirorfile']   = "$regs[1]";		// Directory ==> d, File ==> -
		$listline['dirfilename'] = "$regs[6]";		// Filename
		$listline['size']        = "$regs[3]";		// Number/Owner/Group/Size
		$listline['permissions'] = "$regs[2]";		// Permissions
		$listline['mtime']       = "$regs[4] $regs[5]";	// Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
	}

    // ----------------------------------------------
    // 3. Specific FTP server rules
    // ----------------------------------------------

    // ---------------
    // 3.1 Windows
    // ---------------
	elseif (ereg("([0-9/-]+)[ ]+([0-9:AMP]+)[ ]+([0-9]*)[ ]+(.*)", $rawlistline, $regs) == true) {
    //                  date          time            size        filename

		$listline['scanrule']    = "rule-3.1";
		$listline['size']        = "$regs[3]";		// Size
		$listline['dirfilename'] = "$regs[4]";		// Filename
		$listline['owner']       = "";			// Owner
		$listline['group']       = "";			// Group
		$listline['permissions'] = "";			// Permissions
		$listline['mtime']       = "$regs[1] $regs[2]";	// Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)

		if ($listline['size'] != "") { $listline['dirorfile'] = "-"; }
		else                         { $listline['dirorfile'] = "d"; }
	}

    // ---------------
    // 3.2 Netware
    // Thanks to Danny!
    // ---------------
	elseif (ereg("([-]|[d])[ ]+(.{10})[ ]+([^ ]+)[ ]+([0-9]*)[ ]+([a-zA-Z]*[ ]+[0-9]*)[ ]+([0-9:]*)[ ]+(.*)", $rawlistline, $regs) == true) {
    //                   dir/file perms          owner      size        month        day         hour        filename
		$listline['scanrule']    = "rule-3.2";
		$listline['dirorfile']   = "$regs[1]";		// Directory ==> d, File ==> -
		$listline['dirfilename'] = "$regs[7]";		// Filename
		$listline['size']        = "$regs[4]";		// Size
		$listline['owner']       = "$regs[3]";		// Owner
		$listline['group']       = "";			// Group
		$listline['permissions'] = "$regs[2]";		// Permissions
		$listline['mtime']       = "$regs[5] $regs6";	// Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
	}

    // ---------------
    // 3.3 AS400
    // ---------------
	elseif (ereg("([a-zA-Z0-9_-]+)[ ]+([0-9]+)[ ]+([0-9/-]+)[ ]+([0-9:]+)[ ]+([a-zA-Z0-9_ -\*]+)[ /]+([^/]+)", $rawlistline, $regs) == true) {
    //                  owner               size        date          time          type                     filename

		if ($regs[5] != "*STMF") { $directory_or_file = "d"; }
		elseif ($regs[5] == "*STMF") { $directory_or_file = "-"; }

		$listline['scanrule']    = "rule-3.3";
		$listline['dirorfile']   = "$directory_or_file";// Directory ==> d, File ==> -
		$listline['dirfilename'] = "$regs[6]";		// Filename
		$listline['size']        = "$regs[2]";		// Size
		$listline['owner']       = "$regs[1]";		// Owner
		$listline['group']       = "";			// Group
		$listline['permissions'] = "";			// Permissions
		$listline['mtime']       = "$regs[3] $regs[4]";	// Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
	}

    // ---------------
    // 3.4 Titan
    // Owner, group are modified compared to rule 1
    // TO DO: integrate this rule in rule 1 itself
    // ---------------
	elseif (ereg("([-dl])([rwxst-]{9})[ ]+([0-9]+)[ ]+([a-zA-Z0-9]+)[ ]+([a-zA-Z0-9]+)[ ]+([0-9]+)[ ]+([a-zA-Z]+[ ]+[0-9]+)[ ]+([0-9:]+)[ ](.*)", $rawlistline, $regs) == true) {
    //                   dir/file permissions    number      owner             group             size         month       date        time       file
		$listline['scanrule']    = "rule-3.4";
		$listline['dirorfile']   = "$regs[1]";        // Directory ==> d, File ==> -
		$listline['dirfilename'] = "$regs[9]";        // Filename
		$listline['size']        = "$regs[6]";        // Size
		$listline['owner']       = "$regs[4]";        // Owner
		$listline['group']       = "$regs[5]";        // Group
		$listline['permissions'] = "$regs[2]";        // Permissions
		$listline['mtime']       = "$regs[7] $regs[8]";    // Mtime -- format depends on what FTP server returns (year, month, day, hour, minutes... see above)
	}

    // ----------------------------------------------
    // 4. If nothing matchs, return the raw line
    // ----------------------------------------------
	else {
		$listline['scanrule']    = "rule-4";
		$listline['dirorfile']   = "u";
		$listline['dirfilename'] = $rawlistline;
	}

    // -------------------------------------------------------------------------
    // Remove the . and .. entries
    // Remove the total line that some servers return
    // -------------------------------------------------------------------------
	if ($listline['dirfilename'] == "." || $listline['dirfilename'] == "..") { return ""; }
	elseif (substr($rawlistline,0,5) == "total") { return ""; }


    // -------------------------------------------------------------------------
    // And finally... return the nice list!
    // -------------------------------------------------------------------------
	return $listline;


    } // End function ftp_scanline

}
?>
