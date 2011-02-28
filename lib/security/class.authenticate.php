<?php

	require_once(dirname(dirname(__FILE__))."/database/class.cdbobject.php");

  // user class
	class cUser extends cDBObjectBase {

		var $_username	= 'username';
		var $_password	= 'password';
		var $_rights		= 'rights';

    function cUser( $username='', $userid='' ) {
			if ( isset($GLOBALS['XOX_USER_TABLE']) ) {
				// copy essential fieldnames
				$this->_table			= $GLOBALS['XOX_USER_TABLE']['table'];
				$this->_id				= $GLOBALS['XOX_USER_TABLE']['id'];
				$this->_username	= $GLOBALS['XOX_USER_TABLE']['username'];
				$this->_password	= $GLOBALS['XOX_USER_TABLE']['password'];
				$this->_rights		= $GLOBALS['XOX_USER_TABLE']['rights'];
				// make fields to object variables
				foreach($GLOBALS['XOX_USER_TABLE']['fields'] as $field)
					$this->$field = '';
			} else {
				$this->_table			= 'user';
				$this->_id				= 'id';
			}
			$this->loadUser( $username, $userid );
		}  // cUser constructor

    function loadUser( $username='', $userid='' ) {

      $this->load($userid);

      // check if username was passed and equals current
      if ($username && $this->getUsername()!=$username ) {
        $this->reset();
        $rs = executeQuery("SELECT * FROM $this->_table WHERE $this->_username='$username'");
        $this->set($rs->getrow());
        $rs->free();
      }

			if (XOX_DEBUG_MODE) $this->debug();

      $tag = $this->_password;
      $this->$tag = '';  // not authenticated yet

    }  // cUser constructor

    function isAuthenticated() {
      return ( $this->getUsername() && $this->getPassword() );
    }  // isValid

    function validatePassword( $password, $encrypt=TRUE ) {
			#echo "<h1>calling validatePassword({$password},{$encrypt})</h1>";

      // crypting password
      if ($encrypt && $password) {
        //$salt = substr($this->name, 0, 2);
        //$enc_pw = crypt($password, $salt);
        $enc_pw = md5($password);
      }
      else $enc_pw = $password;

			if (XOX_DEBUG_MODE) $this->debug();

      // get user query
      if ($rs = executeQuery("SELECT $this->_password FROM $this->_table WHERE $this->_id='".$this->getID()."' AND $this->_password='$enc_pw'")) {
      	$this->set($rs->getrow());
      	$rs->free();
			}

      // check authentification
      if ( $this->isAuthenticated() ) {
        return TRUE;  // authenticated
      } else {
      	$tag = $this->_password;
        $this->$tag = '';
        return FALSE;
      }

    }  // validatePassword

    function setPassword( $password, $new_password, $update=TRUE, $encrypt=TRUE ) {
      $tag = $this->_password;
      // check if password is correct
      if ( $this->validatePassword($password) ) {
        // crypting password
        if ($encrypt && $new_password) {
          //$salt = substr($this->name, 0, 2);
          //$this->$this->_password = crypt($new_password, $salt);
          $this->$tag = md5($new_password);
        }
        else $this->$tag = $new_password;
        // and update
        if ($this->getID()>0 && $update) executeSQL("UPDATE $this->_table SET $tag='".$this->getPassword()."' WHERE $this->_id='".$this->getID()."'");
        return TRUE;
      }
      return FALSE;
    }  // validatePassword

    function generatePassword($encrypt=TRUE) {
      $tag = $this->_password;
      if ( $this->getID() > 0 ) {
        $aname = array("sierra","saturn","meridian","bastian","trabant","viper","isfahan","zwieback");
        srand((double)microtime()*1000000);
        $rpass = $aname[rand(0,count($aname)-1)].rand(0,9999);
        // crypting password
        if ($encrypt) {
          //$salt=substr($this->name,0,2);
          //$this->password=crypt($rpass,$salt);
          $this->$tag=md5($rpass);
        }
        else $this->$tag=$rpass;
        // and update
        executeSQL("UPDATE $this->_table SET $this->_password='".$this->getPassword()."' WHERE $this->_id='".$this->getID()."'");
        return $rpass;
      }
      return '';
    }  // generatePassword

    function getUsername() {
			$tag = $this->_username;
      return $this->$tag;
    }  // getID

    function getPassword() {
      $tag = $this->_password;
      return $this->$tag;
    }  // getID

    function getRights() {
      $tag = $this->_rights;
			if (empty($this->$tag)) {
				return array();
			} else {
      	return split(',',$this->$tag);
			}
    }  // hasRight

    function isValid() {
      return ( $this->getUsername() && $this->getPassword() );
    }  // isValid

  }

?>