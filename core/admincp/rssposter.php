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

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision: 99787 $');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
global $phrasegroups, $specialtemplates,$vbphrase;
$phrasegroups = array('cron', 'cpuser', 'prefix');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once(dirname(__FILE__) . '/global.php');
require_once(DIR . '/includes/adminfunctions_prefix.php');
$assertor = vB::getDbAssertor();

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

// ############################# LOG ACTION ###############################
log_admin_action(!empty($vbulletin->GPC['rssfeedid']) ? 'RSS feed id = ' . $vbulletin->GPC['rssfeedid'] : '');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminrss'))
{
	print_cp_no_permission();
}

// #############################################################################
if ($_POST['do'] == 'updatestatus')
{
	$vbulletin->input->clean_gpc('p', 'enabled', vB_Cleaner::TYPE_ARRAY_UINT);

	$feeds_result = $assertor->getRows('vBForum:rssfeed', array(), 'title');
	$options = vB::getDatastore()->getValue('bf_misc_feedoptions');
	foreach ($feeds_result AS $feed)
	{
		$old = ($feed['options'] & $options['enabled'] ? 1 : 0);
		$new = ($vbulletin->GPC['enabled']["$feed[rssfeedid]"] ? 1 : 0);

		if ($old != $new)
		{
			$feeddata =& datamanager_init('RSSFeed', $vbulletin, vB_DataManager_Constants::ERRTYPE_ARRAY);
			$feeddata->set_existing($feed);
			$feeddata->set_bitfield('options', 'enabled', $new);
			$feeddata->save();
		}
	}

	exec_header_redirect2('rssposter');
}

print_cp_header($vbphrase['rss_feed_manager']);

// #############################################################################

if ($_POST['do'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'rssfeedid', vB_Cleaner::TYPE_UINT);

	if ($vbulletin->GPC['rssfeedid'] AND $feed = $assertor->getRow('vBForum:rssfeed', array('rssfeedid' => $vbulletin->GPC['rssfeedid'])))
	{
		$feeddata =& datamanager_init('RSSFeed', $vbulletin, vB_DataManager_Constants::ERRTYPE_ARRAY);
		$feeddata->set_existing($feed);
		$feeddata->delete();

		print_stop_message2(array('deleted_rssfeed_x_successfully',  $feeddata->fetch_field('title')), 'rssposter');
	}
	else
	{
		echo "Kill oops";
	}
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'rssfeedid', vB_Cleaner::TYPE_UINT);

	if ($vbulletin->GPC['rssfeedid'] AND $feed = $assertor->getRow('vBForum:rssfeed', array('rssfeedid' => $vbulletin->GPC['rssfeedid'])))
	{
		print_delete_confirmation('vBForum:rssfeed', $vbulletin->GPC['rssfeedid'], 'rssposter', 'kill', 'rssfeed', 0, '', 'title', 'rssfeedid');
	}
	else
	{
		echo "Delete oops";
	}
}

// #############################################################################

// this array is used by do=preview and do=update
$input_vars = array(
	'rssfeedid'         => vB_Cleaner::TYPE_UINT,
	'title'             => vB_Cleaner::TYPE_NOHTML,
	'url'               => vB_Cleaner::TYPE_STR,
	'ttl'               => vB_Cleaner::TYPE_UINT,
	'maxresults'        => vB_Cleaner::TYPE_UINT,
	'titletemplate'     => vB_Cleaner::TYPE_STR,
	'bodytemplate'      => vB_Cleaner::TYPE_STR,
	'username'          => vB_Cleaner::TYPE_NOHTML,
	'nodeid'           => vB_Cleaner::TYPE_UINT,
	'prefixid'          => vB_Cleaner::TYPE_NOHTML,
	'iconid'            => vB_Cleaner::TYPE_UINT,
	'searchwords'       => vB_Cleaner::TYPE_STR,
	'itemtype'          => vB_Cleaner::TYPE_STR,
	'topicactiondelay' => vB_Cleaner::TYPE_UINT,
	'endannouncement'   => vB_Cleaner::TYPE_UINT,
	'resetlastrun'      => vB_Cleaner::TYPE_BOOL,
	'options'           => vB_Cleaner::TYPE_ARRAY_BOOL
);

