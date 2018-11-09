<?php
/**
 * Get file Function only
 */
/**
 * Set the header into close
 */
function close_header() {
	@header("HTTP/1.1 503 Service Temporarily Unavailable");
	@header("Status: 503 Service Temporarily Unavailable");
	@header("Retry-After: 120");
	@header("Connection: close");
}
/**
 * Descrypt Function for Options
 *
 * @param string that need to descrypt - $str_encrypted
 * @param decrypt code - $encryption_string
 * @return descrpty String
 */
function decryptStr($str_encrypted,$encryption_string=null) {
	$password = "";
    $encryption_string = $encryption_string==null? '~!@#$%^&*()_+|' : $encryption_string;
	if ($encryption_string % 2 == 1) { // we need even number of characters
		$encryption_string .= $encryption_string{0};
	}
	for ($i=0; $i < strlen($str_encrypted); $i += 2) { // decrypts two bytes - one character at once
		$password .= chr(hexdec(substr($encryption_string, $i % strlen($encryption_string), 2)) ^ hexdec(substr($str_encrypted, $i, 2)));
	}
	return $password;
}
?>