<?php

/**
 1: File Download
 	$object = new downloader;
 	$object->set_byfile($filename); //Download from a file
 	$object->use_resume = true; //Enable Resume Mode
 	$object->download(); //Download File
**/

class httpdownloadjoin {

	var $data = null;
	var $data_len = 0;
	var $data_mod = 0;
	var $data_type = 0;
	var $data_section = 0; //section download
	/**
	 * @var ObjectHandler
	 **/
	var $handler = array('auth' => null);
	var $use_resume = true;
	var $use_autoexit = false;
	var $filename = null;
	var $mime = null;
	var $bufsize = 8192;
	var $seek_start = 0;
	var $seek_end = -1;
	
	/**
	 * Total bandwidth has been used for this download
	 * @var int
	 */
	var $bandwidth = 0;
	/**
	 * Speed limit
	 * @var float
	 */
	var $speed = 1048576;
	/**
	 * Part Size - Extreamly useful for memory managements!!
	 */
	var $part_size = 1048576;
	var $size_divide = 32; // fread, echo, and output time divide by 64
	var $temp_read_size = 32768;
	var $echo_divide = 64;
	var $temp_echo_size = 16384;
	var $output_time = 15625; // in microsecond
	
	/*-------------------
	| Download Function |
	-------------------*/
	/**
	 * Check authentication and get seek position
	 * @return bool
	 **/
	function initialize() {
		global $HTTP_SERVER_VARS;
		
		if ($this->mime == null) $this->mime = "application/octet-stream"; //default mime
		
		if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE']))
		{
			
			if (isset($HTTP_SERVER_VARS['HTTP_RANGE'])) $seek_range = substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes='));
			else $seek_range = substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));
			
			$range = explode('-',$seek_range);
			
			if ($range[0] > 0)
			{
				$this->seek_start = intval($range[0]);
			}
			
			if ($range[1] > 0) $this->seek_end = intval($range[1]);
			else $this->seek_end = -1;
			
