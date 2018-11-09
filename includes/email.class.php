<?php
/*
+--------------------------------------------------------------------------
|   Mega File Hosting Script v1.1
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
* Email
*/
define('MAIL_LOW_PRIORITY', 4);
define('MAIL_NORMAL_PRIORITY', 3);
define('MAIL_HIGH_PRIORITY', 2);

class Email
{
	var $vars, $msg, $extra_headers, $replyto, $from, $subject, $encoding;
	var $addresses = array();

	var $mail_priority = MAIL_NORMAL_PRIORITY;
	var $html_email = 'test/plain';
    var $admin_email = true;
	var $content_language = 'utf-8';
	var $tpl_msg = array();
	var $fromemail;

	function Email($tpl_dir)
	{
        $this->template = new Template( $tpl_dir );

        $this->template->cache=0;

		$this->subject = '';
	}
   	// Resets all the data (address, template file, etc etc) to default
	function reset()
	{
		$this->addresses = array();
		$this->vars = $this->msg = $this->extra_headers = $this->replyto = $this->from = $this->encoding = '';
		$this->mail_priority = MAIL_NORMAL_PRIORITY;
	}

	// Sets an email address to send to
	function to($address, $realname = '')
	{
		$this->fromemail = trim($address);
	}

	function cc($address, $realname = '')
	{
		$pos = isset($this->addresses['cc']) ? sizeof($this->addresses['cc']) : 0;
		$this->addresses['cc'][$pos]['email'] = trim($address);
		$this->addresses['cc'][$pos]['name'] = trim($realname);
	}

	function bcc($address, $realname = '')
	{
		$pos = isset($this->addresses['bcc']) ? sizeof($this->addresses['bcc']) : 0;
		$this->addresses['bcc'][$pos]['email'] = trim($address);
		$this->addresses['bcc'][$pos]['name'] = trim($realname);
	}

	function replyto($address)
	{
		$this->replyto = trim($address);
	}

	function from($address)
	{
		$this->from = trim($address);
	}

	// set up subject for mail
	function subject($subject = '')
	{
		$this->subject = trim($subject);
	}

	// set up extra mail headers
	function headers($headers)
	{
		$this->extra_headers .= trim($headers) . "\n";
	}

	function set_mail_priority($priority = MAIL_NORMAL_PRIORITY)
	{
		$this->mail_priority = $priority;
	}
 
	// Send the mail out to the recipients set previously in var $this->address
	function send($handle,$method = NOTIFY_EMAIL)
	{
		global $LANG;

        $this->msg = $this->template->assign_var_from_handle('', $handle,1,1);


		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $LANG['NO_SUBJECT']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $LANG['NO_SUBJECT']);
		}
		if (preg_match('#^(Charset:(.*?))$#m', $this->msg, $match))
		{
			$this->encoding = (trim($match[2]) != '') ? trim($match[2]) : trim($LANG['Charset']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->encoding = trim($LANG['Charset']);
		}
        if (preg_match('#^(HtmlEmail:(.*?))$#m', $this->msg, $match))
		{
			$this->html_email = (trim($match[2]) != '') ? trim($match[2]) : trim($LANG['HtmlEmail']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->html_email = 'text/html';
			
		}
        if (preg_match('#^(Content-language:(.*?))$#m', $this->msg, $match))
		{
			$this->content_language = (trim($match[2]) != '') ? trim($match[2]) : trim('utf-8');
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->content_language = 'utf-8';
		}
		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		$result = $this->msg_email();

		$this->reset();
		return $result;
	}

	function error($type, $msg)
	{
        //echo('critical', 'LOG_ERROR_' ,$type, $msg);
	}


	function msg_email()
	{
		$to = $cc = $bcc = '';
		// Build to, cc and bcc strings
		foreach ($this->addresses as $type => $address_ary)
		{
            if ($type == 'im')
			{
				continue;
			}

			foreach ($address_ary as $which_ary)
			{
				$$type .= (($$type != '') ? ', ' : '') . (($which_ary['name'] != '') ?  '"' . $this->mail_encode($which_ary['name'], $this->encoding) . '" <' . $which_ary['email'] . '>' : $which_ary['email']);
                $$type .= (($$type != '') ? ', ' : '') .  $which_ary['email']; //(($which_ary['name'] != '') ?  '"' . $this->mail_encode($which_ary['name'], $this->encoding) . '" <' . $which_ary['email'] . '>' : $which_ary['email']);
			}
		}
		if (empty($this->replyto))
		{
			$this->replyto = '<' . $this->admin_email . '>';
		}

		if (empty($this->from))
		{
            $this->from = '<' . $this->admin_email . '>';
		}

		// Build header
		$headers = 'From: ' . $this->from . "\n";
		$headers .= ($cc != '') ? "Cc: $cc\n" : '';
		$headers .= ($bcc != '') ? "Bcc: $bcc\n" : '';
		$headers .= 'Reply-to: ' . $this->replyto . "\n";
		$headers .= 'Return-Path: <' . $this->admin_email . ">\n";
		$headers .= 'Sender: <' . $this->admin_email . ">\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= 'Message-ID: <' . md5((time())) . "@" . $_SERVER['HTTP_HOST'] . ">\n";
		$headers .= 'Date: ' . gmdate('D, d M Y H:i:s T', time()) . "\n";
		$headers .= "Content-type: {$this->html_email}; charset={$this->encoding}\n";
		$headers .= "Content-Language: {$this->content_language}\n";
		$headers .= "Content-transfer-encoding: 8bit\n";
		$headers .= "X-Priority: {$this->mail_priority}\n";
		$headers .= 'X-MSMail-Priority: ' . (($this->mail_priority == MAIL_LOW_PRIORITY) ? 'Low' : (($this->mail_priority == MAIL_NORMAL_PRIORITY) ? 'Normal' : 'High')) . "\n";
		$headers .= "X-Mailer: MFHS\n";
		$headers .= "X-MimeOLE: MFHS\n";
		$headers .= ($this->extra_headers != '') ? $this->extra_headers : '';

		// Send message ... removed $this->encode() from subject for time being
		$err_msg = '';

		$result = @mail($this->fromemail, $this->subject, implode("\n", preg_split("/\r?\n/", wordwrap($this->msg))), $headers);
		
        //echo $mail_to, $this->subject,$headers;
        //print_r($this->addresses);
		if (!$result)
		{
			$message = '<u>EMAIL ERROR</u> [ PHP ]<br /><br />' . $err_msg . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . ((!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF']) . '<br />';
            $this->error('EMAIL', $message);
			return false;
		}
		return true;
	}
    /**
    * Encodes the given string for proper display for this encoding ... nabbed
    * from php.net and modified. There is an alternative encoding method which
    * may produce less output but it's questionable as to its worth in this
    * scenario IMO
    */
    function mail_encode($str, $encoding)
    {
    	if ($encoding == '')
    	{
    		return $str;
    	}

    	// define start delimimter, end delimiter and spacer
    	$end = "?=";
    	$start = "=?$encoding?B?";
    	$spacer = "$end\r\n $start";

    	// determine length of encoded text within chunks and ensure length is even
    	$length = 75 - strlen($start) - strlen($end);
    	$length = floor($length / 2) * 2;

    	// encode the string and split it into chunks with spacers after each chunk
    	$str = chunk_split(base64_encode($str), $length, $spacer);

    	// remove trailing spacer and add start and end delimiters
    	$str = preg_replace('#' . preg_quote($spacer, '#') . '$#', '', $str);

    	return $start . $str . $end;
    }
}
?>
