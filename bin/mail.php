<html>
  <head>
    <style type="text/css">
      input { font-family:Arial,Helvetica,sans-serif; font-weight:normal; }
      .button { font-family:Arial,Helvetica,sans-serif; font-weight:normal; color:#5570a7; background-color:#d9dce0; }
    </style>
  </head>
  <body  bgcolor="#ffffff" text="#000000" link="#cc0000" vlink="#999999" alink="#ffcc00">
    <p><font face="Verdana,Arial,helvetica" size=+1><i><b>Neue E-Mail erstellen</b></i></font></p>
    <font face="Arial,helvetica" size=-1>Bitte beachten Sie, dass die hervorgehobenen Felder ausgef&uuml;llt werden müssen.
    <?php

    $error = "";

		function trimvar($name,$default='') {
			global $$name;
			if ( isset($$name) )
				$$name = trim($$name);
			elseif ( isset($_POST[$name]) )
				$$name = trim($_POST[$name]);
			else
				$$name = trim($default);
		}

    // get trimmed values
    trimvar('from','daniel@hexerei-software.de');
    trimvar('to','daniel@hexerei-software.de');
    trimvar('cc');
    trimvar('subject','Testmail');
    trimvar('message','Hallo Test');

    if (isset($_POST['cmd_send'])) {

      $mail_head  = "From: <$from>\n"
                  . "X-Mailer: PHP".phpversion()."\n";

      if ($cc) $mail_head .= "Cc: <$cc>\n";

      // check input
      if ( $from && $to && $subject && $message ) {
        $error = "Nachricht wurde versandt.";
        if (!mail( $to, $subject, $message, $mail_head ))
          $error = "Nachricht konnte nicht versendet werden.";
      } else {
        $error = "Nicht alle ben&ouml;tigten Felder wurden ausgef&uuml;llt.";
      }

    } elseif (isset($_POST['cmd_info'])) {
      echo phpinfo();
      exit();
    }


    if ($error) echo "<p><font color=\"red\"><b>$error</b></font>";

    ?>
    <form method="post" name="frmMail" action="mail.php">
      <table cellpadding=5 cellspacing=2 border=0 width="550">
        <tr>
          <td colspan=2 bgcolor="#5570a7" height=10><font face="Verdana,Arial,helvetica" size=-1 color=white>Neue E-Mail verfassen</font></td>
        </tr>
        <tr>
          <td height=10 bgcolor="#d9dce0" align="right"><font face="Arial,helvetica" size=-1><b>Absender</b></td>
          <td height=10 bgcolor="#d9dce0"><font face="Arial,helvetica" size=-1><input type=text name="from" value="<?php echo $from; ?>" size="60"></td></tr>
        <tr>
          <td height=10 bgcolor="#d9dce0" align="right"><font face="Arial,helvetica" size=-1><b>An</b></td>
          <td height=10 bgcolor="#d9dce0"><font face="Arial,helvetica" size=-1><input type=text name="to" value="<?php echo $to; ?>" size="60"></td>
        </tr>
        <tr>
          <td height=10 bgcolor="#d9dce0" align="right"><font face="Arial,helvetica" size=-1>Kopie</td>
          <td height=10 bgcolor="#d9dce0"><font face="Arial,helvetica" size=-1><input type=text name="cc" value="<?php echo $cc; ?>" size="60"></td>
        </tr>
        <tr>
          <td height=10 bgcolor="#d9dce0" align="right"><font face="Arial,helvetica" size=-1><b>Betreff</b></td>
          <td height=10 bgcolor="#d9dce0"><font face="Arial,helvetica" size=-1><input type=text name="subject" value="<?php echo $subject; ?>" size="60"></td>
        </tr>
        <tr>
          <td height=10 bgcolor="#d9dce0" align="right"><font face="Arial,helvetica" size=-1><b>Text</b></td>
          <td height=10 bgcolor="#d9dce0"><font face="Arial,helvetica" size=-1><textarea name="message" rows="10" cols="60" wrap=vitual><?php echo $message; ?></textarea></td>
        </tr>
        <tr>
          <td height=10  ><font face="Arial,helvetica" size=-1></td>
          <td height=10 align=right><font face="Arial,helvetica" size=-1>
          <input name="cmd_send" type="submit" value="Versenden" class="button">
          <input name="cmd_info" type="submit" value="PHP Info" class="button"></td>
        </tr>
      </table>
    </form>
  </body>
</html>
