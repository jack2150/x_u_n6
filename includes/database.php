<?php
/*
* Database connector class
*
* <b>Example:</b>
* <code>
* $db = new database( 'localhost', 'root', '', 'mambo', 'mos_' );
* $db->setQuery( "SELECT * FROM #__users" );
* if ($db->query()) {
*	echo 'ok';
* } else {
*	echo $db->stderr();
* }
* </code>
*
* @package MOS
* @subpackage LoudMouth
* @author Andrew Eddie <eddieajau@user.sourceforge.net>
*/

class database {
	/** @var string Internal variable to hold the query sql */
	var $_sql='';
	/** @var int Internal variable to hold the database error number */
	var $_errorNum=0;
	/** @var string Internal variable to hold the database error message */
	var $_errorMsg='';
	/** @var string Internal variable to hold the prefix used on all database tables */
	var $_table_prefix='';
	/** @var Internal variable to hold the connector resource */
	var $_resource='';
	/** @var Internal variable to hold the last query cursor */
	var $_cursor=null;
    /** @debug **/
    var $debug=true;
    var $error='';
	/**
	* Database object constructor
	* @param string Database host
	* @param string Database user name
	* @param string Database user password
	* @param string Database name
	* @param string Common prefix for all tables
	*/
	function database( $host='localhost', $user, $pass, $db, $table_prefix ) {
		// perform a number of fatality checks, then die gracefully
		if (!function_exists( 'mysql_connect' )) {
			die( 'FATAL ERROR: MySQL support not available.  Please check your configuration.' );
  			exit();
		}
		if (!($this->_resource = @mysql_connect( $host, $user, $pass ))) {
			die( 'Database Failed!' );
            exit();
		}
		if (!mysql_select_db($db)) {
			die( "Database Failed");
   			exit();
		}
        //@mysql_query("SET CHARACTER SET UTF8");
        //if (!mysql_query("SET NAMES 'utf8'", $this->_resource))
        ///return false;

        $this->_db=$db;
		$this->_table_prefix = $table_prefix;
	}
    function thread_id()
    {
        $thread_id = mysql_thread_id($this->_resource);
        if ($thread_id){
        printf("current thread id is %d\n", $thread_id);
        }
    }
	/**
	* Execute a database query and returns the result
	* @param string The SQL query
	* @return resource Database resource identifier.  Refer to the PHP manual for more information.
	* @deprecated This function is included for tempoary backward compatibility
	*/
	function openConnectionWithReturn($query){
		$result=mysql_query($query) or die("Query failed with error: ".mysql_error());
		return $result;
	}
	/**
	* Execute a database query
	* @param string The SQL query
	* @deprecated This function is included for temporary backward compatibility
	*/
	function openConnectionNoReturn($query){
		mysql_query($query) or die("Query failed with error: ".mysql_error());
	}
	/**
	* @return int The error number for the most recent query
	*/
	function getErrorNum() {
		return $this->_errorNum;
	}
	/**
	* @return string The error message for the most recent query
	*/
	function getErrorMsg() {
		return str_replace( array( "\n", "'" ), array( '\n', "\'" ), $this->_errorMsg );
	}
	/**
	* Get a database escaped string
	* @return string
	*/
	function getEscaped( $text ) {
		return @mysql_escape_string( $text );
	}
	/**
	* Sets the SQL query string for later execution.
	*
	* This function replaces a string identifier <var>$prefix</var> with the
	* string held is the <var>_table_prefix</var> class variable.
	*
	* @param string The SQL query
	* @param string The common table prefix
	*/
	function setQuery( $sql, $prefix='idx_' ) {
		//$this->_sql =$sql;
        //$sql =str_replace( 'idx_users', 'phpbb2.phpbb_users', $sql );
        $this->_sql =str_replace( $prefix, $this->_table_prefix, $sql );
	}
	/**
	* @return string The current value of the internal SQL vairable
	*/
	function getQuery() {
		return "<pre>" . htmlspecialchars( $this->_sql ) . "</pre>";
	}
	/**
	* Execute the query
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query() {
        $this->queryNum=$this->queryNum+1;
        $this->allSql.=$this->_sql."\n";
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		//$this->_cursor = mysql_unbuffered_query( $this->_sql, $this->_resource );
        $this->_cursor = @mysql_query( $this->_sql, $this->_resource );
		if (!$this->_cursor) {
			$this->_errorNum = @mysql_errno( $this->_resource );
			$this->_errorMsg = @mysql_error( $this->_resource )." SQL=$this->_sql";
            if($this->debug&&$this->_errorMsg)
            {
                $this->error.= "$this->_errorMsg<br>";
                echo "$this->_errorMsg<br>";
            }
			return false;
		}
		return $this->_cursor;
	}

	function query_batch( $abort_on_error=true, $p_transaction_safe = false) {
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		if ($p_transaction_safe) {
			$si = mysql_get_server_info();
			preg_match_all( "/(\d+)\.(\d+)\.(\d+)/i", $si, $m );
			if ($m[1] >= 4) {
				$this->_sql = 'START TRANSACTION;' . $this->_sql . '; COMMIT;';
			} else if ($m[2] >= 23 && $m[3] >= 19) {
				$this->_sql = 'BEGIN WORK;' . $this->_sql . '; COMMIT;';
			} else if ($m[2] >= 23 && $m[3] >= 17) {
				$this->_sql = 'BEGIN;' . $this->_sql . '; COMMIT;';
			}
		}
		$query_split = preg_split ("/[;]+/", $this->_sql);
		$error = 0;
		foreach ($query_split as $command_line) {
			$command_line = trim( $command_line );
			if ($command_line != '') {
				$this->_cursor = mysql_query( $command_line, $this->_resource );
				if (!$this->_cursor) {
					$error = 1; echo 'xxx ';
					$this->_errorNum .= mysql_errno( $this->_resource ) . ' ';
					$this->_errorMsg .= mysql_error( $this->_resource )." SQL=$command_line <br />";
					if ($abort_on_error) {
						return $this->_cursor;;
					}
				}
			}
		}
		return $error ? false : true;
	}

	/**
	* Diagnostic function
	*/
	function explain() {
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";
		$this->query();

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buf = "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\">";
		$buf .= $this->getQuery();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($first) {
				$buf .= "<tr>";
				foreach ($row as $k=>$v) {
					$buf .= "<th bgcolor=\"#ffffff\">$k</th>";
				}
				$buf .= "</tr>";
				$first = false;
			}
			$buf .= "<tr>";
			foreach ($row as $k=>$v) {
				$buf .= "<td bgcolor=\"#ffffff\">$v</td>";
			}
			$buf .= "</tr>";
		}
		$buf .= "</table><br />&nbsp;";
		mysql_free_result( $cur );

