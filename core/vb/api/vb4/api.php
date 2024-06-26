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
 * vB_Api_Vb4_api
 *
 * @package vBApi
 * @access public
 */
class vB_Api_Vb4_api extends vB_Api
{
	/**
	 * Takes You to the first unread post of the thread or
	 * if there are no unread posts it takes you to the last post
	 *
	 * @param  [type] $threadid [The nodeid of the thread]
	 * @return [array]          [return array from the showthread api method]
	 */
	public function gotonewpost($threadid)
	{
		$assertor = vB::getDbAssertor();
		$userinfo = vB_Api::instance('user')->fetchUserinfo();
		$perpage  = vB_Library::instance('vb4_functions')->getUsersPostPerPage(0);

		// Get readtime of the thread
		$lastRead = $assertor->getRow('vBForum:noderead', array(
			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT,
			'nodeid' => $threadid,
			'userid' => $userinfo['userid'],
		));

		if (!$lastRead)
		{
			// Thread has never been visited before, got to last page - as per comment on VBV-9310
			$nbReplies = $assertor->getRows('vBForum:node', array(
	 			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT,
				'parentid' => $threadid
	 		));
	 		$pageNumber = ceil((count($nbReplies) + 1) / $perpage);

			return vB_Api::instance('vb4_showthread')->call($threadid, $perpage, $pageNumber);
		}
		else
		{
			// Get the first unread reply after the threadmarking time
			$unreadpost = $assertor->getRow('vBForum:getFirstUnreadReply', array(
				'nodeid' => $threadid,
				'publishdate' => $lastRead['readtime'],
			));
			if ($unreadpost AND !empty($unreadpost['publishdate']))
			{
				$lastvisit = $unreadpost['publishdate'];
			}
			else
			{
				$lastvisit = $lastRead['readtime'];
			}

			// All the replies before the lastunread reply
			$nbreplies = $assertor->getRow('vBForum:getNodeReplyNumber', array(
				'nodeid' => $threadid,
				'publishdate' => $lastvisit,
			));
			// Increase the replies by 1 to count the starter (parentid)
			$pageNumber = ceil(($nbreplies['replies'] + 1) / $perpage);

			return vB_Api::instance('vb4_showthread')->call($threadid, $perpage, $pageNumber);
		}
	}

	public function mobilepublisher()
	{
		$options = vB::getDatastore()->getValue('options');
		$stylevars = vB_Api::instance('stylevar')->fetch();
		return array(
			'bburl' => $options['bburl'],
			'smilies' => array(),
			'logo' => $stylevars['titleimage']['url'],
			'colors' => array(
				'titletext' => $stylevars['main_nav_admin_bar_text_color']['color'],
				'primarytext' => $stylevars['body_link_color']['color'],
				'bodytext' => $stylevars['body_text_color']['color'],
				'highlighttext' => $stylevars['body_text_color']['color'],
				'background' => $stylevars['module_header_background']['color'],
				'foreground' => $stylevars['module_content_background']['color'],
				'buttoncolor' => $stylevars['profcustom_navbarbutton_background']['color'],
				'highlightcolor' => $stylevars['search_result_highlight_color']['color'],
			)
		);
	}

	public function blogcategorylist($userid = 0)
	{
		$cleaner = vB::getCleaner();
		$userid = $cleaner->clean($userid, vB_Cleaner::TYPE_UINT);
		return array(
			'response' => array(
				'globalcategorybits' => vB_Library::instance('vb4_functions')->getGlobalBlogCategories(),
				'localcategorybits' => vB_Library::instance('vb4_functions')->getLocalBlogCategories($userid),
			),
		);
	}