			if (!$this->use_resume)
			{
				$this->seek_start = 0;
			}
			else
			{
				$this->data_section = 1;
			}
			
		}
		else
		{
			$this->seek_start = 0;
			$this->seek_end = -1;
		}
		
		return true;
	}
	/**
	 * Send download information header
	 **/
	function header($size,$seek_start=null,$seek_end=null) {
		header("Pragma: public"); header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
		header('Content-type: ' . $this->mime);
		header('Content-Disposition: attachment'); // do it later
		header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , $this->data_mod));
		header("Content-Transfer-Encoding: binary");
		
		// force download dialog
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		
		if ($this->data_section && $this->use_resume)
		{
			header("HTTP/1.0 206 Partial Content");
			header("Status: 206 Partial Content");
			header('Accept-Ranges: bytes');
			header("Content-Range: bytes $seek_start-$seek_end/$size");
			header("Content-Length: " . ($seek_end - $seek_start + 1));
		}
		else
		{
			header("Content-Length: $size");
		}
	}
	
	/**
	 * Start download
	 * @return bool
	 **/
	function download() {
		if (!$this->initialize()) return false;
		
		$seek = $this->seek_start;
		$speed = $this->speed;
		$bufsize = $this->bufsize;
		$packet = 1;
		
		// set memory management variable
		$this->temp_read_size = $this->part_size / $this->size_divide;
		$this->temp_echo_size = $this->part_size / $this->echo_divide;
		$this->output_time = 1000000 / $this->echo_divide;
		
		//do some clean up
		@ob_end_clean();
		$old_status = ignore_user_abort(true);
		@set_time_limit(0);
		$this->bandwidth = 0;
		
		$size = $this->data_len; // set file size
		$total_parts = ceil($size / $this->part_size); // set total part
			
		if ($seek > ($size - 1)) $seek = 0; // check seek larger then file size if yes then go to start
		
		$start_part = floor($seek / $this->part_size); // set the current part
		
		if ($this->filename == null) $this->filename = basename($this->data); // set real file name
		
		if ($this->seek_end < $seek) $this->seek_end = $size - 1; // if not seek no more then size, then set the eof = size
		
		$part_seek = $seek % $this->part_size; // set the seek for part
		$part_seek_end = $this->part_size - 1; // set the part eof
			
		$this->header($size,$seek,$this->seek_end); //always use the last seek
		$size = $this->seek_end - $seek + 1; // change the size into smaller
			
		while (!(connection_aborted() || connection_status() == 1) && $total_parts > $start_part && $size > 0) // if continue download or still have data to echo
		{
			$res = fopen(($this->data.".".sprintf("%03d",$start_part)),'rb'); // open the file part
			if ($part_seek) fseek($res , $part_seek); // seek the position to file
			
			if ($part_seek) // start from middle of file
			{ 
				$remain_part_size = filesize($this->data.".".sprintf("%03d",$start_part)) - $part_seek;
				$size -= $remain_part_size;
				$part_seek = 0;
				$this->bandwidth += $remain_part_size;
			}
			else 
			{
				if ($size < $this->part_size) // go to last file with filesize different with other file
				{ 
					$size = filesize($this->data.".".sprintf("%03d",$start_part));
					$size = 0;
					$this->bandwidth += $size;
				}
				else // download a file from start to end
				{ 
					$size -= $this->part_size;
					$this->bandwidth += $this->part_size;
				}
			}
			
			// start output all the binary to user
			if (!$this->while_fread($res,$this->temp_read_size,$this->temp_echo_size,$this->output_time)) {
				// check again
				if (connection_aborted() || connection_status() == 1) {
					return false;
				}
			}
			
			//$this->while_fread($res,$this->temp_read_size,$this->temp_echo_size,$this->output_time);
			
			fclose($res); // close the file
			unset($res);
			unset($temp_res);
			
			$start_part += 1; // continue next part
		}		
		
		if ($this->use_autoexit) exit();
		
		//restore old status
		ignore_user_abort($old_status);
		set_time_limit(ini_get("max_execution_time"));
		
		return true;
	}
	
	/**
	 * Set Type of Download to Join
	 *
	 * @param string $dir - path point to downloaded file
	 * @return return true -> found the path file, return false -> not found
	 */
	function set_byjoin($dir) {
		if (is_readable($dir.".000") && is_file($dir.".000")) {
			$this->data = $dir;
			$this->data_type = 0;
			return true;
		} else return false;
	}
	
	/**
	 * File Read with Fix Buffer Size
	 *
	 * @param opened file $file_resouce
	 * @param each time fread size to variable $read_size
	 * @param each time echo output size $echo_size
	 * @param sleep waiting in microsecond $output_time
	 * @return true -> continue until end of file, false -> disconnected
	 */
	/*
	function while_fread($file_resouce,$read_size=32768,$echo_size=16384,$output_time=15625) {
		while (!feof($file_resouce)) {
			if (connection_aborted() || connection_status() == 1) {	
				return false;
			}
			else {
				$temp_binary = fread($file_resouce,$read_size);
				$this->echobig($temp_binary,$echo_size,$output_time);
			}
			return true;
		}
	}
	*/
	function while_fread($file_resouce,$read_size=32768,$echo_size=16384,$output_time=15625) {
		while (!feof($file_resouce)) {
			$temp_binary = fread($file_resouce,$read_size);
			if (!$this->echobig($temp_binary,$echo_size,$output_time)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Output binary by smaller size (split) from time to time
	 * 16384 byte = 0.015625 MB = 1/64 MB and 15625 micro second = 1/64 second
	 * so 1 MB Output = 1 Second
	 * @param binary string $temp_binary
	 * @param echo size $echo_size
	 * @param output time $output_time
	 * @return true -> echo finish, false -> echo false or disconnect
	 */
	function echobig($temp_binary,$echo_size=16384,$output_time=15625)
	{
		@ob_start();
		// suggest doing a test for Integer & positive bufferSize
		for ($chars=strlen($temp_binary)-1 , $start=0 ; $start <= $chars ; $start += $echo_size) {
			// add connection abort check -> if yes then exit
			if (connection_aborted() || connection_status() == 1) {	
				return false; 
			}
			else { 
				echo substr($temp_binary,$start,$echo_size);	
				flush(); 
				@ob_end_flush(); 
				usleep($output_time);
			}
		}
		@ob_end_clean();
		return true;
	}
}

?>