// #############################################################################

if ($_POST['do'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', $input_vars);
	if (empty($vbulletin->GPC['url']))
	{
		print_stop_message2('upload_invalid_url');
	}

	if (empty($_POST['preview']))
	{
		if ($vbulletin->GPC['rssfeedid'])
		{
			// update to follow
			$feed = $assertor->getRow('vBForum:getUserRssFeed', array('rssfeedid' => $vbulletin->GPC['rssfeedid']));
		}
		else
		{
			$feed = array();
		}

		$feeddata =& datamanager_init('RSSFeed', $vbulletin, vB_DataManager_Constants::ERRTYPE_ARRAY);

		if (!empty($feed))
		{
			// doing an update, provide existing data to datamanager
			$feeddata->set_existing($feed);
		}

		$feeddata->set('title', $vbulletin->GPC['title']);
		$feeddata->set('url', $vbulletin->GPC['url']);
		$feeddata->set('ttl', $vbulletin->GPC['ttl']);
		$feeddata->set('maxresults',$vbulletin->GPC['maxresults']);
		$feeddata->set('titletemplate', $vbulletin->GPC['titletemplate']);
		$feeddata->set('bodytemplate', $vbulletin->GPC['bodytemplate']);
		$feeddata->set('searchwords', $vbulletin->GPC['searchwords']);
		$feeddata->set('nodeid', $vbulletin->GPC['nodeid']);
		$feeddata->set('prefixid', $vbulletin->GPC['prefixid']);
		$feeddata->set('iconid', $vbulletin->GPC['iconid']);
		$feeddata->set('topicactiondelay', $vbulletin->GPC['topicactiondelay']);
		$feeddata->set('itemtype', $vbulletin->GPC['itemtype']);
		$feeddata->set('endannouncement', $vbulletin->GPC['endannouncement']);
		$feeddata->set_user_by_name($vbulletin->GPC['username']);

		if ($vbulletin->GPC['resetlastrun'])
		{
			$feeddata->set('lastrun', 0);
		}

		// take allow smilies from selected channel.
		$channelInfo = vB_Library::instance('content_channel')->getBareContent($vbulletin->GPC['nodeid']);
		$channel = $channelInfo[$vbulletin->GPC['nodeid']];
		unset($channelInfo);
		$vbulletin->GPC['options']['allowsmilies'] = $channel['options']['allowsmilies'];

		foreach ($vbulletin->GPC['options'] AS $bitname => $value)
		{
			$feeddata->set_bitfield('options', $bitname, $value);
		}

		if ($feeddata->has_errors(false))
		{
			$feed = array();
			foreach ($input_vars AS $varname => $foo)
			{
				$feed["$varname"] = $vbulletin->GPC["$varname"];
			}

			foreach ($feeddata->errors AS $error)
			{
				echo "<div>$error</div>";
			}

			define('FEED_SAVE_ERROR', true);
			$_REQUEST['do'] = 'edit';
		}
		else
		{
			$feeddata->save();
			print_stop_message2(array('saved_rssfeed_x_successfully',  $feeddata->fetch_field('title')), 'rssposter');
		}
	}
	else
	{
		$_POST['do'] = 'preview';
	}
}

// #############################################################################

if ($_POST['do'] == 'preview')
{
	require_once(DIR . '/includes/class_rss_poster.php');
	require_once(DIR . '/includes/functions_wysiwyg.php');

	$xml = new vB_RSS_Poster($vbulletin);
	$xml->fetch_xml($vbulletin->GPC['url']);

	if (empty($xml->xml_string))
	{
		print_stop_message2('unable_to_open_url');
	}
	else if ($xml->parse_xml() === false)
	{
		print_stop_message2(array(
			'xml_error_x_at_line_y',
			($xml->feedtype == 'unknown' ? 'Unknown Feed Type' : $xml->xml_object->error_string()),
			$xml->xml_object->error_line()
		));
	}

	require_once(DIR . '/includes/class_bbcode.php');
	$bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());

	$output = '';
	$count = 0;

	$bbcodeApi = vB_Api::instanceInternal('bbcode');
	$bbcodeLibrary = vB_Library::instance('bbcode');
	foreach ($xml->fetch_items() AS $item)
	{
		if ($vbulletin->GPC['maxresults'] AND $count++ >= $vbulletin->GPC['maxresults'])
		{
			break;
		}
		if (!empty($item['content:encoded']))
		{
			$content_encoded = true;
		}

		$title = $bbcode_parser->parse(strip_bbcode(convert_wysiwyg_html_to_bbcode($xml->parse_template($vbulletin->GPC['titletemplate'], $item))), 0, false);
		$body = $xml->parse_template($vbulletin->GPC['bodytemplate'], $item);

		$dobbcode = false;
		if ($vbulletin->GPC['options']['html2bbcode'])
		{
			$dobbcode = true;
			$body = nl2br($body);
			$body = $bbcodeApi->convertWysiwygTextToBbcode($body, array('autoparselinks' => 1));
		}

		$body = $bbcodeLibrary->doParse($body, true, false, $dobbcode);
		$output .= '<div class="alt2" style="border:inset 1px; padding:5px; width:400px; height: 175px; margin:10px; overflow: auto;"><h3><em>' . $title . '</em></h3>' . $body . '</div>';
	}

	$feed = array();
	foreach ($input_vars AS $varname => $foo)
	{
		$feed["$varname"] = $vbulletin->GPC["$varname"];
	}

	define('FEED_SAVE_ERROR', true);
	$_REQUEST['do'] = 'edit';

	print_form_header('admincp/', '');
	print_table_header($vbphrase['preview_feed']);
	if ($content_encoded)
	{
		print_description_row($vbphrase['feed_supports_content_encoded']);
	}
	print_description_row($output);
	print_table_footer();
}

