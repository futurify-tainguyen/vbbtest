<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*========================================================================*\
|| ###################################################################### ||
|| # vBulletin 5.5.2
|| # ------------------------------------------------------------------ # ||
|| # Copyright 2000-2019 MH Sub I, LLC dba vBulletin. All Rights Reserved.  # ||
|| # This file may not be redistributed in whole or significant part.   # ||
|| # ----------------- VBULLETIN IS NOT FREE SOFTWARE ----------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html   # ||
|| ###################################################################### ||
\*========================================================================*/

/**
 *
 * @package vBDatabase
 */

abstract class vB_dB_Assertor
{
	use vB_Trait_NoSerialize;

	/**
	* This class is the new master database class
	* The main way of using this is
	* vB_dB_Assertor::instance()->assertQuery($queryid, $params);
	* $queryid can be either the id of a query from the dbqueries table, or the
	* name of a table.
	*
	* if it is the name of a table , $params MUST include vB_dB_Query::TYPE_KEY of either update, insert, select, or delete.
	*
	* $params includes a list of parameters. Here's how it gets interpreted.
	*
	* If the queryid was the name of a table and type was "update", one of the params
	* must be the primary key of the table. All the other parameters will be matched against
	* the table field names, and appropriate fields will be updated. The return value will
	* be false if an error is generated and true otherwise
	*
	* If the queryid was the name of a table and type was "delete", one of the params
	* must be the primary key of the table. All the other parameters will be ignored
	* The return value will be false if an error is generated and true otherwise
	*
	* If the queryid was the name of a table and type was "insert", all the parameters will be
	* matched against the table field names, and appropriate fields will be set in the insert.
	* The return value is the primary key of the inserted record.
	*
	* If the queryid was the name of a table and type was "select", all the parameters will be
	* matched against the table field names, and appropriate fields will be part of the
	* "where" clause of the select. The return value will be a vB_dB_Result object
	* The return value is the primary key of the inserted record.
	*
	* If the queryid is the key of a record in the dbqueries table then each params
	* value will be matched to the query. If there are missing parameters we will return false.
	* If the query generates an error we return false, and otherwise we return either true,
	* or an inserted id, or a recordset.
	*
	*/
	/*Properties====================================================================*/

	//Cached querydef objects
	protected static $tableData = array();
	//the database instance
	protected static $instance = false;

	/** The database connection **/
	protected static $db = false;
	protected static $dbSlave = false;

	/** The user info ***/
	protected static $userinfo = false;

	/** database type **/
	protected static $dbtype = false;

	/**for performance measuring- number of queries on this page **/
	protected $queryCount = 0;
	/**for performance measuring- number of queries on this page **/
	protected $queries = array();

	/**Are we in debug mode? **/
	protected $debug = false;

	/** Do we need to log queries? **/
	protected $debugLog = false;

	//Prevent calling shutdown queries. Useful for css, image handing, etc.
	protected $noShutdown = false;

	/**
	* Array of queries to be executed when the script shuts down
	* Each item contains the paramaters to call assertQuery
	*
	* @var	array
	*/
	protected $shutdownqueries = array();

	/**
	 * Causes the SQL for the next query that is exectuted to be displayed
	 * for debugging purposes. This only works if debug mode is turned on
	 *
	 * @var	bool
	 */
	protected $debugDisplayNextQuerySql = false;

	/**
	 * Reference to the sensitive database configuration array from
	 * class vB. Contains information required to connect to the database.
	 *
	 * @var array
	 */
	protected $dbconfig;


	/*Initialisation================================================================*/

	/* prevent instantiation */
	protected function __construct(&$dbconfig, &$config)
	{
		//init some debug parameters.
		$this->debug = (!empty($config['Misc']['debug']));
		$this->debugLog = (!empty($config['Misc']['debuglogging']));

		$this->dbconfig =& $dbconfig;
	}

