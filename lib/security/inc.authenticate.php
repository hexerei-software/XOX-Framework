<?php

	// include custom, static or default cUser class definition
	if ( defined('XOX_CUSTOM_CUSER') ) require_once(XOX_CUSTOM_CUSER);
	else {
	  if ( isset($GLOBALS['XOX_STATIC_USERS']) ) require_once(dirname(__FILE__)."/class.authenticate_static.php");
	  else require_once(dirname(__FILE__)."/class.authenticate.php");
	}

	// main auth function for authentication, login, logout and lost function
	function auth($cmd='auth',$username='',$password='') {

		if (XOX_DEBUG_MODE) echo "auth($cmd,$username,$password)<br>\n";

		$authenticated = false;

		if ($cmd=='logout') {

			$authenticated = false;

		} elseif ($cmd=='login') {

			// arrive from login form
			$GLOBALS['user'] = new cUser($username);
			$authenticated = $GLOBALS['user']->validatePassword($password);

		} elseif ($cmd=='lost') {

			// forgot password
			$tmp_user = new cUser($username);

			if ( !empty($tmp_user->email) ) {
				return array('email' => $tmp_user->email, 'password' => $tmp_user->generatePassword(), 'displayname' => (isset($tmp_user->displayname)?$tmp_user->displayname:''));
			} else {
				return false;
			}

			$authenticated = false;

		} elseif ( isset($_SESSION['user']) ) {

			$GLOBALS['user'] = $_SESSION['user'];

			if (is_obj($GLOBALS['user'],'cUser')) {
				if ( $username==$GLOBALS['user']->getUsername() && !empty($password) ) {
					$authenticated = $GLOBALS['user']->validatePassword($password);
				} else {
					$authenticated = true;
				}
			}

		}

		if ( $authenticated ) {
			$_SESSION['user'] 			= $GLOBALS['user'];
			$GLOBALS['user_name']   = (!empty($GLOBALS['user']->displayname)) ? $GLOBALS['user']->displayname : $GLOBALS['user']->username;
  		$GLOBALS['user_email']  = $GLOBALS['user']->email;
  		$GLOBALS['user_rights'] = $GLOBALS['user']->getRights();
		} else {
			unset($_SESSION['user']);
			$GLOBALS['user']				= '';
			$GLOBALS['user_name']   = '';
  		$GLOBALS['user_email']  = '';
  		$GLOBALS['user_rights'] = array();
		}

		// return authentication succeeded flag
		return ( is_obj($GLOBALS['user'],'cUser') && $GLOBALS['user']->isAuthenticated() );
	}

	function checkRights( $right, $all=FALSE ) {
		if ( empty($right)
			|| !isset($GLOBALS['user_rights'])
			|| !is_array($GLOBALS['user_rights'])
			|| count($GLOBALS['user_rights'])<1
			) return FALSE;
		if ( is_array($right) ) {
			$found=0;
			foreach($right as $r)
				if (in_array($r,$GLOBALS['user_rights'])) ++$found;
			if ( $all && $found==count($right) ) return TRUE;
			if ($found>0) return TRUE;
		} else {
			return (in_array($right,$GLOBALS['user_rights']));
		}
		return FALSE;
  }  // hasRight

	// --- user name and rights initialization -----------------------

	$GLOBALS['user']				= '';
	$GLOBALS['user_name']   = '';
  $GLOBALS['user_email']  = '';
  $GLOBALS['user_rights'] = array();

?>