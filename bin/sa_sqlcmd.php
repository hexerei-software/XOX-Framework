<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="content-language" content="en" />
		<meta http-equiv="content-script-type" content="text/javascript" />
		<meta http-equiv="content-style-type" content="text/css" />
		<meta http-equiv="imagetoolbar" content="no" />
		<title>SQLCmd - Standalone Version</title>
		<meta name="keywords" content="sql, mysql, sqlcmd, sql commander, command" />
		<meta name="description" content="Compact online database query tool which allows sql file uploads" />
		<link rel="SHORTCUT ICON" href="http://www.hexerei.net/favicon.ico" />
	</head>
	<body>
<?php

//=== D E F I N E S =================================================

define('XOX_DB_HOST','mysql5.1-2-3-praxismarketing.de');
define('XOX_DB_NAME','db98518_2');
define('XOX_DB_USER','db98518_2');
define('XOX_DB_PASS','123mmedia22');

define('XOX_DB_ENCODING','');

define('XOX_APP_BASE',dirname(__FILE__));


//=== F U N C T I O N S =============================================

function postvar($v,$d=''){return (isset($_POST[$v]))?$_POST[$v]:$d; }
function getvar($v,$d=''){return (isset($_GET[$v]))?$_GET[$v]:$d; }

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function mydie($msg) {
	echo "<strong>$msg</strong><br />";
	return false;
}


//=== A C T I O N S =================================================

$action = strtolower(getvar('action'));
switch($action) {
	case 'dump':
		@include_once('lib_dump.php');
		$connection = @mysql_connect(XOX_DB_HOST,XOX_DB_USER,XOX_DB_PASS);
		$dumper = new MySQLDump(XOX_DB_NAME,'dump'.date(YmdHis).'.sql',false,false);
		$dumper->doDump();
		exit();
	case 'info':
		phpinfo();
		exit();
}


//=== M A I N =======================================================

$sql_query = stripslashes(postvar('SQL',getvar('sql',getvar('SQL',"SHOW TABLES FROM ".XOX_DB_NAME))));

$sql_form = '
        <form method="post" name="frmSQL" action="">
            <textarea name="SQL" rows="7" cols="60" wrap="physical" style="float:left;margin-right:20px;">'.htmlspecialchars($sql_query,ENT_NOQUOTES,'UTF-8').'</textarea>
            <input type="submit" value="Ausf&uuml;hren" style="background-color: #F5DEB3">
            <input type="reset" value="Leeren" style="background-color: #Ffeecc" onclick="document.forms[\'frmSQL\'].elements[\'SQL\'].innerHTML=\'\';">
        </form>';

echo "<div id=\"sqlcommander\"><fieldset><legend>SQL-Commandline</legend>$sql_form</fieldset>";

/** /
phpinfo();
/**/

$connection_id  = 0;
$result         = 0;
$error          = 0;
$table_list     = ''; //sessionvar('table_list');

