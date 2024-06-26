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

class vB_Notification_Content_GroupByStarter_Reply extends vB_Notification_Content_GroupByStarter
{

	protected static $triggers = array(
		'new-content'	=> 5,
		//'updated-content'	=> 5,

	);

	const TYPENAME = 'Reply';

	/*
	 * Whether it supports FCM or not
	 */
	const FCM_SUPPORTED = true;

	protected function addAdditionalRecipients()
	{
		$nodeid = $this->notificationData['sentbynodeid'];
		$node = vB_Library::instance('node')->getNodeBare($nodeid);
		// Is this a reply?
		if ($node['parentid'] == $node['starter'])
		{
			$starter = vB_Library::instance('node')->getNodeBare($node['starter']);
			if (!empty($starter['userid']) AND $starter['userid'] != $this->notificationData['sender'])
			{
				return array($starter['userid']);
			}
		}

		return array();
	}

	protected function typeEnabledForUser($user)
	{
		static $bf_masks;
		if (empty($bf_masks))
		{
			$bf_masks = vB::getDatastore()->getValue('bf_misc_usernotificationoptions');
		}

		// The original mapping was taken from vB_Library_Privatemessage->userReceivesNotification()
		return ((bool) ($user['notification_options'] & $bf_masks['discussions_on']));
	}

	/**
	 * @see vB_Notification::fetchPhraseArray()
	 */
	public static function fetchPhraseArray($notificationData)
	{
		$nodelink = vB5_Route::buildUrl('node|fullurl', array('nodeid' => $notificationData['sentbynodeid']));

		$phraseTitle = "missing phrase for " . __CLASS__;
		$phraseData = array();
		if (empty($notificationData['sender']) OR is_null($notificationData['sender_username']))
		{
			switch ($notificationData['otherParticipantsCount'])
			{
				case 0:
					$phraseTitle = 'guest_replied_to_z';	// There's a 'guest_replied_to_y' phrase being used bo other templates.
					$phraseData = array(
						$nodelink,
						$notificationData['aboutstartertitle']
					);
					break;
				case 1:
					$phraseTitle = 'guest_and_one_other_replied_to_z';
					$phraseData = array(
						$notificationData['sentbynodeid'],
						$nodelink,
						$notificationData['aboutstartertitle']
					);
					break;
				default:
					$phraseTitle = 'guest_and_y_others_replied_to_z';
					$phraseData = array(
						$notificationData['sentbynodeid'],
						$notificationData['otherParticipantsCount'],
						$nodelink,
						$notificationData['aboutstartertitle']
					);
					break;
			}
		}
		else
		{
			$userid = $notificationData['sender'];
			$username = $notificationData['sender_username'];
			$userInfo = array('userid' => $userid, 'username' => $username);
			try
			{
				$userProfileUrl = vB5_Route::buildUrl('profile|fullurl', $userInfo);
			}
			catch (Exception $e)
			{
				$userProfileUrl = "#";
			}
			switch ($notificationData['otherParticipantsCount'])
			{
				case 0:
					$phraseTitle = 'x_replied_to_z';
					$phraseData = array(
						$userProfileUrl,
						$username,
						$nodelink,
						$notificationData['aboutstartertitle']
					);
					break;
				case 1:
					$phraseTitle = 'x_and_one_other_replied_to_z';
					$phraseData = array(
						$userProfileUrl,
						$username,
						$notificationData['sentbynodeid'],
						$nodelink,
						$notificationData['aboutstartertitle']
					);
					break;
				default:
					$phraseTitle = 'x_and_y_others_replied_to_z';
					$phraseData = array(
						$userProfileUrl,
						$username,
						$notificationData['sentbynodeid'],
						$notificationData['otherParticipantsCount'],
						$nodelink,
						$notificationData['aboutstartertitle']
					);
					break;
			}
		}

		return array($phraseTitle, $phraseData);
	}

	public static function getFCMExtraData($data, $languageids)
	{
		// We also have data.sender, but let's just grab the username from the node itself
		// to skip unnecessary queries/methods
		if (empty($data['sentbynodeid']))
		{
			return array();
		}

		$nodeLib = vB_Library::instance('node');
		$node = $nodeLib->getNode($data['sentbynodeid']);
		if (empty($node['starter']))
		{
			// Something weird happened. Maybe the node wasn't a content node (e.g. channels don't have starters)?
			return array();
		}

		// Grab starter for title.
		if ($node['starter'] == $node['nodeid'])
		{
			$starter = $node;
		}
		else
		{
			$starter = $nodeLib->getNode($node['starter']);
		}

		if (empty($starter['title']))
		{
			// Something weird happened & we can't recover from this.
			return array();
		}

		$phraseApi = vB_Api::instanceInternal('phrase');
		$phraseid = 'fcm_replied_to_y';
		$phraseArgs = array($starter['title']);
		$return = array();
		$data = array(
			'INTENT_EXTRA_THREAD_ID' => $node['starter'],
			'INTENT_EXTRA_POST_ID' => $node['nodeid'],
		);

		foreach ($languageids AS $__langid)
		{
			$__phrase = $phraseApi->fetch($phraseid, $__langid);
			$__phrase = $__phrase[$phraseid];
			$__renderedPhrase = vsprintf($__phrase, $phraseArgs);
			$return[$__langid] = array(
				'title' => $node['authorname'], // this will be unescaped in the FCM lib
				'body' => $__renderedPhrase,
				'click_action' => vB_Library_FCMessaging::CLICK_ACTION_THREAD,
				'data' => $data,
			);
		}

		return $return;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
