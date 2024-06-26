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
global $phrasegroups, $specialtemplates, $vbphrase;
$phrasegroups = array('cppermission');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once(dirname(__FILE__) . '/global.php');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['administrator_permissions_manager']);

$vb5_config =& vB::getConfig();
$limitedAdmin = false;
$superAdmins = preg_split('#\s*,\s*#s', $vb5_config['SpecialUsers']['superadmins'], -1, PREG_SPLIT_NO_EMPTY);
if (!in_array($vbulletin->userinfo['userid'], $superAdmins))
{
	if (!empty($vb5_config['SpecialUsers']['administrators']))
	{
		$adminUsers = preg_split('#\s*,\s*#s', $vb5_config['SpecialUsers']['administrators'], -1, PREG_SPLIT_NO_EMPTY);
		if (in_array(vB::getCurrentSession()->get('userid'), $adminUsers))
		{
			$limitedAdmin = array();

			if (file_exists(DIR .'/includes/xml/administrator_permissions.xml'))
			{
				$parser = new vB_XML_Parser(false, DIR .'/includes/xml/administrator_permissions.xml');
				$xml = $parser->parse();
				$result = array();
				$bitfields = array_pop($xml);
				foreach($bitfields AS $bitfield)
				{
					$limitedAdmin[$bitfield['name']] = $bitfield['value'];
				}

			}
		}
	}

	if (empty($limitedAdmin))
	{
		print_stop_message2('sorry_you_are_not_allowed_to_edit_admin_permissions');
	}
}
// ############################# LOG ACTION ###############################
$vbulletin->input->clean_array_gpc('r', array(
	'userid' => vB_Cleaner::TYPE_INT
));

if ($vbulletin->GPC['userid'])
{
	$user = $vbulletin->db->query_first("
		SELECT administrator.*, IF(administrator.userid IS NULL, 0, 1) AS isadministrator,
			user.userid, user.username
		FROM " . TABLE_PREFIX . "user AS user
		LEFT JOIN " . TABLE_PREFIX . "administrator AS administrator ON(administrator.userid = user.userid)
		WHERE user.userid = " . $vbulletin->GPC['userid']
	);

	if (!$user)
	{
		print_stop_message2('no_matches_found_gerror');
	}
	else if (!$user['isadministrator'])
	{
		// should this user have an administrator record??
		$userinfo = fetch_userinfo($user['userid']);
		cache_permissions($userinfo);
		if ($userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
		{
			$admindm =& datamanager_init('Admin', $vbulletin, vB_DataManager_Constants::ERRTYPE_SILENT);
			$admindm->set('userid', $userinfo['userid']);
			$admindm->save();
			unset($admindm);
		}
		else
		{
			print_stop_message2('invalid_user_specified');
		}
	}

	$admindm =& datamanager_init('Admin', $vbulletin, vB_DataManager_Constants::ERRTYPE_CP);
	$admindm->set_existing($user);
}
else
{
	$user = array();
}

require_once(DIR . '/includes/class_bitfield_builder.php');
if (vB_Bitfield_Builder::build(false) !== false)
{
	$myobj =& vB_Bitfield_Builder::init();
}
else
{
	echo "<strong>error</strong>\n";
	print_r(vB_Bitfield_Builder::fetch_errors());
}
foreach ($myobj->data['ugp']['adminpermissions'] AS $title => $values)
{
	// don't show settings that have a group for the usergroup page
	if (empty($values['group']))
	{
		$ADMINPERMISSIONS["$title"] = $values['value'];
		$permsphrase["$title"] = $vbphrase["$values[phrase]"];
	}
}

$vbulletin->input->clean_array_gpc('p', array(
	'oldpermissions' 	 => vB_Cleaner::TYPE_INT,
	'adminpermissions' => vB_Cleaner::TYPE_ARRAY_INT
));

require_once(DIR . '/includes/functions_misc.php');
log_admin_action(iif($user, "user id = $user[userid] ($user[username])" . iif($_POST['do'] == 'update', " (" . $vbulletin->GPC['oldpermissions'] ." &raquo; " . convert_array_to_bits($vbulletin->GPC['adminpermissions'], $ADMINPERMISSIONS) . ")")));

// #############################################################################

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

// #############################################################################

if ($_POST['do'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'cssprefs'      => vB_Cleaner::TYPE_STR,
		'dismissednews' => vB_Cleaner::TYPE_STR
	));

	if (!empty($limitedAdmin) AND !empty($superAdmins) AND in_array($vbulletin->GPC['userid'], $superAdmins))
	{
		print_cp_no_permission();
	}

	foreach ($ADMINPERMISSIONS as $key => $value)
	{
		//Does the current user have rights to set this?
		if (empty($limitedAdmin) OR isset($limitedAdmin[$key]))
		{
			$admindm->set_bitfield('adminpermissions', $key, $vbulletin->GPC['adminpermissions'][$key]);
		}
		else if (!empty($user['adminpermissions']) AND ($user['adminpermissions'] & $value))
		{
			$admindm->set_bitfield('adminpermissions', $key, $value);
		}
	}



	// Legacy Hook 'admin_permissions_process' Removed //

	$admindm->set('cssprefs', $vbulletin->GPC['cssprefs']);
	$admindm->set('dismissednews', $vbulletin->GPC['dismissednews']);
	$admindm->save();

	parse_str(vB::getCurrentSession()->get('sessionurl'),$extra);
	$extra['#'] = "user$user[userid]";
	vB_Cache::instance()->event('permissions_' . $user['userid']);
	vB_Cache::instance()->event('userPerms_' . $user['userid']);
	print_stop_message2('saved_administrator_permissions_successfully','adminpermissions', $extra);
}