		$this->_sql = $temp;

		return "<div style=\"background-color:#FFFFCC\" align=\"left\">$buf</div>";
	}
	/**
	* @return int The number of rows returned from the most recent query.
	*/
	function getNumRows( $cur=null ) {
		return @mysql_num_rows( $cur ? $cur : $this->_cursor );
	}
    /**
	* @return int The number of affected rows returned from the most recent update/delete query.
	*/
	function getAffectedRows( $cur=null ) {
		return mysql_affected_rows( $this->_resource );
	}
 
	/**
	* @return The first row of the query.
	*/
	function loadRow() {
		/*if (!($cur = $this->query())) {
			return null;
		}*/
        $cur= $this->_cursor;
		$ret = null;
		if ($row = @mysql_fetch_assoc( $cur )) {
			$ret = $row;
		}
		@mysql_free_result( $cur );
		return $ret;
	}
    function loadObject() {
		/*if (!($cur = $this->query())) {
			return null;
		}*/
        $cur= $this->_cursor;
		$ret = null;
		if ($row = @mysql_fetch_object( $cur )) {
			$ret = $row;
		}
		mysql_free_result( $cur );
		return $ret;
	}
	/**
	* Load a list of database rows (numeric column indexing)
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadRowList( $key='' ) {
		/*if (!($cur = $this->query())) {
			return null;
		}*/
        $cur= $this->_cursor;
		$array = array();
		while ($row = @mysql_fetch_assoc( $cur )) {
			if ($key) {
				$array[ $row[$key] ] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
    function loadRowList2( $key='',$from='',$limit='' ) {
		/*if (!($cur = $this->query())) {
			return null;
		}*/
        $cur= $this->_cursor;
		$array = array();$i=0;
		while ($row = mysql_fetch_assoc( $cur )) {
            $i++; if($limit&&($i<$from+1||$i>$from+$limit)) continue;
            if($limit&&($i>$from+$limit)) break;
			if ($key) {
				$array[ $row[$key] ] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	/**
	* Document::db_insertObject()
	*
	* { Description }
	*
	* @param [type] $keyName
	* @param [type] $verbose
	*/
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
		$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
		foreach (get_object_vars( $object ) as $k => $v) {
			//if (is_array($v) or is_object($v) or $v == NULL) {
            if (is_array($v) or is_object($v)) {
				continue;
			}
			if ($k[0] == '_') { // internal field
			continue;
			}
			$fields[] = "`$k`";
			$values[] = "'" . $this->getEscaped( $v ) . "'";
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		($verbose) && print "$sql<br />\n";

		if (!$this->query()) {
			return false;
		}
		$id = mysql_insert_id();
		($verbose) && print "id=[$id]<br />\n";
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	* Document::db_updateObject()
	*
	* { Description }
	*
	* @param [type] $updateNulls
	*/
	function updateObject( $table, &$object, $keyName, $updateNulls=true ) {
		$fmtsql = "UPDATE $table SET %s WHERE %s";
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
			continue;
			}
			if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . $this->getEscaped( $v ) . "'";
			continue;
			}
			if ($v === NULL && !$updateNulls) {
				continue;
			}
			//if( $v == '' ) {
			//	$val = "''";
			//} else {
				$val = "'" . $this->getEscaped( $v ) . "'";
			//}
			$tmp[] = "`$k`=$val";
		}
        $s=  get_object_vars( $object );

		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );

        return $this->query();
	}

	/**
	* @param boolean If TRUE, displays the last SQL statement sent to the database
	* @return string A standised error message
	*/
	function stderr( $showSQL = false ) {
		return "DB function failed with error number $this->_errorNum"
		."<br /><font color=\"red\">$this->_errorMsg</font>"
		.($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
	}

	function insertid()
	{
		return mysql_insert_id();
	}

	function getVersion()
	{
		return mysql_get_server_info();
	}
    /*========================================================================*/
    // Return an array of tables
    /*========================================================================*/

    function get_table_names() {

		$result     = mysql_list_tables($this->_db);
		$num_tables = @mysql_numrows($result);
		for ($i = 0; $i < $num_tables; $i++)
		{
			$tables[] = mysql_tablename($result, $i);
		}

		mysql_free_result($result);

		return $tables;
   	}

   	/*========================================================================*/
    // Return an array of fields
    /*========================================================================*/

    function get_result_fields( $cur=null )
    {
		while ($field = mysql_fetch_field( $cur ? $cur : $this->_cursor ))
		{
            $Fields[$field['name']] = $field;
		}
        //mysql_free_result(  $cur ? $cur : $this->_cursor  );
		return $Fields;
   	}
    /*========================================================================*/
    // Return an array of table fields
    /*========================================================================*/

    function get_table_fields( $table, $cur=null )
    {
        $this->setQuery("SHOW COLUMNS FROM $table");
        $this->query();
        $Fields=$this->loadRowList();
        foreach($Fields as $Field)
        {
            $Fields2[$Field[Field]]=$Field;
        }
		return $Fields2;
   	}
	/*========================================================================*/
    // Test to see if a field exists by forcing and trapping an error.
    // It ain't pretty, but it do the job don't it, eh?
    // Posh my ass.
    // Return 1 for exists, 0 for not exists and jello for the naked guy
    // Fun fact: The number of times I spelt 'field' as 'feild'in this part: 104
    /*========================================================================*/

    function field_exists($field, $table)
    {

        $$this->setQuery("SELECT COUNT($field) as count FROM $table");
        $this->query();

		return $this->getNumRows();
	}
    /*========================================================================*/
    // Shut down the database
    /*========================================================================*/

    function close_db() {
        return mysql_close( $this->_resource );
    }

    //function build

}
/*
+--------------------------------------------------------------------------
|   YABLinks Directory v1.1
|   ========================================
|   by Stephen Yabziz
|   (c) 2005 YABSoft Services
|   http://www.yabsoft.com
|   ========================================
|   Web: http://www.yabsoft.com
|   Time: Wed, 28 Mar 2005 17:09:21 GMT
|   Email: ywyhnchina@163.com
+---------------------------------------------------------------------------
|
|   > Script written by Stephen Yabziz
|   > Date started: 14th February 2004
|   > Some great function writen by me:)
|
+--------------------------------------------------------------------------
*/
class TABLE{
    var $_db;
    var $_tbl;
    var $_tbl_key;
    var $_err_msg;

    function TABLE($db,$tbl,$key){
        $this->_db=$db;
        $this->_tbl=$tbl;
        $this->_tbl_key=$key;
    }
    function insert(){
        $this->_db->insertObject($this->_tbl,$this,"",false);
        $this->setErr($this->_db->getErrorMsg());
    }
    function update(){
        $this->_db->updateObject($this->_tbl,$this,$this->_tbl_key,false);
        $this->setErr($this->_db->getErrorMsg());
    }
    function delete($ID){
        $this->_db->setQuery("DELETE FROM $this->_tbl WHERE $this->_tbl_key='$ID'");
        $this->_db->query();
    }
    function insertid(){
        return $this->_db->insertid();
    }
    function getAffectedRows()
    {
        return $this->_db->getAffectedRows();
    }
    function setErr($errMsg){
        $this->_db->_err_msg.=$errMsg;

    }
    function getErr(){
        return $this->_db->_err_msg;

    }
    function inputData($prefix='tbl_')
    {
    global $input,$SET;
       foreach($input as $key=>$value)
       {

          if(substr($key,0,strlen($prefix))==$prefix)
          {
               $var=substr($key,strlen($prefix));
               $this->$var=$value;

          }

       }
    }
    function inputUpload($upload,$prefix='file_')
    {
    global $input;
       if($upload)
       foreach($upload as $key=>$value)
       {
          if(strtolower(substr($key,0,strlen($prefix)))==$prefix)
          {
               $var=substr($key,strlen($prefix));
               $this->$var=$value[name];
               $this->setErr($value[err]);
          }
       }
    }
}
?>
