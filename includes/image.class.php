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
* Image class
*/
class Image {
    var $GD_INSTALLED      = true;
    var $IM_INSTALLED      = false;
    var $MPLAYER_INSTALLED = false;
    var $FFMPEG_INSTALLED  = false;
    
    var $IM_PATH           = '';
    var $MPLAYER_PATH      = '';
    var $FFMPEG_PATH       = '';
    
    var $USE_MPLAYER       = true;
    var $USE_IM            = false;

    var $thumb_width       = 150;
    var $thumb_height      = 150;
    var $thumb_type        = 'fix';
    var $small_thumb       = 'continue';
    
    var $bar_info          = '{width}x{height} {size}';
    var $font              = 3;
    var $info_height       = 15;

    var $watermark_type    = 'TEXT';
    var $watermark_font    = 'TEXT';
    var $watermark_color   = 'TEXT';
    var $watermark_stamp   = 'Powered by Image class';
    var $watermark_postion = 'TEXT';
    
    var $play_time         = 0.001;

    var $errors            = null;

    var $tmpdir            = 'temp';
    var $srcdir            = 'upload';
    var $thbdir            = 'thumb';
    
    # image gd can
    var $ImageFile         = array('jpg','jpeg','gif','png');
    # image imagemagic can
    var $ImageFile2        = array('bmp','tiff','tif');
    //var $ThumbFile         = array('jpg','jpeg','bmp','tiff','tif','gif','png','mpeg','mpg','avi','wmv');
    var $VideoFile         = array('mpeg','mpg','avi','wmv');
    var $FlashFile         = array('swf');
    var $AduioFile         = array();
    var $AchiveFile        = array();

	/**
	* image object constructor
	*/
	function Image()
    {
        $this->GD_INSTALLED = extension_loaded('gd');
	}
 
    function setWorkDirs($src='files',$thb='thumb',$tmp='tmp')
    {
        is_dir($src) ? $this->srcdir = $src : die('Source directory doesn\'t exists:'.$src);
        is_dir($thb) ? $this->thbdir = $thb : die('Thumb directory doesn\'t exists:'.$thb);
        is_dir($tmp) ? $this->tmpdir = $tmp : die('Temporary directory doesn\'t exists:'.$tmp);
    }
    
    function setModuleStatus($im=0,$mplayer=0,$ffmpeg=0)
    {
        $this->IM_INSTALLED      = $im;
        $this->MPLAYER_INSTALLED = $mplayer;
        $this->FFMPEG_INSTALLED  = $ffmpeg;
    }
    
    function canThumb($name,$sep='.')
    {
        return (($this->GD_INSTALLED||$this->IM_INSTALLED)&&in_array($this->getExt($name,$sep),$this->ImageFile))
               ||($this->IM_INSTALLED&&in_array($this->getExt($name,$sep),$this->ImageFile2))
               ||(($this->MPLAYER_INSTALLED||$this->FFMPEG_INSTALLED)&&in_array($this->getExt($name,$sep),$this->VideoFile));
    }
    
    function isImage($name,$sep='.')
    {
        if($this->USE_IM)
        return in_array($this->getExt($name,$sep),$this->ImageFile)||in_array($this->getExt($name,$sep),$this->ImageFile2);
        else
        return in_array($this->getExt($name,$sep),$this->ImageFile);
    }
    
    function isVideo($name,$sep='.')
    {
        return in_array($this->getExt($name,$sep),$this->VideoFile);
    }
    
    function isAduio($name,$sep='.')
    {
        return in_array($this->getExt($name,$sep),$this->AduioFile);
    }
    
    function isFlash($name,$sep='.')
    {
        return in_array($this->getExt($name,$sep),$this->FlashFile);
    }
    
    function isAchive($name,$sep='.')
    {
        return in_array($this->getExt($name,$sep),$this->AchiveFile);
    }
    
