<?php

	require_once('../validate/inc.functions.php');

	define('FORMFIELD_DEFAULT_TYPE',	 0);
	define('FORMFIELD_LONGTEXT_TYPE',	 1);
	
	define('FORMFIELD_INTEGER_TYPE',	 2);
	define('FORMFIELD_NUMERIC_TYPE',	 3);
	define('FORMFIELD_CURRENCY_TYPE',	 4);
	
	define('FORMFIELD_DATE_TYPE',			 5);
	define('FORMFIELD_SHORTDATE_TYPE', 6);
	
	define('FORMFIELD_TIME_TYPE',			 7);
	define('FORMFIELD_SHORTTIME_TYPE', 8);

	define('FORMFIELD_EMAIL_TYPE',		 9);
	define('FORMFIELD_URI_TYPE',			10);
	
	
	/*****************************************************************
		wrapper for one field in a form
															 19.09.2004 23:21
	*****************************************************************/
	class cFormfield
	{
		var $name;
		
		var $value;
		var $value_type;

		var $min_len;
		var $max_len;

		var $min_val;
		var $max_val;

		var $mandatory;

		var $preg_rule;

		var $error;
		
		function cFormfield($name,$value='',$mandatory=FALSE,$type=FORMFIELD_DEFAULT_TYPE) {
			$this->name = $name;
			$this->value = $value;
			$this->value_type = $type;
			$this->min_len = 0;
			$this->max_len = 0;
			$this->min_val = 0;
			$this->max_val = 0;
			$this->mandatory = $mandatory;
			$this->preg_rule = '';
		}

		function isValid() {
			#echo "checking field $this->name\n";
			#print_r($this);
			$this->error = '';
			$no_errors = TRUE;
			if ( !empty($this->value) ) {
				if ( $this->preg_rule == '' ) {
					switch ( $this->value_type ) {
						
						case FORMFIELD_EMAIL_TYPE:
							$no_errors = xox_check_email($this->value);
							if (!$no_errors) $this->error = 'INVALID_EMAIL_TYPE';
							break;

						case FORMFIELD_URI_TYPE:
							$no_errors = xox_check_url($this->value);
							if (!$no_errors) $this->error = 'INVALID_URI_TYPE';
							break;
	
						case FORMFIELD_INTEGER_TYPE:
							$no_errors = is_integer($this->value);
							if (!$no_errors) $this->error = 'INVALID_INTEGER_TYPE';
							break;

						case FORMFIELD_NUMERIC_TYPE:
						case FORMFIELD_CURRENCY_TYPE:
							$no_errors = is_numeric($this->value);
							if (!$no_errors) $this->error = 'INVALID_NUMERIC_TYPE';
							break;
	
						case FORMFIELD_DATE_TYPE:
							$no_errors = (preg_match('/^[1-9][0-9][0-9][0-9]-[0-1]?[0-9]-[0-3]?[0-9]$/',$this->value)
												 || preg_match('/^[0-3]?[0-9]\.[0-1]?[0-9].[1-9][0-9][0-9][0-9]$/',$this->value));
							if (!$no_errors) $this->error = 'INVALID_DATE_TYPE';
							break;

						case FORMFIELD_SHORTDATE_TYPE:
							$no_errors = (preg_match('/^[0-1]?[0-9]-[0-3]?[0-9]$/',$this->value)
												 || preg_match('/^[0-3]?[0-9]\.[0-1]?[0-9]$/',$this->value));
							if (!$no_errors) $this->error = 'INVALID_SHORTDATE_TYPE';
							break;
	
						case FORMFIELD_TIME_TYPE:
							$no_errors = preg_match('/[0-2]?[0-9]:[0-9][0-9]:[0-9][0-9]/',$this->value);
							if (!$no_errors) $this->error = 'INVALID_TIME_TYPE';
							break;
	
						case FORMFIELD_SHORTTIME_TYPE:
							$no_errors = preg_match('/[0-2]?[0-9]:[0-9][0-9]/',$this->value);
							if (!$no_errors) $this->error = 'INVALID_SHORTTIME_TYPE';
							break;
	
					}
				} else {
					$no_errors = preg_match($this->preg_rule,$this->value);
					if (!$no_errors) $this->error = 'INVALID_BREAKS_RULE';
				}

				if ( $no_errors && !($this->min_val==0 && $this->max_val==0) ) {
					$no_errors = ($this->value >= $this->min_val && $this->value <= $this->max_val);
					if (!$no_errors) $this->error = 'INVALID_VALUE';
				}
				if ( $no_errors && !($this->min_len==0 && $this->max_len==0) ) {
					$no_errors = (strlen($this->value) >= $this->min_len && strlen($this->value) <= $this->max_len);
							if (!$no_errors) $this->error = 'INVALID_LENGTH';
				}
			} elseif ( $this->mandatory ) {
				$no_errors = FALSE;
				if (!$no_errors) $this->error = 'INVALID_EMPTY';
			}
			#if (!$no_errors) echo '=== '.$this->error.' ===';
			return $no_errors;
		}

	}	// finish class cFormfield

	
	/*****************************************************************
		a single form containing a collection of form fields
															 19.09.2004 23:30
	*****************************************************************/
	class cForm
	{
		var $name;
		var $fields;
		var $errors;

		function cForm($name='') {
			$this->name = $name;
			$this->fields = array();
		}

		function addFields($name,$value='',$mandatory=FALSE,$type=FORMFIELD_DEFAULT_TYPE) {
			if ( is_array($name) ) {
				foreach ( $name as $key=>$value ) {
					$this->fields[$key] = new cFormfield($key,$value);
				}
			} else {
				$this->fields[$name] = new cFormfield($name,$value,$mandatory,$type);
			}
		}

		function isValid() {
			$this->errors = array();
			$no_errors = TRUE;
			foreach( $this->fields as $key=>$field) {
				if (!$field->isValid()) {
					$no_errors = FALSE;
					$this->errors[$field->name] = $field->error;
				}
			}
			return $no_errors;
		}

		function getErrors($pre='',$post="\n") {
			$errors = '';
			foreach($this->errors as $key=>$error) {
				$errors.="$pre$error$post";
			}
			return $errors;
		}
		
		function set($name,$value='',$autoadd=FALSE) {
			if ( is_array($name) ) {
				foreach ( $name as $key=>$value ) {
					if ( !isset($this->fields[$key]) ) {
						if ($autoadd && $this->onchange($key,'',$value))
							$this->fields[$key] = new cFormfield($key,$value);
					} else {
						if ( $this->onchange($key,$this->fields[$key]->value,$value) )
							$this->fields[$key]->value = $value;
					}
				}
			} else {
				if ( !isset($this->fields[$name]) ) {
					if ($autoadd && $this->onchange($name,'',$value))
						$this->fields[$name] = new cFormfield($name,$value);
				} else {
					if ( $this->onchange($name,$this->fields[$name]->value,$value) )
						$this->fields[$name]->value = $value;
				}
			}
		}

		function onchange($name,$old_val,$new_val) {
			return TRUE;
		}

	}	// finish class cForm


	/*****************************************************************
		implements a minimal contact form
															 19.09.2004 23:36
	*****************************************************************/
	class cContactform extends cForm
	{
		function cContactform($name='') {
			
			$this->name = $name;
			$this->fields = array();

			$this->addFields('gender',		1,	TRUE,	FORMFIELD_INTEGER_TYPE);
			$this->addFields('company',		'',	FALSE);
			$this->addFields('firstname',	'',	TRUE);
			$this->addFields('lastname',	'',	TRUE);
			$this->addFields('street',		'',	TRUE);
			$this->addFields('zip',				'',	TRUE);
			$this->addFields('city',			'',	TRUE);
			$this->addFields('country',		'de',	TRUE);
			$this->addFields('telephone',	'',	FALSE);
			$this->addFields('email',			'',	TRUE,FORMFIELD_EMAIL_TYPE);
			$this->addFields('comments',	'',	TRUE,FORMFIELD_LONGTEXT_TYPE);
			
			
			$f =& $this->fields['gender'];
			$f->min_val = 1;
			$f->max_val = 3;

			$f =& $this->fields['zip'];
			$f->min_len = 6;
			$f->max_len = 8;

		}
		
		function onchange($name,$old_val,$new_val) {
			if ( $name=='gender' ) {
				if ( $new_val == 3 ) {
					$this->fields['company']->mandatory = TRUE;
					$this->fields['firstname']->mandatory = FALSE;
					$this->fields['lastname']->mandatory = FALSE;
				} else {
					$this->fields['company']->mandatory = FALSE;
					$this->fields['firstname']->mandatory = TRUE;
					$this->fields['lastname']->mandatory = TRUE;
				}
			}
			return TRUE;
		}

	}	// finish class cContactform

	echo '<pre>';
	$tf = new cContactform('testform');
	#print_r($tf);

	$tf->set(array('email'=>'daniel@hexerei.net','comments'=>'What about','salery'=>'251','gender'=>'2'));

	#print_r($tf);

	echo ( $tf->isValid() ) ? 'VALID' : 'INVALID';
	echo "\n";
	echo $tf->getErrors()."\n";
	print_r($tf);
	echo '</pre>';

?>