<?php
	/*****************************************************************
	 *****************************************************************

	 Utility class to protect forms from spam-attack

	 @file xox/lib/forms/class.spamstop.php

	 @created 17.08.2007 12:37
	 @version 17.08.2007 12:37

	 @author dvorhauer

	 *****************************************************************
	 *****************************************************************

	 XOX PHP Library 2.0
	 (c) 1997-2007 hexerei software creations

	 This library is not yet free software. If you are a member of the
	 hexerei software development team, you have a non-exclusive right
	 to use this library for projects that are either for the hexerei
	 or one of it's customers under the label of hexerei. You may also
	 use the library for your own purposes if hexerei has granted you
	 a license to do so.

	 If you have received a copy of this source without explicit right
	 or licence from hexerei, then you may not modify it or reuse it
	 in any form without prior notice to hexerei. Most likely you have
	 received a copy along with an implemented application from the
	 hexerei or one of its licensees and herby have no right to reuse,
	 modify or publish this code under any terms other than the rights
	 you have acquired for the given application.

	 This library is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY of FITNESS FOR A PARTICULAR PURPOSE.

	 Under no circumstances may you remove this header and or any
	 copyright notice which marks hexerei software creations as the
	 owner and author of this library and its sources.

	 Daniel Vorhauer
	 daniel@hexerei.net

	*/

	/** require xox logger for logging of spam attacks */
	require_once(XOX_LIB.'/security/class.logger.php');

	/*****************************************************************
	*****************************************************************/

	class SpamStop
	{
		/** servername on which the form resides */
		var $servername;

		/** method of form */
		var $method;

		/** true if form passed spamtest */
		var $validated;

		/** not allowed strings to check for */
		var $badStrings;

		/** logfile to log spam attack to */
		var $logfile;

		/** id to show in logfile */
	 	var $logid;

		/**
		 * constructs object with given settings
		 * @param servername the name of the server on which the form resides
		 * @param method the action method of the form (POST or GET)
		 */
		function SpamStop($servername,$method='POST') {

			$this->servername	= $servername;
			$this->method 		= strtoupper($method);
			$this->validated 	= false;
			$this->logfile		= '';
			$this->logid			= '';

			$this->badStrings = array(
				"Content-Type:",
				"MIME-Version:",
				"Content-Transfer-Encoding:",
				"bcc:",
				"cc:",
				"<a href="
			);
		}

		/**
		 * set logger filename and id
		 */
		function SetLog($filename, $id='SPAMSTOP') {
			$this->logfile 	= $filename;
			$this->logid 		= $id;
		}

		/**
		 * validate spamfree contents of form
		 * @return true if form is spam free
		 */
		function validate($vals=0) {

			// optimistic approach
			$this->validated = true;

			/**
			 * first check if context is valid
			 * - user agent must be set
			 * - request method should not vary
			 * - restrict to single server
			 */
			if ( !isset($_SERVER['HTTP_USER_AGENT'])
				|| $_SERVER['REQUEST_METHOD'] != $this->method
				|| !eregi($this->servername,$_SERVER['HTTP_REFERER'])
			) {

				$this->validated = false;

			} else {

				/**
				 * assure we have values
				 */
				if ( $vals == 0 ) {
					$vals = array();
					switch($this->method) {
						case 'POST':	$vals = $_POST;	break;
						case 'GET':		$vals = $_GET;	break;
					}
				}

				/**
				 * check all fields for any bad string match
				 */
				foreach($vals as $k => $v) {
	 				foreach($this->badStrings as $v2)
				 		if (strpos($v, $v2) !== false) { $this->validated = false; break; }
					if ( !$this->validated ) break;
				}

				/**
				 * log spam attack
				 */
				if ( !$this->validated && !empty($this->logfile) ) {
					// build message
					$msg = '';
					foreach ($vals as $key=>$value)
						$msg .= substr('          '.$key,-10)
							.': '.stripslashes($value)."\r\n";
					// log to file
					$logger = new xoxLogger($this->logfile, 6);
					$logger->setID($this->logid);
					$logger->message("Detected Spam-Attack!\r\n".$msg);
				}
			}

			// default exit
			return $this->validated;
		}

	}	// finish class spamstop

?>
