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
 * vB_Api_Vb4_forum
 *
 * @package vBApi
 * @access public
 */
class vB_Api_Vb4_forum extends vB_Api
{
	public function call()
	{
		$contenttype = vB_Api::instance('contenttype')->fetchContentTypeIdFromClass('Channel');
		$nodes = vB_Api::instance('node')->listNodeFullContent(1, 1, 100, 4, $contenttype, false);

		if (!empty($nodes) AND empty($nodes['errors']))
		{
			foreach ($nodes AS $node)
			{
				$channels[$node['nodeid']] = array(
					'parentid'      => $node['parentid'],
					'forumid' 		=> $node['nodeid'],
					'title'			=> $node['title'],
					'description'	=> $node['description'],
					'title_clean'	=> $node['htmltitle'],
					'description_clean'	=> strip_tags($node['description']),
					'threadcount'		=> $node['textcount'],
					'replycount'	=> $node['totalcount'],
					'lastpostinfo' 	=> array(
						'nodeid' => $node['lastcontentid'],
						'lastpostinfo'	=> array(
							'lastposter' => $node['lastcontentauthor'],
							'lastposterid'	=> $node['lastauthorid'],
							'lastposttime' => $node['created']
						)
					),
					'subforums' 	=> array(),
				);
			}

			foreach ($channels as $channel_id => $channel)
			{
				$nodeId = $channel['forumid'];
				$parentId = $channel['parentid'];
				unset($channels[$nodeId]['parentid']);
				if ($channel['lastpostinfo']['nodeid'] > 0)
				{
					$node = vB_Api::instance('node')->getFullContentforNodes(array($channel['lastpostinfo']['nodeid']));
					if (is_array($node))
					{
						$node = array_pop($node);
					}

					$channels[$nodeId]['lastpostinfo']['lastpostinfo']['lastthreadid'] = $node['content']['starter'];
					$channels[$nodeId]['lastpostinfo']['lastpostinfo']['lastthreadtitle'] = $node['content']['startertitle'];
					unset($channels[$nodeId]['lastpostinfo']['nodeid']);
				}


				if (isset($channels[$parentId]))
				{
					// assign by reference, so subchannels can be filled in later
					$channels[$parentId]['subforums'][$nodeId] =& $channels[$nodeId];
					unset($channels[$channel_id]);
				}
				else
				{
					// assign by reference, so subchannels can be filled in later
					$channelHierarchy[$nodeId] =& $channels[$nodeId];
				}
			}
		}

		$forumbits = array();
		if (!empty($channels))
		{
			foreach($channels as $channel_key => $channel)
			{
				$channels[$channel_key] = $this->removeChannelKeys($channel);
			}

			$forumbits = array_values($channels);
		}


		$notices_dirty = vB_Api::instance('notice')->fetch();
		$notices = array();
		foreach($notices_dirty as $notice_id => $notice_dirty)
		{
			$notice = array();
			$notice['notice_html'] = (string)new vB_Phrase('global', $notice_dirty['notice_phrase_varname']);
			$notice['notice_plain'] = strip_tags((string)new vB_Phrase('global', $notice_dirty['notice_phrase_varname']));
			$notice['_noticeid'] = $notice_id;
			$notices[] = $notice;
		}

		$response = array();
		$response['response']['forumbits'] = $forumbits;
		$response['response']['header'] = array();
		$response['response']['header']['notices'] = $notices;

		$userInfo = vB_Api::instance('user')->fetchUserinfo();

		$userid = $userInfo['userid'];
		$notifications = array();

		$response['response']['header']['notifications_menubits'] = $notifications;
		$response['response']['header']['notifications_total'] = 0;

		if($userid > 0)
		{
			$notif_summary = vB_Api::instance('content_privatemessage')->fetchSummary();
			if (empty($notif_summary['errors']))
			{
				if($notif_summary['folders']['requests']['qty'] > 0)
				{
					$notifications[] = array(
						'notification' => array(
							'total' => $notif_summary['folders']['requests']['qty'],
							'phrase' => $notif_summary['folders']['requests']['title'],
							'name' => 'friendreqcount',
						)
					);
				}

				if($notif_summary['folders']['messages']['qty'] > 0)
				{
					$notifications[] = array(
						'notification' => array(
							'total' => $notif_summary['folders']['messages']['qty'],
							'phrase' => $notif_summary['folders']['messages']['title'],
							'name' => 'pmunread'
						)
					);
				}

				if($notif_summary['folders']['notifications']['qty'] > 0)
				{
					$allNotifications = vB_Api::instance('content_privatemessage')->listNotifications(array('readFilter' => "unread_only"));
					$pmLib = vB_Library::instance('content_privatemessage');
					/*
						VBV-14837 - Add return for Subscriptions & Topic Replies
						Keep this in sync with vB_Api_Vb4_notification::get()
					 */
					$notificationCounts = array(
						vB_Notification_VisitorMessage::TYPENAME => 0,
						vB_Notification_Content_GroupByStarter_Subscription::TYPENAME => 0,
						vB_Notification_Content_GroupByStarter_Reply::TYPENAME => 0,
						vB_Notification_Content_GroupByParentid_Comment::TYPENAME => 0,
						vB_Notification_Content_GroupByParentid_ThreadComment::TYPENAME => 0,
					);

					foreach ($allNotifications as $key => $val)
					{
						if (isset($notificationCounts[$val['typename']]))
						{
							$notificationCounts[$val['typename']]++;
						}
					}

					foreach ($notificationCounts AS $typename => $count)
					{
						switch($typename)
						{
							case vB_Notification_VisitorMessage::TYPENAME:
								$phraseid = ($count === 1) ? 'special_visitormessage_singular' : 'special_visitormessage';
								$phrase = (string) new vB_Phrase('global', $phraseid);
								$name = 'vmunreadcount';
								break;
							case vB_Notification_Content_GroupByStarter_Subscription::TYPENAME:
								$phraseid = ($count === 1) ? 'content_subscription_singular' : 'content_subscription';
								$phrase = (string) new vB_Phrase('global', $phraseid);
								$name = $typename;
								break;
							case vB_Notification_Content_GroupByStarter_Reply::TYPENAME:
								$phraseid = ($count === 1) ? 'content_reply_singular' : 'content_reply';
								$phrase = (string) new vB_Phrase('global', $phraseid);
								$name = $typename;
								break;
							case vB_Notification_Content_GroupByParentid_Comment::TYPENAME:
								$phraseid = ($count === 1) ? 'content_comment_singular' : 'content_comment';
								$phrase = (string) new vB_Phrase('global', $phraseid);
								$name = $typename;
								break;
							case vB_Notification_Content_GroupByParentid_ThreadComment::TYPENAME:
								$phraseid = ($count === 1) ? 'content_threadcomment_singular' : 'content_threadcomment';
								$phrase = (string) new vB_Phrase('global', $phraseid);
								$name = $typename;
								break;
							default:
								unset($name, $phrase, $id);
								break;
						}

						if (!empty($count) AND !empty($phrase) AND !empty($name))
						{
							$newArr =  array(
								'notification' => array(
									'total' => $count,
									'phrase' => $phrase,
									'name' => $name,
								),
								'notificationid' => $name,
							);
							$notifications[] = $newArr;
						}
						unset($name, $phrase, $id);
					}
				}

				/*
					Reports

					Based on perm check in template widget_privatemessage_navigation that shows the link
					to the special folder.
						{vb:data specialChannelViewPerms, content_channel, canViewReportsAndInfractions}

						<!--Flag Reports-->
						<vb:if condition="$specialChannelViewPerms['result']['can_view_reports']">
				 */
				$canViewSpecials = vB_Api::instance('content_channel')->canViewReportsAndInfractions();
				if (!empty($canViewSpecials['result']['can_view_reports']))
				{
					$phrase = (string) new vB_Phrase('global', 'flag_reports');
					$total = vB::getDbAssertor()->getRow('vBForum:report', array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_COUNT));
					$total = $total['count'];
					$newArr =  array(
						'notification' => array(
							'total' => $total,
							'phrase' => $phrase,
							'name' => "Flag",
						),
						'notificationid' => "Flag",
					);
					$notifications[] = $newArr;
				}

				$response['response']['header']['notifications_menubits'] = $notifications;
				$response['response']['header']['notifications_total'] = count($notifications);
			}
		}

		return $response;
	}

	private function removeChannelKeys(&$channel)
	{
		if(is_array($channel['subforums']))
		{
			foreach($channel['subforums'] as &$channel1)
			{
				$this->removeChannelKeys($channel1);
			}
			$channel['subforums'] = array_values($channel['subforums']);
		}
		return $channel;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
