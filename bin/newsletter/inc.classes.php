<?php

require_once (XOX_LIB.'/database/class.cdbobject.php');
require_once (XOX_LIB.'/validate/inc.functions.php');

define('XOX_NLS_ERROR_VALID_DOMAIN',			1);
define('XOX_NLS_ERROR_VALID_EMAIL',				2);
define('XOX_NLS_ERROR_VALID_NAME',				3);
define('XOX_NLS_ERROR_VALID_TITLE',				4);
define('XOX_NLS_ERROR_VALID_NEWSLETTER',	5);
define('XOX_NLS_ERROR_VALID_ISSUE',				6);

define('XOXME_FORM_TEMPLATES',XOX_APP_BASE.'/de/templates/forms');

$GLOBALS['cSubscriberDetails'] = array (
    'company' => 'v',
    'gender' => 'm|f',
    'firstname' => 'v',
    'lastname' => 'v',
    'street' => 'v',
    'zip' => 'v5',
    'city' => 'v',
    'country' => 'v3',
    'telephone' => 'v',
    'fax' => 'v',
    'mobile' => 'v'
    );


 $GLOBALS['cXMLFormFields'] = array(
	'firma',
	'anrede',
	'email',
	'vorname',
	'nachname',
	'strasse',
	'land',
	'plz',
	'ort',
	'telefon',
	'fax',
	'mobil',
	'html_mail'

 );
/*****************************************************************
	subscriber details
														 19.07.2004 18:42
*****************************************************************/
class cSubscriber extends cDBObjectBase {
	var $_details = array ();

	var $domain_id = 0;
	var $email = '';
	var $uid = 0;
	var $passwd = '';
	var $created = '0000-00-00 00:00:00';
	var $passwd_changed = '0000-00-00 00:00:00';
	var $config = 'html';
	var $mode_id = 0;
	var $mode_data = '';
	var $displayname = '';


	function cSubscriber($cid = 0) {
		$this->_table = 'subscriber';
		$this->_details = array ();
		foreach ($GLOBALS['cSubscriberDetails'] as $key => $value) $this->_details[$key] = '';
		$this->load($cid);
	}

	function isValid() {
    $valid = TRUE;
		if ($this->domain_id < 1) {
      $this->_error[XOX_NLS_ERROR_VALID_DOMAIN] = 'No valid Domain';
			$valid = FALSE;
		}
		if (empty($this->email) ){//|| !xox_check_email($this->email)) {
      $this->_error[XOX_NLS_ERROR_VALID_EMAIL] = 'No valid Email';
			$valid = FALSE;
		}
		if (empty($this->displayname)) {
      $this->_error[XOX_NLS_ERROR_VALID_NAME] = 'No valid Displayname';
		  $valid = FALSE;
    }
		return $valid;
	}

	function load($cid = '') {
		$retval = parent :: load($cid);
		if ($retval && $this->getID()) {
			if ($rs = executeQuery("SELECT name, value FROM subscriber_detail WHERE subscriber_id='".$this->getID()."'")) {
				while ($row = $rs->getrow()) {
					$this->_details[$row['name']] = $row['value'];
				}
			}
		}
	}
	function loadEmail($email = '') {
		$this->reset();
		if ($this->_table && $email && $email != '') {
			if ($rs = executeQuery("SELECT $this->_id FROM $this->_table WHERE email='$email'")) {
				if ($row = $rs->getrow())
					$this->load($row[$this->_id]);
				$rs->free();
			}
		}
	}

	function save() {
		if (parent :: save()) {
			$id = $this->getID();
			if (isset ($this->_details) && is_array($this->_details)) {
				executeSQL("DELETE FROM subscriber_detail WHERE subscriber_id='$id'");
				foreach ($this->_details as $name => $value) executeSQL("INSERT INTO subscriber_detail (subscriber_id,name,value) VALUES('$id','$name','$value')");
			}
			return TRUE;
		}
		return FALSE;
	}