    function createThumb($image,$info=1)
    {
        if($this->USE_IM)
        {
            $return =  $this->createThumbByIM($image,$info);
        }
        else
        {
            $return =  $this->createThumbByGD($image,$info);
        }
        
        return $return;
    }
    
    function createThumbByGD($image,$info=1)
    {
        # detecting gd
        if(!$this->GD_INSTALLED)
        {
            $this->errors .= 'GD is not installed!';
            return -1;
        }
        /**
        *0:prepare the var,...
        */
        # convert non normal image to jpg format:
        if(!in_array($this->getExt($image[name],'_')?$this->getExt($image[name],'_'):$this->getExt($image[name]),$this->ImageFile)) $image[thumb] = $this->convertFiletype($image[thumb],'jpg',1);
        
        $s_file  =$this->srcdir.'/'.$image[name];
        $d_file  =$this->thbdir.'/'.$image[thumb];

        # checking error
        if(!is_file($s_file))
        {
            $this->setError(sprintf('%s doesn\'t exist',$s_file));
            return -1;
        }
        
        # get image info
        $imginfo = getimagesize($s_file);
        $image[width]  = $imginfo[0];
        $image[height] = $imginfo[1];
        $image[type]   = $imginfo[2];
        $image[size]   = filesize($s_file);
        
        /**
        *1:create the image source
        */
        $s_img = $this->input($s_file,$image[type]?$image[type]:-1);
        
        # checking error
        if(!is_resource($s_img))
        {
            $this->setError(sprintf('%s can\'t handled, file corruption or by gd libery issue!',$s_file));
            return -1;
        }

        /**
        *2:get the thumb width and height by the limitation
        */
        list($new_x,$new_y)=($this->getWH($image[width],$image[height]));

        # processing thumbnail of small images
        if($new_x==$image[width] && $new_y==$image[height])
        {
            if($this->small_thumb=='copy')
            {
                if(!copy($s_file,$d_file))
                {
                    $this->setError(sprintf('%s can\'t be copied to %s!',$s_file,$d_file));
                    return -1;
                }
                return $image;
            }
            if($this->small_thumb=='return')
            {
                return $image;
            }
            // continue
        }
        
        /**
        * 3:generate the thumb images
        */
        if($info&&strlen($this->bar_info))
        {
            /**
            *3.1:build the info text and position
            */
            $string = str_replace('{width}',$image[width],$this->bar_info);
            $string = str_replace('{height}',$image[height],$string);
            $string = str_replace('{imagename}',$image[name],$string);
            $string = str_replace('{size}',$this->convertsize($image[size],1),$string);

            list($new_x2,$new_y2,$str_x,$str_y,$imagestring) = $this->getBarInfo($new_x,$new_y,strlen($string));

            $d_img = imagecreatetruecolor($new_x2, $new_y2);
            $black = ImageColorAllocate($d_img, 0, 0 ,0 );
            $white = ImageColorAllocate($d_img, 255, 255 ,255 );
            $imagestring ($d_img, $this->font, $str_x, $str_y,  $string, $white);
            imagecopyresized($d_img, $s_img, 1, 1, 0, 0, $new_x, $new_y, $image[width], $image[height]);
        }
        else
        {
            $d_img = imagecreatetruecolor($new_x, $new_y);
            imagecopyresized($d_img, $s_img, 0, 0, 0, 0, $new_x, $new_y, $image[width], $image[height]);
        }

        /**
        *5:store the thumb images to file!
        */
        $success = $this->output($d_img,$image[type],$d_file);
        # checking error
        if(!$success)
        {
            $this->setError(sprintf('%s can\'t saved correctly',$d_file));
            return -1;
        }
        
        ImageDestroy($s_img);
        ImageDestroy($d_img);
        
        # get the status of thumbnails, fail or ok?
        if(!is_file($d_file))
        {
            $this->setError(sprintf('Unknown error: thumbnail %s doesn\'t exist!',$d_file));
            return -1;
        }
        
        return $image;
    }
    