	/**
	 * This sets the db. It will normally be called in the boot process
	 * @param array		config array
	 */
	public static function init(&$dbconfig, &$config)
	{
		//currently mysqli is handled by the mysql class
		$dbtype = $dbconfig['Database']['dbtype'];
		if (strcasecmp($dbtype, 'mysqli') === 0 OR strcasecmp($dbtype, 'mysqli_slave') === 0)
		{
			self::$dbtype = 'MYSQL';
		}
		else
		{
			self::$dbtype = strtoupper($dbconfig['Database']['dbtype']);
		}
		$class = 'vB_dB_' . self::$dbtype . '_Assertor';

		if (class_exists($class))
		{
			self::$instance = new $class($dbconfig, $config);
			//we want instance to be set even if the connection fails
			//the installer needs a created class structure to properly handle this case
			self::$instance->load_database($dbconfig, $config);
		}
		else
		{
			die('Fatal error: Database class not found for dbtype "' . $dbtype . '" use "mysqli" or "mysqli_slave"');
		}

		vB_Shutdown::instance()->add(array(self::$instance, 'executeShutdownQueries'));
	}

	/**
	 * returns the singleton instance
	 */
	public static function instance()
	{
		if (!isset(self::$instance))
		{
			return false;
		}

		return self::$instance;
	}

	public static function getDbType()
	{
		return self::$dbtype;
	}

	/**
	 * Gets the raw database connection object.  This is solely implemented as a temporary measure to support
	 * legacy code.  Do not use it without checking with the dev lead.
	 *
	 *	@TODO remove when legacy code is refactored to use the assertor.
	 *
	 *	@deprecated.
	 */
	public function getDBConnection()
	{
		return self::$db;
	}

	public function setLogQueries($debugLog)
	{
		$this->debugLog = $debugLog;
	}

	/**
	 * Disable the shutdown queries. Primarily reduces contention for session table.
	 */
	public function skipShutdown($noShutdown = true)
	{
		$this->noShutdown = $noShutdown;
	}

	/**
	 * Core function- validates, composes, and executes a query. See above for more
	 *
	 * @param string
	 * @param array
	 * @param string
	 *
	 * @return mixed	boolean, integer, or results object
	 */
	public function assertQuery($queryid, $params = array(), $orderby = false)
	{
		//make sure we have been initialized
		if (!isset(self::$instance))
		{
			return false;
		}

		if ($this->debugDisplayNextQuerySql)
		{
			$params[vB_dB_Query::DEBUG_QUERY] = 1;
		}

		// get the query object
		$query = vB_dB_Query::getQuery($queryid, $params, self::$db, self::$userinfo, self::$dbtype, self::$dbSlave);

		if (isset($params[vB_dB_Query::DEBUG_QUERY]))
		{
			unset($params[vB_dB_Query::DEBUG_QUERY]);
		}

		if ($this->debugDisplayNextQuerySql)
		{
			$this->debugDisplayNextQuerySql = false;
			$query->debugDisplayNextQuerySql();
		}

		//set the parameters. The children will raise an error if they don't have enough data.
		$check = $query->setQuery($params, $orderby);

		/**If we are in development mode, record this query **/
		if ($this->debug)
		{
			$this->queryCount += 1;
			/**for performance measuring- number of queries on this page **/
			if (!empty($_REQUEST['querylist']))
			{
				$displayParams = $params;
				unset($displayParams[vB_dB_Query::TYPE_KEY]);
				$displayParam = var_export($displayParams, true);

				if (strlen($displayParam) > 256)
				{
					$this->queries[] = $queryid . ': ' . substr($displayParam, 0, 256) . '...';
				}
				else
				{
					$this->queries[] = $queryid . ': ' . $displayParam;
				}
			}
		}

		if ($this->debugLog)
		{
			$starttime = microtime(true);
			//We don't want a full trace.
			$stack = array();
			$trace = debug_backtrace(false);
			foreach ($trace as $key => $step)
			{
				$line = "Step $key: in " . $step['function'] ;

				foreach(array('line', 'step', 'file') AS $field)
				{
					if (!empty($step[$field]))
					{
						$line .= ' ' . $field . ' '. $step[$field];
					}
				}
				$stack[] = $line;
			}
			$info = "---------------------\nQuery: " . $queryid . "\n" .
				var_export($params, true) . "\n" .
				implode("\n", $stack) . "\n"  ;

			if (isset($params[vB_dB_Query::TYPE_KEY]))
			{
				vB::getLogger("dbAssertor.$queryid." . $params[vB_dB_Query::TYPE_KEY])->info($info);
			}
			else
			{
				vB::getLogger("dbAssertor.$queryid")->info($info);
			}

			$result = $query->execSQL();
			vB::getLogger("dbAssertor.$queryid")->info("time: " . (microtime(true) - $starttime));

			return $result;
		}

		return $query->execSQL();
	}

