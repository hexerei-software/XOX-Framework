
<?php

	/************************************************************************
	 * form handler v1.3
	 * (c) 2002-2004 hexerei software creations
	 * author: daniel vorhauer
	 ************************************************************************/

	// === D A T A ==========================================================

	$hosts_allow = array(
		'www.archefilo.de',
		'www.arche-filo.de',
		'www.archefilo.net',
		'www.arche-filo.net',
		'www.archefilo.com'
	);
	// set defaults to your server in case they are not set
	if (!isset($_SERVER['SERVER_NAME'])) 	$_SERVER['SERVER_NAME']	= $hosts_allow[0];
	if (!isset($_SERVER['HTTP_HOST'])) 		$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
	if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER']	= '';
	
	// copy servername to hosts_allow
	$hosts_allow[] = $_SERVER['HTTP_HOST'];
	$hosts_allow[] = $_SERVER['SERVER_NAME'];

	// set defaults
	$config['_mail_to']       	= '';
	$config['_mail_bcc']      	= '';
	$config['_mail_subject']  	= 'Anfrage vom Kontaktformular';
	$config['_mail_header']   	= "###START XOX 1.5 automatische email benachrichtigung ###\n\n";
	$config['_mail_footer']   	= "\n###ENDE";
	$config['_mail_from']     	= '';
	$config['_mail_confirm']  	= '';
	$config['_mail_cheader']  	= "Folgende Angaben wurden an uns gesendet:\n\n";
	$config['_mail_cfooter']  	= "\nMit freundlichen Grüssen,\n\nIhr Markmann-Consulting Team!";

	$config['_html_return']   	= $_SERVER['HTTP_REFERER'];
	$config['_html_confirm']  	= '';
	$config['_html_thanks']   	= '';

	$config['_database_host'] 	= '';
	$config['_database_user'] 	= '';
	$config['_database_pass'] 	= '';
	$config['_database_name'] 	= '';
	$config['_save_database'] 	= '';

	$config['_csv_directory'] 	= '/tmp';
	$config['_save_csv']      	= '';

	$config['_mandatory']				= '';

	// array of formfields
	$store = array();
	$missing = 0;

	// get http params and parse each entry
	$params = array_merge($HTTP_GET_VARS,$HTTP_POST_VARS);
	foreach( $params as $key=>$val ) parse_key($key,$val);


	// === M A I N ==========================================================

	// check if page is refered from our server
	check_referer(((empty($_SERVER['HTTP_REFERER']))?$config['_html_return']:$_SERVER['HTTP_REFERER']));

	// check if we have action to take
	if (empty($config['_html_return'])
		||(empty($config['_html_confirm'])
		&&empty($config['_mail_to'])
		&&empty($config['_save_csv'])
		&&empty($config['_save_database'])))
		missing_action();

	// check if mandatory fields are entered or cancel was pressed
	if ( !empty($config['_mandatory']) ) {
		if ( ereg(',',$config['_mandatory']) ) {
			$amkeys = split( ',', $config['_mandatory'] );
			foreach( $amkeys as $mkey ) if (!isset($store[$mkey])) {
				$store[$mkey]="__ERROR__ ";
				++$missing;
			}
		} elseif (!isset($store[$config['_mandatory']])) {
			$store[$config['_mandatory']]="__ERROR__ ";
			++$missing;
		}
	}		

	if ($missing||isset($_cancel)) {
		write_errors($config['_html_return'],$store);

  	// show confirm page
  } elseif (!empty($config['_html_confirm'])) {
		parse_html($config['_html_confirm'],$store);

	// perform all actions
	} else {
  		if ($config['_mail_to'])       			write_mail();
  		if ($config['_save_csv'])      			write_csv($config['_csv_directory'].'/'.$config['_save_csv'].'.csv');
  		if ($config['_save_database']) 			write_database($config['_save_database']);
  		if ($config['_mail_confirm']) 			write_receipt();
  		if (empty($config['_html_thanks'])) parse_html($config['_html_return'],$store);
  		else                                parse_html($config['_html_thanks'],$store);
	}

  	exit();


	// === C O D E ==========================================================

	/**
	 *	make sure the script is called from this server only
	 * otherwise exit to block usage
	 */
	function check_referer($ref) {
		// get host name from URL
		preg_match("/^http:\/\/([^\/]+)/i",$ref, $matches);
		$host = (isset($matches[1]))?$matches[1]:'';
  	if (!($host==''||in_array($host,$GLOBALS['hosts_allow']))) {
			print "<html><head><title>ZUGRIFF UNERW&UUML;NSCHT</title></head><body><h3 style=\"color:red;\"></h3>Sie sind nicht berechtigt auf diesen Service über '$host' zuzugreifen.</body></html>";
 			die();
  	}
  } // check_referer


	/**
	 * parse key/value and split to configuration and
	 * variable storage. check mandatory and email and urls.
	 */
	function parse_key( $key, $value ) {
  	  global $config,$store,$missing;
  	  switch (substr($key,0,1)) {
				// configuration variables
  	  	case '_':
  	  		  $config[$key]=stripslashes($value);
  	  		break;
				// mandatory variables
				case '#':
  	  		  $key=substr($key,1);
  	  		  if (
						/* check for empty values */
						empty($value) || trim($value)==''
						/* check for valid urls */
						|| (eregi("^(url|href|link)(_|$)",$key)
						&& !eregi('^(((f|ht)tp(s?)|(fil|r)e(s?)):/+)?(([[:alnum:]_-]+).)?([[:alnum:]_-]+)\.([[:alnum:]]+)(:[0-9]+)?(/|$)',$value))
						/* check for valid email */
						|| (eregi("^e(-)?mail",$key)
						&& !eregi('^[[:alnum:]._-]+@[[:alnum:]._-]+\.[[:alnum:]._-]+$',$value)) ) {
							$value="__ERROR__$value";
							$missing++;
						}
				// all other variables
				default:
  	  		  if (empty($config['_mail_from'])&&eregi("^e(-)?mail",$key)) $config['_mail_from']=$value;
						if (is_array($value))
	  	  		  $store[$key]=stripslashes(join('|',$value));
						else
	  	  		  $store[$key]=stripslashes($value);
  	  		break;
  	  }
  }  // parse_key


	/**
	 * write email to webmaster
	 */
	function write_mail() {
		global $config,$store;

		// make sure we have a valid to adress
		$to = trim($config['_mail_to']);
		if (empty($to)||!eregi('^[[:alnum:]._-]+@[[:alnum:]._-]+\.[[:alnum:]._-]+$',$to))
			return FALSE;

		// add from and bcc adress if present
		$mail_head = "X-Mailer: XOXPHP".phpversion()."\n";
		if (!empty($config['_mail_from'])) $mail_head.= "From: <".$config['_mail_from'].">\n";
      if (!empty($config['_mail_bcc'])) $mail_head .= "Bcc: <".$config['_mail_bcc'].">\n";

		// make sure we have a subject
		$subject = stripslashes($config['_mail_subject']);
		if (empty($subject)) $subject = "XOX UFM Forms Automail";

		// build message
		$message = $config['_mail_header'];
		reset($store);
    	while (list($key, $val)=each($store)) $message.="$key: $val\n";
		$message .= $config['_mail_footer'];

      // send mail
     	return (mail( $to, $subject, $message, $mail_head ))? TRUE : FALSE;

	}  // write_mail


	/**
	 * write receipt email to user
	 */
	function write_receipt() {
		global $config,$store;

		// make sure we have a valid to adress
		$to = trim($config['_mail_from']);
		if (empty($to)||!eregi('^[[:alnum:]._-]+@[[:alnum:]._-]+\.[[:alnum:]._-]+$',$to))
			return FALSE;

		// add from adress if present
		$mail_head = "X-Mailer: XOXPHP".phpversion()."\n";
		if (!empty($config['_mail_to'])) $mail_head.= "From: <".$config['_mail_to'].">\n";

		// make sure we have a subject
		$subject = stripslashes($config['_mail_subject']);
		if (empty($subject)) $subject = "XOX UFM Forms Automail";

		// build message
		$message = $config['_mail_cheader'];
		reset($store);
    	while (list($key, $val)=each($store)) $message.="$key: $val\n";
		$message .= $config['_mail_cfooter'];

      // send mail
     	return (mail( $to, $subject, $message, $mail_head ))? TRUE : FALSE;

  	}  // write_receipt


	/**
	 * write comma seperated values to file
	 */
	function write_csv($csv_name) {
		global $store;

		// try to open file and append
		if ($fp=fopen($csv_name,"ab"))
			fwrite($fp,join($store,",")."\n");

  	}  // write_csv


	/**
	 * insert fields into database
	 */
	function write_database($table_name) {
  	global $store,$config;

		// build sql string
		$sql = "INSERT INTO $table_name SET ";
  	  	reset($store);
  	  	while (list($key, $val)=each($store)) $sql.="$key='".ereg_replace("'","\\'",$val)."', ";
		$sql = substr($sql,0,-2);

		// write to mysql database
		$cid = @mysql_pconnect($config['_database_host'], $config['_database_user'], $config['_database_pass']);
    if ($cid) {
      if (@mysql_select_db($config['_database_name']))
      	if ($result=@mysql_query($sql,$cid)) @mysql_free_result($result);
    	@mysql_close($cid);
    }

  }  // write_database


	/**
	 * show error if no action was configured
	 */
	function missing_action() {
		die("<html><body><h3>Warnung an den Webmaster</h3>Es wurde keine Mailadresse, "
			."	CSV-Datei oder Tabelle angegeben in denen die Formulardaten gespeichert werden sollen."
			."</body></html>");
  }


	//=== template handling ======================================================

	/**
	 * return to form and display errors if any
	 */
	function write_errors($template,$vars) {
		global $missing;
		$template=absolute_url($template);
		
		error_reporting(1);
		// read entire template from url
  	if(!($template_content=join('',file($template))))
  	  	die("FATAL ERROR! Can't read template $template");

  	header("Content-Location: $template");
  	header("Content-Type: text/html; charset=ISO-8859-1");
  	header("Referer: $template");

  	// copy to preg params
  	$regs=$values=array();
  	$regs[] = "/<head>/i";
  	$values[] = '<head><base href="'.$template.'" />';
  	if ($missing) {
  	  $regs[] = '/<![- ]*(fehler|error) \s*(.*)([- ]+)->/i';
  	  $values[] = '$2';
  	}
  	reset($vars);
  	while (list($name, $value)=each($vars)) {
  	  	$regs[] = "/(input name=[\"']?[#]?".$name."[\"']?) *(value=[\"']?([^\"']*)[\"']?)?/i";
  	  	$regs[] = "/(textarea name=[\"']?[#]?".$name."[\"']?.*)>.*<\/textarea>/i";
  	  	if (!ereg('^__ERROR__',$value)) {
  	  	  $values[] = '$1 value="'.htmlentities(stripslashes($value)).'"';
  	  	  $values[] = '$1>'.htmlentities(stripslashes($value)).'</textarea>';
  	  	} else {
  	  	  	$values[] = '$1 value="'.htmlentities(stripslashes(substr($value,9))).'"';
  	  	  	$values[] = '$1>'.htmlentities(stripslashes(substr($value,9))).'</textarea>';
  	  	  	$regs[] = '/<![- ]*(fehler|error)_'.$name.' \s*(.*) -->/i';
  	  	  	$values[] = '$2';
  	  	}
  	}

  	// transform template and output result
  	$result = (!count($regs)) ? $template_content : preg_replace($regs,$values,$template_content);
  	echo $result;
  }


	/**
	 * parse html and replace variables
	 */
	function parse_html($template,$vars) {
		
		$template=absolute_url($template);
		
		error_reporting(1);
  	// read entire template from url
  	if(!($template_content=join('',file($template))))
  	die("FATAL ERROR! Can't read template $template");

  	header("Content-Location: $template");
  	header("Content-Type: text/html; charset=ISO-8859-1");
  	header("Referer: $template");

  	// copy to preg params
  	$regs=$values=array();
  	$regs[] = '/<![- ]*(erfolg|success) \s*(.*)([- ]+)->/i';
  	$values[] = '$2';

  	$regs[] = "/<head>/i";
  	$values[] = '<head><base href="'.$template.'" />';

		$allvars='';
		foreach($vars as $name=>$value) {
  	  $regs[] = "/%$name%/";
  	  $values[] = htmlspecialchars(stripslashes($value));
			$allvars .= htmlspecialchars(stripslashes($name)).':'.htmlspecialchars(stripslashes($value))."<br />\n";
  	}

		$regs[] = "/%store%/";
		$values[] = $allvars;

  	// remove unknown variables
		$regs[] = '@%\s*([\w\d_-]+)\s*%@sm';
		$values[] = '';

  	// transform template and output result
  	print preg_replace($regs,$values,$template_content);
  }

	//compose_url does the reverse as the standard parse_url
	function compose_url($url_array) {
		$url = "";
		extract($url_array); //extract variables
		if (isset($scheme)) $url = "$scheme:";
		if (isset($host)) {
			$url .= "//$host";
			if (isset($port)) $url .= ":$port";
		}
		if (isset($path)) $url .= (!ereg('^/',$path))?"/$path":$path;
		if (isset($query)) {
			if (!isset($path)) $url .= "/";
			$url .= "?$query";
		}
		if (isset($fragment)) $url .= "#$fragment";
		return $url;
	}

	//return the absolute url given a (possibly relative) url and the document's
	// absolute base url.
	function absolute_url($url,$base='') {

		if (empty($base)) $base = $_SERVER['PHP_SELF'];

		extract(parse_url($url));
		extract(parse_url($base), EXTR_PREFIX_ALL, "B");

		if (!isset($B_scheme)) 	$B_scheme = 'http';
		if (!isset($B_host)) 		$B_host		= (!empty($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
		if (!isset($B_path)) 		$B_path		= '/';

		if (!isset($scheme)) $scheme = $B_scheme;
		if (!isset($host)) {
			$host = $B_host;
		 	if (isset($B_port)) $port = $B_port;
		}

		if (!isset($path)) {
		 	$path=$B_path;
		 	if (!isset($query) && isset($B_query)) $query=$B_query;
		} elseif (!preg_match("@^/@", $path)) {

		 	$path = preg_replace("@/[^/]*$@", "/", $B_path).$path;

		 	$oldpath = "";
		 	do {$oldpath=$path; $path=preg_replace('@/\./@','/',$path);}
		 	while($path!=$oldpath);
		 	$path=preg_replace('@/\.$@', '/', $path);
		 	do {$oldpath=$path; $path=preg_replace('@/[^/]*/\.\./@','/',$path);}
		 	while($path!=$oldpath);
		 	$path=preg_replace('@/[^/]/\.\.$@','/',$path);
		 	$path=preg_replace('@/\.\./@','/',$path);
		}

		$url_array = compact('scheme','host','port','path','query','fragment');
		return compose_url($url_array);
	}

?>