// #############################################################################

if ($_REQUEST['do'] == 'edit')
{
	echo "<p align=\"center\">{$vbphrase['give_admin_access_arbitrary_html']}</p>";
	print_form_header('admincp/adminpermissions', 'update');
	construct_hidden_code('userid', $vbulletin->GPC['userid']);
	construct_hidden_code('oldpermissions', $user['adminpermissions']);
	print_table_header(construct_phrase($vbphrase['x_y_id_z'], $vbphrase['administrator_permissions'], $user['username'], $user['userid']));
	print_label_row("$vbphrase[administrator]: <a href=\"admincp/user.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;u=" . $vbulletin->GPC['userid'] . "\">$user[username]</a>", '<div align="' . vB_Template_Runtime::fetchStyleVar('right') .'"><input type="button" class="button" value=" ' . $vbphrase['all_yes'] . ' " onclick="js_check_all_option(this.form, 1);" /> <input type="button" class="button" value=" ' . $vbphrase['all_no'] . ' " onclick="js_check_all_option(this.form, 0);" /></div>', 'thead');

	foreach (convert_bits_to_array($user['adminpermissions'], $ADMINPERMISSIONS) AS $field => $value)
	{
		//sklp bitfields this user can't set.
		if (!empty($limitedAdmin) AND !isset($limitedAdmin[$field]))
		{
			continue;
		}
		print_yes_no_row(($permsphrase["$field"] == '' ? $vbphrase['n_a'] : $permsphrase["$field"]), "adminpermissions[$field]", $value);
	}

	// Legacy Hook 'admin_permissions_form' Removed //

	print_select_row($vbphrase['control_panel_style_choice'], 'cssprefs', array_merge(array('' => "($vbphrase[default])"), fetch_cpcss_options()), $user['cssprefs']);
	print_input_row($vbphrase['dismissed_news_item_ids'], 'dismissednews', $user['dismissednews']);

	print_submit_row();
}

// #############################################################################

if ($_REQUEST['do'] == 'modify')
{
	print_form_header('admincp/adminpermissions', 'edit');
	print_table_header($vbphrase['administrator_permissions'], 3);

	$users = $vbulletin->db->query_read("
		SELECT user.username, usergroupid, membergroupids, infractiongroupids, administrator.*
		FROM " . TABLE_PREFIX . "administrator AS administrator
		INNER JOIN " . TABLE_PREFIX . "user AS user USING(userid)
		ORDER BY user.username
	");
	while ($user = $vbulletin->db->fetch_array($users))
	{
		$perms = fetch_permissions(0, $user['userid'], $user);

		if ($perms['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
		{
			//limited admins can't edit superadmins
			if (!empty($limitedAdmin) AND !empty($superAdmins) AND in_array($user['userid'], $superAdmins))
			{
				continue;
			}
			print_cells_row(array(
				"<a href=\"admincp/user.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;u=$user[userid]\" name=\"user$user[userid]\"><b>$user[username]</b></a>",
				'-',
				construct_link_code($vbphrase['view_control_panel_log'], "adminlog.php?" . vB::getCurrentSession()->get('sessionurl') . "do=view&script=&u=$user[userid]") .
				construct_link_code($vbphrase['edit_permissions'], "adminpermissions.php?" . vB::getCurrentSession()->get('sessionurl') . "do=edit&amp;u=$user[userid]")
			), 0, '', 0);
		}
	}

	print_table_footer();
}

print_cp_footer();

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - Shadow @ www.nulled.ch
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
