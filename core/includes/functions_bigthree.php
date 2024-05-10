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
* Fetches the online states for the user, taking into account the browsing
* user's viewing permissions. Also modifies the user to include [buddymark]
* and [invisiblemark]
*
* @param	array	Array of userinfo to fetch online status for
* @param	boolean	True if you want to set $user[onlinestatus] with template results
*
* @return	integer	0 = offline, 1 = online, 2 = online but invisible (if permissions allow)
*/
function fetch_online_status(&$user)
{
	static $buddylist, $datecut;
	$session = vB::getCurrentSession();

	if (empty($session))
	{
		$currentUserId = 0;
	}
	else
	{
		$currentUserId = vB::getCurrentSession()->get('userid');
	}

	// get variables used by this function
	if (!isset($buddylist) AND !empty($currentUserId))
	{
		$buddylist = array();

		//If we are asking for the current user's status we can skip the fetch
		if ($currentUserId == $user['userid'])
		{
			$currentUser = &$user;
		}
		else
		{
			$currentUser = vB_Api::instanceInternal('user')->fetchCurrentUserInfo();
		}

		if (isset($currentUser['buddylist']) AND $currentUser['buddylist'] = trim($currentUser['buddylist']))
		{
			$buddylist = preg_split('/\s+/', $currentUser['buddylist'], -1, PREG_SPLIT_NO_EMPTY);
		}
	}

	if (!isset($datecut))
	{
		$datecut = vB::getRequest()->getTimeNow() - vB::getDatastore()->getOption('cookietimeout');
	}

	// is the user on bbuser's buddylist?
	if (isset($buddylist) AND is_array($buddylist) AND in_array($user['userid'], $buddylist))
	{
		$user['buddymark'] = '+';
	}
	else
	{
		$user['buddymark'] = '';
	}

	// set the invisible mark to nothing by default
	$user['invisiblemark'] = '';

	$onlinestatus = 0;
	$user['online'] = 'offline';

	// now decide if we can see the user or not
	if ($user['lastactivity'] > $datecut AND $user['lastvisit'] != $user['lastactivity'])
	{
		$bf_misc_useroptions = vB::getDatastore()->getValue('bf_misc_useroptions');
		if ($user['options'] & $bf_misc_useroptions['invisible'])
		{
			if (!isset($userContext))
			{
				$userContext = vB::getUserContext();
			}

			if (
					$currentUserId == $user['userid'] OR
					($userContext AND $userContext->hasPermission('genericpermissions','canseehidden'))
				)
			{
				// user is online and invisible BUT bbuser can see them
				$user['invisiblemark'] = '*';
				$user['online'] = 'invisible';
				$onlinestatus = 2;
			}
		}
		else
		{
			// user is online and visible
			$onlinestatus = 1;
			$user['online'] = 'online';
		}
	}

	return $onlinestatus;
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
