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
 * AccessDenied Exception
 * Thrown whenever an unrecoverable access denied occurs.
 * This should cause a reroute to the error page with a generic permissions error.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 99788 $
 * @since $Date: 2018-10-24 17:26:31 -0700 (Wed, 24 Oct 2018) $
 
 */
class vB_Exception_NodePermission extends vB_Exception_Api
{
	private $nodeid;

	public function __construct($nodeid, $nodetype = "", $user = "")
	{
		//unfortunately this has been used extensively in non node contexts
		//despite its name and cleaning that up is going to be it's own exercise.
		//so we hack around that here
		if (is_numeric($nodeid))
		{
			$this->nodeid = $nodeid;

			if (!$nodetype)
			{
				$nodetype = 'node';
			}

			if ($user)
			{
				parent::__construct('node_permission_user', array($nodeid, $nodetype, $user));
			}
			else
			{
				parent::__construct('node_permission', array($nodeid, $nodetype));
			}
		}
		else
		{
			$this->nodeid = 0;
			parent::__construct('node_permission_section', array($nodeid));
		}
	}

	public function getNodeId()
	{
		return $this->nodeid;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99788 $
|| #######################################################################
\*=========================================================================*/