	/**
	 * This function is deprecated and will be removed.  Do not use it.
	 * @deprecated
	 */
	public function escape_string($string)
	{
		return self::$db->escape_string($string);
	}

	public function affected_rows()
	{
		return self::$db->affected_rows();
	}

	/**
	* Switches database error display ON
	*/
	public function show_errors()
	{
		self::$db->show_errors();
	}

	/**
	* Switches database error display OFF
	*/
	public function hide_errors()
	{
		self::$db->hide_errors();
	}

	/**
	* Registers a query to be executed at shutdown time. If shutdown functions are disabled, the query is run immediately.
	*
	* @param	string
	* @param	array
	* @param	string
	* @param	mixed	(Optional) Allows particular shutdown queries to be labelled
	*
	* @return	boolean
	*/
	public function shutdownQuery($queryid, $params, $arraykey = -1)
	{
		$query = array(
			'id'		=> $queryid,
			'params'	=> $params,
		);

		if ($arraykey === -1)
		{
			$this->shutdownqueries[] = $query;
			return true;
		}
		else
		{
			$this->shutdownqueries["$arraykey"] = $query;
			return true;
		}
	}

	public function unregisterShutdownQuery($queryKey)
	{
		unset($this->shutdownqueries[$queryKey]);
	}

	public function executeShutdownQueries()
	{
		if ($this->noShutdown)
		{
			return;
		}

		$this->hide_errors();
		foreach($this->shutdownqueries AS $name => $query)
		{
			if (!empty($query) AND ($name !== 'pmpopup' OR !defined('NOPMPOPUP')))
			{
				// the structure of query is defined in $this->shutdownQuery
				$this->assertQuery($query['id'], $query['params']);
			}
		}
		$this->show_errors();
	}

	/** This gets the structure of a specific table. Used initially for complex queries
	*
	* 	@param	string	standard table definition syntax
	*
	* 	@return	mixed	querydef syntax- includes key and structure.
	**/
	public static function fetchTableStructure($table)
	{
		$class = 'vB_Db_' . self::$dbtype . "_QueryDefs";
		if (strpos($table, ':'))
		{
			$values = explode(':', $table);
			if (count($values) > 1)
			{
				$class_prefix = $values[0];
				$table = $values[1];
			}
		}

		if (isset($class_prefix))
		{
			$classname = $class_prefix . '_dB_' . self::$dbtype . "_QueryDefs";
			$filename = DIR . '/packages/' . strtolower(str_replace('_', '/', $classname)) . '.php';

			if (file_exists($filename))
			{
				include_once($filename);
			}
			//make sure this is valid
			if (class_exists($classname, false))
			{
				$class = $classname;
			}
		}

		if (empty(self::$tableData[$class]))
		{
			$queryDef = new $class();
			self::$tableData[$class] = $queryDef->getTableData();
		}

		if (isset(self::$tableData[$class][$table]))
		{
			return self::$tableData[$class][$table];
		}

		return false;
	}