// connect to server
if ($connect_id=mysql_pconnect(XOX_DB_HOST, XOX_DB_USER,XOX_DB_PASS)) {
	if (defined('XOX_DB_ENCODING') && XOX_DB_ENCODING != '')
		@mysql_query("SET NAMES '".XOX_DB_ENCODING."'", $connect_id);

	// select database
	if ($result=@mysql_select_db(XOX_DB_NAME)) {

		// check if table_list is filled
		#if ( empty($table_list) )
        {
			// execute query
			if ($result = @mysql_query('SHOW TABLES FROM '.XOX_DB_NAME, $connect_id)) {
				if ($row = @mysql_fetch_array($result, MYSQL_NUM)) {

					$i = 0;
					$p = '?sql=';
					$f = 'SHOW+FIELDS+FROM+';
					$d = 'SELECT+*+FROM+';
					$table_list = '<div id="sqltablelist">';
					do {
						foreach($row as $value)
							$table_list.= "<div".(($i%2)?' class="odd"':'')
								.'><a href="'.$p.$f.$value.'" class="slink" title="Show structure">S</a>&nbsp;'
								.'<a href="'.$p.$d.$value.'+LIMIT+0,30" class="dlink" title="Show data">'.$value.'</a></div>';
						$i++;
					} while ($row = @mysql_fetch_array($result,MYSQL_NUM));
					$table_list.= "</div>";
				} else {   $error="NO TABLES"; }
			} else {      $error="Could not generate table list!"; }
		}

		$time_start = getmicrotime();

		// upload sql command file
		if ( eregi('^!',$sql_query) ) {

			// load sql commands
			$sqlfile = XOX_APP_BASE.'/'.substr($sql_query,1);
			$sqlcmds = array();
			$total = 0;
			if ( !empty($sqlfile) && file_exists($sqlfile) ) {
				$sqlcmds = file($sqlfile);
				if ( count($sqlcmds) > 0 ) {
					$mcmd = "";
					foreach($sqlcmds as $sqlcmdline) {
						$cmd = trim($sqlcmdline);
						if ( !empty($cmd) && substr($cmd,0,1) != '#' && substr($cmd,0,2) != '--' ) {
							if ( ereg(';$',$cmd) ) {
								$cmd = $mcmd.substr($cmd,0,-1);
								$mcmd = "";
								if ( ++$total % 100 ) {
									set_time_limit(120);
								}
								$result=@mysql_query($cmd, $connect_id);
							} else {
								$mcmd.= " $cmd";
							}
						}
					}
					$error = ($total+0)." Command executed";
					$time_end = getmicrotime();
					$time = $time_end - $time_start;
					echo "<div class=\"timer\">$error in ".$time." seconds</div>";
				} else { $error = "The SQL file is empty!"; }
			} else { $error = "Could not find file!<br>$sqlfile"; }

		} else {

			if ( !empty($sql_query) ) {
				$sqllines = split("[\r\n]",$sql_query);
				if ( count($sqllines) > 0 ) {
					$sqlcmds = array();
					$mcmd = "";
					foreach($sqllines as $sqlcmdline) {
						if ( !ereg('^[#-]',$sqlcmdline) ) {
							$cmd = trim($sqlcmdline);
							if (!empty($cmd)) {
								if ( substr($cmd,-1)==';' ) {
									$sqlcmds[] = $mcmd.substr($cmd,0,-1);
									$mcmd = "";
								} else {
									$mcmd.= " $cmd";
								}
							}
						}
					}
					if (!empty($mcmd)) $sqlcmds[]=$mcmd;
					$total=0;
					foreach($sqlcmds as $cmd) {
						if ( ++$total % 100 ) {
							set_time_limit(120);
						}
						if ($result=@mysql_query($cmd, $connect_id)) {
							$numrows=@mysql_num_rows($result);
							if ($numrows) {
								echo "<b>$numrows rows in the result</b><br />";
								if ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
									$i = 0;
									$result_list = '<table cellspacing="0" cellpadding="2"><tr>';
									foreach($row as $key=>$value)
										$result_list.= "<th>$key</th>";
									$result_list.= "</tr>";
									do {
										$result_list.= "<tr>";
										foreach($row as $value)
											$result_list.="<td".(($i%2)?' class="odd"':'').">$value</td>";
                                		$result_list.= "</tr>";
                                		$i++;
                            		} while ($row=@mysql_fetch_array($result, MYSQL_ASSOC));
									$result_list.= "</table>";
									#echo wrapCorners('',$result_list,'c|c');
                                    echo "<fieldset>$result_list</fieldset>";
								}
							}
						}
					}
					$error = ($total+0)." commands executed";
					$time_end = getmicrotime();
					$time = ($time_end - $time_start) * 1000;
					echo "<div class=\"timer\">$error in ".$time." seconds</div>";
				} else { $error = "No SQL command!"; }
			} else { $error = "No SQL command!"; }

			/* / execute query
			if ($result = @mysql_query($sql_query, $connect_id)) {
				$time_end = getmicrotime();
				$time = ($time_end - $time_start) * 1000;
				echo "<div class=\"timer\">SQL Befehl in ".$time." Sekunden ausgef?hrt</div>";
				echo "<b>".@mysql_num_rows($result)." Zeilen im Ergebnis</b><br />";
				if ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
					$i = 0;
					$result_list = '<table cellspacing="0" cellpadding="2"><tr>';
					foreach($row as $key=>$value)
						$result_list.= "<th>$key</th>";
					$result_list.= "</tr>";
					do {
						$result_list.= "<tr>";
						foreach($row as $value)
							$result_list.= "<td".(($i%2)?' class="odd"':'').">$value</td>";
						$result_list.= "</tr>";
						$i++;
					} while ($row = @mysql_fetch_array($result,MYSQL_ASSOC));
					$result_list.= "</table>";
					#echo wrapCorners('',$result_list,'c|c');
					echo "<fieldset>$result_list</fieldset>";
				} else {   $error="NO RESULTS"; }
			} else {      $error="Could not execute query [$sql_query]"; }
				*/
		}
		echo "\n$table_list\n</div>";
	} else { $error="Could not connect to database [".XOX_DB_NAME."]"; }
} else { $error="Could not connect to database server [".XOX_DB_HOST."] using ".XOX_DB_USER; }

// clean up
if ($error) {
   echo "<b>$error</b><br />";
}
echo @mysql_error();
if ($result) @mysql_free_result($result);
if ($connect_id) @mysql_close($connect_id);

?>
		<script type="text/javascript" language="javascript" for="frmSQL">
		    document.frmSQL.SQL.focus();
		</script>
	</body>
</html>
