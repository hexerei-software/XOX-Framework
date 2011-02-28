<?php

	$mod_lang 		= ( isset($GLOBALS['xox_language_id']) ) 	? $GLOBALS['xox_language_id'] : 'de';
	$inp_username = ( isset($_POST['inp_username']) ) 			? $_POST['inp_username'] 			: '';
	$render = TRUE;

	include_once(XOX_APP_BASE."/$mod_lang/lng.xox.php");
	include_once(XOX_APP_BASE."/$mod_lang/lng.error.php");

	// check if user is allready authenticated
	if ( !auth() ) {

		// check if forgot password service was performed
		if (isset($_POST['cmd_lost'])||isset($_POST['cmd_lost_x'])) {
			// must have a username
			if ( trim($inp_username)=='' ) {
  		  $error = ERROR_XOX_LOGIN_USERNAME_MISSING;
  		} else{
				$result = auth('lost',$inp_username);
  	  	if ( is_array($result) ) {
					$recipient = $result['email'];
					if ( !empty($result['displayname']) ) $recipient = str_replace('"',"'",$result['displayname'].' <'.$result['email'].'>');
					$subject = defined('LANG_XOX_LOGIN_LOST_MAIL_SUBJECT') ? LANG_XOX_LOGIN_LOST_MAIL_SUBJECT : $GLOBALS['xox_html_title'].' Service';
					$text = defined('LANG_XOX_LOGIN_LOST_MAIL_TEXT') ? sprintf(LANG_XOX_LOGIN_LOST_MAIL_TEXT,$result['password']) : $result['password'];
					$text.= defined('LANG_XOX_LOGIN_LOST_MAIL_FOOTER') ? LANG_XOX_LOGIN_LOST_MAIL_FOOTER : '';
					$from = defined('LANG_XOX_LOGIN_LOST_MAIL_FROM') ? "From: ".str_replace('"',"'",LANG_XOX_LOGIN_LOST_MAIL_FROM).">\r\n" : (empty($_SERVER['SERVER_NAME']) ? '' : "From: ".str_replace('"',"'",$GLOBALS['xox_html_title'])." Service <noreply@".$_SERVER['SERVER_NAME'].">\r\n");
					//echo "<pre>sending mail to $recipient\nBetreff: ".$subject."\n\n".$text."\n".$from.'</pre>';
					if (@mail( $recipient, $subject, $text, $from )) {
  	  	  	$error = LANG_XOX_LOGIN_LOST_SENT;
					} else {
  	  	  	$error = ERROR_XOX_LOGIN_LOST_FAILED;
					}
  	  	} else {
  	  	  $error = ERROR_XOX_LOGIN_LOST_FAILED;
  	  	}
			}

		// check if login was performed
		} elseif (isset($_POST['cmd_login'])||isset($_POST['cmd_login_x'])) {
			// must have a username
			if ( trim($inp_username)=='' ) {
  		  $error = ERROR_XOX_LOGIN_USERNAME_MISSING;
  		} else{
				// try to authenticate with given username and password
  	  	if ( auth('login',$inp_username,$_POST['inp_password']) ) {
					// redirect to startpage
					$jumpurl = (!isset($xox_jump_url)) ? XOX_WWW_PAGE.'?p='.$mod_lang : $xox_jump_url;
					if (!headers_sent()) {
						header('Location: '.$jumpurl);
					} else {
						echo '<script language="javascript">document.location.href=\''.$jumpurl.'\';</script>';
						if (defined('LANG_XOX_BROWSER_REDIRECT')) echo sprintf(LANG_XOX_BROWSER_REDIRECT,$jumpurl);
					}
    			$render = FALSE;
  	  	} else {
  	  	  $error = ERROR_XOX_LOGIN_FAILED;
  	  	}
  	  }
  	}

		// only render loginpage if desired
		if ( $render ) {

			if ( defined('LANG_XOX_LOGIN_TEMPLATE') ) {

				$form_login = new xoxSimpleTemplate('',LANG_XOX_LOGIN_TEMPLATE);
				if (isset($error)) $form_login->setVar('error',$error);
				if (isset($login_vars)) $form_login->setVar($login_vars);
				$form_login->setVar('inp_username',$inp_username);
				$form_login->show();

			} else {

				echo '<div id="login_area">';

				if (isset($error)) echo '<p align="center" class="serror">'.$error.'</p>';

				$form_login = '<div id="login_form"><form method="POST" name="frmLogin"><table border="0" cellspacing="0"><tr>';
				$form_login.= '<td nowrap valign="middle" align="right"><label for="inp_username">'.LANG_XOX_LOGIN_USERNAME.'</label>&nbsp;</td>';
				$form_login.= '<td nowrap valign="top" align="left"><input align="left" maxlength="255" name="inp_username" size="16" type="text" value="'.$inp_username.'"></td>';
				$form_login.= '</tr><tr>';
				$form_login.= '<td nowrap valign="middle" align="right"><label for="inp_password">'.LANG_XOX_LOGIN_PASSWORD.'</label>&nbsp;</td>';
				$form_login.= '<td nowrap valign="top" align="left"><input align="left" maxlength="255" name="inp_password" size="16" type="password" value=""></td>';
				$form_login.= '</tr><tr>';
				$form_login.= '<td align="right" colspan="2"><br><input name="cmd_login" type="submit" value="'.LANG_XOX_LOGIN_BUTTON.'" ';
				$form_login.= 'class="button" onmouseover="this.style.border=\'1 inset\'" onmouseout="this.style.border=\'1 outset\'"></td>';
				$form_login.= '</tr></table></form></div>';

				echo getBoxed(LANG_XOX_LOGIN_CAPTION,$form_login,'b').'<br />';

				if ( defined('LANG_XOX_LOGIN_LOST') ) {

					$form_lost = '<div id="login_lost_form"><form method="POST" name="frmForgot"><table border="0" cellspacing="0"><tr>';
					$form_lost.= '<td valign="top" align="left" colspan="2"><h4>'.LANG_XOX_LOGIN_LOST_TITLE.'</h4>'.LANG_XOX_LOGIN_LOST_TEXT.'<br><br></td>';
					$form_lost.= '</tr><tr>';
					$form_lost.= '<td nowrap valign="middle" align="right"><label for="inp_username">'.LANG_XOX_LOGIN_USERNAME.'</label>&nbsp;</td>';
					$form_lost.= '<td nowrap valign="top" align="left"><input align="left" maxlength="255" name="inp_username" size="16" type="text" value="'.$inp_username.'"></td>';
					$form_lost.= '</tr><tr>';
					$form_lost.= '<td align="right" colspan="2"><br><input name="cmd_lost" type="submit" value="'.LANG_XOX_LOGIN_LOST_SEND.'" ';
					$form_lost.= 'class="button" onmouseover="this.style.border=\'1 inset\'" onmouseout="this.style.border=\'1 outset\'"></td>';
					$form_lost.= '</tr></table></form></div>';

					echo getBoxed(LANG_XOX_LOGIN_LOST,$form_lost,'b');
				}

				echo '</div>';
			}
		}

	} else {

		if (isset($_POST['cmd_logout'])||isset($_POST['cmd_logout_x'])) {
  	  if ( !auth('logout') ) {
				echo '<script language="javascript">document.location.href=\''.XOX_WWW_PAGE.'\';</script>';
				if (defined('LANG_XOX_BROWSER_REDIRECT')) echo sprintf(LANG_XOX_BROWSER_REDIRECT,XOX_WWW_PAGE);
  	    $render = FALSE;
  	  }
  	}

		if ( $render ) {
			$form_logout = '<form method="POST" name="frmLogout"><p>'.LANG_XOX_LOGOUT_TEXT.'</p>';
			$form_logout.= '<p align="right"><input name="cmd_logout" type="submit" value="'.LANG_XOX_LOGOUT_BUTTON.'" ';
			$form_logout.= 'class="button" onmouseover="this.style.border=\'1 inset\'" onmouseout="this.style.border=\'1 outset\'">&nbsp;&nbsp;&nbsp;';
			$form_logout.= '<input type="button" class="button" name="cmd_no" value="'.LANG_XOX_NO.'" onmouseover="this.style.border=\'1 inset\'" onmouseout="this.style.border=\'1 outset\'" onclick="location.href=\''.XOX_WWW_PAGE.'\'" /></p>';
			$form_logout.= '</form>';
			echo getBoxed(LANG_XOX_LOGOUT_CAPTION,$form_logout,'b',480);
		}

	}

?>