	function delete() {
		$id = $this->getID();
		if (parent :: delete()) {
			executeSQL("DELETE FROM subscriber_detail WHERE subscriber_id='$id'");
			executeSQL("DELETE FROM subscribtion_form_subscriber WHERE subscriber_id='$id'");
			executeSQL("DELETE FROM subscriber_topic WHERE subscriber_id='$id'");
			return TRUE;
		}
		return FALSE;
	}

	function setPassword($pass = '') {
		$this->passwd = $pass;
		$this->passwd_changed = date('Y-m-d H:i:s');
	}

	function reset() {
		parent :: reset();
		$this->domain_id = 0;
		$this->uid = 0;
		$this->mode_id = 0;
		$this->created = '0000-00-00 00:00:00';
		$this->passwd_changed = '0000-00-00 00:00:00';
		foreach ($this->_details as $key => $value) $this->_details[$key] = '';
	}

} // finish class cContact

/*****************************************************************
	baseclass for cpotclient, cclient and ckeykeeper
														 19.07.2004 19:21
*****************************************************************/
class cDomain extends cDBObjectBase {

	var $name = 0;

	function cDomain($cid) {
		$this->load($cid);
	}

	function getSender($cid) {
		if (!isset ($this->_sender)) {
			$and = ($cid > 0) ? " AND id='$cid'" : '';
			if ($rs = executeQuery("SELECT id, email FROM sender WHERE id='".$this->getID()."'$and")) {
				while ($row = $rs->getrow()) {
					$this->_contacts['id'.$row['id']] = $row['email'];
				}
				$rs->close();
			}
			return ($cid > 0) ? $this->_sender[$cid] : $this->_sender;
		} else
			return 0;
	}

	// READ ONLY
	function save() {
		return FALSE;
	}
	function delete() {
		return FALSE;
	}

} // finish class cPerson

/*****************************************************************
        newsletter
                                  21.07.2004 23:00
*****************************************************************/
class cNewsletter extends cDBObjectBase {
	var $_issues = array ();
	var $_topics = array ();
	var $_subscribers = array();

	var $domain_id = 0;
	var $sender_id = 0;
	var $reply_id = 0;
	var $return_id = 0;
	var $sender_name = '';
	var $name = '';
	var $description = '';
	var $prefix = '';
	var $created = '0000-00-00 00:00:00';
	var $changed = 0;
	var $sort = 0;
	var $active = 0;
	var $owner = 0;
	var $template_html = '';
	var $template_text = '';
	var $uflags = 0;

	/**
	 * @author sepo
	 * @abstract Constructor
	 */
	function cNewsletter($cid = 0) {
		$this->_table = 'newsletter';
		$this->load($cid);
	}

	/**
	* @author sepo
	* @abstract check if the object is valid
	* @uses domain_id > 1
	* @uses name != ''
	* @return boolean valid or not
	*/
	function isValid() {
		$valid = TRUE;
		if ($this->domain_id < 1) {
      $this->_error[XOX_NLS_ERROR_VALID_DOMAIN] = 'No valid Domain';
			$valid = FALSE;
		}
		if ($this->name == '') {
      $this->_error[XOX_NLS_ERROR_VALID_NAME] = 'No valid Name';
			$valid = FALSE;
		}
		return $valid;
	}

