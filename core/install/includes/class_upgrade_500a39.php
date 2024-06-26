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

class vB_Upgrade_500a39 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '500a39';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '5.0.0 Alpha 39';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '5.0.0 Alpha 38';

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


	public function step_1()
	{
		$this->show_message(sprintf($this->phrase['vbphrase']['update_table'], TABLE_PREFIX . 'permission'));
		$assertor = vB::getDbAssertor();
		$assertor->assertQuery('vBInstall:updateModeratorPermission', array('modPermissions' => 59902175, 'systemgroups' => array(9,12)));
		$assertor->assertQuery('vBInstall:updateModeratorPermission', array('modPermissions' => 26347743, 'systemgroups' => array(10,13)));
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
