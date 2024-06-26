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
global $phrasegroups, $specialtemplates, $vbphrase, $vbulletin;
$phrasegroups = array('logging', 'threadmanage');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once(dirname(__FILE__) . '/global.php');
require_once(DIR . '/includes/functions_log_error.php');

// ############################# LOG ACTION ###############################
if (!can_administer('canadminmodlog'))
{
	print_cp_no_permission();
}

log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$vb5_config =& vB::getConfig();
print_cp_header($vbphrase['moderator_log_gthreadmanage']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'choose';
}

// ###################### Start view #######################
if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'    => vB_Cleaner::TYPE_UINT,
		'pagenumber' => vB_Cleaner::TYPE_UINT,
		'userid'     => vB_Cleaner::TYPE_UINT,
		'modaction'  => vB_Cleaner::TYPE_STR,
		'orderby'    => vB_Cleaner::TYPE_NOHTML,
		'product'    => vB_Cleaner::TYPE_STR,
		'startdate'  => vB_Cleaner::TYPE_UNIXTIME,
		'enddate'    => vB_Cleaner::TYPE_UNIXTIME,
	));

	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}

	$counterres = vB::getDbAssertor()->assertQuery('fetchModlogCount',$vbulletin->GPC);
	$counter = $counterres->current();
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}

	$logs = vB::getDbAssertor()->assertQuery('fetchModlogs', $vbulletin->GPC);

	if ($logs AND $logs->valid())
	{
		$baseUrl = 'admincp/modlog.php?';
		$query = array(
			'do' => 'view',
			'modaction' => $vbulletin->GPC['modaction'],
			'u' => $vbulletin->GPC['userid'],
			'pp' => $vbulletin->GPC['perpage'],
			'orderby' => $vbulletin->GPC['orderby'],
			'page' => $vbulletin->GPC['pagenumber'],
			'startdate' => $vbulletin->GPC['startdate'],
			'enddate' => $vbulletin->GPC['enddate'],
		);

		print_form_header('admincp/modlog', 'remove');
		print_description_row(construct_link_code($vbphrase['restart'], 'modlog.php'), 0, 5, 'thead', vB_Template_Runtime::fetchStyleVar('right'));
		print_table_header(construct_phrase($vbphrase['moderator_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);

		$headings = array();
		$headings[] = $vbphrase['id'];

		$query['orderby'] = 'user';
		$url = htmlspecialchars_uni($baseUrl . http_build_query($query));
		$headings[] = "<a href=\"$url\">" . str_replace(' ', '&nbsp;', $vbphrase['username']) . "</a>";

		$query['orderby'] = 'date';
		$url = htmlspecialchars_uni($baseUrl . http_build_query($query));
		$headings[] = "<a href=\"$url\">" . $vbphrase['date'] . "</a>";

		$query['orderby'] = 'modaction';
		$url = htmlspecialchars_uni($baseUrl . http_build_query($query));
		$headings[] = "<a href=\"$url\">" . $vbphrase['action'] . "</a>";

		$headings[] = str_replace(' ', '&nbsp;', $vbphrase['ip_address']);
		$query['orderby'] = $vbulletin->GPC['orderby'];
		print_cells_row($headings, 1, 0, -3);

		foreach ($logs as $log)
		{
			$cell = array();
			$cell[] = $log['moderatorlogid'];
			$cell[] = "<a href=\"admincp/user.php?do=edit&u=$log[userid]\"><b>$log[username]</b></a>";
			$cell[] = '<span class="smallfont">' . vbdate($vbulletin->options['logdateformat'], $log['dateline']) . '</span>';

			if ($log['type'])
			{
				$phrase = vB_Library_Admin::GetModlogAction($log['type']);

				if (!$log['nodeid'])
				{
					// Pre vB5 logs
					if ($unserialized = @unserialize($log['action']))
					{
						array_unshift($unserialized, $vbphrase[$phrase]);
						$action = call_user_func_array('construct_phrase', $unserialized);
					}
					else
					{
						$action = construct_phrase($vbphrase[$phrase], $log['action']);
					}

					if ($log['threadtitle'])
					{
						$action .= ', \'' . $log['threadtitle'] . '\'';
					}
				}
				else
				{
					// vB5 logs
					$temp = array();
					$logdata = @unserialize($log['action']);
					$action = construct_phrase($vbphrase[$phrase], $log['username']);

					if ($logdata['userid'] AND $logdata['username'])
					{
						$name = '<a href="admincp/user.php?do=edit&u=' . $logdata['userid'] . '">' . $logdata['username'] . '</a>';
						$temp[] = $vbphrase['author'] . ' = ' . $name;
						unset($logdata['userid'], $logdata['username']);
					}

					$logdata['nodeid'] = $log['nodeid'];
					$title = $log['nodetitle'] ? $log['nodetitle'] : $vbphrase['untitled'];
					if ($log['routeid'])
					{
						$data = array(
							'nodeid' => $log['nodeid'],
							'title' => $title,
							'innerPost' => $log['nodeid'],
						);
						$titleurl = vB5_Route::buildUrl($log['routeid'] . '|fullurl', $data, array(), '#post' . $log['nodeid']);
						$logdata['title'] = '<a href="' . $titleurl . '">' . $title . '</a>';
					}
					else
					{
						$logdata['title'] = $title;
					}

					if (!empty($logdata))
					{
						foreach ($logdata AS $key => $data)
						{
							$temp[] = "$key = $data";
						}

						$action .= '<br />' . implode('; ', $temp);
					}
				}
			}
			else
			{
				$action = '-';
			}

			$cell[] = $action;

			$ipaddresscell = '&nbsp;';
			if($log['ipaddress'])
			{
				$ipaddresscell = '<a href="admincp/usertools.php?do=gethost&ip=' . $log['ipaddress'] . '">' . $log['ipaddress'] . '</a>';
			}

			$cell[] = '<span class="smallfont">' . $ipaddresscell . '</span>';

			print_cells_row($cell, 0, 0, -3);
		}

		$paging = get_log_paging_html($vbulletin->GPC['pagenumber'], $totalpages, $baseUrl, $query, $vbphrase);
		print_table_footer(5, $paging);
	}
	else
	{
		print_stop_message2('no_results_matched_your_query');
	}
}

// ###################### Start prune log #######################
if ($_REQUEST['do'] == 'prunelog' AND can_access_logs($vb5_config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>'))
{
	$vbulletin->input->clean_array_gpc('r', array(
		'daysprune' => vB_Cleaner::TYPE_UINT,
		'userid'    => vB_Cleaner::TYPE_UINT,
		'modaction' => vB_Cleaner::TYPE_STR,
		'product'   => vB_Cleaner::TYPE_STR,
	));

	$datecut = TIMENOW - (86400 * $vbulletin->GPC['daysprune']);
	$conditions[] = array('field' => 'dateline', 'value' => $datecut, 'operator' => vB_dB_Query::OPERATOR_LT);

	if ($vbulletin->GPC['userid'])
	{
		$conditions[] = array('field' => 'userid', 'value' => $vbulletin->GPC['userid'], 'operator' => vB_dB_Query::OPERATOR_EQ);
	}

	if ($vbulletin->GPC['modaction'])
	{
		$conditions[] = array('field' => 'action', 'value' => $vbulletin->GPC['modaction'], 'operator' => vB_dB_Query::OPERATOR_INCLUDES);
	}

	if ($vbulletin->GPC['product'])
	{
			if ($vbulletin->GPC['product'] == 'vbulletin')
		{
			$conditions[] = array('field' => 'product', 'value' => array('', 'vbulletin'), 'operator' => vB_dB_Query::OPERATOR_EQ);
		}
		else
		{
			$conditions[] = array('field' => 'product', 'value' => $vbulletin->GPC['product'], 'operator' => vB_dB_Query::OPERATOR_EQ);
		}
	}

	$logsres = vB::getDbAssertor()->assertQuery('getModLogsByConds', array('conds' => $conditions));
	$logs = $logsres->current();

	if ($logs['total'])
	{
		print_form_header('admincp/modlog', 'doprunelog');
		construct_hidden_code('datecut', $datecut);
		construct_hidden_code('modaction', $vbulletin->GPC['modaction']);
		construct_hidden_code('userid', $vbulletin->GPC['userid']);
		construct_hidden_code('product', $vbulletin->GPC['product']);
		print_table_header($vbphrase['prune_moderator_log']);
		print_description_row(construct_phrase($vbphrase['are_you_sure_you_want_to_prune_x_log_entries_from_moderator_log'], vb_number_format($logs['total'])));
		print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
	}
	else
	{
		print_stop_message2('no_logs_matched_your_query');
	}

}

// ###################### Start do prune log #######################
if ($_POST['do'] == 'doprunelog' AND can_access_logs($vb5_config['SpecialUsers']['canpruneadminlog'], 0, '<p>' . $vbphrase['control_panel_log_pruning_permission_restricted'] . '</p>'))
{
	$vbulletin->input->clean_array_gpc('p', array(
		'datecut'   => vB_Cleaner::TYPE_UINT,
		'modaction' => vB_Cleaner::TYPE_STR,
		'userid'    => vB_Cleaner::TYPE_UINT,
		'product'   => vB_Cleaner::TYPE_STR,
	));
	$conditions[] = array('field' => 'dateline', 'value' => $vbulletin->GPC['datecut'], 'operator' => vB_dB_Query::OPERATOR_LT);
	if (!empty($vbulletin->GPC['modaction']))
	{
		$conditions[] = array('field' => 'action', 'value' => $vbulletin->GPC['modaction'], 'operator' => vB_dB_Query::OPERATOR_INCLUDES);
	}
	if (!empty($vbulletin->GPC['userid']))
	{
		$conditions[] = array('field' => 'userid', 'value' => $vbulletin->GPC['userid'], 'operator' => vB_dB_Query::OPERATOR_EQ);
	}
	if ($vbulletin->GPC['product'])
	{
		if ($vbulletin->GPC['product'] == 'vbulletin')
		{
			$conditions[] = array('field' => 'product', 'value' => array('', 'vbulletin'), 'operator' => vB_dB_Query::OPERATOR_EQ);
		}
		else
		{
			$conditions[] = array('field' => 'product', 'value' => $vbulletin->GPC['product'], 'operator' => vB_dB_Query::OPERATOR_EQ);
		}
	}

	vB::getDbAssertor()->delete('moderatorlog', $conditions);

	print_stop_message2('pruned_moderator_log_successfully', 'modlog', array('do'=>'choose'));
}

// ###################### Start modify #######################
if ($_REQUEST['do'] == 'choose')
{
	$users = vB::getDbAssertor()->assertQuery('chooseModLog');
	$userlist = array('no_value' => $vbphrase['all_log_entries']);
	foreach ($users as $user)
	{
		$userlist["$user[userid]"] = $user['username'];
	}

	print_form_header('admincp/modlog', 'view');
	print_table_header($vbphrase['moderator_log_viewer']);
	print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_select_row($vbphrase['show_only_entries_generated_by'], 'userid', $userlist);
	print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
	print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
	if (count($products = fetch_product_list()) > 1)
	{
		print_select_row($vbphrase['product'], 'product', array('' => $vbphrase['all_products']) + $products);
	}

	$orderby = array(
		'date' => $vbphrase['date'],
		'user' => $vbphrase['username'],
		'modaction' => $vbphrase['action'],
	);

	print_select_row($vbphrase['order_by_gcpglobal'], 'orderby', $orderby, 'date');
	print_submit_row($vbphrase['view'], 0);

	if (can_access_logs($vb5_config['SpecialUsers']['canpruneadminlog'], 0, ''))
	{
		print_form_header('admincp/modlog', 'prunelog');
		print_table_header($vbphrase['prune_moderator_log']);
		print_select_row($vbphrase['remove_entries_logged_by_user'], 'userid', $userlist);
		if (count($products) > 1)
		{
			print_select_row($vbphrase['product'], 'product', array('' => $vbphrase['all_products']) + $products);
		}
		print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
		print_submit_row($vbphrase['prune_log_entries'], 0);
	}

}

print_cp_footer();

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
