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
@set_time_limit(0);
ignore_user_abort(true);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision: 99787 $');

// ################### DEFINE LOCAL SCRIPT CONSTANTS ######################

// #################### PRE-CACHE TEMPLATES AND DATA ######################
global $phrasegroups, $specialtemplates, $vbphrase;
$phrasegroups = array('tagscategories');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once(dirname(__FILE__) . '/global.php');
require_once DIR . "/includes/functions.php";
require_once(DIR . '/includes/class_taggablecontent.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadmintags'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################


if (empty($_REQUEST['do']))
{
	$action = 'modify';
}
else
{
	$action = $_REQUEST['do'];
}

//I'm not sure how much we need this, but the old branch logic checks some
//actions against REQUEST and some against POST. This should maintain
//equivilent behavior (error instead of explicit fallthrough;
$post_only_actions = array('taginsert', 'tagclear', 'tagkill', 'tagmerge', 'tagdomerge');
if (in_array($action, $post_only_actions) AND empty($_POST['do']))
{
	exit;
}

$dispatch = array
(
	'taginsert' => 'taginsert',
	'tagclear' => 'tagclear',
	'tagmerge' => 'tagmerge',
	'tagdomerge' => 'tagdomerge',
	'tagdopromote' => 'tagdopromote',
	'tagkill' => 'tagkill',
	'tags' => 'displaytags', //legacy from when this was part of threads
	'modify' => 'displaytags',
);

global $stop_file, $stop_args;

$stop_file = '';
$stop_args = array();
if (array_key_exists($action, $dispatch))
{
	// these three actions need to set cookies, and will print the cp header themselves.
	if (!in_array($action, array('tagclear', 'tagdomerge', 'tagkill')))
	{
		print_cp_header($vbphrase['tag_manager']);
	}
	tagcp_init_tag_action();
	call_user_func($dispatch["$action"]);
	print_cp_footer();
}


// ########################################################################
// some utility function for the actions
function tagcp_init_tag_action()
{
	global $vbulletin, $stop_file, $stop_args;

	$vbulletin->input->clean_array_gpc('r', array(
		'pagenumber' => vB_Cleaner::TYPE_UINT,
		'sort'       => vB_Cleaner::TYPE_NOHTML
	));

	$stop_file = 'tag';
	$stop_args = array(
		'do' => 'tags',
		'page' => $vbulletin->GPC['pagenumber'],
		'sort' => $vbulletin->GPC['sort']
	);
}


function tagcp_fetch_tag_list()
{
	global $vbulletin;

	$vbulletin->input->clean_array_gpc('p', array(
		'tag' => vB_Cleaner::TYPE_ARRAY_KEYS_INT
	));

	$vbulletin->input->clean_array_gpc('c', array(
		'vbulletin_inlinetag' => vB_Cleaner::TYPE_STR,
	));

	$taglist = $vbulletin->GPC['tag'];

	if (!empty($vbulletin->GPC['vbulletin_inlinetag']))
	{
		$cookielist = explode('-', $vbulletin->GPC['vbulletin_inlinetag']);
		$cookielist = $vbulletin->cleaner->clean($cookielist, vB_Cleaner::TYPE_ARRAY_UINT);

		$taglist = array_unique(array_merge($taglist, $cookielist));
	}

	return $taglist;
}


// ########################################################################
// handled inserting a form
function taginsert()
{
	global $vbulletin, $vphrase, $stop_file, $stop_args;

	$vbulletin->input->clean_array_gpc('p', array('tagtext' => vB_Cleaner::TYPE_NOHTML));

	$response = vB_Api::instance('Tags')->insertTags($vbulletin->GPC['tagtext']);
	if ($response['errors'])
	{
		print_stop_message2($response['errors'][0]);
	}
	else
	{
		print_stop_message2('tag_saved', $stop_file, $stop_args);
	}
}

// ########################################################################
// clear the tag selection cookie
function tagclear()
{
	global $vbphrase;

	setcookie('vbulletin_inlinetag', '', TIMENOW - 3600, '/');

	print_cp_header($vbphrase['tag_manager']);
	displaytags();
}

// ########################################################################

function tagmerge()
{
	global $vbulletin, $vbphrase, $stop_file, $stop_args;

	tagcp_init_tag_action();
	$taglist = tagcp_fetch_tag_list();
	if (!sizeof($taglist))
	{
		print_stop_message2('no_tags_selected', $stop_file, $stop_args);
	}

	$tags = vB::getDbAssertor()->getRows('vBForum:tag',
		array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT, 'tagid' => $taglist),
		array('field' => 'tagtext', 'direction' => vB_dB_Query::SORT_ASC)
	);

	if (!$tags)
	{
		print_stop_message2('no_tags_selected', $stop_file, $stop_args);
	}

	print_form_header('admincp/tag', 'tagdomerge');
	$columns = array('','','');
	$counter = 0;
	foreach ($tags AS $tag)
	{
		$id = $tag['tagid'];
		$text = $tag['tagtext'];
		$column = floor($counter++ / ceil(count($tags) / 3));
		$columns[$column] .= '<label for="taglist_' . $id . '"><input type="checkbox" name="tag[' . $id . ']" id="taglist_' . $id . '" value="' . $id . '" tabindex="' . $column . '" checked="checked" /> ' . $text . '</label><br/>';
	}

	print_description_row($vbphrase['tag_merge_description'], false, 3, '', vB_Template_Runtime::fetchStyleVar('left'));
	print_cells_row($columns, false, false, -3);
	construct_hidden_code('page', $vbulletin->GPC['pagenumber']);
	construct_hidden_code('sort', $vbulletin->GPC['sort']);

	print_input_row($vbphrase['new_tag'], 'tagtext', '', true, 35, 0, '', false, false, array(1,2));
	print_submit_row($vbphrase['merge_tags'], false, 3, $vbphrase['go_back']);
}


