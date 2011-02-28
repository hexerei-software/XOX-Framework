<?php

	require_once(dirname(dirname(__FILE__))."/database/class.cdbobject.php");
  
	// user class
	class cUser extends cDBObjectBase {

		var $_username	= 'username';
		var $_password	= 'password';
		var $_rights		= 'rights';
		
		var $id	= '';
		var $username	= '';
		var $password = '';
		var $displayname = '';
		var $email = '';
		var $rights = array();

    function cUser( $username='', $userid='' ) {
			$this->_table			= '';
			$this->_id				= 'id';
			$this->loadUser( $username, $userid );
		}  // cUser constructor

    function load($cid='') {
      $this->reset();
      if ($cid && $cid!='new' && isset($GLOBALS['XOX_STATIC_USERS']) ) {
				$users = $GLOBALS['XOX_STATIC_USERS'];
				$users_count = count($users);
				for ( $i=0;$i<$users_count;$i++ ) {
					if ($users[$i][$this->_id]==$cid) {
						$this->set($users[$i]);
						break;
					}
				}
      }
      return ($this->getID());
    }  // load

    function save() {
      return FALSE;
    }  // save

    function delete() {
      return FALSE;
    }  // delete


    function loadUser( $username='', $userid='' ) {
			
			#print "loadUser( $username, $userid )\n";
			
			if ( isset($GLOBALS['XOX_STATIC_USERS']) ) {
				$users = $GLOBALS['XOX_STATIC_USERS'];
				$users_count = count($users);
				if ($userid!='') {
					for ( $i=0;$i<$users_count;$i++ ) {
						if ($users[$i][$this->_id]==$userid) {
							$this->set($users[$i]);
							break;
						}
					}
				} elseif ($username!='') {
					for ( $i=0;$i<$users_count;$i++ ) {
						if ($users[$i][$this->_username]==$username) {
							$this->set($users[$i]);
							break;
						}
					}
				}
			}

      $tag = $this->_password;
      $this->$tag = '';  // not authenticated yet

			if (XOX_DEBUG_MODE) echo "<h4>loadUser Result</h4>".$this->debug();

    }  // cUser constructor

    function isAuthenticated() {
      return ( $this->getUsername() && $this->getPassword() );
    }  // isValid

    function validatePassword( $password, $encrypt=TRUE ) {
			
			if (XOX_DEBUG_MODE) { echo "<h4>Validate User with password: $password</h4>\n"; $this->debug(); }
			
			$idtag = $this->_id;
			
			if ( empty($this->$idtag) ) return FALSE;

      // crypting password
      $enc_pw = ($encrypt && $password) ? md5($password) : $password;

			#if (XOX_DEBUG_MODE) $this->debug();

      // get user query
			if ( isset($GLOBALS['XOX_STATIC_USERS']) ) {
				$users = $GLOBALS['XOX_STATIC_USERS'];
				$users_count = count($users);
				for ( $i=0;$i<$users_count;$i++ ) {
					if ($users[$i][$this->_id]==$this->$idtag && $users[$i][$this->_password]==$enc_pw) {
						$this->set($users[$i]);
						if (XOX_DEBUG_MODE) { echo "<h4>Authenticated User</h4>\n"; $this->debug(); }
						break;
					}
				}
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
      return FALSE;
    }  // validatePassword

    function generatePassword($encrypt=TRUE) {
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
      	return $this->$tag; //split(',',$this->$tag);
			}
    }  // hasRight

    function isValid() {
      return ( $this->getUsername() && $this->getPassword() );
    }  // isValid

		function reset() {
			#cDBObjectBase::reset();
			$this->id	= '';
			$this->username	= '';
			$this->password = '';
			$this->rights = array();
		}

  }

?>