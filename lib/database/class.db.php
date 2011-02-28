<?PHP
/*****************************************************************************
  CDB, MySQL module, version 2.4
  Copyright (C) 2000  Hexerei Software Creations
  based on Abstract DB - Copyright (C) 1998  Muze
*****************************************************************************
  Changelog:
  v2.2 15.11.2000
    - modified for use with GSW-Database - http://www.gottfried-schultz.de
  v2.3 23.10.2001
    - modified for use with Klassenkasse-Database - http://www.hexenkinder.de
  v2.4 29.10.2001
    - modified for use with Service Finder - http://www.koenig-kun.de
*****************************************************************************/

if(!defined("_DATABASE_INCLUDED")){
  define("_DATABASE_INCLUDED", 1 );

if(!defined("_DEBUG_SQL")) define("_DEBUG_SQL", FALSE);

	// return proper umlaut sensitive order clause for given fieldname
	function orderUmlaut($fieldname='name') {
		return "REPLACE(REPLACE(REPLACE(REPLACE(LOWER($fieldname),'?','ae'),'?','oe'),'?','ue'),'?','sz')";
	}

  /** abstract mysql database class.
	 this class represents one single database connection.
	 currently only mysql is supported.
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @version 2.4
	 */
  class db {

    /** connection id of the current connection */
	var $connect_id;
	/** database type (currently only "mysql" is supported) */
    var $type;
	/** array of active query ids */
    var $query_id;

    /** constructor.
	 	 currently only mysql is supported.
		 @param database_type what database to use ("mysql" only)
		 */
	function db($database_type="mysql") {
      $this->type=$database_type;
    }

    /** open database connection with user/pass authentication.
		 @param database the name of the database to connect to
		 @param host the database host name
		 @param user the username to use for authentication
		 @param password the password to the given username
		 @return connection id of the current connection
		 */
	function open($database, $host, $user, $password) {
		if ($this->connect_id) $this->close();
      $this->connect_id = @mysql_connect($host, $user, $password);
      if ($this->connect_id) {
        $result=@mysql_select_db($database);
        if (!$result) {
          @mysql_close($this->connect_id);
          $this->connect_id=$result;
        }
      } else mysql_connect($host, $user, $password);
      return $this->connect_id;
    }

    /** close database connection and free ressources
		 @return boolean flag, true on success and false on errors
		 */
	function close() {
      if ($this->query_id && is_array($this->query_id))
        while (list($key,$val)=each($this->query_id))
          @mysql_free_result($val);
      return @mysql_close($this->connect_id);
    }

    /** adds a query object to the array of queries */
	function addquery($query_id) {
      $this->query_id[]=$query_id;
    }

    function setEncoding($enc) {
		if (!empty($enc)) @mysql_query("SET NAMES '$enc'", $this->connect_id);
    }

  } // class db

  /** abstract query class.
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @version 2.4
	 */
  class query {

    /** connection id of the current connection */
    var $connect_id;
    /** the result returned by the query */
    var $result;
    /** the current row within the resultset */
    var $row;
    /** local copy of the sql query string */
		var $m_sql;

    /** constructor performs given query on given database connection
		 @param conn reference to the open database object
		 @param query the sql query to perform
		 */
		function query(&$conn, $query="") {
      if($query!=""){
        if (isset($this->result)) $this->free(); // there may be something to clean up.
				if(is_object($conn)){
          $this->connect_id = $conn->connect_id;
          $this->result = @mysql_query($query, $this->connect_id);
          $conn->addquery($this->result);
		  		$this->m_sql = $query;
  				if ( _DEBUG_SQL ) {
					if (isset($GLOBALS['fire'])) {
						if ( eregi('^select',$query) ) {
							$GLOBALS['fire']->info($query);
						} elseif ( eregi('^insert',$query) ) {
							$GLOBALS['fire']->warn($query);
						} elseif ( eregi('^delete',$query) ) {
							$GLOBALS['fire']->error($query);
						} else {
							$GLOBALS['fire']->warn($query);
						}
					} else {
						echo '<pre style="padding:4px;';
						if ( eregi('^select',$query) ) {
							echo 'border:1px solid blue;background-color:#ccccff;';
						} elseif ( eregi('^insert',$query) ) {
							echo 'border:1px solid green;background-color:#ccffff;';
						} elseif ( eregi('^delete',$query) ) {
							echo 'border:1px solid red;background-color:#ffcccc;';
						} else {
							echo 'border:1px solid black;background-color:#cccccc;';
						}
						echo "\">$query</pre>";
					}
				}
        } else echo "NO OBJECT";
      }
    }

    /** get next row from resultset.
		 returns the first row on first call.
		 @param row optional row number to get distinct row from result
		 @return the result row
		 */
		function getrow($row="-1") {
      $tmp=TRUE;
      if ($row!=-1) {
				$tmp = @mysql_data_seek($this->result,$row);
      	if ($tmp) $this->row = @mysql_fetch_array($this->result, MYSQL_BOTH);
	  		else $this->row = false;
			} else {
				$this->row = @mysql_fetch_array($this->result, MYSQL_ASSOC);
			}
      return $this->row;
    }

    /** returns the number of rows in the resultset
		 @return number of rows in resultset
		 */
		function numrows() {
      return @mysql_num_rows($this->result);
    }

    /** returns the last error
		 @return error string
		 */
		function error() {
      return @mysql_error();
    }

    /** get value of given field in current or given row
		 @param field the name of the field in the table row
		 @param row optional row number (default current row)
		 */
		function f($field, $row="-1") {
      if ($row!=-1) {
        $tmp = @mysql_data_seek($this->result,$row);
        if ($tmp) $this->row = @mysql_fetch_array($this->result, MYSQL_ASSOC);
      }
      if (is_array($this->row) && !empty($this->row[$field]))
        return $this->row[$field];
      return '';
    }
    /** synonym to the f(field,row) function
		 @param field the name of the field in the table row
		 @param row optional row number (default current row)
		 */
		function field($field, $row="-1") {
      return f($field,$row);
    }

    /** returns the first row from the current resultset
		 @return first row or 0 when no result
		 */
		function firstrow() {
      $tmp = @mysql_data_seek($this->result,0);
      if ( $tmp ) {
        $tmp = $this->getrow();
        return $this->row;
      }
      return 0;
    }

    /** returns the last insert id
		 @param id from last insert command
		 */
		function lastinsert() {
      return @mysql_insert_id($this->connect_id);
    }

    /** free resources
		 @return boolean flag for success
		 */
		function free() {
      return @mysql_free_result($this->result);
    }

  } // class query

}
?>