// ########################################################################
function tagdomerge()
{
	global $vbulletin, $vbphrase, $stop_file, $stop_args;

	$taglist = tagcp_fetch_tag_list();
	if (!sizeof($taglist))
	{
		print_cp_header($vbphrase['tag_manager']);
		print_stop_message2('no_tags_selected', $stop_file, $stop_args);
	}

	$vbulletin->input->clean_array_gpc('p', array(
		'tagtext' => vB_Cleaner::TYPE_NOHTML
	));

	$tagtext = $vbulletin->GPC['tagtext'];

	$name_changed = false;
	$tagExists = vB_Api::instance('Tags')->fetchTagByText($tagtext);
	if (!$tagExists['tag'])
	{
		//Create tag
		$response = vB_Api::instance('Tags')->insertTags($tagtext);
		if ($response['errors'])
		{
			print_cp_header($vbphrase['tag_manager']);
			print_stop_message2($response['errors'][0]);
		}
	}
	else
	{
		//if the old tag and new differ only by case, then update
		if ($tagtext != $tagExists['tag']['tagtext'] AND vbstrtolower($tagtext) == vbstrtolower($tagExists['tag']['tagtext']))
		{
			$name_changed = true;
			$update = vB_Api::instance('Tags')->updateTags($tagtext);
		}
	}

	$tagExists = vB_Api::instance('Tags')->fetchTagByText($tagtext);
	if (!$tagExists['tag'])
	{
		print_cp_header($vbphrase['tag_manager']);
		print_stop_message2('no_changes_made', $stop_file, $stop_args);
	}
	else
	{
		$targetid = $tagExists['tag']['tagid'];
	}

	// check if source and targed are the same
	if (sizeof($taglist) == 1 AND in_array($targetid, $taglist))
	{
		if ($name_changed)
		{
			print_cp_header($vbphrase['tag_manager']);
			print_stop_message2('tags_edited_successfully', $stop_file, $stop_args);
		}
		else
		{
			print_cp_header($vbphrase['tag_manager']);
		 	print_stop_message2('no_changes_made', $stop_file, $stop_args);
		}
	}

	if (false !== ($selected = array_search($targetid, $taglist)))
	{
		// ensure targetid is not in taglist
		unset($taglist[$selected]);
	}

	$synonym = vB_Api::instance('Tags')->createSynonyms($taglist, $targetid);
	if ($synonym['errors'])
	{
		print_stop_message2($synonym['errors'][0]);
	}

	// need to invalidate the search and tag cloud caches
	build_datastore('tagcloud', '', 1);
	build_datastore('searchcloud', '', 1);

	setcookie('vbulletin_inlinetag', '', TIMENOW - 3600, '/');
	print_cp_header($vbphrase['tag_manager']);
	print_stop_message2('tags_edited_successfully', $stop_file, $stop_args);
}

// ########################################################################
function tagdopromote()
{
	global $vbulletin, $vbphrase, $stop_file, $stop_args;

	$taglist = tagcp_fetch_tag_list();
	if (!sizeof($taglist))
	{
		print_stop_message2('no_tags_selected', $stop_file, $stop_args);
	}
	$promote = vB_Api::instance('Tags')->promoteTags($taglist);
	if ($promote['errors'])
	{
		print_cp_header($vbphrase['tag_manager']);
		print_stop_message2($promote['errors'][0]);
	}
	else
	{
		print_stop_message2('tags_edited_successfully', $stop_file, $stop_args);
	}
}

// ########################################################################

