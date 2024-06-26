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

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
// if (!is_object($vbulletin->db))
// {
// 	exit;
// }

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('ONEDAY', 86400);
define('TWODAYS', 172800);
define('FIVEDAYS', 432000);
define('SIXDAYS', 518400);

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

// Send the reminder email only twice. After 1 day and then 5 Days.
$timenow = vB::getRequest()->getTimeNow();
$bf_misc_useroptions = vB::getDatastore()->get_value('bf_misc_useroptions');
$users = vB::getDbAssertor()->assertQuery('fetchUsersToActivate', array(
			'time1' => $timenow - TWODAYS,
			'time2' => $timenow - ONEDAY,
			'time3' => $timenow - SIXDAYS,
			'time4' => $timenow - FIVEDAYS,
			'noactivationmails' => $bf_misc_useroptions['noactivationmails']
		)
	);

vB_Mail::vbmailStart();

$emails = array();

foreach ($users as $user)
{
	// make random number
	if (empty($user['activationid']))
	{ //none exists so create one
		$user['activationid'] = fetch_random_string(40);
		/*insert query*/
		vB::getDbAssertor()->assertQuery('user_replaceuseractivation2', array(
			'userid' => $user['userid'],
			'timenow' => $timenow,
			'activateid' => $user['activationid'],
			'type' => 0,
			'usergroupid' => 0
		));
	}
	else
	{
		$user['activationid'] = fetch_random_string(40);
		vB::getDbAssertor()->update('useractivation',
				array('dateline' => $timenow, 'activationid' => $user['activationid']),
				array('userid' => $user['userid'], 'type' => 0)
		);
	}

	$userid = $user['userid'];
	$username = $user['username'];
	$activateid = $user['activationid'];

	$maildata = vB_Api::instanceInternal('phrase')->fetchEmailPhrases(
		'activateaccount',
		array(
			$username,
			vB::getDatastore()->getOption('bbtitle'),
			vB::getDatastore()->getOption('frontendurl'),
			$userid,
			$activateid,
			vB::getDatastore()->getOption('webmasteremail'),
		),
		array(vB::getDatastore()->getOption('bbtitle')),
		$user['languageid']
	);
	vB_Mail::vbmail($user['email'], $maildata['subject'], $maildata['message']);

	$emails[] = $user['username'];
}

if ($emails)
{
	log_cron_action(implode(', ', $emails), $nextitem, 1);
}

vB_Mail::vbmailEnd();

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
