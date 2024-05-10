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
 * vB_Api_Notice
 *
 * @package vBApi
 * @access public
 */
class vB_Api_Notice extends vB_Api
{
	/**
	 * @var vB_dB_Assertor
	 */
	protected $assertor;

	protected $disableFalseReturnOnly = array('fetch');


	protected function __construct()
	{
		parent::__construct();

		$this->assertor = vB::getDbAssertor();
	}

	public function dismiss($noticeid)
	{
		$userinfo = vB::getCurrentSession()->fetch_userinfo();
		if (!$userinfo['userid'])
		{
			throw new vB_Exception_Api('no_permission');
		}

		$noticecache = vB::getDatastore()->getValue('noticecache');

		if (!$noticecache[$noticeid]['dismissible'])
		{
			throw new vB_Exception_Api('notice_not_dismissible');
		}

		$this->assertor->assertQuery('vBForum:dismissnotice', array(
			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_STORED,
			'noticeid' => intval($noticeid),
			'userid' => $userinfo['userid'],
		));

		return true;
	}

	/**
	 * Fetch notices to be displayed
	 *
	 * @param int $channelid Current Channel ID
	 * @param array $ignore_np_notices Ignored non-persistent notice ids
	 * @param boolean Whether or not to do the phrase replacement, if false, the client is responsible
	 * @return array Notices
	 */
	public function fetch($channelid = 0, $ignore_np_notices = array(), $replace_phrases = false)
	{
		if ($channelid)
		{
			$channelapi = vB_Api::instanceInternal('content_channel');
			// This is to verify $channelid
			$channelapi->fetchChannelById($channelid);
		}

		$noticecache = vB::getDatastore()->getValue('noticecache');
		$userinfo = vB::getCurrentSession()->fetch_userinfo();
		$vboptions = vB::getDatastore()->getValue('options');
		$display_notices = array();

		if (is_null($noticecache))
		{
			/*
				Notice cache should be an array, not null. If it's null, suspect that
				something borked/truncated the datastore and automagically rebuild it.
			 */
			require_once(DIR . '/includes/adminfunctions_notice.php');
			build_notice_datastore();
			$noticecache = vB::getDatastore()->getValue('noticecache');
		}

		foreach ($noticecache AS $noticeid => $notice)
		{
			foreach ($notice AS $criteriaid => $conditions)
			{
				switch ($criteriaid)
				{
					case 'persistent':
					{
						if (($conditions == 0) AND (is_array($ignore_np_notices)) AND in_array($noticeid, $ignore_np_notices)) // session cookie set in print_output()
						{
							continue 3;
						}
						break;
					}
					case 'dismissible':
					{
						if ($conditions == 1 AND in_array($noticeid, $this->fetchDismissedNotices()))
						{
							continue 3;
						}
						break;
					}
					/*case 'notice_x_not_displayed': // this is now handled differently - see $remove_display_notices below
					{
						if (in_array(intval($conditions[0]), $display_notices))
						{
							continue 3;
						}
						break;
					}*/
					case 'in_usergroup_x':
					{
						if (!is_member_of($userinfo, intval($conditions[0])))
						{
							continue 3;
						}
						break;
					}
					case 'not_in_usergroup_x':
					{
						if (is_member_of($userinfo, intval($conditions[0])))
						{
							continue 3;
						}
						break;
					}
					case 'browsing_forum_x':
					{
						if (!$channelid OR $channelid != intval($conditions[0]))
						{
							continue 3;
						}
						break;
					}
					case 'browsing_forum_x_and_children':
					{
						if (!$channelid)
						{
							continue 3;
						}
						$parents = vB_Library::instance('node')->getParents($channelid);
						$parentids = array();
						foreach ($parents as $parent)
						{
							if ($parent['nodeid'] != 1)
							{
								$parentids[] = $parent['nodeid'];
							}
						}
						if (!in_array(intval($conditions[0]), $parentids))
						{
							continue 3;
						}
						break;
					}
					case 'no_visit_in_x_days':
					{
						if ($userinfo['lastvisit'] > vB::getRequest()->getTimeNow() - $conditions[0] * 86400)
						{
							continue 3;
						}
						break;
					}
					case 'has_never_posted':
					{
						if ($userinfo['posts'] > 0)
						{
							continue 3;
						}
						break;
					}
					case 'no_posts_in_x_days':
					{
						if ($userinfo['lastpost'] == 0 OR $userinfo['lastpost'] > vB::getRequest()->getTimeNow() - $conditions[0] * 86400)
						{
							continue 3;
						}
						break;
					}
					case 'has_x_postcount':
					{
						if (!$this->checkNoticeCriteriaBetween($userinfo['posts'], $conditions[0], $conditions[1]))
						{
							continue 3;
						}
						break;
					}
					case 'has_x_reputation':
					{
						if (!$this->checkNoticeCriteriaBetween($userinfo['reputation'], $conditions[0], $conditions[1]))
						{
							continue 3;
						}
						break;
					}
					case 'has_x_infraction_points':
					{
						if (!$this->checkNoticeCriteriaBetween($userinfo['ipoints'], $conditions[0], $conditions[1]))
						{
							continue 3;
						}
						break;
					}
					case 'pm_storage_x_percent_full':
					{
						$pmquota = vB::getUserContext()->getLimit('pmquota');
						if ($pmquota)
						{
							$pmboxpercentage = ($userinfo['pmtotal'] / $pmquota) * 100;
							if (!$this->checkNoticeCriteriaBetween($pmboxpercentage, $conditions[0], $conditions[1]))
							{
								continue 3;
							}
						}
						else
						{
							continue 3;
						}
						break;
					}
					case 'username_is':
					{
						if (strtolower($userinfo['username']) != strtolower(trim($conditions[0])))
						{
							continue 3;
						}
						break;
					}
					case 'is_birthday':
					{
						if (substr($userinfo['birthday'], 0, 5) != vbdate('m-d', vB::getRequest()->getTimeNow(), false, false))
						{
							continue 3;
						}
						break;
					}
					case 'came_from_search_engine':
						if (!is_came_from_search_engine())
						{
							continue 3;
						}
						break;
					case 'style_is_x':
					{
						$styleid = vB::getCurrentSession()->get('styleid');
						if ($styleid != intval($conditions[0]))
						{
							continue 3;
						}
						break;
					}
					case 'in_coventry':
					{
						if (!in_array($userinfo['userid'], preg_split('#\s+#', $vboptions['globalignore'], -1, PREG_SPLIT_NO_EMPTY)))
						{
							continue 3;
						}
						break;
					}
					case 'is_date':
					{
						if (empty($conditions[1]) AND vbdate('d-m-Y', vB::getRequest()->getTimeNow(), false, false) != $conditions[0]) // user timezone
						{
							continue 3;
						}
						else if ($conditions[1] AND gmdate('d-m-Y', vB::getRequest()->getTimeNow()) != $conditions[0]) // utc
						{
							continue 3;
						}
						break;
					}
					case 'is_time':
					{
						if (preg_match('#^(\d{1,2}):(\d{2})$#', $conditions[0], $start_time) AND preg_match('#^(\d{1,2}):(\d{2})$#', $conditions[1], $end_time))
						{
							if (empty($conditions[2])) // user timezone
							{
								$start = mktime($start_time[1], $start_time[2]) + $userinfo['servertimediff'];
								$end   = mktime($end_time[1], $end_time[2]) + $userinfo['servertimediff'];
								$now = time();
							}
							else // utc
							{
								$start = gmmktime($start_time[1], $start_time[2]);
								$end   = gmmktime($end_time[1], $end_time[2]);
								$now   = gmmktime();
							}

							//if we end earlier than we start, interpret that as a range that overlaps
							//midnight.  However we need account for the range that starts yesterday
							//as well as the one that starts today.  If we are currently less than the
							//start time we can only be in yesterday's range.  If we are currently greater
							//than the start time we can only be in today's range.  We can simplify this
							//a little since if we are greater than the start time we *are* in todays range
							//since we've established that the end time is tomorrow.  However given the
							//negation of the logic below (we skip this case if we aren't in the range)
							//it's not clear that checking the intervals directly instead of doing the
							//math is going to overall simply the code and it could obscure what we are doing.
							if ($end < $start)
							{
								if ($now < $start)
								{
									$start = $start - (24 * 60 * 60);
								}
								else
								{
									$end = $end + (24 * 60 * 60);
								}
							}

							if ($now < $start OR $now > $end)
							{
								continue 3;
							}
						}
						else
						{
							continue 3;
						}
						break;
					}
					default:
					{
						$abort = false;

						if ($abort)
						{
							continue 3;
						}
					}
				}
			}

			$display_notices["$noticeid"] = $noticeid;
		}

		// now go through removing notices using the 'notice_x_not_displayed' criteria
		$remove_display_notices = array();
		foreach ($noticecache AS $noticeid => $notice)
		{
			if (isset($notice['notice_x_not_displayed']) AND isset($display_notices[intval($notice['notice_x_not_displayed'][0])]))
			{
				$remove_display_notices["$noticeid"] = $noticeid;
			}
		}
		foreach ($remove_display_notices AS $noticeid)
		{
			unset($display_notices["$noticeid"]);
		}

		$return = array();

		if ($display_notices)
		{
			if ($replace_phrases)
			{
				// Prefech phrases
				$phrases = array();
				foreach ($display_notices as $display_notice)
				{
					$phrases[] = "notice_{$display_notice}_html";
				}
				$vbphrase = vB_Api::instanceInternal('phrase')->fetch($phrases);

				$replace_map = array (
					'{sitename}' => $vboptions['bbtitle'],
					'{musername}' => $userinfo['musername'],
					'{username}' => $userinfo['username'],
					'{userid}' => $userinfo['userid'],
					'{register_page}' => vB5_Route::buildUrl('register|fullurl'),
					'{activation_page}' => vB5_Route::buildUrl('activateuser|fullurl'),
					'{help_page}' => vB5_Route::buildUrl('help|fullurl'),
					//these are deprecated, but leave them in so that if they are used
					//they won't suddenly pop up in old notices.
					'{sessionurl}' => '',
					'{sessionurl_q}' => '',
				);

				foreach ($display_notices as $display_notice)
				{
					$notice_html = str_replace(array_keys($replace_map), $replace_map, $vbphrase["notice_{$display_notice}_html"]);
					$return[$display_notice] = $noticecache[$display_notice];
					$return[$display_notice]['notice_html'] = $notice_html;
				}
			}
			else
			{
				foreach ($display_notices as $display_notice)
				{
					$return[$display_notice] = $noticecache[$display_notice];
					$return[$display_notice]['notice_phrase_varname'] = "notice_{$display_notice}_html";
				}
			}
		}

		return $return;
	}

