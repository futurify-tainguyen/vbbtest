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

error_reporting(E_ALL & ~E_NOTICE);

function save_notice (
	$noticeid,
	$title,
	$html,
	$displayorder,
	$active,
	$persistent,
	$dismissible,
	$criteria_array,
	$username,
	$templateversion
)
{
	$noticeid = save_notice_info($noticeid, $title, $displayorder, $active, $persistent,
		$dismissible, $criteria_array);
	save_notice_phrase($noticeid, $html, $username, $templateversion);

	// update the datastore notice cache
	build_notice_datastore();

	// rebuild languages
	require_once(DIR . '/includes/adminfunctions_language.php');
	build_language(-1);
}

function build_notice_datastore()
{
	$result = vB::getDbAssertor()->assertQuery('vBForum:fetchnoticecachevalues');

	$notice_cache = array();
	foreach($result AS $noticecriteria)
	{
		$noticeid = $noticecriteria['noticeid'];
		if (!isset($notice_cache[$noticeid]))
		{
			$notice_cache[$noticeid]['persistent'] = $noticecriteria['persistent'];
			$notice_cache[$noticeid]['dismissible'] = $noticecriteria['dismissible'];
		}

		if ($noticecriteria['criteriaid'])
		{
			foreach (array('condition1', 'condition2', 'condition3') AS $condition)
			{
				$notice_cache[$noticeid][$noticecriteria['criteriaid']][] = $noticecriteria[$condition];
			}
		}
	}

	build_datastore('noticecache', serialize($notice_cache), 1);
}



/*
	Should be considered internal to this file for the time being.
*/
function save_notice_info (
	$noticeid,
	$title,
	$displayorder,
	$active,
	$persistent,
	$dismissible,
	$criteria_array
)
{
	global $db;

	// make sure we have some criteria active, or this notice will be invalid
	$have_criteria = false;
	foreach ($criteria_array AS $criteria)
	{
		if ($criteria['active'])
		{
			$have_criteria = true;
			break;
		}
	}

	if (!$have_criteria)
	{
		throw new vb_Exception_AdminStopMessage('no_notice_criteria_active');
	}

	if ($title === '')
	{
		throw new vb_Exception_AdminStopMessage('invalid_title_specified');
	}

	// we are editing
	if ($noticeid)
	{
		// update notice record
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "notice SET
				title = '" . $db->escape_string($title) . "',
				displayorder = " . intval($displayorder) . ",
				active = " . intval($active) . ",
				persistent = " . intval($persistent) . ",
				dismissible = " . intval($dismissible) . "
			WHERE noticeid = " . intval($noticeid)
		);

		// delete criteria
		$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "noticecriteria
			WHERE noticeid = " . intval($noticeid)
		);

		if (!$dismissible)
		{
			// removing old dismissals
			$db->query_write("
				DELETE FROM " . TABLE_PREFIX . "noticedismissed
				WHERE noticeid = " . intval($noticeid)
			);
		}
	}
	// we are adding a new notice
	else
	{
		// insert notice record
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "notice
				(title, displayorder, persistent, active, dismissible)
			VALUES (" .
				"'" . $db->escape_string($title) . "', " .
			 	intval($displayorder) . ", " .
				intval($persistent) . ", " .
				intval($active) . ", " .
				intval($dismissible) . "
			)
		");

		$noticeid = $db->insert_id();
	}

	// assemble criteria insertion query
	$criteria_sql = array();
	foreach ($criteria_array AS $criteriaid => $criteria)
	{
		if ($criteria['active'])
		{
			$criteria_sql[] = "(
				$noticeid,
				'" . $db->escape_string($criteriaid) . "',
				'" . $db->escape_string(trim($criteria['condition1'])) . "',
				'" . $db->escape_string(trim($criteria['condition2'])) . "',
				'" . $db->escape_string(trim($criteria['condition3'])) . "'
			)";
		}
	}

	// insert criteria
	$db->query_write("
		INSERT INTO " . TABLE_PREFIX . "noticecriteria
			(noticeid, criteriaid, condition1, condition2, condition3)
		VALUES " . implode(', ', $criteria_sql)
	);

	return $noticeid;
}

/*
	Should be considered internal to this file for the time being.
*/
function save_notice_phrase($noticeid, $html, $username, $templateversion)
{
	global $vbulletin;

	// insert / update phrase
	$vbulletin->db->query_write("
		REPLACE INTO " . TABLE_PREFIX . "phrase
			(languageid, varname, text, product, fieldname, username, dateline, version)
		VALUES (
			0,
			'notice_{$noticeid}_html',
			'" . $vbulletin->db->escape_string($html) . "',
			'vbulletin',
			'global',
			'" . $vbulletin->db->escape_string($username) . "',
			" . TIMENOW . ",
			'" . $vbulletin->db->escape_string($templateversion) . "'
		)
	");
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