    function createThumbByIM($image,$info=1)
    {
        /**
        *prepare the var,...
        */
        # convert non normal image to jpg format:
        if(!in_array($this->getExt($image[name],'_')?$this->getExt($image[name],'_'):$this->getExt($image[name]),$this->ImageFile)) $image[thumb] = $this->convertFiletype($image[thumb],'jpg',1);

        $s_file  = $this->srcdir.'/'.$image[name];
        $d_file  = $this->thbdir.'/'.$image[thumb];

        # checking error
        if(!is_file($s_file))
        {
            $this->setError(sprintf('%s doesn\'t exist',$s_file));
            return -1;
        }
        
        # get image info
        $imginfo = getimagesize($s_file);
        $image[width]  = $imginfo[0];
        $image[height] = $imginfo[1];
        $image[type]   = $imginfo[2];
        $image[size]   = filesize($s_file);

        /**
        *2:get the thumb width and height by the limtation
        */
        list($new_x,$new_y)=($this->getWH($image[width],$image[height]));

        # processing thumbnail of small images
        if($new_x==$image[width] && $new_y==$image[height])
        {
            if($this->small_thumb=='copy')
            {
                if(!copy($s_file,$d_file))
                {
                    $this->setError(sprintf('%s can\'t be copied to %s!',$s_file,$d_file));
                    return -1;
                }
                return $image;
            }
            if($this->small_thumb=='return')
            {
                return $image;
            }
            // continue
        }
        
        # font file
        $this->font_file='fonts/arialbd.ttf';

        /**
        *3:build the info text and position
        */
        if($info&&strlen($this->bar_info))
        {
            $string = str_replace('{width}',$image[width],$this->bar_info);
            $string = str_replace('{height}',$image[height],$string);
            $string = str_replace('{imagename}',$image[name],$string);
            $string = str_replace('{size}',$this->convertsize($image[size],1),$string);

            list($info_x,$info_y,$str_x,$str_y,$imagestring) = $this->getBarInfo($new_x,$new_y,strlen($string),'IM');

            $cmd = $this->IM_PATH."convert -size {$info_x}x{$info_y} xc:black -font $this->font_file -pointsize 11 -fill white -draw \"text $str_x, $str_y '$string'\" -draw \"image over 0,0 $new_x,$new_y $s_file\" $d_file";

        }
        else
        {
            $cmd = $this->IM_PATH."convert -size {$new_x}x{$new_y} xc:black -draw \"image over 0,0 $new_x,$new_y $s_file\" $d_file";
        }
        system($cmd);
        
        # get the status of thumbnails, fail or ok?
        if(!is_file($d_file))
        {
            $this->setError(sprintf('Unknown error: thumbnail %s doesn\'t exist!',$d_file));
            return -1;
        }

        return $image;
    }
    
    function createVideoThumb($image,$info=1)
    {
        /**
        *0:prepare the var,...
        */
        # convert non normal image to jpg format:
        if(!in_array($this->getExt($image[name],'_')?$this->getExt($image[name],'_'):$this->getExt($image[name]),$this->ImageFile)) $image[thumb] = $this->convertFiletype($image[thumb],'jpg',1);

        $s_file  = $this->srcdir.'/'.$image[name];
        $d_file  = $this->thbdir.'/'.$image[thumb];

        /**
        *1:extracting a frame from video file
        */
        if($this->USE_MPLAYER)
        {
            $this->extractFrameByMplayer($s_file,$d_file);
        }
        else
        {
            $this->extractFrameByFFmpeg($s_file,$d_file);
        }

        if(!file_exists($d_file))
        {
            $this->setError(sprintf('Frame %s can\'t be extracted from video!',$d_file));
            return -1;
        }
        /**
        *2:thumbnailing the extracted frame of video files
        */
        # resetting source dir
        $tmp = $this->srcdir;
        $this->srcdir = $this->thbdir;
        
        $newimage = array('name'=>$image[thumb],'thumb'=>$image[thumb]);
        if($this->USE_IM)
        {
            $return =  $this->createThumbByIM($newimage,$info);
        }
        else
        {
            $return =  $this->createThumbByGD($newimage,$info);
        }
        
        # restoring source dir
        $this->srcdir = $tmp;
        
        return $return;
    }
    
