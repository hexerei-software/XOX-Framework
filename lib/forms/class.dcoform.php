<?php
	/*****************************************************************************
	 *****************************************************************************

	 This helper class can be used to generate automatic validating forms for
	 instances of cdbobjectbase objects (DCO Data Container Object)

	 @file /lib/bin/class.dcoform.php
	 @created 25.08.2006 17:07
	 @version 25.08.2006 17:07

	 @author dvorhauer

	 *****************************************************************************
	 *****************************************************************************

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

	require_once(XOX_LIB.'/forms/class.dcodata.php');
	require_once(XOX_LIB.'/validate/inc.functions.php');
	/*** strings *****************************************************/

	/** error string when no template was set */
	if (!defined('DCO_FORM_NO_TEMPLATE')) define('DCO_FORM_NO_TEMPLATE','FATAL! No template set!');

	/** error string when no data was passed */
	if (!defined('DCO_FORM_NO_DATA')) 		define('DCO_FORM_NO_DATA','No data was passed!');

	/** error string when mandatory data is missing */
	if (!defined('DCO_FORM_ERR_EMPTY'))		define('DCO_FORM_ERR_EMPTY','Missing mandatory data!');
	if (!defined('DCO_FORM_ERR_LENGTH'))	define('DCO_FORM_ERR_LENGTH','Invalid length in data!');
	if (!defined('DCO_FORM_ERR_RANGE'))		define('DCO_FORM_ERR_RANGE','Invalid range in data!');
	if (!defined('DCO_FORM_ERR_FORMAT'))	define('DCO_FORM_ERR_FORMAT','Invalid format of data!');

	if (!defined('DCO_FORM_TITLE_CLASS')) define('DCO_FORM_TITLE_CLASS','sfieldcaption');
	if (!defined('DCO_FORM_ERROR_CLASS')) define('DCO_FORM_ERROR_CLASS','sfielderror');

	/*** field types *************************************************/

	// for compatability to older version (use DCO_DATA constants)
	define('DCO_FIELD_TEXT', 		DCO_DATA_TEXT);
	define('DCO_FIELD_INTEGER', DCO_DATA_INTEGER);
	define('DCO_FIELD_NUMBER', 	DCO_DATA_NUMBER);
	define('DCO_FIELD_EMAIL', 	DCO_DATA_EMAIL);
	define('DCO_FIELD_URL', 		DCO_DATA_URL);
	define('DCO_FIELD_DATE', 		DCO_DATA_DATE);
	define('DCO_FIELD_BOOL', 		DCO_DATA_BOOL);
	define('DCO_FIELD_PLAIN', 		DCO_DATA_PLAIN);
	define('DCO_FIELD_NULL', 		DCO_DATA_NULL);

	// possible field errors
	define('DCO_FIELD_ERR_EMPTY', 		1);
	define('DCO_FIELD_ERR_LENGTH', 		2);
	define('DCO_FIELD_ERR_RANGE', 		3);
	define('DCO_FIELD_ERR_FORMAT', 		4);


	/*****************************************************************
	*****************************************************************/
	class dcoField extends dcoData
	{
		/**
		 * constructs a single field used for the dco form
		 * @param name the name of the field
		 * @param type one of the predefined dco field types
		 * @param mandatory set true if field is mandatory
		 * @param title field title for international forms
		 */
		function dcoField($name,$type=DCO_DATA_TEXT,$mandatory=false,$title='') {
			parent::dcoData($name,$type,$mandatory,$title);
		}
	}	// finish class dcoField


	/*****************************************************************
	 This helper class can be used to generate automatic validating
	 forms for instances of cdbobjectbase objects
	*****************************************************************/
	class dcoForm
	{
		var $dco = 0;
		var $conf = array();
		var $validated = false;
		var $error = '';
		var $auto_html = false;
		var $auto_entities = false;
		var $charset_encoding = '';

		// constructor
		function dcoForm($conf='') {
			$this->reset();
			if (is_array($conf)) {
				$this->conf = array_merge($this->conf,$conf);
				if (isset($conf['dco'])) $this->setDCO($conf['dco']);
				if (isset($conf['auto_html'])) $this->auto_html = $conf['auto_html'];
				if (isset($conf['auto_entities'])) $this->auto_entities = $conf['auto_entities'];
				if (isset($conf['charset_encoding'])) $this->charset_encoding = $conf['charset_encoding'];
			}
		}

		// set data container object
		function setDCO(&$dco) {
			$this->dco = is_object($dco) ? $dco : 0;
			return ($this->dco!==0);
		}

		function addField($name,$type=DCO_DATA_TEXT,$mandatory=false,$title='') {
			if ( !isset($this->conf['fields']) || !is_array($this->conf['fields']) ) $this->conf['fields'] = array();
			$this->conf['fields'][$name] = new dcoField($name,$type,$mandatory,$title);
			$this->conf['fields'][$name]->autohtml = $this->auto_html;
			$this->conf['fields'][$name]->autoentities = $this->auto_entities;
			$this->conf['fields'][$name]->charset_encoding = $this->charset_encoding;
			#$this->conf['data'][$name.'_class'] = DCO_FORM_TITLE_CLASS;
		}

		function validate($data='') {
			// check if data was passed
			$this->setData($data);
			// make sure we have some data to check
			if ( !is_array($this->conf['data']) ) {
				$this->validated = false;
				$this->error = DCO_FORM_NO_DATA;
			} else {
				// reset error
				$this->error = '';
				// optimistic approach
				$this->validated = true;

				// validate fields
				if ( isset($this->conf['fields']) && is_array($this->conf['fields']) ) {
					foreach($this->conf['fields'] as $key=>$field) {
						if ( !empty($this->conf['data'][$field->name]) ) {
                            if(is_array($this->conf['data'][$field->name])) {
                                $k=0;
                                foreach($this->conf['data'][$field->name] as $val) {
                                    //validate only first element of array
                                    if($k==0) $field->set($val);
                                    $k++;
                                }
                            }
                            else
							$field->set($this->conf['data'][$field->name]);
						}
						if ( !$field->validate() ) {
							$this->validated = false;
							switch ( $field->reason ) {
								case DCO_FIELD_ERR_EMPTY:
									$this->error = DCO_FORM_ERR_EMPTY;
									break;
								case DCO_FIELD_ERR_LENGTH:
									$this->error = DCO_FORM_ERR_LENGTH;
									break;
								case DCO_FIELD_ERR_RANGE:
									$this->error = DCO_FORM_ERR_RANGE;
									break;
								case DCO_FIELD_ERR_FORMAT:
									$this->error = DCO_FORM_ERR_FORMAT;
									break;
								default:
									$this->error = "Unknown Error ".$field->reason." in field ".$key.' with value ['.$field->value.']';
									break;
							}
						}
					}
				}
			}
			return $this->validated;
		}

		// show the form
		function show($data='') {

			// make sure a template has been set
			if ( empty($this->conf['template']) ) {
				echo DCO_FORM_NO_TEMPLATE;
				return false;
			}

			// check if data was passed
			$this->setData($data);

			// make sure we have a fields array
			if ( !isset($this->conf['fields']) || !is_array($this->conf['fields']) )
				$this->conf['fields'] = array();

			// render form
			$form = new xoxSimpleTemplate('',$this->conf['template']);

			$form->setVar('error',$this->error);

			foreach($this->dco as $key=>$value) {
				if ( isset($this->conf['fields'][$key]) ) {
					$this->conf['fields'][$key]->set($value);
					$form->setVar($key,$this->conf['fields'][$key]->get());
					$form->setVar($key.'_class',($this->conf['fields'][$key]->validated||$this->error=='')?DCO_FORM_TITLE_CLASS:DCO_FORM_ERROR_CLASS);
				} else {
					$form->setVar($key,$value);
				}
			}

			foreach($this->conf['data'] as $key=>$value) {
				if ( isset($this->conf['fields'][$key]) ) {
					$this->conf['fields'][$key]->set($value);
					$form->setVar($key,$this->conf['fields'][$key]->get());
					$form->setVar($key.'_class',($this->conf['fields'][$key]->validated||$this->error=='')?DCO_FORM_TITLE_CLASS:DCO_FORM_ERROR_CLASS);
				} else {
					$form->setVar($key,$value);
				}
			}

			// copy fieldnames and classes
			foreach($this->conf['fields'] as $key=>$field) {
				#$form->setVar($field->name.'_class',($field->validated||$this->error=='')?DCO_FORM_TITLE_CLASS:DCO_FORM_ERROR_CLASS);
				if (!empty($field->title)) $form->setVar('ls_'.$field->name,$field->title);
			}

			if ( is_array($data) && !empty($data['_tostring']) ) {
				return $form->show(true);
			} else {
				$form->show();
			}

			return true;
		}

		# save dco changes
		function save() {
			if ( is_object($this->dco) && is_array($this->conf['data']) ) {
				$updated=false;
				// get fieldnames to save
				if ( !isset($this->conf['savemask']) || !is_array($this->conf['savemask']) )
					$this->conf['savemask'] = array_keys($this->conf['data']);
				// check if we have values for those fields and set them
				foreach($this->conf['savemask'] as $field) {
					if ( isset($this->conf['data'][$field])
						&& isset($this->dco->$field) ) {
							$this->dco->$field = stripslashes($this->conf['data'][$field]);
							$updated = true;
					}
				}
				$retval = true;
				if ( $updated ) {
					$retval = $this->dco->save();
					#print_r($this);
				}
				return $retval;
			} else {
				return false;
			}
		}

		// check if data was passed and add it to our data
		function setData($data) {
			if ($data!='') {
				if ( is_array($this->conf['data']) ) {
					if ( is_array($data) ) {
						$this->conf['data'] = array_merge($this->conf['data'],$data);
					} else if (is_string($data) && isset($GLOBALS[$data])) {
						$this->conf['data'][$data] = $GLOBALS[$data];
					}
				} else {
					$this->conf['data'] = $data;
				}
			}
		}

		// set configuration defaults
		function reset() {
			$this->dco = '';
			$this->conf = array(
				'template'	=> '',
				'mandatory' => '',
				'fields'		=> array(),
				'data' 			=> array(),
			);
			$this->validated = false;
			$this->error = '';
		}

	}	// finish class dcoForm

?>
