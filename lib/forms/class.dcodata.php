<?php
	/*****************************************************************
	 *****************************************************************

	 Base DCO (Data Container Object) data class for the DCO helpers

	 @file xox/lib/forms/inc.dcodata.php

	 @created 25.08.2006 17:07
	 @version 25.08.2006 17:07

	 @see xox/lib/database/class.cdbobject.php
	 @see xox/lib/forms/class.dcoform.php
	 @see xox/lib/forms/class.dcolist.php

	 @author dvorhauer

	 *****************************************************************
	 *****************************************************************

	 XOX PHP Library 2.0
	 (c) 1997-2006 hexerei software creations

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

	/*** data types *************************************************/

	define('DCO_DATA_TEXT', 		0);
	define('DCO_DATA_INTEGER',	1);
	define('DCO_DATA_NUMBER',		2);
	define('DCO_DATA_EMAIL', 		3);
	define('DCO_DATA_URL', 			4);
	define('DCO_DATA_DATE', 		5);
	define('DCO_DATA_BOOL', 		6);
	define('DCO_DATA_PLAIN', 		7);
	define('DCO_DATA_NULL', 		'__N_U_L_L__');

	// possible field errors
	define('DCO_DATA_ERR_EMPTY', 		1);
	define('DCO_DATA_ERR_LENGTH', 		2);
	define('DCO_DATA_ERR_RANGE', 		3);
	define('DCO_DATA_ERR_FORMAT', 		4);

	/*****************************************************************
	*****************************************************************/
	class dcoData
	{
		/** name of the field - should match dco field name */
		var $name;
		/** type of field controls validation */
		var $type;
		/** true if field is mandatory */
		var $mandatory;
		/** title can be used for international forms */
		var $title;

		/** set true to check length of field value */
		var $checklength;
		/** maximum length in charactars */
		var $maxlength;
		/** minimum length in characters */
		var $minlength;

		/** set true to check range of field value */
		var $checkrange;
		/** minimum value */
		var $minvalue;
		/** maximum value */
		var $maxvalue;

		/** regular expression to validate */
		var $regex;

		/** true if field has been validated */
		var $validated;
		/** the reason why validation failed */
		var $reason;
		/** the value of the field */
		var $value;

		/** set true to automatically trim leading and trailing spaces */
		var $autotrim;
		/** set true to automatically cut to maxlength + traillength */
		var $autocut;
		/** auto format to html */
		var $autohtml;
		/** auto format html entities */
		var $autoentities;
		/** add trail when value > maxlength + traillength */
		var $autocut_trail='...';
		/** charset encoding (defaults to ISO-8859-1) */
		var $charset_encoding = '';

		/**
		 * constructs a single field used for the dco form
		 * @param name the name of the field
		 * @param type one of the predefined dco field types
		 * @param mandatory set true if field is mandatory
		 * @param title field title for international forms
		 */
		function dcoData($name,$type=DCO_DATA_TEXT,$mandatory=false,$title='') {

			$this->name 				= $name;
			$this->type 				= $type;
			$this->mandatory 		= $mandatory;
			$this->title 				= $title;

			$this->checklength 	= false;
			$this->minlength 		= DCO_DATA_NULL;
			$this->maxlength 		= DCO_DATA_NULL;
			$this->checkrange 	= false;
			$this->minvalue 		= DCO_DATA_NULL;
			$this->maxvalue 		= DCO_DATA_NULL;
			$this->regex 				= DCO_DATA_NULL;
			$this->validated 		= false;
			$this->reason 			= 0;
			$this->value 				= '';
			$this->autotrim 		= true;
			$this->autocut 			= false;
			$this->autohtml			= true;
			$this->autoentities		= true;
			$this->charset_encoding = '';
		}

		/**
		 * set the value of the field performing instant validation
		 */
		function set($value) {
			$this->value = $value;
			if ($this->type == DCO_DATA_TEXT) {
				if ($this->autotrim) {
					if (is_array($value)) {
						$trimmed = array();
						foreach($value as $key=>$val) {
							$trimmed[$key] = $val;
						}
						$this->value = $trimmed;
					} else {
						$this->value = trim($value);
					}
				}
				if ($this->autocut) {
					$len = strlen($this->value);
					if ($len > $this->maxlength) {
						if ($len > $this->maxlength + strlen($this->autocut_trail)) {
							$this->value = substr($value,0,$this->maxlength);
						} else {
							$this->value = substr($value,0,$this->maxlength+strlen($this->autocut_trail));
						}
					}
				}
			}
			return $this->validate();
		}

		function get() {
			switch ( $this->type ) {
				case DCO_DATA_BOOL:
					return ($this->value) ? true : false;
				case DCO_DATA_INTEGER:
					return intval($this->value);
				case DCO_DATA_NUMBER:
					return is_numeric($this->value) ? numberformat($this->value) : $this->value;
				case DCO_DATA_DATE:
					return strpos($this->value,'-') ? shortdate($this->value) : $this->value;
				case DCO_DATA_PLAIN:
					return $this->value;
				default:
					if (is_array($this->value)) {
						$sval = stripslashes(implode('|',$this->value));
						if ($this->autohtml) {
							if ($this->autoentities) $sval = htmlentities($sval,ENT_QUOTES,$this->charset_encoding);
							$sval = nl2br($sval);
						}
						return explode('|',$sval);
					} else {
						$sval = stripslashes($this->value);
						if ($this->autohtml) {
							if ($this->autoentities) $sval = htmlentities($sval,ENT_QUOTES,$this->charset_encoding);
							$sval = nl2br($sval);
						}
						return $sval;
					}
			}
		}

		/**
		 * validate contents of field
		 * @return true if field is valid
		 */
		function validate() {

			// optimistic approach
			$this->validated = true;

			// get length of value
			$len = ($this->value==DCO_DATA_NULL) ? 0 : (is_array($this->value)) ? count($this->value) : strlen($this->value);

			// check mandatory
			if ( $this->mandatory && $len < 1 ) {
				$this->validated = false;
				$this->reason = DCO_DATA_ERR_EMPTY;
				return false;
			}

			// check length
			if ( $this->checklength && ($len>0)
				&&( $this->minlength != DCO_DATA_NULL && $len < $this->minlength )
				||( $this->maxlength != DCO_DATA_NULL && $len > $this->maxlength )) {
				$this->validated = false;
				$this->reason = DCO_DATA_ERR_LENGTH;
				return false;
			}

			// check range
			if ( $this->checkrange && ($len>0)
				&&( $this->minvalue != DCO_DATA_NULL && $this->value < $this->minvalue )
				||( $this->maxvalue != DCO_DATA_NULL && $this->value > $this->maxvalue )) {
				$this->validated = false;
				$this->reason = DCO_DATA_ERR_RANGE;
				return false;
			}

			// only check formats when a value is set
			if ($len > 0) {
				// regular expression has precedence to default regex for email and url
				if ( $this->regex != DCO_DATA_NULL && !empty($this->regex) ) {
					$this->validated = preg_match($this->regex,$this->value);
				// check default regex for email field
				} elseif ( $this->type == DCO_DATA_EMAIL ) {
					$this->validated = xox_check_email($this->value);
				// check default regex for url field
				} elseif ( $this->type == DCO_DATA_URL ) {
					$this->validated = xox_check_url($this->value);
				// check default regex for url field
				} elseif ( $this->type == DCO_DATA_DATE ) {
					$this->validated = xox_check_date($this->value);
				}
				// if validation failed here, then it was a format error
				if ( !$this->validated ) {
					$this->reason = DCO_DATA_ERR_FORMAT;
				}
			}

			// default exit
			return $this->validated;
		}

	}	// finish class dcoField

?>