	// TABLE-BASED FUNCTIONS
	/**
	 * Table-based insert
	 * @param string $table
	 * @param array $params
	 * @param mixed $shutdown
	 */
	public function insert($table, $params, $shutdown = false)
	{
		$params[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_INSERT;

		if ($shutdown)
		{
			$this->shutdownQuery($table, $params, ($shutdown === true) ? -1 : $shutdown);
		}
		else
		{
			return $this->assertQuery($table, $params);
		}
	}

	/**
	 * Table-based insert ignore
	 * @param string $table
	 * @param array $params
	 * @param mixed $shutdown
	 */
	public function insertIgnore($table, $params, $shutdown = false)
	{
		$params[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_INSERTIGNORE;

		if ($shutdown)
		{
			$this->shutdownQuery($table, $params, ($shutdown === true) ? -1 : $shutdown);
		}
		else
		{
			return $this->assertQuery($table, $params);
		}
	}

	/**
	 * Table-based replace query
	 * @param string $table
	 * @param array $params
	 * @param mixed $shutdown
	 */
	public function replace($table, $params, $shutdown = false)
	{
		$params[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_REPLACE;

		if ($shutdown)
		{
			$this->shutdownQuery($table, $params, ($shutdown === true) ? -1 : $shutdown);
		}
		else
		{
			return $this->assertQuery($table, $params);
		}
	}

	/**
	 * Table-based multiple insert
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @param mixed $shutdown
	 */
	public function insertMultiple($table, $fields, $values, $shutdown = false)
	{
		$params[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_MULTIPLEINSERT;
		$params[vB_dB_Query::FIELDS_KEY] = $fields;
		$params[vB_dB_Query::VALUES_KEY] = $values;

		if ($shutdown)
		{
			$this->shutdownQuery($table, $params, ($shutdown === true) ? -1 : $shutdown);
		}
		else
		{
			return $this->assertQuery($table, $params);
		}
	}

	/**
	 * Table-based update
	 * @param string $table
	 * @param array $values
	 * @param array $conditions OR string vB_dB_Query::CONDITION_ALL
	 * @param mixed $shutdown
	 */
	public function update($table, $values, $conditions, $shutdown = false)
	{
		$values[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_UPDATE;
		$values[vB_dB_Query::CONDITIONS_KEY] = $conditions;

		if ($shutdown)
		{
			$this->shutdownQuery($table, $values, ($shutdown === true) ? -1 : $shutdown);
		}
		else
		{
			return $this->assertQuery($table, $values);
		}
	}

	/**
	 * Table-based delete
	 * @param string $table
	 * @param array $conditions OR string vB_dB_Query::CONDITION_ALL
	 * @param mixed $shutdown
	 */
	public function delete($table, $conditions, $shutdown = false)
	{
		$params[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_DELETE;
		$params[vB_dB_Query::CONDITIONS_KEY] = $conditions;

		if ($shutdown)
		{
			$this->shutdownQuery($table, $params, ($shutdown === true) ? -1 : $shutdown);
		}
		else
		{
			return $this->assertQuery($table, $params);
		}
	}

	/**
	 * Table-based select
	 * @param string $table
	 * @param array $conditions OR string vB_dB_Query::CONDITION_ALL
	 * @param mixed $orderBy
	 * @param array $columns
	 * @return vB_dB_Result
	 */
	public function select($table, $conditions, $orderBy = false, $columns = array())
	{
		$params[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_SELECT;
		if (!empty($conditions))
		{
			$params[vB_dB_Query::CONDITIONS_KEY] = $conditions;
		}

		if (!empty($columns))
		{
			$params[vB_dB_Query::COLUMNS_KEY] = $columns;
		}

		return $this->assertQuery($table, $params, $orderBy);
	}

	// WRAPPER FUNCTIONS FOR SELECT QUERIES (BOTH FOR TABLED-BASED AND DEFINED)

	/**
	 * Retrieves the first column of the first row for a select query (either defined or table-based)
	 * @param string $queryId
	 * @param array $conditions
	 * @param mixed $orderBy
	 * @return mixed
	 */
	public function getField($queryId, $conditions = array(), $orderBy = false)
	{
		$result = $this->assertQuery($queryId, $conditions, $orderBy);

		if ($result AND $result->valid())
		{
			$row = $result->current();
			return current($row);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Retrieves the first row for a select query either (defined or table-based)
	 * @param string $queryId
	 * @param array $conditions
	 * @param mixed $orderBy
	 * @return array
	 */
	public function getRow($queryId, $conditions = array(), $orderBy = false)
	{
		//attempt to limit the number of results.  This probably won't work for defined or method
		//queries, but we should really be limiting those if we are going to use them with with function.
		//Avoids selecting thousands of rows from the DB and then throwing away all but one of them
		$conditions[vB_dB_Query::PARAM_LIMIT] = 1;

		$result = $this->assertQuery($queryId, $conditions, $orderBy);

		if ($result AND $result->valid())
		{
			return $result->current();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns all rows for a select query, either a defined query or table-based query.
	 *
	 * @param	string	Query ID for a defined query, or table name for a table-based query
	 * @param	array	Conditions for the where clause
	 * @param	mixed	Order by (optional)
	 * @param	string	If specified, the returned rows will be keyed using the value of this field (optional)
	 *
	 * @return	array	The rows
	 */
	public function getRows($queryId, array $conditions = array(), $orderBy = false, $keyField = '')
	{
		$result = $this->assertQuery($queryId, $conditions, $orderBy);

		$res = array();
		if ($result AND $result->valid())
		{
			foreach ($result AS $item)
			{
				if (!empty($keyField) AND isset($item[$keyField]))
				{
					$res[$item[$keyField]] = $item;
				}
				else
				{
					$res[] = $item;
				}
			}
		}

		return $res;
	}

	/**
	 * Returns only one table field for all rows for a select query, either a defined query or table-based query.
	 *
	 * @param	string	Query ID for a defined query, or table name for a table-based query
	 * @param	string	The table field name to return
	 * @param	array	Conditions for the where clause
	 * @param	mixed	Order by (optional)
	 * @param	string	If specified, the returned rows will be keyed using the value of this field (optional)
	 *
	 * @return	array	The rows
	 */
	public function getColumn($queryId, $column, array $conditions = array(), $orderBy = false, $keyField = '')
	{
		$columns[] = $column;

		//no point in adding the keyField
		if (!empty($keyField) AND $keyField != $column)
		{
			$columns[] = $keyField;
		}

		$conditions[vB_dB_Query::COLUMNS_KEY] = $columns;

		$result = $this->assertQuery($queryId, $conditions, $orderBy);

		$res = array();
		if ($result AND $result->valid())
		{
			foreach ($result AS $item)
			{
				$value = $item[$column];
				if (!empty($keyField) AND isset($item[$keyField]))
				{
					$res[$item[$keyField]] = $value;
				}
				else
				{
					$res[] = $value;
				}
			}
		}

		return $res;
	}
	/**
	 * Causes the SQL for the next query that is exectuted to be displayed
	 * for debugging purposes. This only works if debug mode is turned on
	 */
	public function debugDisplayNextQuerySql()
	{
		$this->debugDisplayNextQuerySql = true;
	}

	/**
	 * This returns the performance data
	 *
	 * @return array --
	 * 	int queryCount
	 * 	array(string) queries
	 */
	public function getQryCount()
	{
		return array(
			'queryCount' => $this->queryCount,
			'queries' => $this->queries
		);
	}

	/**
	 * Standard transaction handler. Needs to be implemented in the child
	 */
	public function beginTransaction()
	{
		self::$db->beginTransaction();
	}


	/**
	 * Standard transaction handler. Needs to be implemented in the child
	 */
	public function rollbackTransaction()
	{
		self::$db->rollbackTransaction();
	}

	/**
	 * Standard transaction handler. Needs to be implemented in the child
	 */
	public function commitTransaction()
	{
		self::$db->commitTransaction();
	}


	/**
	 * Standard transaction handler. Needs to be implemented in the child
	 *
	 * @return	bool	whether a transaction has been started but not committed/rolled back
	 */
	public function inTransaction()
	{
		return self::$db->fetchInTransaction();
	}



	public function getNoForceSqlMode()
	{
		if (isset($this->dbconfig['Database']['no_force_sql_mode']))
		{
			return $this->dbconfig['Database']['no_force_sql_mode'];
		}
		return null;
	}

	public function getForceSqlMode()
	{
		if (isset($this->dbconfig['Database']['force_sql_mode']))
		{
			return $this->dbconfig['Database']['force_sql_mode'];
		}
		return null;
	}

	// Technical email is common enough that it's bypassed into the global config in vB::fetch_config()

}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 100726 $
|| #######################################################################
\*=========================================================================*/