	function save() {
	 	if (!$this->isValid()) return FALSE;
    $sql = "";
		$this->changed = date("YmdHis");
    foreach ($this as $key=>$value)
      if ($key != $this->_id && substr($key,0,1) != "_") {
				if ( $key=='created' && !$this->getID() ) {
					$value = date("Y-m-d H:i:s");
				}
				$sql.="$key='".$this->dbquote($value)."',";
			}
    if (!$sql) return FALSE;
		$sql=substr($sql,0,-1);
    if ($this->getID()) {
			if ( !executeSQL("UPDATE $this->_table SET $sql WHERE $this->_id='".$this->getID()."'") ) {
				$this->_error['update'] = mysql_error();
				return 0;
			}
    } else {
			$idtag = $this->_id;

	    $this->$idtag = executeInsert("INSERT INTO $this->_table SET $sql");
			if ( !$this->$idtag ) {
				$this->_error['insert'] = mysql_error();
			}
    }
		return $this->getID();
	}
	/**
	 * @author sepo
	 * @abstract
	 * @return array all issues related to this newsletter
	 */
	function getIssues() {
		if (empty ($this->_issues) || !is_array($this->_issues)) {
			$this->_issues = array ();
			if ($rs = executeQuery("SELECT * FROM issue WHERE newsletter_id='".$this->getID()."' ORDER BY mode, delivery, changed")) {
				while ($row = $rs->getrow()) {
					$issue = new cIssue();
					$issue->set($row);
					$this->_issues[] = $issue;
				}
			}
		}
		return $this->_issues;
	} //  getIssues

	/**
	 * @author sepo
	 * @abstract
	 * @return array all topics related to this newsletter
	 */
	function getTopics() {
		if (empty ($this->_topics) || !is_array($this->_topics)) {
			$this->_topics = array ();
			if ($rs = executeQuery("SELECT * FROM topic WHERE newsletter_id='".$this->getID()."'")) {
				while ($row = $rs->getrow()) {
					$topic = new cTopic();
					$topic->set($row);
					$this->_topics[] = $topic;
				}
			}
		}
		return $this->_topics;

	}

	/**
	 * @author dav
	 * @abstract
	 * @return array all topics related to this newsletter
	 */
	function getSubscribers() {
		if (empty ($this->_subscribers) || !is_array($this->_subscribers)) {
			$this->_subscribers = array ();
			if ($rs = executeQuery("SELECT subscriber.* FROM subscriber LEFT JOIN subscription_form_subscriber ON subscriber.id=subscription_form_subscriber.subscriber_id WHERE newsletter_id='".$this->getID()."' ORDER BY subscriber.id")) {
				while ($row = $rs->getrow()) {
					$subscriber = new cSubscriber();
					$subscriber->set($row);
					$this->_subscribers[] = $subscriber;
				}
			}
		}
		return $this->_subscribers;

	}

	/**
	 * @author sepo
	 * @abstract
	 * @return cTopic topic by id
	 */
	function getTopic($id = 0) {
		if ($id != 0) {
			return new cTopic($id);
		}
		return false;
	}

	/**
	 * @author sepo
	 * @abstract
	 * @return string replyer email adress
	 */
	function getReply() {
  	 if ($this->reply_id != 0) {
  	   if ($rs = executeQuery("SELECT email FROM sender WHERE id='".$this->reply_id."'")) {
           if ($row = $rs->getrow()) return $row['email'];
       }
     }
     return '';
    }

   /**
   * @author sepo
   * @abstract
   * @return string sender email adress
   */
   function getSender() {
    if ($this->sender_id != 0) {
      if ($rs = executeQuery("SELECT email FROM sender WHERE id='".$this->sender_id."'")) {
        if ($row = $rs->getrow()) return $row['email'];
      }
    }
    return '';
  }

   /**
   * @author dav
   * @abstract
   * @return string return email adress
   */
   function getReturn() {
    if ($this->return_id != 0) {
      if ($rs = executeQuery("SELECT email FROM sender WHERE id='".$this->return_id."'")) {
        if ($row = $rs->getrow()) return $row['email'];
      }
    }
    return '';
  }

	/**
	 * @author sepo
	 * @abstract
	 * @return array of cForm objects
	 */
	function getForms() {
		return true;
	}

	/**
	* @author sepo
	* @abstract
	* @return boolean indicate if the update operation wend well
	*/
	function delete() {
		$cid = $this->getID();
		executeSQL("UPDATE newsletter SET active=0 WHERE id='$cid'");
		return TRUE;
	}

