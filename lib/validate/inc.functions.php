<?php

	/** check if given email address is valid.
	 uses complex regular expression to check validity
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @param email the email address to check
	 @return true if email is valid, false otherwise
	 @note no check is performed to confirm address with mailserver
	 */
	function xox_check_email($email) {
    return xox_check_internet_address($email,'email');
	}

	/** check if given url is valid.
	 uses complex regular expression to check validity
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @param url the url to check
	 @return true if url is valid, false otherwise
	 @note no check is performed to confirm if url is reachable
	 */
	function xox_check_url($url) {
    return xox_check_internet_address($url);
	}

  /** check if given internet address (email or url) is valid.
   uses complex regular expression to check validity
   @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
   @param address the address to check
   @param type can be 'email' or 'url' (default)
   @return true if address is valid, false otherwise
   @note no check is performed to confirm if address is reachable
   */
  function xox_check_internet_address($address,$type='url') {

    // allowed and not allowed ascii char masks
    $allascii = 'a-zA-Z0-9\x80-\x9F\xA2-\xA4\xC0-\xD6\xD8-\xF6\xF9-\xFF';
    $notascii = '\x00-\x1f\x7F\xA0\xA1\xA5-\xBF\xD7\xF7\xF8';

    // prepare regex for domain
    $dom_main = "[$allascii][$allascii._-]*\\.";
    $dom_sub  = "(?:[$allascii][$allascii._-]*\\\\.)*";
    $dom_tld  = '[a-zA-Z]{2,6}';
    $domain   = "$dom_sub$dom_main$dom_tld";

    if ( $type=='email' ) {
      // prepare regex for user
      $norm   = "[$allascii][$allascii._-]*";
      $quoted = "\"(?:[^\\\\$notascii\015\012\"]|\\\\[^$notascii])+\"";
      $user   = "(?:$norm|$quoted)";
      // build regex for email
      $regex  = "(?:mailto:)?$user\@$domain";
    } else {
      // build regex for url
      $regex  = "(http[s]?:\/\/)?$domain(\/.*)?";
    }

    #echo "preg_match(\"/^$regex$/\",\"$address\") = ";

    return preg_match("/^$regex$/",$address);
  }

	function xox_check_date($date) {
		$date = trim($date);
		if (empty($date)||strpos(' '.$date,'0000-00-00')>0) return false;
		if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',$date)) return true;
		if ( preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$date)) return true;
		if ( preg_match('/^(\d{1,2}).(\d{1,2}).(\d{4})$/',$date)) return true;
		return false;
	}

?>