    function input($filename,$imagetype=-1)
    {
        if($imagetype==-1)
        {
            $type = @getimagesize($filename);
            $imagetype = $type[2];
        }

        switch($imagetype)
        {
        case 1:
        case 'gif':
            $imagetype = "gif";
            if( function_exists("imagecreatefromgif") )
         	{
			    $s_img = @imagecreatefromgif($filename);
			    break;
			}
        case 2:
        case 'jpg':
        case 'jpeg':
			$imagetype = "jpg";
            $s_img = @imagecreatefromjpeg($filename);
			break;
        case 3:
        case 'png':
			$imagetype = "png";
            $s_img = @imagecreatefrompng($filename);
			break;
        case 'tif':
        case 'tiff':
        case 6:
        case 'bmp':
			$imagetype = "jpg";
            $filename = $this->convertFiletype($filename,$imagetype);
            $this->converted_filename = $filename;
            $s_img = @imagecreatefromjpeg($filename);
			break;
        default:

	    }

        return $s_img;
    }
    
    function output($imgrs,$imagetype=-1,$file='')
    {
        if($imagetype==-1)
        {
            $type = getimagesize($filename);
            $imagetype = $type[type];
        }
        
        switch($imagetype)
        {
        case 1:
        case 'gif':
            $ok = imagegif($imgrs,$file);
			break;
        case 2:
        case 'jpg':
        case 'jpeg':
            $ok = imagejpeg($imgrs,$file);
			break;
        case 3:
        case 'png':
            $ok = imagepng($imgrs,$file);
			break;
        case 'tif':
        case 'tiff':
        case 6:
        case 'bmp':
            $ok = imagejpeg($imgrs,$file);
            if(strlen($this->converted_filename))@unlink( $this->converted_filename);
			break;
        default:

	    }
        return $ok;
    }
    
    function getExt($filename,$sep='.')
    {
        $ext = substr(strtolower(strrchr($filename,$sep)),1);
        #if($ext==''&&strpos($name,$sep)!==false) return ' ';
        return $ext;
    }
    /**
    * no dot . for newtype
    */
    function convertFiletype($file,$newtype,$virtual=0)
    {
        $type    = $this->getExt($file);
        $newfile = ($type?substr($file,0,-strlen($type)):$file.'.').$newtype;
        
        if(!$virtual)
        {
            if(strlen($this->tmpdir)) $newfile=$this->tmpdir.'/'.basename($newfile);
            $cmd = $this->IM_PATH."convert $file $newfile";

            system($cmd);
            return  $newfile;
        }
        else
        {
            return  $newfile;
        }
    }

    function getWH($width,$height)
    {
        # if fix size is allowed
        if($this->thumb_type == 'fix') return array($this->thumb_width,$this->thumb_height);

        # get the thumb width and height by the limtation
        $maxx = $this->thumb_width ==-1 ? $width  : $this->thumb_width;
        $maxy = $this->thumb_height==-1 ? $height : $this->thumb_height;


        # too small,not to genarate thumb:(
        if($maxx>$width&&$maxy>$height) return array($width,$height);
        if(($height/$width)>($maxy/$maxx))
        {
           $new_y=$maxy;
           $new_x=$new_y*($width/$height);
        }
        else
        {
           $new_x=$maxx;
           $new_y=$new_x*($height/$width);
        }
        return array($new_x,$new_y);
    }
    function getBarInfo($width,$height,$strlen,$type='GD')
    {
        if($type=='GD')
        {
            $str_width  = ImageFontWidth($this->font) * $strlen;
            $str_height = ImageFontHeight($this->font);
        }
        else
        {
            $str_width  = 6 * $strlen;
            $str_height = 12;
        }

        if($width>$height||$type=='IM')
        {
            $new_x=$width+2;
            $new_y=$height+$this->info_height+1;

            $str_x=($width-$str_width)/2;
            
            if($type=='GD')
            $str_y=$height+($this->info_height-$str_height)/2;
            else
            $str_y=$height+($this->info_height+$str_height)/2;
            
            $imagestring='imagestring';
        }
        else
        {
            $new_x=$width+$this->info_height+1;
            $new_y=$height+2;
            
            if($type=='GD')
            $str_x=$width+($this->info_height-$str_height)/2;
            else
            $str_x=$width+($this->info_height+$str_height)/2;
            
            $str_y=$height-($new_y-$str_width)/2;
            $imagestring='imagestringup';
        }
        return array($new_x,$new_y,$str_x,$str_y,$imagestring);
    }