	/**
	 * Fetches the IDs of the dismissed notices so we do not display them for the user.
	 *
	 * @return array
	 */
	protected function fetchDismissedNotices()
	{
		static $dismissed_notices = null;
		if ($dismissed_notices === null)
		{
			$userinfo = vB::getCurrentSession()->fetch_userinfo();

			$dismissed_notices = array();

			if (!$userinfo['userid'])
			{
				return $dismissed_notices;
			}

			$noticeids = $this->assertor->getRows('vBForum:fetchdismissednotices', array(
				vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_STORED,
				'userid' => $userinfo['userid'],
			));

			foreach ($noticeids as $noticeid)
			{
				$dismissed_notices[] = $noticeid['noticeid'];
			}
		}
		return $dismissed_notices;
	}

	/**
	* Checks if the specified criteria is between 2 values.
	* If either bound is the empty string, it is ignored.
	* Bounds are inclusive on either side (>= / <=).
	*
	* @param	integer			Value to check
	* @param	string|integer	Lower bound. If === '', ignored.
	* @param	string|integer	Upper bound. If === '', ignored.
	*
	* @return	boolean			True if between
	*/
	protected function checkNoticeCriteriaBetween($value, $cond1, $cond2)
	{
		if ($cond1 === '')
		{
			// no value for first condition, treat as <= $cond2
			return ($value <= intval($cond2));
		}
		else if ($cond2 === '')
		{
			// no value for second condition, treat as >= $cond1
			return ($value >= intval($cond2));
		}
		else
		{
			// check that value is between (inclusive) the two given conditions
			return ($value >= intval($cond1) AND $value <= intval($cond2));
		}
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