function tagkill()
{
	global $vbulletin, $vbphrase, $stop_file, $stop_args;

	$taglist = tagcp_fetch_tag_list();
	if (sizeof($taglist))
	{
		$kill = vB_Api::instance('Tags')->killTags($taglist);
		if ($kill['errors'])
		{
			print_cp_header($vbphrase['tag_manager']);
			print_stop_message2($promote['errors'][0], $stop_file, $stop_args);
		}

		// need to invalidate the search and tag cloud caches
		build_datastore('tagcloud', '', 1);
		build_datastore('searchcloud', '', 1);
	}

	setcookie('vbulletin_inlinetag', '', TIMENOW - 3600, '/');
	print_cp_header($vbphrase['tag_manager']);
	print_stop_message2('tags_deleted_successfully', $stop_file, $stop_args);
}


// ########################################################################

function displaytags()
{
	global $vbulletin, $vbphrase;
	$assertor = vB::getDbAssertor();
	$datastore = vB::getDatastore();
	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}

	$synonyms_in_list = $vbulletin->GPC['sort'] == 'alphaall' ? true : false ;
	$column_count = 3;
	$max_per_column = 15;
	$perpage = $column_count * $max_per_column;

	$query = array(
		vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_COUNT,
	);

	//alphaall is the only "sort" that shows the synonyms in the list.
	if (!$synonyms_in_list)
	{
		$query['canonicaltagid'] = 0;
	}

	$tag_counts = $assertor->getRow('vBForum:tag', $query);
	$tag_count  = $tag_counts['count'];

	$start = ($vbulletin->GPC['pagenumber'] - 1) * $perpage;
	if ($start >= $tag_count)
	{
		$start = max(0, $tag_count - $perpage);
	}

	$query[vB_dB_Query::TYPE_KEY] = vB_dB_Query::QUERY_SELECT;
	$query[vB_dB_Query::PARAM_LIMIT] = $perpage;
	$query[vB_dB_Query::PARAM_LIMITSTART] = $start;

	$order = array('field' => 'tagtext', 'direction' => vB_dB_Query::SORT_ASC);
	if ($vbulletin->GPC['sort'] == 'dateline')
	{
		$order = array('field' => 'dateline', 'direction' => vB_dB_Query::SORT_DESC);
	}

	$tags = $assertor->assertQuery('vBForum:tag', $query, $order);

	print_form_header('admincp/tag', '', false, true, 'tagsform');
	print_table_header($vbphrase['tag_list'], 3);
	if ($tags AND $tags->valid())
	{
		$columns = array();
		$counter = 0;

		// build page navigation
		$pagenav = tagcp_build_page_nav($vbulletin->GPC['pagenumber'], ceil($tag_count / $perpage), $vbulletin->GPC['sort']);
		$sort_links[''] =  '<a href="admincp/tag.php?do=tags">' . $vbphrase['display_alphabetically'] . '</a>';
		$sort_links['dateline'] = '<a href="admincp/tag.php?do=tags&amp;sort=dateline">' . $vbphrase['display_newest'] . '</a>';
		$sort_links['alphaall'] = '<a href="admincp/tag.php?do=tags&amp;sort=alphaall">' . $vbphrase['display_alphabetically_all'] . '</a>';

		//dont show the current sort
		unset($sort_links[$vbulletin->GPC['sort']]);

		print_description_row(
			"<div style=\"float: " . vB_Template_Runtime::fetchStyleVar('left') . "\">" . implode("&nbsp;&nbsp;" , $sort_links) . "</div>$pagenav",
			false, 3, 'thead', 'right'
		);
		// build columns
		foreach ($tags AS $tag)
		{
			$columnid = floor($counter++ / $max_per_column);
			$columns["$columnid"][] = tagcp_format_tag_entry($tag, $synonyms_in_list);

		}
		// make column values printable
		$cells = array();
		for ($i = 0; $i < $column_count; $i++)
		{
			if ($columns["$i"])
			{
				$cells[] = implode("<br />\n", $columns["$i"]);
			}
			else
			{
				$cells[] = '&nbsp;';
			}
		}

		print_column_style_code(array(
			'width: 33%',
			'width: 33%',
			'width: 34%'
		));
		print_cells_row($cells, false, false, -3);

		?>
		<tr>
			<td colspan="<?php echo $column_count; ?>" align="center" class="tfoot">
				<select id="select_tags" name="do">
					<option value="tagmerge" id="select_tags_merge"><?php echo $vbphrase['merge_selected_synonym']; ?></option>
					<option value="tagdopromote" id="select_tags_delete"><?php echo $vbphrase['promote_synonyms_selected']; ?></option>
					<option value="tagkill" id="select_tags_delete"><?php echo $vbphrase['delete_selected']; ?></option>
					<optgroup label="____________________">
						<option value="tagclear"><?php echo $vbphrase['deselect_all_tags']; ?></option>
					</optgroup>
				</select>
				<input type="hidden" name="page" value="<?php echo $vbulletin->GPC['pagenumber']; ?>" />
				<input type="hidden" name="sort" value="<?php echo $vbulletin->GPC['sort']; ?>" />
				<input type="submit" value="<?php echo $vbphrase['go']; ?>" id="tag_inlinego" class="button" />
			</td>
		</tr>
		<?php echo '</table>';?>

		<script type="text/javascript" src="core/clientscript/vbulletin_inlinemod.js?v=<?php echo $datastore->getOption('simpleversion'); ?>"></script>
		<script type="text/javascript">
			<!--
			inlineMod_tags = new vB_Inline_Mod('inlineMod_tags', 'tag', 'tagsform', '<?php echo $vbphrase['go_x']; ?>', 'vbulletin_inline', 'tag');
			/* vBmenu.register("inlinemodsel"); */
			//-->

			function js_show_synlist(trigger, listid)
			{
				list = document.getElementById(listid);
				list.style.display = 'block';
				trigger.onclick = function() {return js_hide_synlist(trigger, listid)};
				trigger.getElementsByTagName('img')[0].src = '<?php echo get_cpstyle_href('collapse_generic.gif'); ?>';
				return false;
			}

			function js_hide_synlist(trigger, listid)
			{
				list = document.getElementById(listid);
				list.style.display = 'none';
				trigger.onclick = function() {return js_show_synlist(trigger, listid)};
				trigger.getElementsByTagName('img')[0].src = '<?php echo get_cpstyle_href('collapse_generic_collapsed.gif'); ?>';
				return false;
			}
		</script>
	<?php
		echo '</form>';
	}
	else
	{
		print_description_row($vbphrase['no_tags_defined'], false, 3, '', 'center');
		print_table_footer();
	}

	construct_hidden_code('page', $vbulletin->GPC['pagenumber']);
	construct_hidden_code('sort', $vbulletin->GPC['sort']);

	print_form_header('admincp/tag', 'taginsert');
	print_input_row($vbphrase['add_tag'], 'tagtext');
	print_submit_row();
}

