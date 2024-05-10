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

// ## Function takes an array from fetch_userinfo and an array from cache_permissions()
// ## Returns the user's reputation altering power (for positive)
function fetch_reppower(&$userinfo, &$perms, $reputation = 'pos')
{
	global $vbulletin;

	// User does not have permission to leave negative reputation
	if (!($perms['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['cannegativerep']))
	{
		$reputation = 'pos';
	}

	if (!($perms['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canuserep']))
	{
		$reppower = 0;
	}
	else if ($perms['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] AND $vbulletin->options['adminpower'])
	{
		$reppower = iif($reputation != 'pos', $vbulletin->options['adminpower'] * -1, $vbulletin->options['adminpower']);
	}
	else if (($userinfo['posts'] < $vbulletin->options['minreputationpost']) OR ($userinfo['reputation'] < $vbulletin->options['minreputationcount']))
	{
		$reppower = 0;
	}
	else
	{
		$reppower = 1;

		if ($vbulletin->options['pcpower'])
		{
			$reppower += intval($userinfo['posts'] / $vbulletin->options['pcpower']);
		}
		if ($vbulletin->options['kppower'])
		{
			$reppower += intval($userinfo['reputation'] / $vbulletin->options['kppower']);
		}
		if ($vbulletin->options['rdpower'])
		{
			$reppower += intval(intval((TIMENOW - $userinfo['joindate']) / 86400) / $vbulletin->options['rdpower']);
		}

		if ($reputation != 'pos')
		{
			// make negative reputation worth half of positive, but at least 1
			$reppower = intval($reppower / 2);
			if ($reppower < 1)
			{
				$reppower = 1;
			}
			$reppower *= -1;
		}
	}

	// Legacy Hook 'reputation_power' Removed //

	return $reppower;
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101131 $
|| #######################################################################
\*=========================================================================*/
