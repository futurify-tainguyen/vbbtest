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

class vBMAPI_dB_MYSQL_QueryDefs extends vB_dB_MYSQL_QueryDefs
{
	protected $db_type = 'MYSQL';

	protected $table_data = array(
		'mapiposthash' => array(
			'key' => 'posthashid',
			'structure' => array(
				'posthash',
				'filedataid',
				'dateline',
			),
		),
	);

	protected $query_data = array(
		'getPosthashFiledataids' => array(
			vB_dB_Query::QUERYTYPE_KEY => vB_dB_Query::QUERY_SELECT,
			'query_string' => 'SELECT filedataid FROM {TABLE_PREFIX}mapiposthash WHERE posthash = {posthash}'
		),
		'insertPosthashFiledataid' => array(
			vB_dB_Query::QUERYTYPE_KEY => vB_dB_Query::QUERY_INSERT,
			'query_string' => 'INSERT INTO {TABLE_PREFIX}mapiposthash(posthash, filedataid, dateline) VALUES({posthash}, {filedataid}, {dateline})'
		),
		'cleanPosthash' => array(
			vB_dB_Query::QUERYTYPE_KEY => vB_dB_Query::QUERY_DELETE,
			'query_string' => 'DELETE FROM {TABLE_PREFIX}mapiposthash WHERE dateline < {cutoff}'
		),
	);
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
