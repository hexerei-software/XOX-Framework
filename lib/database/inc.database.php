<?PHP

	require_once(dirname(__FILE__).'/class.db.php');

if(!defined("_DATABASE_HELPERS_INCLUDED")){
  define("_DATABASE_HELPERS_INCLUDED", 1 );

  /** database helper script which opens global connection and offers shortcuts for queries.
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
   */

  /** current table */
	if (!isset($table)) $table='';
  /** sorting order */
	$order='';
  /** current page in result */
	$cur_page='1';

  /** open global database connection using database settings from globals */
	$GLOBALS['sysdb'] = new db();

	if (!defined('XOX_DB_HOST')) define('XOX_DB_HOST', ($GLOBALS['db_host'])?$GLOBALS['db_host']:'localhost');
	if (!defined('XOX_DB_NAME')) define('XOX_DB_NAME', ($GLOBALS['db_name'])?$GLOBALS['db_name']:'test');
	if (!defined('XOX_DB_USER')) define('XOX_DB_USER', ($GLOBALS['db_user'])?$GLOBALS['db_user']:'root');
	if (!defined('XOX_DB_PASS')) define('XOX_DB_PASS', ($GLOBALS['db_pass'])?$GLOBALS['db_pass']:'');

	#echo "sysdb->open(".XOX_DB_NAME.",".XOX_DB_HOST.",".XOX_DB_USER.",".XOX_DB_PASS.")<br />\n";
	$GLOBALS['sysdb']->open(XOX_DB_NAME,XOX_DB_HOST,XOX_DB_USER,XOX_DB_PASS);
	if (defined('XOX_DB_ENCODING')) $GLOBALS['sysdb']->setEncoding(XOX_DB_ENCODING);
	#print_r($sysdb);

  /**
   * count number of results for select and return number.
	 * given sql can be a normal query, it will be split at the FROM part and
	 * reassembled to perform as count query automatically.
	 * @param sql the query to use for building the count query
	 * @param distinctField optional name of distinct field to count (defaults to all fields *)
	 * @return number or rows that would be in the result of the query
   */
  function countQuery( $sql='', $distinctField='' ) {
    if ($csql=stristr($sql,'FROM ')) {
			if ($tail=stristr($csql,'GROUP BY ')) $csql = substr($csql,0,-strlen($tail));
			#echo 'SELECT count('.(($distinctField)?'DISTINCT '.$distinctField:'*').') AS num'.' '.$csql;
      $rs = new query($GLOBALS['sysdb'],'SELECT count('.(($distinctField)?'DISTINCT '.$distinctField:'*').') AS num'.' '.$csql);
			echo mysql_error();
      if ($rs->getrow()) {
			$num = $rs->f("num");
			return ($num > 0) ? $num : 0;
		}
		return 0;
    }
    return -1;
  }

  /**
   * execute sql statement and return query object
	 * @param sql the sql query to perform
	 * @return the result from the query
   */
  function executeQuery( $sql='' ) {
    $rs = new query($GLOBALS['sysdb'],$sql);
		//if ( _DEBUG_SQL ) echo "executeQuery: $sql <br />\n";
   return $rs;
  }

  /**
   * execute sql statement and return first row
	 * @param sql the sql query to perform
	 * @return first row from the result of the query
   */
  function fetchRow( $sql='' ) {
		$row=false;
    if ($rs = new query($GLOBALS['sysdb'],$sql.((stristr($sql,' limit '))?'':' limit 1'))) {
			$row=$rs->getrow();
			$rs->free();
		}
   	return $row;
  }

	/**
   * execute sql statement and return first rows first value
	 * @param sql the sql query to perform
	 * @return the value of the first rows first field returned
   */
  function fetchValue( $sql='' ) {
		$value=false;
    if ($rs = new query($GLOBALS['sysdb'],$sql.((stristr($sql,' limit '))?'':' limit 1'))) {
			if ($row=$rs->getrow(0)) $value=(isset($row[0])?$row[0]:false);
			$rs->free();
		}
   	return $value;
  }

	/**
   * execute sql statement and return success
	 * @param sql the sql commands to perform
	 * @return on success true, else false
   */
  function executeSQL( $sql='' ) {
    if(is_object($GLOBALS['sysdb'])){
      if ($result = @mysql_query($sql, $GLOBALS['sysdb']->connect_id)) {
        @mysql_free_result($result);
		if ( _DEBUG_SQL ) {
			if (isset($GLOBALS['fire'])) {
				if ( eregi('^select',$sql) ) {
					$GLOBALS['fire']->info($sql);
				} elseif ( eregi('^insert',$sql) ) {
					$GLOBALS['fire']->warn($sql);
				} elseif ( eregi('^delete',$sql) ) {
					$GLOBALS['fire']->error($sql);
				} else {
					$GLOBALS['fire']->warn($sql);
				}
			} else {
				echo '<pre style="padding:4px;';
				if ( eregi('^select',$sql) ) {
					echo 'border:1px solid blue;background-color:#ccccff;';
				} elseif ( eregi('^insert',$sql) ) {
					echo 'border:1px solid green;background-color:#ccffff;';
				} elseif ( eregi('^delete',$sql) ) {
					echo 'border:1px solid red;background-color:#ffcccc;';
				} else {
					echo 'border:1px solid black;background-color:#cccccc;';
				}
				echo "\">$sql</pre>";
			}
		}
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * execute insert and return new id
	 * @param sql the sql command to insert a new object
	 * @return the id of the inserted object or 0 on error
   */
  function executeInsert( $sql='' ) {
    $id=0;
    if (is_object($GLOBALS['sysdb'])){
		if ( _DEBUG_SQL ) {
			if (isset($GLOBALS['fire'])) {
				$GLOBALS['fire']->warn($sql);
			} else {
				echo '<pre style="padding:4px;';
				echo 'border:1px solid green;background-color:#ccffff;';
				echo "\">$sql</pre>";
			}
		}
      if ($result = @mysql_query($sql, $GLOBALS['sysdb']->connect_id)) {
    		$id = @mysql_insert_id($GLOBALS['sysdb']->connect_id);
        @mysql_free_result($result);
      }else{
      	if (mysql_errno() && _DEBUG_SQL )
           echo "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$sql\n<br>";
      }
		}
 		if ( _DEBUG_SQL ) echo "\n<span style=\"color:green;font-weight:bold;\">Inserted with _id: $id </span><br />\n";
		return $id;
  }

  /**
   * die on database error and display error
   */
  function db_die() {
    $dberror = @mysql_error();
    die($dberror);
  }

  /**
   * print drop down from sql query list with given options
	 * @param sqlcmd query for the options
	 * @param tagname name for the select tag
	 * @param default default selected option
	 * @param tag_attr attributes for the select tag
	 * @param allow_null allow empty selection (defaults to false)
	 * @param update_default update default entry in POST (defaults to false)
	 * @param eval_function function to call for each entry wich returns option
	 * @param data ready data array with key=>value pairs to use for select options
	 * @param id to set as select attribute
	 * @param disabled status at startup
	 * @param multiselect boolean to state if select is multiselect or not
	 * @param multiselectsize the number of entries shown
	 * @return html code for a dropdown select form element containing given data
   */
	function HTMLSelect(
		$sqlcmd 		= '',
		$tagname		= '',
		$default		= null,
		$tag_attr		= '',
		$allow_null		= false,
		$update_default	= false,
		$eval_function	= '',
		$data			= null,
		$id 			= '',
		$disabled 		= false,
		$multiSelect 	= false,
		$multiSelectSize= 5
	)
	{
		$htmlselect = '';
		$firstrowid = '';
		$found_def 	= false;
        $defaultArr	= (is_array($default)) ? $default : array();

		// auto update post value and default if necessary
		if (empty($_POST[$tagname]) && $update_default && !is_null($default)) $_POST[$tagname] = $default;
		elseif (is_null($default)) $default = $_POST[$tagname];

		// construct null option if desired
		if ($allow_null) {
			$firstrowid = 0;
			$htmlselect.='<option value=""';
			if (is_null($default)||empty($default)) $htmlselect.=' selected';
			$htmlselect.='>- - - - - - -</option>'."\n";
		}

		// get data from database if not set allready
		if (!is_array($data) && !empty($sqlcmd)) {
			$data = array();
			$rownum=0;
			if ($rs = executeQuery($sqlcmd)) {
				while($row=$rs->getrow($rownum++)) {
					$data[] = $row;
				}
				$rs->free();
			}
		}

        // build options from data
		if(is_array($data)) {
            foreach($data as $row) {
				if ($firstrowid=='') {
					$firstrowid = $row[0];
					if (is_null($default)) $default = $firstrowid;
				}
				$htmlselect.= '<option value="'.$row[0].'"';
			    if ($default == $row[0] or in_array($row[0],$defaultArr)) {
					$found_def = TRUE;
					$htmlselect.= ' selected>';
				} else {
					$htmlselect.= '>';
				}
				if ( !empty($eval_function) ) {
					if ( strpos($eval_function,'%s') ) {
						eval('$htmlselect.='.sprintf($eval_function,$row[1]));
					} elseif ( function_exists($eval_function) ) {
						eval('$htmlselect.='.$eval_function.'('.$row[1].')');
					} else { $htmlselect.= $row[1]; }
				} else { $htmlselect.= $row[1]; }
				$htmlselect.= "</option>\n";
			}
			if ($update_default) $_POST[$tagname] = (($found_def) ? $default : $firstrowid);
		}

        if (empty($id)) $id=$tagname;
        $disabled = ($disabled) ? 'disabled="disabled"' : '';
        $multiSelect = ($multiSelect) ? 'multiple="multiple" size="'.$multiSelectSize.'"' : '';
		if ( $htmlselect != '' )
		    $htmlselect = '<select name="'.$tagname.'" id="'.$id.'" '.$disabled.' '.$multiSelect.' '.(($tag_attr)?' '.$tag_attr:'').">\n".$htmlselect."</select>\n";
		elseif ( $update_default ) {
			$_POST[$tagname]='';
		}

		return $htmlselect;

	}  // HTMLSelect

}
?>
