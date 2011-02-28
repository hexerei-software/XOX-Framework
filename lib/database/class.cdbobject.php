<?PHP

	/** the name of the global variable holding the systems frontend language iso code */
	define('CDB_SYSTEM_LANGUAGE_VARNAME','xox_language_id');

	require_once('inc.database.php');

  /** class database object base
   DO NOT INSTANTIATE DIRECTLY! this is only a base class
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @version 1.8
	 */
  class cDBObjectBase {

    var $_id    	= 'id';		/**< name of the primary key field */
    var $_table		= '';       /**< name of the source table */
	var $_error		= array();	/**< collection of last errors */
	var $_lang  	= '';		/**< system language */
	var $_updates	= array();	/**< collection of updated fields */

    /** constructor calls objects load function
		@param cid the id of the object to load, if empty an empty class will be created
		*/
	function cDBObjectBase($cid='') {
      $this->load($cid);
    }

	function create($data) {
		if (!is_array($data)) return false;
		$class = (isset($this)) ? get_class($this) : get_class();
		$obj = new $class();
		$obj->set($data);
		$obj->save();
		return $obj;
	}
	
	function findID($cid) {
		if ($row = $this->getrow($cid)) {
			$class = (isset($this)) ? get_class($this) : get_class();
			$obj = new $class();
			$obj->set($row);
			return $obj;
		}
        return false;
	}
	
	function update($cid, $data) {
		$obj = $this->findID($cid);
		if ($cid>0 && is_object($obj) && (is_array($data)||is_object($data))) {
			$obj->set($data);
			$obj->save();
			return $obj;
		}
		return false;
	}
	
	function destroy($cid) {
		$obj = $this->findID($cid);
		if ($cid && is_object($obj)) {
			return $obj->delete();
		}
		return false;
	}
	
	function getrow($cid) {
		return fetchRow("SELECT * FROM $this->_table WHERE $this->_id='$cid'");
	}
	
	function getall(&$total,$limit=0,$start=0,$sort='',$where='',$fields='*',$tables='') {
		if (empty($tables)) $tables = $this->_table;
		$where = ($where>'') ? "WHERE $where" : '';
		$sql = "SELECT $fields FROM $tables $where";
		$total = countQuery($sql);
		$rows = array();
		$limit = ($limit>0) ? "LIMIT $start,$limit" : ''; 
		$sort = ($sort>'') ? "ORDER BY $sort" : '';
        if ($rs = executeQuery("$sql $sort $limit")) {
			while($row = $rs->getrow()) $rows[] = $row;
			$rs->free();
		}
        return $rows;
	}
	
	function gethash() {
		$vars = get_object_vars($this);
		$public_vars = array();
		foreach($vars as $var=>$val) if (substr($var,0,1)!='_') $public_vars[$var] = $val;
		return $public_vars;
	}

    /** shortcut function to get object id
		@return the id of the current object if set, else 0
	*/	
	function getID() {
		$idtag = $this->_id;
		if (!isset($this->$idtag)) $this->$idtag = 0;
		//if (XOX_DEBUG_MODE) echo '<h4>'.$this->_table.' '.$this->_id.':'.$this->$idtag.'</h4>';
		return $this->$idtag;
	}  // getID

    /** loads object from table using primary key
		 @param cid the id of the object to load
		 @return the id of the loaded object
		 */
	function load($cid='') {
      $this->reset();
      if ($this->_table && $cid && $cid!='new') {
        $rs = executeQuery("SELECT * FROM $this->_table WHERE $this->_id='$cid'");
        $this->set($rs->getrow());
        $rs->free();
      }
      return ($this->getID());
    }  // load

    /** base function to approve validity */
		function isValid() {return TRUE; }  // isValid

    /** saves the object to its table.
		 if the objects id is set, it will be updated in the table.
		 if no id is set, then the object will be inserted into the table.
		 @return the id of the updated/inserted object
		 */
		function save() {
			if (!$this->isValid()) return FALSE;
			if ($sql = $this->getSaveQuery()) {
				if ($this->getID()) {
					if ( executeSQL($sql) ) return $this->getID();
					$this->_error['update'] = mysql_error();
				} else {
					$idtag = $this->_id;
					if ($this->$idtag = executeInsert($sql)) {
						return $this->$idtag;
					}
					$this->_error['insert'] = mysql_error();
				}
			}
			return FALSE;
		}  // save
		
		function getSaveQuery() {
			$sql = '';
			foreach ($this as $key=>$value)
				if ($key != $this->_id && substr($key,0,1) != "_")
					$sql.="$key='".$this->dbquote($value)."',";
			if (empty($sql)) return FALSE;
			$sql=substr($sql,0,-1);
			if ($this->getID())
				return "UPDATE $this->_table SET $sql WHERE $this->_id='".$this->getID()."'";
	    	return "INSERT INTO $this->_table SET $sql";
		} 

    /** delete the object from the database */
		function delete() {
      if ($this->getID() && $this->_table) {
        if (executeSQL("DELETE FROM $this->_table WHERE $this->_id='".$this->getID()."'")) {
          $this->reset();
          return TRUE;
        }
      }
      return FALSE;
    }  // load

		/**
			this function will delete the whole table (eg. for Import reasons)
			@note USE WITH CARE!!
		  @author Lutz Dornbusch
		  @date 2005-03-08
		*/
		function deleteAll(){
			executeSQL('TRUNCATE TABLE '.$this->_table);

		}

    /** set the variables of the class.
		 this functions copy the key->values of an array or another
		 object to the current instance. used mainly for reading
		 the object from the database, you can also pass post data
		 if form fields are named like table fields.
		 @param arr can be an array or an object
	*/
	function set($arr) {
		if (!$arr || (!is_array($arr) && !is_object($arr))) return FALSE;
		$idtag = $this->_id;
  		if (is_array ($arr)) {
			foreach ($this as $key=>$value) {
				if (isset($arr[$key]) && substr($key,0,1) != '_') {
					/*&& !empty($arr[$key])*/
  	        		$this->$key=$arr[$key];
					//echo '<br>aseting '.$key.' to '.$arr[$key];
				}
			}
			if (isset($arr[$idtag])) $this->$idtag=$arr[$idtag];
			return TRUE;
  		} elseif (is_object($arr)) {
  	    	foreach ($this as $key=>$value)  {
  	      		if (isset($arr->$key) && substr($key,0,1) != '_') {
					/*&& !empty($arr[$key])*/
  	        		$this->$key=$arr->$key;
					//echo '<br>osetting '.$key.' to '.$arr->$key;
				}
			}
			if (isset($arr->$idtag)) $this->$idtag=$arr->$idtag;
			return TRUE;
		}
		return false;
    }  // set

    /** get all variables of this class as an array.
		 usually all variables starting with _ wil be ignored, unless the all parameter is set to true.
		 @param all set to true to get private variables as well
		 @return named (key/value) array with all variables
		 */
	function get($all=FALSE) {
		$aVal = array();
		foreach ($this as $key=>$value) if ($all || substr($key,0,1) != '_') $aVal[$key] = $this->$key;
		return $aVal;
    }  // get

    /** utility function to retreive data
		 @param class the class to use
		 @param id the id of the object
		 @param key the key of the value to retreive
		 @return either the data or FALSE on failure to retreive a value
		*/
		function getData ($class, $id = 0, $key = "") {
			static $data;
			if ($id) {
				$val = &$data[$class][$id];
				if (!isset ($val)) $val = new $class ($id);
				if (isset ($key) && isset($val->$key)) {
					return $val->$key;
				} else return ($val);
			} else return FALSE;
    }  // getPerson

    /** get the last error message.
		 @return error string
		*/
		function getLastError() {
			$last_error='';
			foreach($this->_error as $key=>$err)
				if (!empty($err)) $last_error=$err;
			return $last_error;
		}

		/** get all errors as html formatted string
		 @return html string with all errors
		*/
		function getErrors() {
			$error = implode('</p><p>',$this->_error);
			return (!empty($error)) ? "<p>$error</p>" : '';
		}

		/** base function to override which sets all variables back to default values */
		function setDefault () {  }  // setDefault

    	/** reset all variables */
		function reset() {
			// good place to set the language ?
			if ( $this->_lang=='' ) $this->_lang = ( isset($GLOBALS[CDB_SYSTEM_LANGUAGE_VARNAME]) ? $GLOBALS[CDB_SYSTEM_LANGUAGE_VARNAME] : 'en' );
      		// avoid resetting internal vars - so remember to reset them if desired by implementing own reset
			foreach ($this as $key=>$value)
				if (substr($key,0,1) != "_") $this->$key='';
			// reset id to zero
			$idtag = $this->_id;
			$this->$idtag='0';
		}  // reset

		/** make string db safe
		 @param v the string to quote
		 @return the quoted string
		*/
		function dbquote($v) {
			return addslashes(stripslashes($v));
		}

		/** generates html formatted debug output of the object */
		function debug() {
			echo '<table cellpadding="4" cellspacing="0" border="1">';
			foreach ($this as $key=>$value) if (substr($key,0,1)=='_') echo "<tr><td style=\"background:#cccccc;\"><b>$key</b></td><td style=\"background:#efefef;\">&nbsp;$value</td></tr>\n";
			foreach ($this as $key=>$value) if (substr($key,0,1)!='_') echo "<tr><td style=\"background:#eeeeee;\"><b>$key</b></td><td>&nbsp;$value</td></tr>\n";
			echo '</table>';
		}  // debug

	    function showCard() {
	        $info = "<table border='1'' cellspacing=0 cellpadding=0 width=350>";
	        $info .= "<tr><th align=center colspan=2>$this->_table info</th></tr>";
	        foreach($this as $key=>$value) {
	            if ($key != $this->_id && substr($key,0,1) != "_") {
	                $info .= "<tr>";
	                $info .=    "<td align=right>$key</td>";
	                $info .=    "<td align=left><b>$value</b></td>";
	                $info .= "</tr>";
	            }
	        }
	        $info .= "</table>";
	        return $info;
	    }

	} // class cDBObjectBase

?>