	public function getnewtop($timestamp = 60, $max_items_per_category = 10)
	{
		$response = array();
		$response['new']['thread'] = array();
		$response['new']['blog'] = array();
		$response['top'] = $response['new'];

		$top = vB_Api::instance('content_channel')->fetchTopLevelChannelIds();
		$forumid = $top['forum'];

		$search = array('channel' => $forumid);
		$search['starter_only'] = true;
		$search['sort']['lastcontent'] = 'desc';
		$search['date']['from'] = $timestamp;

		$newestNodes = vB_Api::instance('search')->getInitialResults($search, $max_items_per_category, 1, true);

		if (!isset($newestNodes['errors']) AND !empty($newestNodes['results']))
		{
			foreach ($newestNodes['results'] AS $key => $node)
			{
				$response['new']['thread'][] = $this->parseThreadNode($node);
			}
		}

		$top = vB_Api::instance('content_channel')->fetchTopLevelChannelIds();
		$forumid = $top['forum'];

		$search = array('channel' => $forumid);
		$search['starter_only'] = true;
		$search['sort']['votes'] = 'desc';
		$search['date']['from'] = $timestamp;

		$newestNodes = vB_Api::instance('search')->getInitialResults($search, $max_items_per_category, 1, true);

		if (!isset($newestNodes['errors']) AND !empty($newestNodes['results']))
		{
			foreach ($newestNodes['results'] AS $key => $node)
			{
				$response['top']['thread'][] = $this->parseThreadNode($node);
			}
		}

		$blog_channel = vB_Api::instanceInternal('blog')->getBlogChannel();
		$search = array('channel' => $blog_channel);
		$search['starter_only'] = true;
		$search['sort']['lastcontent'] = 'desc';
		$search['date']['from'] = $timestamp;

		$newestNodes = vB_Api::instanceInternal('search')->getInitialResults($search, $max_items_per_category, 1, true);

		if (!isset($newestNodes['errors']) AND !empty($newestNodes['results']))
		{
			foreach ($newestNodes['results'] AS $key => $node)
			{
				$response['new']['blog'][] = $this->parseBlogNode($node);
			}
		}

		$search = array('channel' => $blog_channel);
		$search['starter_only'] = true;
		$search['sort']['views'] = 'desc';
		$search['date']['from'] = $timestamp;

		$newestNodes = vB_Api::instanceInternal('search')->getInitialResults($search, $max_items_per_category, 1, true);

		if (!isset($newestNodes['errors']) AND !empty($newestNodes['results']))
		{
			foreach ($newestNodes['results'] AS $key => $node)
			{
				$response['top']['blog'][] = $this->parseBlogNode($node);
			}
		}
		$response['top']['all'] = $response['top']['thread'];
		$response['new']['all'] = $response['new']['thread'];
		$response['top']['all'] = array_merge($response['top']['thread'], $response['top']['blog']);
		$response['new']['all'] = array_merge($response['new']['thread'], $response['new']['blog']);
		return $response;
	}

	private function parseThreadNode($node)
	{
		return array(
			'forumid' => $node['content']['channelid'],
			'forumtitle' => $node['content']['channeltitle'],
			'id' => $node['nodeid'],
			'username' => $node['userid'] > 0 ? $node['authorname'] : ((string)new vB_Phrase('global', 'guest')),
			'userid' => $node['userid'],
			'type' => 'thread',
			'title' => $node['title'],
			'lastposttime' => $node['lastcontent'] ? $node['lastcontent'] : $node['publishdate'],
			'lastpostuser' => !empty($node['lastcontentauthor']) ? $node['lastcontentauthor'] : $node['authorname'],
			'replycount' => $node['textcount'],
			'viewcount' => $node['totalcount'] + $node['votes'], // Fake views because we don't have them in 5
			'avatarurl' => vB_Library::instance('vb4_functions')->avatarUrl($node['userid']),
		);
	}

	private function parseBlogNode($node)
	{
		return array(
			'blogid' => $node['nodeid'],
			'username' => $node['userid'] > 0 ? $node['authorname'] : ((string)new vB_Phrase('global', 'guest')),
			'userid' => $node['userid'],
			'type' => 'blog',
			'title' => $node['title'],
			'lastposttime' => $node['lastcontent'] ? $node['lastcontent'] : $node['publishdate'],
			'replycount' => $node['textcount'],
			'viewcount' => $node['totalcount'] + $node['votes'], // Fake views because we don't have them in 5
			'avatarurl' => vB_Library::instance('vb4_functions')->avatarUrl($node['userid']),
		);
	}

	public function forumlist()
	{
		$nodes = vB_Api::instance('node')->fetchChannelNodeTree(-1, 3, 1, 100);
		$channels = array();

		if (!empty($nodes) AND empty($nodes['errors']))
		{
			foreach ($nodes['channels'] AS $node)
			{
				$channels[] = vB_Library::instance('vb4_functions')->parseForum($node);
			}
		}
		return $channels;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