function format_tag_list_item($id, $text)
{
	return '<label for="taglist_' . $id . '"><input type="checkbox" ' .
		'name="tag[' . $id . ']" id="taglist_' . $id . '" ' .
		'value="1" tabindex="1" /> ' . $text . '</label>';
}

function tagcp_build_page_nav($page, $total_pages, $sort)
{
	global $vbphrase, $session;

	if ($total_pages > 1)
	{
		$pagenav = '<strong>' . $vbphrase['go_to_page'] . '</strong>';
		for ($thispage = 1; $thispage <= $total_pages; $thispage++)
		{
			if ($page == $thispage)
			{
				$pagenav .= " <strong>[$thispage]</strong> ";
			}
			else
			{
				$pagenav .= " <a href=\"admincp/tag.php?$session[sessionurl]do=tags&amp;page=$thispage&amp;sort=$sort\" class=\"normal\">$thispage</a> ";
			}
		}

	}
	else
	{
		$pagenav = '';
	}
	return $pagenav;
}

function tagcp_format_tag_entry($tag, $synonyms_in_list)
{
	global $vbulletin;

	if (!$synonyms_in_list)
	{
		$label = $tag['tagtext'];
		$synonyms = vB_Api::instance('Tags')->getTagSynonyms($tag['tagid']);
		if (empty($synonyms['errors']) AND count($synonyms['tags']))
		{
			$list_id = 'synlist_' . $tag['tagid'];
			$synonym_list = '<span class="cbsubgroup-trigger" onclick="return js_show_synlist(this, \'' . $list_id . '\')">' .
				'<img src="' .  get_cpstyle_href('collapse_generic_collapsed.gif')  . '" />'.
				'</span>';

			$synonym_list .= '<ul class="cbsubgroup" id="' . $list_id . '" style="display:none">';
			foreach ($synonyms['tags'] AS $tagid => $tagtext)
			{
				$synonym_list .= '<li>' . format_tag_list_item($tagid, $tagtext) . '</li>';
			}
			$synonym_list .= '</ul>';
		}
	}
	else
	{
		if($tag['canonicaltagid'])
		{
			$canonical = vB_Api::instance('Tags')->getTags($tag['canonicaltagid']);
			$canonical = $canonical['tags'][$tag['canonicaltagid']];

			$label = '<i>' . $tag['tagtext'] . '</i> (' . $canonical['tagtext'] . ')';
		}
		else
		{
			$label = $tag['tagtext'];
		}


		$synonym_list = '';
	}

	$tag_item_text = format_tag_list_item($tag['tagid'], $label);

	return '<div id="tag' . $tag['tagid'] . '" class="alt1" style="float:' .
		vB_Template_Runtime::fetchStyleVar('left') . ';clear:' . vB_Template_Runtime::fetchStyleVar('left') . '">' . "\n" .
		$tag_item_text . "\n" . $synonym_list . "\n" .
	'</div>';
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