    /**
    * extract a frame from video files by mplayer
    */
    function extractFrameByMplayer($movie,$destfile)
    {
        $work_dir=$this->tmpdir.'/'.time().'/';
        //mkdir($work_dir); //the below comand line will create the workdir
        $cmd = $this->MPLAYER_PATH."mplayer \"$movie\" -ss $this->play_time -nosound -vo jpeg:outdir=\"$work_dir\" -frames 1";
        exec($cmd,$output,$ret);

        if(file_exists($work_dir.'00000001.jpg'))
        {
            copy($work_dir.'00000001.jpg',$destfile);
        }
        else
        {
            copy($work_dir.'00000002.jpg',$destfile);
        }
       
        unlink($work_dir.'00000001.jpg');
        unlink($work_dir.'00000002.jpg');
        rmdir($work_dir);
    }
    /**
    * extract a frame from vedio files by mmfpeg
    * note:it doesn't work with some .wmv
    */
    function extractFrameByFFmpeg($movie,$destfile)
    {
        $cmd = $this->FFMPEG_PATH."ffmpeg -an -t 0:0:$this->play_time -i \"$movie\" -f singlejpeg \"$destfile\"";
        exec($cmd,$output,$ret);
    }
    /**
    * resize the images
    * @mode:done,preview.
    */
    function resizeImage($info,$mode='done')
    {
        $s_file = $this->srcdir.'/'.$info[file];
        # checking error
        if(!is_file($s_file))
        {
            $this->setError(sprintf('%s doesn\'t exist',$s_file));
            return -1;
        }
        
        # get image info
        $imginfo = getimagesize($s_file);
        $image[width]  = $imginfo[0];
        $image[height] = $imginfo[1];
        $image[type]   = $imginfo[2];

        $return = array();
        $return[width]  = ($width=intval($info[width]))<=0 ? $image[width] : $width;
        $return[height] = ($height=intval($info[height]))<=0 ? $image[height] : $height;

        /**
        * 1:create source images
        */
        $filetype=$this->getExt($info[file]);
        $s_img = $this->input($s_file,$filetype);

        # checking error
        if(!is_resource($s_img))
        {
            $this->setError(sprintf('%s can\'t handled, file corruption or by gd libery issue!',$s_file));
            return -1;
        }
        
        /**
        * 2:create dest images and resized to it
        */
        $d_img = @imagecreatetruecolor($return[width], $return[height]);
        $black = @ImageColorAllocate($d_img, 0, 0 ,0 );
        @imagecopyresized($d_img, $s_img, 0, 0, 0, 0, $return[width], $return[height], $size[0], $size[1]);

        /**
        * 3:output croped image info
        */
        if($mode=='done')
        {
            $success = $this->output($d_img,$filetype,$s_file);
        }
        if($mode=='preview')
        {
            $success = $this->output($d_img,$filetype);
            ImageDestroy($s_img);
            ImageDestroy($d_img);
            exit;
        }
        
        ImageDestroy($s_img);
        ImageDestroy($d_img);
        
        # checking error
        if(!$success)
        {
            $this->setError(sprintf('%s can\'t saved correctly',$s_file));
            return -1;
        }
        
        # get the status of thumbnails, fail or ok?
        if(!is_file($s_file))
        {
            $this->setError(sprintf('Unknown error: image %s doesn\'t exist!',$s_file));
            return -1;
        }
        
        /**
        * 4:return resized image info
        */
        return $return;
    }
    /**
    * crop the images
    * @mode:done,preview.
    */
    function cropImage($info,$mode='done')
    {
        $s_file = $this->srcdir.'/'.$info[file];
        # checking error
        if(!is_file($s_file))
        {
            $this->setError(sprintf('%s doesn\'t exist',$s_file));
            return -1;
        }
        
        $size = getimagesize($s_file);
        if($info[w]>$size[0]) $info[w]=$size[0];
        if($info[h]>$size[1]) $info[h]=$size[1];
        if($info[x]>$size[0]) $info[x]=0;
        if($info[y]>$size[1]) $info[y]=0;
        
        $return = array();
        $return[width]  = $width=intval($info[w]-$info[x]);
        $return[height] = $height=intval($info[h]-$info[y]);
        
        /**
        * 1:create source images
        */
        $filetype=$this->getExt($info[file]);
        $s_img = $this->input($s_file,$filetype);

        # checking error
        if(!is_resource($s_img))
        {
            $this->setError(sprintf('%s can\'t handled, file corruption or by gd libery issue!',$s_file));
            return -1;
        }
        
        /**
        * 2:create dest images and croped to it
        */
        $d_img = @imagecreatetruecolor($width, $height);
        $black = @ImageColorAllocate($d_img, 0, 0 ,0 );
        @imagecopyresized($d_img, $s_img, 0, 0, $info[x], $info[y], $width, $height, $width, $height);

        /**
        * 3:output croped image info
        */
        if($mode=='done')
        {
            $success = $this->output($d_img,$filetype,$s_file);
        }
        if($mode=='preview')
        {
            $success = $this->output($d_img,$filetype);
            ImageDestroy($s_img);
            ImageDestroy($d_img);
            exit;
        }

        ImageDestroy($s_img);
        ImageDestroy($d_img);

        # checking error
        if(!$success)
        {
            $this->setError(sprintf('%s can\'t saved correctly',$s_file));
            return -1;
        }

        # get the status of thumbnails, fail or ok?
        if(!is_file($s_file))
        {
            $this->setError(sprintf('Unknown error: image %s doesn\'t exist!',$s_file));
            return -1;
        }
        
        /**
        * 4:return croped image info
        */
        return $return;
    }
    function watermark($file)
    {
        require_once(dirname(__FILE__)."/transparentWatermark.inc.php");
        $WM=new  transparentWatermark($this->watermark_type);

        $WM->setOptions(array('stamp'=>$this->watermark_stamp,'font'=>$this->watermark_font,'color'=>$this->watermark_color));

	    // set logo's position (optional)
	    $WM->setStampPosition ( transparentWatermarkOnRight, transparentWatermarkOnBottom);

	    // create new image with logo
	    if (!$WM->markImageFile ($file,$file))
        die("Error:".$WM->getLastErrror()."\r\n");
    }
    
    function setError($err)
    {
        $this->errors .= $err."\n";
    }
    
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
    function display($image,$name='')
    {
        @ob_end_clean();
        
        $name = $name == '' ? $image : $name;
        $content_type = $this->GetMime('.'.$this->getExt($image));
        header ("Content-type: $content_type");

        if(in_array($filetype,array('jpg','jpeg','gif','png','bmp')))
        {
            header("Content-Disposition: inline; filename=\"$name\"");
        }
        else
        {
            header("Content-Disposition: attachment; filename=\"$name\"");
        }
        
        $fp = fopen($this->srcdir .'/'.$image,'r');
        while($data=fread($fp,1024))
        {
           echo $data;
        }
        fclose($fp);
        exit;
    }
    function GetMime($type)
    {
        global $mime_mapping;
        return in_array($type, array_keys($mime_mapping)) ? $mime_mapping[$type]:$mime_mapping['.'];

    }
}
?>
