<?php
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

class vB_Upgrade_542a1 extends vB_Upgrade_Version
{
	/*Constants=====================================================================*/

	/*Properties====================================================================*/

	/**
	* The short version of the script
	*
	* @var	string
	*/
	public $SHORT_VERSION = '542a1';

	/**
	* The long version of the script
	*
	* @var	string
	*/
	public $LONG_VERSION  = '5.4.2 Alpha 1';

	/**
	* Versions that can upgrade to this script
	*
	* @var	string
	*/
	public $PREV_VERSION = '5.4.1';

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
		$this->add_field(
			sprintf($this->phrase['core']['altering_x_table'], 'adcriteria', 1, 1),
			'adcriteria',
			'conditionjson',
			'text',
			array('null' => false, 'default' => '')
		);
	}

}

/*======================================================================*\
|| ####################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| ####################################################################
\*======================================================================*/
