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
 * vB_Library_Search
 *
 * @package vBLibrary
 */

class vB_Library_Search extends vB_Library
{

	/**
	 * Re-indexes the whole database
	 * Returns true if the full indexing is implemented and successful for the selected search implementation
	 * Returns false if the full indexing is not implemented
	 * @var bool $silent whther to print the progress to the output
	 * @return boolean
	 */
	public function reIndexAll($silent = false)
	{
		return vB_Search_Core::instance()->reIndexAll($silent);
	}

	/**
	 *
	 * Index a node
	 * @param int $node_id to index
	 * @param boolean $propagate flag to propagate the indexing to the nodes parents
	 */
	public function index($node_id, $propagate = true)
	{
		vB_Search_Core::instance()->index($node_id, $propagate);
	}

	public function indexText($node, $title, $text)
	{
		vB_Search_Core::instance()->indexText($node, $title, $text);
	}

	public function indexTrending($lookback, $minimum)
	{
		vB_Search_Core::instance()->indexTrending($lookback, $minimum);
	}

	public function attributeChanged($nodeid)
	{
		vB_Search_Core::instance()->attributeChanged($nodeid);
	}

	public function emptyIndex()
	{
		vB_Search_Core::instance()->emptyIndex();
	}

	/**
	 * Index a range of nodes based on the prior node
	 *
	 * This is intended to page through a list of nodes to break up a reindex operation
	 * without using a (limitstart, limit) process that doesn't scale well to large
	 * node tables.
	 *
	 * We skip channels and reports when doing the range.
	 *
	 * @param int $previousNodeId -- the node to start after.  This is frequently the nodeid
	 * 	returned from the previous batch.  Pass 0 to start from the beginning.  Pass
	 * 	$nodid-1 to start at a particular node inclusive.
	 * @param int $perpage -- the number of nodes to index per run. You can pass a false
	 * 	value to index the entire table in one go, but this isn't really a good idea on large sites.
	 * @param
	 * @param string|int|array -- only index the provided contenttypes.  This can use either
	 * 	the string version or the internal id value or an array of either.
	 *
	 * @return int|bool -- returns the id of the last node indexed or false if there are
	 * 	no more nodes to index.
	 */
	public function indexRangeFromNode($previousNodeId, $perpage, $channelid = null, $contenttype = null)
	{
		$params = $this->getNodeRangeConditions($previousNodeId, $channelid, $contenttype);
		if($perpage)
		{
			$params[vB_Db_Query::PARAM_LIMIT] = $perpage;
		}

		$nodes = vB::getDbAssertor()->getRows('vBForum:getNodesToIndex', $params, false, 'nodeid');
		vB_Search_Core::instance()->indexBatch($nodes, true);

		//we processed a full batch so there are more
		//(in the edge case where there is an exact mutiple of perpage we
		//will process an empty batch.  This isn't a problem.
		if($perpage AND (count($nodes) == $perpage))
		{
			$last = end($nodes);
			return $last['nodeid'];
		}

		return false;
	}

	/**
	 * Gets the nodes to index from a particular node
	 *
	 * The companion of indexRangeFromNode, this get's the total number of nodes to count.  The startNode
	 * is provided in case a partial index is intended.  The expectation is that this is called
	 * once with the starting node and not for each batch.  This will take some time on larger sites
	 * so should be avoided if time is an issue (it may cause the webserver to time out).
	 *
	 * This is primarily hear to avoid duplicating logic affecting the count that might change.
	 *
	 * @param int $previousNodeId -- the node to start after.  This is frequently the nodeid
	 * 	returned from the previous batch.  Pass 0 to start from the beginning.  Pass
	 * 	$nodid-1 to start at a particular node inclusive.
	 * @param string|int|array -- only index the provided contenttypes.  This can use either
	 * 	the string version or the internal id value or an array of either.
	 *
	 * @return int -- the number of nodes to index
	 */
	public function getRangeFromNodeCount($previousNodeId, $channelid = null, $contenttype = null)
	{
		$params = $this->getNodeRangeConditions($previousNodeId, $channelid, $contenttype);
		$row = vB::getDbAssertor()->getRow('vBForum:getNodesToIndexCount', $params);
		return $row['count'];
	}

	private function getNodeRangeConditions($previousNodeId, $channelid, $contenttype)
	{
		$types = vB_Types::instance();

		$conditions = array();

		if($channelid)
		{
			$conditions['channelid'] = $channelid;
		}

		//handle no contenttype, a single content type, or an array.
		//This should handle ids or string values.
		if($contenttype)
		{
			if (!is_array($contenttype))
			{
				$contenttype = array($contenttype);
			}

			foreach($contenttype AS $index => $value)
			{
				$contenttype[$index] = $types->getContentTypeID($value);
			}

			$conditions['contenttypeids'] = $contenttype;
		}

		$conditions['excludecontenttypeids'] = array(
//			$types->getContentTypeID('vBForum_Channel'),
			$types->getContentTypeID('vBForum_Report'),
		);

		$conditions['nodeid'] = $previousNodeId;
		return $conditions;
	}

	public function delete($nodeid, $node = false)
	{
		vB_Search_Core::instance()->delete($nodeid, $node);
	}

	/**
	 * Purge search log cache for current logged-in user
	 */
	public function purgeCacheForCurrentUser($from = false)
	{
		// changed the default value, see VBV-17655. This works because
		// the vB_Search_Core function was also changed to default to
		// the configured setting, which is shipped at 300 seconds
		/*
			Even though the comment above says "logged-in user", this could be a guest, and guests might have
			legitimate cached values (see vB_Search_Core::saveSecondPassResults()).

			It seems a bit wrong to allow any guest to blow the cache for *all* guests, but I'm not going to add
			a guest check here at this point because of regression risk.
		 */
		$userid = vB::getUserContext()->fetchUserId();
		vB_Cache::instance(vB_Cache::CACHE_STD)->event(array('vB_SearchResults_chg_' . $userid));
		vB_Search_Core::instance()->purgeCacheForUser($userid, $from);
	}

	public function clean()
	{
		vB_Search_Core::instance()->clean();
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
