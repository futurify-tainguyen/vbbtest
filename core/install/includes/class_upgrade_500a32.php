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

/*
if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}
*/

class vB_Upgrade_500a32 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '500a32';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '5.0.0 Alpha 32';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '5.0.0 Alpha 31';

	/**
	* Beginning version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_STARTS = '';

	/**
	* Ending version compatibility
	*
	* @var	string
	*/
	public $VERSION_COMPAT_ENDS   = '';

	// need to fix perm_groupid index in the permission table
	// need to make perm_group_node index unique
	function step_1()
	{
		// drop them first
		$this->drop_index(
			sprintf($this->phrase['core']['altering_x_table'], 'permission', 1, 4),
			'permission',
			'perm_groupid'
		);
		$this->drop_index(
			sprintf($this->phrase['core']['altering_x_table'], 'permission', 2, 4),
			'permission',
			'perm_group_node'
		);
	}

	function step_2()
	{
		// Add new indexes
		$this->add_index(
				sprintf($this->phrase['core']['altering_x_table'], 'permission', 3, 4),
				'permission',
				'perm_groupid',
				array('groupid')
		);
		$this->add_index(
				sprintf($this->phrase['core']['altering_x_table'], 'permission', 4, 4),
				'permission',
				'perm_group_node',
				array('nodeid', 'groupid')
		);
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