	/**
	 * @author sepo
	 * @abstract delete the newsletter from the db and call kill of all issues and topics
	 * @return boolean text if killed
	 */
	function kill() {
		if (parent :: delete()) {
			// delete issues
			if (!empty ($this->_issues) || is_array($this->_issues)) {
				foreach ($this->_issues as $issue) {
					$issue->kill();
				}
			}
			// delete topics
			if (!empty ($this->_topics) || is_array($this->_topics)) {
				foreach ($this->_topics as $topic) {
					$topic->kill();
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @author sepo
	 * @abstract reset the object
	 */
	function reset() {
		parent :: reset();
		/*$this->domain_id = 0;
		$this->sender_id = 0;
		$this->reply_id = 0;
		$this->name = '';
		$this->description = '';
		$this->prefix = '';
		$this->created = '0000-00-00 00:00:00';
		$this->changed = '0000-00-00 00:00:00';
		$this->sort = 0;
		$this->active = 0;
		$this->owner = 0;
		$this->template_html = '';
		$this->template_text = '';*/

	}

} // finish class cNewsletter

/*****************************************************************
Issue
                                  22.07.2004 23:00
*****************************************************************/
class cIssue extends cDBObjectBase {
	var $_contents = array ();
	var $_sortedcontents = array();

	var $newsletter_id = 0;
	var $title = '';
	var $introduction = '';
	var $date = '0000-00-00 00:00:00';
	var $delivery = '0000-00-00 00:00:00';
	var $changed = 0;
	var $mode = '';
	var $recipients = 0;

	/**
	 * @author sepo
	 * @abstract Constructor
	 */
	function cIssue($cid=0,$nlid=0) {
		$this->_table = 'issue';
		$this->load($cid);
		if ( $nlid>0 ) $this->newsletter_id = $nlid;
		if ( empty($this->title) && $this->newsletter_id > 0) {
			$nl = $this->getNewsletter();
			$this->title = $nl->name;
		}
	}

	function getNewsletter($id=0) {
		if ( $id==0 ) $id = $this->newsletter_id;
		return new cNewsletter($id);
	}

	/**
	* @author sepo
	* @abstract check if the object is valid
	* @uses newsletter_id > 1
	* @uses title != ''
	* @return boolean valid or not
	*/
	function isValid() {
		$valid = TRUE;
		if ($this->newsletter_id < 1) {
      $this->_error[XOX_NLS_ERROR_VALID_NEWSLETTER] = 'No valid Newsletter';
			$valid = FALSE;
		}
		if ($this->title == '') {
      $this->_error[XOX_NLS_ERROR_VALID_TITLE] = 'No valid Title';
			$valid = FALSE;
		}
		return $valid;
	}
	/**
	 * @author sepo
	 * @abstract
	 * @return array all content objects relatet to this issue
	 */
	function getContents() {
		if (empty ($this->_contents) || !is_array($this->_contents)) {
			$this->_contents = array ();
			if ($rs = executeQuery("SELECT * FROM content WHERE issue_id='".$this->getID()."' ORDER BY displayorder")) {
				while ($row = $rs->getrow()) {
					$content = new cContent();
					$content->set($row);
					$this->_contents[] = $content;
				}
			}
		}
		return $this->_contents;
	} //  getContents

	/**
	 * @author sef
	 * @abstract call to move a content object
	 * @param $cid the id of a content object
	 * @param $direction 1 (move up) or -1 (move down)
	 * @return
	 */
	function sortContent($cid=0,$direction=1,$typ='all'){

		if (empty($this->_sortedcontents[]))$this->getSortedContents($typ);
		if($cid > 0){
			$content = new cContent($cid);
			$pos_from = $content->displayorder;
			$pos_to = $content->displayorder + $direction;

			//mydump($content,"content", TRUE, __FILE__, __LINE__ );
			if($direction != 0){
				if(isset($this->_sortedcontents[$content->displayorder + $direction])){
 					$content2 = $this->_sortedcontents[$content->displayorder + $direction];
 					$content2->displayorder = $pos_from;
 					$content2->save();
 					$content->displayorder = $content->displayorder + $direction;
 					//mydump($content2,"content2", TRUE, __FILE__, __LINE__ );
 				}else{
 					if($direction > 0) $content->displayorder = 0;
 					else $content->displayorder = 1000;
 				}
 			}
			$content->save();

		}
	}

	/**
	 * @author sef
	 * @abstract sort all content obejcts relatet to this issue. the sort goes from 1 to n.
	 * @return array sorted content relatet to this issue
	 */
	function getSortedContents($typ = 'all') {

		//if (empty ($this->_sortedcontents) || !is_array($this->_sortedcontents)) {
			$this->_sortedcontents = array ();
			$sql = "SELECT * FROM content WHERE issue_id='".$this->getID()."'";
			if($typ != 'all') $sql .= " AND flags='".$typ."'";
			$sql .=  " ORDER BY displayorder";
			//echo $sql;
			if ($rs = executeQuery($sql)) {
				$order = 1;
				while ($row = $rs->getrow()) {
					$content = new cContent();
					$content->set($row);
					//echo "<br>".$order;
					if($row['displayorder']!=$order){
					//echo $row['displayorder'];
						$content->displayorder=$order;
						$content->save();
					}
					$this->_sortedcontents[$content->displayorder] = $content;

					$this->maxsortnumber = $content->displayorder;
					$order++;
				}
			}


		return $this->_sortedcontents;
	} //  getContents

	/**
	* @author sef
	* @abstract
	* @return boolean indicate if the update operation wend well
	*/
	function delete() {
		$cid = $this->getID();
		executeSQL("UPDATE issue SET mode='deleted' WHERE id='$cid'");
		return TRUE;
	}

	/**
	 * @author sef
	 * @abstract delete the newsletter from the db and call kill of all issues and topics
	 * @return boolean text if killed
	 */
	function kill() {
		if (parent :: delete()) {
			if (!empty ($this->_contents) || is_array($this->_contents)) {
				foreach ($this->_contents as $content) {
					$content->delete();
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	function save() {
		$this->changed = mktime();
		return parent::save();
	}

	/**
	 * @author sef
	 * @abstract reset the object
	 */
	function reset() {
		parent :: reset();
		/*$this->newsletter_id = 0;
		$this->name = '';
		$this->introduction = '';
		$this->date = '0000-00-00 00:00:00';
		$this->delivery = '0000-00-00 00:00:00';
		$this->changed = '0000-00-00 00:00:00';
		$this->mode = '';
		$this->recipients = 0;*/

	}

} // finish class cIssue

/*****************************************************************
Content
                                  22.07.2004 23:00
*****************************************************************/
class cContent extends cDBObjectBase {

	var $issue_id = 0;
	var $topic_id = 0;
	var $title = '';
	var $body = '';
	var $url = '';
	var $changed = 0;
	var $flags = '';
	var $displayorder = 255; //einsorieren an unterster stelle

	/**
	 * @author sef
	 * @abstract Constructor
	 */
	function cContent($cid = 0) {
		$this->_table = 'content';
		$this->load($cid);
	}

	/**
	* @author sef
	* @abstract check if the object is valid
	* @uses newsletter_id > 1
	* @uses title != ''
	* @return boolean valid or not
	*/
	function isValid() {
		$valid = TRUE;
		if ($this->issue_id < 1) {
      $this->_error[XOX_NLS_ERROR_VALID_ISSUE] = 'No valid Issue';
			$valid = FALSE;
		}
		#if ($this->topic_id < 1)
		#	$valid = FALSE;
		if ($this->title == '') {
      $this->_error[XOX_NLS_ERROR_VALID_TITLE] = 'No valid Title';
			$valid = FALSE;
		}
		return $valid;
	}

	function save() {
		$this->changed = mktime();
		return parent::save();
	}
	/**
	 * @author sef
	 * @abstract reset the object
	 */
	function reset() {
		parent :: reset();
		$this->issue_id = 0;
		$this->topic_id = 0;
		$this->title = '';
		$this->body = '';
		$this->url = '';
		$this->changed = '0000-00-00 00:00:00';
		$this->flags = '';
		$this->displayorder = 255;
	}


} // finish var $Content

/*****************************************************************
Topic
                                  22.07.2004 23:00
*****************************************************************/
class cTopic extends cDBObjectBase {
	var $_contents = array ();

	var $newsletter_id = 0;
	var $name = '';
	var $description = '';
	var $checked = 0;
	var $template_html = '';
	var $template_text = '';

	/**
	 * @author sef
	 * @abstract Constructor
	 */
	function cTopic($cid = 0) {
		$this->_table = 'topic';
		$this->load($cid);
	}

	/**
	* @author sef
	* @abstract check if the object is valid
	* @uses newsletter_id > 1
	* @uses title != ''
	* @return boolean valid or not
	*/
	function isValid() {
		$valid = TRUE;
		if ($this->newsletter_id < 1) {
      $this->_error[XOX_NLS_ERROR_VALID_NEWSLETTER] = 'No valid Newsletter';
			$valid = FALSE;
		}
		if ($this->name == '') {
      $this->_error[XOX_NLS_ERROR_VALID_NAME] = 'No valid Name';
			$valid = FALSE;
		}
		return $valid;
	}

	/**
	* @author sef
	* @abstract
	* @return array all contents relatet to this topic
	*/
	function getContents($issue_id = 0) {
			//if($issue_id==0){ //return all contents objects
	    if (empty ($this->_contents) || !is_array($this->_contents)) {
			$this->_contents = array ();
			if ($rs = executeQuery("SELECT * FROM content WHERE topic_id='".$this->getID()."'". ($issue_id > 0 ? " AND issue_id='".$issue_id."'" : ''))) {
				while ($row = $rs->getrow()) {
					$content = new cContent();
					$content->set($row);
					$this->_contents[] = $content;
				}
			}
		}
		return $this->_contents;
		/*
		}else{
		if (empty ($this->_contents) || !is_array($this->_contents)) {
		  $this->_contents = array ();
		  if ($rs = executeQuery("SELECT * FROM content WHERE topic_id='".$this->getID()."' )) {
		    while ($row = $rs->getrow()) {
		      $content = new cContent();
		      $content->set($row);
		      $this->_contents[] = $content;
		    }
		  }
		}
		return $this->_contents;*/

	} //  getContents

	/**
	 * @author sef
	 * @abstract
	 * @return cNewsletter the newsletter related to this topic
	 */
	function getNewsletter() {

		if ($this->isValid()) {
			return new cNewsletter($this->_newsletter_id);
		}
		return false;
	} //  getNewsletter

	/**
	* @author sef
	* @abstract
	* @return boolean indicate if the update operation wend well
	*/
	function delete() {
		parent :: delete();
	}

	/**
	 * @author sef
	 * @abstract reset the object
	 */
	function reset() {
		parent :: reset();
		$this->newsletter_id = 0;
		$this->name = '';
		$this->description = '';
		$this->checked = 0;
		$this->template_html = '';
		$this->template_text = '';

	}

} // finish class cTopic

/*****************************************************************
subscription_form
                                  22.07.2004 23:00
*****************************************************************/
class cSubscriptionForm extends cDBObjectBase {
	//var $_newsletters = array ();
	//var $_subscribers = array ();

	var $domain_id = 0;
	var $name = '';
	var $_newsletter_id = 0;
	var $_subscriber_id = 0;

	var $_xml_form = array();

	/**
	 * @author sef
	 * @abstract Constructor
	 */
	function cSubscriptionForm($cid = 0) {
		$this->_table = 'subscription_form';
		$this->load($cid);
		if($cid > 0){
			$xml_file = XOXME_FORM_TEMPLATES.'/'.$this->name.'.xml';
		}else $xml_file ='';
		$this->_xml_form = new cSubscriptionFormXMLHandler($xml_file);

	}

	function save()
	{
		parent :: save();
		$this->_xml_form->save();
	}  //  save

	function set($arr)
	{
		parent :: set($arr);
		$this->setxml($arr);
	}  //  set()

	function setxml($arr)
	{
		if(is_object($this->_xml_form)){
			$this->_xml_form->_name = $this->name;
			$this->_xml_form->setDataPath(XOXME_FORM_TEMPLATES);
		  	$this->_xml_form->set($arr);
			//echo "SET";

		}else
		{
			//echo "NOTSET";
			//print_r($this->_xml_form);
		}

		parent :: set($arr);


	}  //  set

	/**
	* @author sef
	* @abstract check if the object is valid
	* @uses newsletter_id > 1
	* @uses title != ''
	* @return boolean valid or not
	*/
	function isValid() {
		$valid = TRUE;
		if ($this->domain_id < 1) {
      $this->_error[XOX_NLS_ERROR_VALID_DOMAIN] = 'No valid Domain';
			$valid = FALSE;
		}
		if ($this->name == '') {
      $this->_error[XOX_NLS_ERROR_VALID_NAME] = 'No valid Name';
			$valid = FALSE;
		}
		return $valid;
	}
}
//cSubscriptionForms

class cSubscriptionFormXMLHandler{
		// data wird dynamisch aus post oder aus $GLOBALS['cXMLFormFields'] ermittelt

		var $_name 			= '';

		var $_datapath  = '';
		var $_datafile	= '';
		var $_error  		= '';
		var $_value			= '';
		var $_accessed 	= '';
		var $_modified	= '';
		var $_changed		= '';
		var $_size			= 0;


		// constructor
	function cSubscriptionFormXMLHandler($xml = '') {

		$this->_name = basename($xml,'.xml');
		$this->setDataPath(dirname($xml));
		$this->load($xml);

	}


		function setDataPath($path='') {
			$this->_datapath=$path;
		}

		// is form valid
		function isValid() {
      return TRUE;
    }

		// handlers for loading xml
		function startLoadElement($parser,$name,$attrs) {
			if ( $name=="XOXMEFORM" ) {
				if (isset($attrs['NAME'])) $this->_name=$attrs['NAME'];
				if (isset($attrs['CREATED'])) $this->_created=$attrs['CREATED'];
				if (isset($attrs['LASTCHANGE'])) $this->_lastchange=$attrs['LASTCHANGE'];

			}else{
				//echo "name:".$name."<br />";
				//print_r($attrs);
				$lowname=strtolower($name);
				if(in_array($lowname,$GLOBALS['cXMLFormFields'])) {
					//default
				  	$this->$lowname = array('value'=>'','show'=>0,'mandatory'=>0);
					//überschreiben falls gesetzt
					$this->$lowname=array('value'=>'','mandatory'=>(isset($attrs['MANDATORY'])?$attrs['MANDATORY']:0),'show'=>(isset($attrs['SHOW'])?$attrs['SHOW']:0));
				}
				//}

			}
			//print "$name\n";
		}
		function endLoadElement($parser,$name) {
			$name=strtolower($name);
			if ( !empty($this->_value) && isset($this->$name) ) $this->$name=$this->_value;
		}
		function readLoadCData($parser,$value) {
			$this->_value=$value;
		}

		// load form from xml source
		function load($xml='') {
      $this->reset();
			// check filename and open file
		//echo "file :".$xml;
			$filename=trim($xml);
      if (!empty($filename)) {
				if ( !eregi('.xml$',$filename) ) $filename.="$this->_datapath/$filename.xml";
				if(file_exists($filename)){
					$this->_datafile = $filename;

				if ( $fh=fopen($filename,'r') ) {
					// read file stats... size and timestamps
					if ($s=fstat($fh)) {
						$this->_size			= (isset($s['size'])) ? $s['size'] : 0;
						$this->_accessed 	= (isset($s['atime'])) ? date('d.m.Y H:i:s',$s['atime']) : '';
						$this->_modified	= (isset($s['mtime'])) ? date('d.m.Y H:i:s',$s['mtime']) : '';
						$this->_changed		= (isset($s['ctime'])) ? date('d.m.Y H:i:s',$s['ctime']) : '';
					}
					// setup xml parser
					$xml_parser = xml_parser_create();
					xml_set_object($xml_parser, &$this);
					xml_set_element_handler($xml_parser, "startLoadElement", "endLoadElement");
					xml_set_character_data_handler($xml_parser, "readLoadCData");
					// parse entire file
					while ($data = fread($fh, 4096)) {
					  if (!xml_parse($xml_parser, $data, feof($fh))) {
							$this->_error = sprintf("XML error: %s at line %d",
					    	xml_error_string(xml_get_error_code($xml_parser)),
					    	xml_get_current_line_number($xml_parser));
							return FALSE;
					  }
					}
					// free resources, set filename and return
					xml_parser_free($xml_parser);
					fclose($fh);
				if (empty($this->_name)) $this->_name = basename($filename,".xml");

      		return TRUE;
				}
			}
      }
			// filename is empty
			// file could not be opened
			return FALSE;
    } // load

		function makeElement($name,$value='',$attrs='') {
			$a='';
			if (is_array($attrs)) foreach($attrs as $k=>$v) if (!is_numeric($k)) $a.=" $k=\"$v\"";
			if (empty($value)) return "<$name$a />";
			else               return "<$name$a>$value</$name>";
		}

		// save form to xml file
		function save() {
			if ( empty($this->_datafile) ) $this->_datafile="$this->_datapath/$this->_name.xml";
			if ( $fh=fopen($this->_datafile,'w') ) {
				// set elements

				$newform='';
				foreach($this as $key=>$value){
					//echo "save:".$key." value:".$value."<br />";
					if(substr($key,0,1) != '_' && is_array($value)){
						$tmp = $this->$key;
						$newform.=$this->makeElement($key,$tmp['value'],array('show'=>$tmp['show'],'mandatory'=>$tmp['mandatory']));

					}
				}
				// wrap in xoxmeform element and save xml
				$newform=$this->makeElement('xoxmeform',$newform,array('name'=>$this->_name,'version'=>'1.0'));
				fwrite($fh,'<?xml version="1.0" encoding="ISO-8859-1" ?'.">$newform");
				fclose($fh);
				return TRUE;
			}
			return FALSE;
    }  // save

		// delete xml file
		function delete() {
	      if (!empty($this->_datafile) && file_exists($this->_datafile)) {
					unlink($this->_datafile);
					$this->reset();
	      }
	      return FALSE;
    	}  // delete

    function set($arr) {

		$this->resetDefaultFields();
      if (!$arr||!is_array($arr)) return FALSE;
		foreach ($arr as $key=>$value){
		  //echo "key:".$key." vaslue:".$value."<br />";
			if($key=='_xml_form'){
				foreach ($value as $name=>$set){

				  //	echo "key:".$name." value:";
					//print_r($set);
					//echo "<br />";
					$this->$name=array('value'=>'','show'=>(in_array('show',array_values($set))?1:0),'mandatory'=>(count($set)==2?1:0));
				}
			}

		}
      return TRUE;
    }  // set

    function reset() {
      foreach ($this as $key=>$value) $this->$key='';
		$this->resetDefaultFields();
    }  // reset

	 function resetDefaultFields()
	 {
		foreach ($GLOBALS['cXMLFormFields'] as $key) $this->$key = array('value'=>'','show'=>0,'mandatory'=>0);
	 }  //  resetDefaultFields

    function debug() {
      foreach ($this as $key=>$value) echo "$key='$value'\n";
    }  // debug

}
	?>
