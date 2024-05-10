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
 * This is the query processor for delete queries.

 *
 * @package vBDatabase
 * @version $Revision: 99787 $
 */
class vB_dB_Query_Delete extends vB_dB_Query
{
	/**
	 * This class is called by the new vB_dB_Assertor database class
	 * It does the actual execution. See the vB_dB_Assertor class for more information
	 *
	 * $queryid can be either the id of a query from the dbqueries table, or the
	 * name of a table.
	 *
	 * if it is the name of a table , $params MUST include self::TYPE_KEY of either update, insert, select, or delete.
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
	 */



	/*Initialisation================================================================*/


	/**
	 * standard constructor.
	 *
	 *	@param 	string	id of the query
	 * 	@param 	mixed		the shared db object
	 * 	@param	array		the user information
	 */
	public function __construct($queryid, &$db, $userinfo, $dbSlave)
	{
		parent::__construct($queryid, $db, $userinfo, $dbSlave);

		$this->query_type = self::QUERY_DELETE;
		$this->table_query = true;
	}

	/**
	 * This loads and validates the data- ensures we have all we need
	 *
	 *	@param	array		the data for the query
	 */
	public function setQuery($params, $sortorder)
	{
		return parent::setTableQuery($params, $sortorder);

	}

	/**
	 * This function is the public interface to actually execute the SQL.
	 *
	 * @return 	mixed
	 */
	public function execSQL()
	{
		return $this->doDeletes();
	}

	/**
	 * This function does the deletes and returns a flag to indicate whether the delete worked
	 *
	 *	@param	char
	 *
	 *	@return 	boolean
	 */
	protected function doDeletes()
	{
		$results = array();
		if ($this->structure)
		{
			if ($querystring = $this->buildQuery($this->params))
			{
				$this->db->query_write($querystring);
				$this->error = $this->db->error();
				$results = empty($this->error);
			}
			else
			{
				$results = false;
			}
		}
		return $results;
	}
}
/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