// #############################################################################

if ($_REQUEST['do'] == 'edit')
{
	$vbulletin->input->clean_array_gpc('r', array('rssfeedid' => vB_Cleaner::TYPE_UINT));

	if (defined('FEED_SAVE_ERROR') AND is_array($feed))
	{
		// save error, show stuff again
		$form_title = ($feed['rssfeedid'] ? $vbphrase['edit_rss_feed'] : $vbphrase['add_new_rss_feed']);
	}
	else if ($vbulletin->GPC['rssfeedid'] AND $feed = $assertor->getRow('vBForum:getUserRssFeed', array('rssfeedid' => $vbulletin->GPC['rssfeedid'])))
	{
		// feed is defined
		$form_title = $vbphrase['edit_rss_feed'];
	}
	else
	{
		// add new feed
		$feed = array(
			'options'         => 1025,
			'ttl'             => 1800,
			'maxresults'      => 0,
			'endannouncement' => 7,
			'titletemplate'   => $vbphrase['rssfeed_title_template'],
			'bodytemplate'    => $vbphrase['rssfeed_body_template'],
			'itemtype'        => 'topic'
		);
		$form_title = $vbphrase['add_new_rss_feed'];
	}

	$checked = array();

	if (!defined('FEED_SAVE_ERROR') AND !is_array($feed['options']))
	{
		$feed['options'] = convert_bits_to_array($feed['options'], $vbulletin->bf_misc_feedoptions);
	}

	foreach ($feed['options'] AS $bitname => $bitvalue)
	{
		$checked["$bitname"] = ($bitvalue ? ' checked="checked"' : '');
	}

	$checked['itemtype']["$feed[itemtype]"] = ' checked="checked"';

	print_form_header('admincp/rssposter', 'update');
	print_table_header($form_title);
	if ($feed['rssfeedid'])
	{
		print_checkbox_row($vbphrase['reset_last_checked_time'], 'resetlastrun', 0, 1, $vbphrase['reset']);
	}
	print_yes_no_row($vbphrase['feed_is_enabled'], 'options[enabled]', $feed['options']['enabled']);
	print_input_row($vbphrase['title'], 'title', $feed['title'], false, 50);
	print_input_row($vbphrase['url_of_feed'], 'url', $feed['url'], true, 50);
	print_select_row($vbphrase['check_feed_every'], 'ttl', array(
		600  => construct_phrase($vbphrase['x_minutes'], 10),
		1200 => construct_phrase($vbphrase['x_minutes'], 20),
		1800 => construct_phrase($vbphrase['x_minutes'], 30),
		3600 => construct_phrase($vbphrase['x_minutes'], 60),
		7200 => construct_phrase($vbphrase['x_hours_gcron'], 2),
	  14400 => construct_phrase($vbphrase['x_hours_gcron'], 4),
	  21600 => construct_phrase($vbphrase['x_hours_gcron'], 6),
	  28800 => construct_phrase($vbphrase['x_hours_gcron'], 8),
	  36000 => construct_phrase($vbphrase['x_hours_gcron'], 10),
	  43200 => construct_phrase($vbphrase['x_hours_gcron'], 12),
	), $feed['ttl']);
	print_input_row($vbphrase['maximum_items_to_fetch'], 'maxresults', $feed['maxresults'], true, 50);
	print_label_row($vbphrase['search_items_for_words'],'
		<div><textarea name="searchwords" rows="5" cols="50" tabindex="1">' . $feed['searchwords'] . '</textarea></div>
		<input type="hidden" name="options[searchboth]" value="0" />
		<input type="hidden" name="options[matchall]" value="0" />
		<div class="smallfont">
			<label for="cb_searchboth"><input type="checkbox" name="options[searchboth]" id="cb_searchboth" value="1" tabindex="1"' . $checked['searchboth'] . ' />' . $vbphrase['search_item_body'] . '</label>
			<label for="cb_matchall"><input type="checkbox" name="options[matchall]" id="cb_matchall" value="1" tabindex="1"' . $checked['matchall'] . ' />' . $vbphrase['match_all_words_gcron'] . '</label>
		</div>
	', '', 'top', 'searchwords');
	print_input_row($vbphrase['username'], 'username', $feed['username'], false, 50);
	print_channel_chooser($vbphrase['channel'], 'nodeid', $feed['nodeid'], null, true, false, '[%s]');
	print_yes_no_row($vbphrase['convert_html_to_bbcode'], 'options[html2bbcode]', $feed['options']['html2bbcode']);

	print_table_header($vbphrase['templates']);
	print_description_row('<div class="smallfont">' . $vbphrase['rss_templates_description'] . '</div>');
	print_input_row($vbphrase['title_template'], 'titletemplate', $feed['titletemplate'], true, 50);
	print_textarea_row($vbphrase['body_template'], 'bodytemplate', $feed['bodytemplate'], 10, 50);

	print_description_row('<label for="rb_itemtype_thread"><input type="radio" name="itemtype" value="topic" id="rb_itemtype_thread"' . $checked['itemtype']['topic'] . "  />$vbphrase[post_items_as_threads]</label>", false, 2, 'thead', 'left', 'itemtype');
	if ($prefix_options = construct_prefix_options(0, $feed['prefixid']))
	{
		print_label_row(
			$vbphrase['prefix'] . '<dfn>' . $vbphrase['note_prefix_must_allowed_forum'] . '</dfn>',
			'<select name="prefixid" class="bginput">' . $prefix_options . '</select>',
			'', 'top', 'prefixid'
		);
	}

	// build thread icon picker
	$icons = array();

	$icons_result = vB_Api::instanceInternal('icon')->fetchAll(array('imagecategoryid', 'displayorder'));
	$icons_total = count($icons_result);
	foreach ($icons_result AS $icon)
	{
		$icons[] = $icon;
	}

	$icon_count = 0;
	$icon_cols = 7;
	$icon_rows = ceil($icons_total / $icon_cols);

	// build icon html
	$icon_html = "<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\" width=\"100%\">";
	$corepath = vB::getDatastore()->getOption('bburl');

	for ($i = 0; $i < $icon_rows; $i++)
	{
		$icon_html .= "<tr>";

		for ($j = 0; $j < $icon_cols; $j++)
		{
			if ($icons["$icon_count"])
			{
				$icon =& $icons["$icon_count"];
				if (strtolower(substr($icon['iconpath'], 0, 4)) != 'http' AND substr($icon['iconpath'], 0, 1) != '/')
				{
					$icon['iconpath'] = $corepath . '/' . $icon['iconpath'];
				}
				$icon_html .= "<td class=\"smallfont\"><label for=\"rb_icon_$icon[iconid]\" title=\"$icon[title]\"><input type=\"radio\" name=\"iconid\" value=\"$icon[iconid]\" tabindex=\"1\" id=\"rb_icon_$icon[iconid]\"" . ($feed['iconid'] == $icon['iconid'] ? ' checked="checked"' : '') . " /><img src=\"$icon[iconpath]\" alt=\"$icon[title]\" /></label></td>";
				$icon_count++;
			}
			else
			{
				$remaining_cols = $icon_cols - $j;
				$icon_html .= "<td class=\"smallfont\" colspan=\"$remaining_cols\">&nbsp;</td>";
				break;
			}
		}

		$icon_html .= '</tr>';
	}
	$icon_html .= "<tr><td colspan=\"$icon_cols\" class=\"smallfont\"><label for=\"rb_icon_0\" title=\"$icon[title]\"><input type=\"radio\" name=\"iconid\" value=\"0\" tabindex=\"1\" id=\"rb_icon_0\"" . ($feed['iconid'] == 0 ? ' checked="checked"' : '') . " />$vbphrase[no_icon]</label></td></tr></table>";

	print_label_row($vbphrase['post_icons'], $icon_html, '', 'top', 'iconid');
	print_yes_no_row($vbphrase['make_thread_sticky'], 'options[stickthread]', $feed['options']['stickthread']);
	print_yes_no_row($vbphrase['moderate_thread'], 'options[moderatethread]', $feed['options']['moderatethread']);
	print_input_row($vbphrase['thread_action_delay'], 'topicactiondelay', $feed['topicactiondelay']);
	print_yes_no_row($vbphrase['unstick_thread_after_delay'], 'options[unstickthread]', $feed['options']['unstickthread']);
	print_yes_no_row($vbphrase['close_thread_after_delay'], 'options[closethread]', $feed['options']['closethread']);
	print_description_row('<label for="rb_itemtype_announcement"><input type="radio" name="itemtype" value="announcement" id="rb_itemtype_announcement"' . $checked['itemtype']['announcement'] . "  />$vbphrase[post_items_as_announcements]</label>", false, 2, 'thead', 'left', 'itemtype');
	print_yes_no_row($vbphrase['allow_html_in_announcements'], 'options[allowhtml]', $feed['options']['allowhtml']);
	print_input_row($vbphrase['days_for_announcement_to_remain_active'], 'endannouncement', $feed['endannouncement']);
	construct_hidden_code('rssfeedid', $feed['rssfeedid']);
	print_submit_row('', $vbphrase['reset'], 2, '', "<input type=\"submit\" class=\"button\" name=\"preview\" tabindex=\"1\" accesskey=\"p\" value=\"$vbphrase[preview_feed]\" />");
}

if ($_REQUEST['do'] == 'modify')
{
	$feeds = array();

	$feeds_result = $assertor->getRows('vBForum:getRssFeedsDetailed');
	if (count($feeds_result))
	{
		foreach ($feeds_result AS $feed)
		{
			$feeds["$feed[rssfeedid]"] = $feed;
		}
	}

	if (empty($feeds))
	{
		print_stop_message2(array('no_feeds_defined',  vB::getCurrentSession()->get('sessionurl')));
	}
	else
	{
?>
	<script type="text/javascript">
		$(document).ready(function() {
			function verifyAllChecked() {
				// Search if any check boxes are already checked.
				if ($('.rssenabled:checked').length == $('.rssenabled').length)
				{
					$('[name="allbox"]').prop('checked', true);
				}
				else
				{
					$('[name="allbox"]').prop('checked', false);
				}
			}

			$('.rssenabled').click(verifyAllChecked);

			verifyAllChecked();
		});
	</script>
<?php
		print_form_header('admincp/rssposter', 'updatestatus');
		print_table_header($vbphrase['rss_feed_manager'], 5);
		print_cells_row(array(
			'<input type="checkbox" name="allbox" title="' . $vbphrase['check_all'] . '" onclick="js_check_all(this.form);" />',
			$vbphrase['rss_feed_gcron'],
			$vbphrase['forum'] . ' / ' . $vbphrase['username'],
			$vbphrase['last_checked'],
			$vbphrase['controls']
		), true, '', -4);

		foreach ($feeds AS $rssfeedid => $feed)
		{
			$x = @vB_String::parseUrl($feed['url']);

			if ($feed['lastrun'] > 0)
			{
				$date = vbdate($vbulletin->options['dateformat'], $feed['lastrun'], true);
				$time = vbdate($vbulletin->options['timeformat'], $feed['lastrun']);
				$datestring = $date . ($vbulletin->options['yestoday'] == 2 ? '' : ", $time");
			}
			else
			{
				$datestring = '-';
			}

			print_cells_row(array(
				"<input type=\"checkbox\" class=\"rssenabled\" name=\"enabled[$rssfeedid]\" value=\"$rssfeedid\" title=\"$vbphrase[enabled]\"" . ($feed['options'] & $vbulletin->bf_misc_feedoptions['enabled'] ? ' checked="checked"' : '') . " />",
				"<div><a href=\"admincp/rssposter.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;rssfeedid=$feed[rssfeedid]\" title=\"" . htmlspecialchars_uni($feed['url']) . "\"><strong>$feed[title]</strong></a></div>
				<div class=\"smallfont\"><a href=\"" . htmlspecialchars_uni($feed['url']) . "\" target=\"feed\">$x[host]</a></div>",
				"<div><a href=\"admincp/forum.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;nodeid=$feed[nodeid]\">$feed[channeltitle]</a></div>
				<div class=\"smallfont\"><a href=\"admincp/user.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;userid=$feed[userid]\">$feed[username]</a></div>",
				"<span class=\"smallfont\">$datestring</span>",
				construct_link_code($vbphrase['edit'], "rssposter.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;rssfeedid=$feed[rssfeedid]") .
				construct_link_code($vbphrase['delete'], "rssposter.php?" . vB::getCurrentSession()->get('sessionurl') . "do=delete&amp;rssfeedid=$feed[rssfeedid]")
			), false, '', -4);
		}

		if (vB::getUserContext()->hasAdminPermission('canadmincron'))
		{
			$runNow = "<input type=\"button\" class=\"button\" value=\"$vbphrase[run_scheduled_task_now]\" onclick=\"vBRedirect('admincp/cronadmin.php?" . vB::getCurrentSession()->get('sessionurl') . "do=runcron&amp;varname=rssposter');\" />";		}
		else
		{
			$runNow = '';
		}
		print_submit_row(
			$vbphrase['save_enabled_status'], false, 5, '',
			$runNow . "
				<input type=\"button\" class=\"button\" value=\"$vbphrase[add_new_rss_feed]\" onclick=\"vBRedirect('admincp/rssposter.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit');\" />
			");
	}
}

print_cp_footer();

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
