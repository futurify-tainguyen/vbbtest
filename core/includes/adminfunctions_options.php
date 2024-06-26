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

error_reporting(E_ALL & ~E_NOTICE);

/**
* Prints a setting group for use in options.php?do=options
*
* @param	string	Settings group ID
* @param	boolean	Show advanced settings?
*/
function print_setting_group($dogroup, $advanced = 0)
{
	global $settingscache, $grouptitlecache, $bgcounter, $settingphrase;

	if (!is_array($settingscache["$dogroup"]))
	{
		return;
	}
	$userContext = vB::getUserContext();

	if (!empty($settingscache[$dogroup]['groupperm']) AND !$userContext->hasAdminPermission($settingscache[$dogroup]['groupperm']))
	{
		return;
	}

	$vbphrase = vB_Api::instanceInternal('phrase')->fetch(array('group_requires_admin_perm', 'edit', 'delete', 'add_setting', 'rebuild', 'rebuild_all_attachments'));
	print_column_style_code(array('width:45%', 'width:55%'));

	echo "<thead>\r\n";

	$vb5_config = vB::getConfig();
	$title = $settingphrase["settinggroup_$grouptitlecache[$dogroup]"];
	if ($vb5_config['Misc']['debug'] AND $userContext->hasAdminPermission('canadminsettingsall'))
	{
		$title .= '<span class="normal">' .
			construct_link_code($vbphrase['edit'], "options.php?" . vB::getCurrentSession()->get('sessionurl') .
				"do=editgroup&amp;grouptitle=$dogroup") .
			construct_link_code($vbphrase['delete'], "options.php?" . vB::getCurrentSession()->get('sessionurl') .
				"do=removegroup&amp;grouptitle=$dogroup") .
			construct_link_code($vbphrase['add_setting'], "options.php?" . vB::getCurrentSession()->get('sessionurl') .
			 	"do=addsetting&amp;grouptitle=$dogroup") .
			'</span>';
	}

	print_table_header($title);
	echo "</thead>\r\n";

	$bgcounter = 1;

	foreach ($settingscache["$dogroup"] AS $settingid => $setting)
	{
		if (!empty($setting['adminperm']) AND !$userContext->hasAdminPermission($setting['adminperm']))
		{
			continue;
		}

		if (($advanced OR !$setting['advanced']) AND !empty($setting['varname']))
		{
			print_setting_row($setting, $settingphrase, true, $userContext);
		}
	}

}

/**
* Prints a setting row for use in options.php?do=options
*
* @param	array	Settings array
* @param	array	Phrases
*/
function print_setting_row($setting, $settingphrase, $option_config = true, $userContext = false)
{
	global $vbulletin, $bgcounter, $vbphrase;
	$settingid = $setting['varname'];

	if (empty($userContext))
	{
		$userContext = vB::getUserContext();
	}
	echo '<tbody>';

	$vb5_config = vB::getConfig();
	print_description_row(
		iif(($vb5_config['Misc']['debug'] AND $option_config AND $userContext->hasAdminPermission('canadminsettingsall')),
		'<div class="smallfont" style="float:' . vB_Template_Runtime::fetchStyleVar('right') . '">' . construct_link_code($vbphrase['edit'], "options.php?" . vB::getCurrentSession()->get('sessionurl') . "do=editsetting&amp;varname=$setting[varname]") . construct_link_code($vbphrase['delete'], "options.php?" . vB::getCurrentSession()->get('sessionurl') . "do=removesetting&amp;varname=$setting[varname]") . '</div>') .
		'<div>' . $settingphrase["setting_$setting[varname]_title"] . "<a name=\"$setting[varname]\"></a></div>",
		0, 2, 'optiontitle' . ($vb5_config['Misc']['debug'] ? "\" title=\"\$vbulletin->options['" . $setting['varname'] . "']" : '')
	);
	echo "</tbody><tbody id=\"tbody_$settingid\">\r\n";

	// make sure all rows use the alt1 class
	$bgcounter--;

	$description = "<div class=\"smallfont\"" . ($vb5_config['Misc']['debug'] ? " title=\"\$vbulletin->options['$setting[varname]']\"" : '') . ">" . $settingphrase["setting_$setting[varname]_desc"] . '</div>';
	$name = "setting[$setting[varname]]";
	$right = "<span class=\"smallfont\">$vbphrase[error]</span>";
	$width = 40;
	$rows = 8;

	if (preg_match('#^input:?(\d+)$#s', $setting['optioncode'], $matches))
	{
		$width = $matches[1];
		$setting['optioncode'] = '';
	}
	else if (preg_match('#^textarea:?(\d+)(,(\d+))?$#s', $setting['optioncode'], $matches))
	{
		$rows = $matches[1];
		if ($matches[2])
		{
			$width = $matches[3];
		}
		$setting['optioncode'] = 'textarea';
	}
	else if (preg_match('#^bitfield:(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = 'bitfield';
		$setting['bitfield'] =& fetch_bitfield_definitions($matches[1]);
	}
	else if (preg_match('#^(select|selectmulti|radio):(piped|eval)(\r\n|\n|\r)(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = "$matches[1]:$matches[2]";
		$setting['optiondata'] = trim($matches[4]);
	}
	else if (preg_match('#^usergroup:?(\d+)$#s', $setting['optioncode'], $matches))
	{
		$size = intval($matches[1]);
		$setting['optioncode'] = 'usergroup';
	}
	else if (preg_match('#^(usergroupextra)(\r\n|\n|\r)(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = 'usergroupextra';
		$setting['optiondata'] = trim($matches[3]);
	}
	else if (preg_match('#^profilefield:?([a-z0-9,;=]*)(?:\r\n|\n|\r)(.*)$#siU', $setting['optioncode'], $matches))
	{
		$setting['optioncode'] = 'profilefield';
		$setting['optiondata'] = array(
			'constraints'  => trim($matches[1]),
			'extraoptions' => trim($matches[2]),
		);
	}

	// Make setting's value the default value if it's null
	if ($setting['value'] === NULL)
	{
		$setting['value'] = $setting['defaultvalue'];
	}

	switch ($setting['optioncode'])
	{
		// input type="text"
		case '':
		{
			print_input_row($description, $name, $setting['value'], 1, $width);
		}
		break;

		// input type="radio"
		case 'yesno':
		{
			print_yes_no_row($description, $name, $setting['value']);
		}
		break;

		// textarea
		case 'textarea':
		{
			print_textarea_row($description, $name, $setting['value'], $rows, "$width\" style=\"width:90%");
		}
		break;

		// bitfield
		case 'bitfield':
		{
			$setting['value'] = intval($setting['value']);
			$setting['html'] = '';

			if ($setting['bitfield'] === NULL)
			{
				print_label_row($description, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
			}
			else
			{
				#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
				$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
				$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
				foreach ($setting['bitfield'] AS $key => $value)
				{
					$value = intval($value);
					$setting['html'] .= "<table style=\"width:175px; float:" . vB_Template_Runtime::fetchStyleVar('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
					<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
					<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key($key) . "</label></td>\r\n</tr></table>\r\n";
				}

				$setting['html'] .= "</div>\r\n";
				#$setting['html'] .= "</fieldset>";
				print_label_row($description, $setting['html'], '', 'top', $name, 40);
			}
		}
		break;

		// select:piped
		case 'select:piped':
		{
			$options = fetch_piped_options($setting['optiondata']);
			$options = additional_options($settingid, $setting['optioncode'], $options);
			print_option_select_row($description, $name, $options, $setting['value']);
		}
		break;

		// select:eval
		case 'select:eval':
		{
			$options = null;
			eval($setting['optiondata']);
			$options = additional_options($settingid, $setting['optioncode'], $options);
			print_option_select_row($description, $name, $options, $setting['value']);
		}
		break;

		// select:eval
		case 'selectmulti:eval':
		{
			$options = null;
			eval($setting['optiondata']);
			$options = additional_options($settingid, $setting['optioncode'], $options);
			print_option_select_row($description, $name, $options, $setting['value'], 5);
		}
		break;

		// radio:piped
		case 'radio:piped':
		{
			print_radio_row($description, $name, fetch_piped_options($setting['optiondata']), $setting['value'], 'smallfont');
		}
		break;

		// radio:eval
		case 'radio:eval':
		{
			$options = null;

			eval($setting['optiondata']);

			if (is_array($options) AND !empty($options))
			{
				print_radio_row($description, $name, $options, $setting['value'], 'smallfont');
			}
			else
			{
				print_input_row($description, $name, $setting['value']);
			}
		}
		break;

		case 'username':
		{
			$userinfo = vB::getDbAssertor()->assertQuery('user',
				array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT, 'userid' => $setting['value'])
			);
			if (intval($setting['value']) AND $userinfo AND $userinfo->valid())
			{
				$userInfo = $userinfo->current();
				print_input_row($description, $name, $userInfo['username'], false);
			}
			else
			{
				print_input_row($description, $name);
			}
			break;
		}

		case 'usergroup':
		{
			$usergrouplist = array();
			$usergroupcache = vB::getDatastore()->getValue('usergroupcache');
			foreach ($usergroupcache AS $usergroup)
			{
				$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
			}

			if ($size > 1)
			{
				print_select_row($description, $name . '[]', array(0 => '') + $usergrouplist, unserialize($setting['value']), false, $size, true);
			}
			else
			{
				print_select_row($description, $name, $usergrouplist, $setting['value']);
			}
			break;
		}

		case 'usergroupextra':
		{
			$usergrouplist = fetch_piped_options($setting['optiondata']);
			$usergroupcache = vB::getDatastore()->get_value('usergroupcache');
			foreach ($usergroupcache AS $usergroup)
			{
				$usergrouplist["$usergroup[usergroupid]"] = $usergroup['title'];
			}

			print_select_row($description, $name, $usergrouplist, $setting['value']);
			break;
		}

		case 'profilefield':
		{
			static $profilefieldlistcache = array();
			$profilefieldlisthash = md5(serialize($setting['optiondata']));

			if (!isset($profilefieldlistcache[$profilefieldlisthash]))
			{
				$profilefieldlist = fetch_piped_options($setting['optiondata']['extraoptions']);

				$constraints = preg_split('#;#', $setting['optiondata']['constraints'], -1, PREG_SPLIT_NO_EMPTY);
				//$where = array();
				$conditions = array();
				foreach ($constraints AS $constraint)
				{
					$constraint = explode('=', $constraint);
					switch ($constraint[0])
					{
						case 'editablegt':
							//$where[] = 'editable > ' . intval($constraint[1]);
							$conditions[] = array('field' => 'editable', 'value' => intval($constraint[1]), 'operator' => vB_dB_Query::OPERATOR_GT);
							break;
						case 'types':
							$constraint[1] = preg_split('#,#', $constraint[1], -1, PREG_SPLIT_NO_EMPTY);
							if (!empty($constraint[1]))
							{
								$conditions[] = array('field' => 'type', 'value' => $constraint[1], 'operator' => vB_dB_Query::OPERATOR_EQ);
							}
							break;
					}
				}

				$profilefields = vB::getDbAssertor()->assertQuery('vBForum:profilefield',
					array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT, vB_dB_Query::CONDITIONS_KEY => $conditions),
					array('field' => 'displayorder', 'direction' => vB_dB_Query::SORT_ASC)
				);

				foreach ($profilefields AS $profilefield)
				{
					$fieldname = "field$profilefield[profilefieldid]";
					$profilefieldlist[$fieldname] = construct_phrase($vbphrase['profilefield_x_fieldid_y'], fetch_phrase_from_key("{$fieldname}_title"), $fieldname);
				}

				$profilefieldlistcache[$profilefieldlisthash] = $profilefieldlist;
				unset($profilefieldlist, $constraints, $constraint, $where, $profilefields, $profilefield, $fieldname);
			}

			print_select_row($description, $name, $profilefieldlistcache[$profilefieldlisthash], $setting['value']);
			break;
		}

		//note this isn't currently used and doesn't entirely work.
		// arbitrary number of <input type="text" />
		case 'multiinput':
		{
			$setting['html'] = "<div id=\"ctrl_$setting[varname]\"><fieldset id=\"multi_input_fieldset_$setting[varname]\" style=\"padding:4px\">";

			$setting['values'] = unserialize($setting['value']);
			$setting['values'] = (is_array($setting['values']) ? $setting['values'] : array());
			$setting['values'][] = '';

			foreach ($setting['values'] AS $key => $value)
			{
				$setting['html'] .= "<div id=\"multi_input_container_$setting[varname]_$key\">" . ($key + 1) . " <input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][$key]\" id=\"multi_input_$setting[varname]_$key\" size=\"40\" value=\"" . htmlspecialchars_uni($value) . "\" tabindex=\"1\" /></div>";
			}

			$i = sizeof($setting['values']);
			if ($i == 0)
			{
				$setting['html'] .= "<div><input type=\"text\" class=\"bginput\" name=\"setting[$setting[varname]][$i]\" size=\"40\" tabindex=\"1\" /></div>";
			}

			$userinfo = vB_User::fetchUserinfo(0, array('admin'));
			$setting['html'] .= "
				</fieldset>
				<div class=\"smallfont\"><a href=\"#\" onclick=\"return multi_input['$setting[varname]'].add()\">Add Another Option</a></div>
				<script type=\"text/javascript\">
				<!--
				multi_input['$setting[varname]'] = new vB_Multi_Input('$setting[varname]', $i, '" . $user['cssprefs'] . "');
				//-->
				</script>
			";

			print_label_row($description, $setting['html']);
			break;
		}

		// default registration options
		case 'defaultregoptions':
		{
			$setting['value'] = intval($setting['value']);

			$checkbox_options = array(
				'receiveemail' => 'display_email_gcpuser',
				'adminemail' => 'receive_admin_emails_guser',
				'invisiblemode' => 'invisible_mode_guser',
				'vcard' => 'allow_vcard_download_guser',
				'signature' => 'display_signatures_gcpuser',
				'avatar' => 'display_avatars_gcpuser',
				'image' => 'display_images_gcpuser',
				'showreputation' => 'display_reputation_gcpuser',
				'enablepm' => 'receive_private_messages_guser',
				'emailonpm' => 'send_notification_email_when_a_private_message_is_received_guser',
				'pmpopup' => 'pop_up_notification_box_when_a_private_message_is_received',
			);

			$setting['value'] = intval($setting['value']);

			$setting['html'] = '';
			#$setting['html'] .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
			$setting['html'] .= "<div id=\"ctrl_setting[$setting[varname]]\" class=\"smallfont\">\r\n";
			$setting['html'] .= "<input type=\"hidden\" name=\"setting[$setting[varname]][0]\" value=\"0\" />\r\n";
			foreach ($checkbox_options AS $key => $phrase)
			{
				$bf_misc_regoptions = vB::getDatastore()->getValue('bf_misc_regoptions');
				$value = $bf_misc_regoptions["$key"];

				$setting['html'] .= "<table style=\"width:175px; float:" . vB_Template_Runtime::fetchStyleVar('left') . "\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
				<td><input type=\"checkbox\" name=\"setting[$setting[varname]][$value]\" id=\"setting[$setting[varname]]_$key\" value=\"$value\"" . (($setting['value'] & $value) ? ' checked="checked"' : '') . " /></td>
				<td width=\"100%\" style=\"padding-top:4px\"><label for=\"setting[$setting[varname]]_$key\" class=\"smallfont\">" . fetch_phrase_from_key($phrase) . "</label></td>\r\n</tr></table>\r\n";
			}
			#$setting['html'] .= "</fieldset>";
			print_label_row($description, $setting['html'], '', 'top', $name, 40);
		}
		break;

		// cp folder options
		case 'cpstylefolder':
		{
			if ($folders = fetch_cpcss_options() AND !empty($folders))
			{
				print_select_row($description, $name, $folders, $setting['value'], 1, 6);
			}
			else
			{
				print_input_row($description, $name, $setting['value'], 1, 40);
			}
		}
		break;

		// cookiepath / cookiedomain options
		case 'cookiepath':
		case 'cookiedomain':
		{
			$func = 'fetch_valid_' . $setting['optioncode'] . 's';

			$cookiesettings = $func(($setting['optioncode'] == 'cookiepath' ? $vbulletin->script : $_SERVER['HTTP_HOST']), $vbphrase['blank']);

			$setting['found'] = in_array($setting['value'], array_keys($cookiesettings));

			$setting['html'] = "
			<div id=\"ctrl_$setting[varname]\">
			<fieldset>
				<legend>$vbphrase[suggested_settings]</legend>
				<div style=\"padding:4px\">
					<select name=\"setting[$setting[varname]]\" tabindex=\"1\" class=\"bginput\">" .
						construct_select_options($cookiesettings, $setting['value']) . "
					</select>
				</div>
			</fieldset>
			<br />
			<fieldset>
				<legend>$vbphrase[custom_setting]</legend>
				<div style=\"padding:4px\">
					<label for=\"{$settingid}o\"><input type=\"checkbox\" id=\"{$settingid}o\" name=\"setting[{$settingid}_other]\" tabindex=\"1\" value=\"1\"" . ($setting['found'] ? '' : ' checked="checked"') . " />$vbphrase[use_custom_setting]
					</label><br />
					<input type=\"text\" class=\"bginput\" size=\"25\" name=\"setting[{$settingid}_value]\" value=\"" . ($setting['found'] ? '' : $setting['value']) . "\" />
				</div>
			</fieldset>
			</div>";

			print_label_row($description, $setting['html'], '', 'top', $name, 50);
		}
		break;

		case 'forums:all':
		{
			$array = construct_forum_chooser_options(-1,$vbphrase['all']);
			$size = sizeof($array);

			$vbphrase['forum_is_closed_for_posting'] = $vbphrase['closed'];
			print_select_row($description, $name.'[]', $array, unserialize($setting['value']), false, ($size > 10 ? 10 : $size), true);
		}

		break;

		case 'forums:none':
		{
			$array = construct_forum_chooser_options(0,$vbphrase['none']);
			$size = sizeof($array);

			$vbphrase['forum_is_closed_for_posting'] = $vbphrase['closed'];
			print_select_row($description, $name.'[]', $array, unserialize($setting['value']), false, ($size > 10 ? 10 : $size), true);
		}
		break;

		// just a label
		default:
		{
			$handled = false;
			// Legacy Hook 'admin_options_print' Removed //
			if (!$handled)
			{
				eval("\$right = \"<div id=\\\"ctrl_setting[$setting[varname]]\\\">$setting[optioncode]</div>\";");
				print_label_row($description, $right, '', 'top', $name, 50);
			}
		}
		break;
	}

	echo "</tbody>\r\n";
	$valid = exec_setting_validation_code($setting['varname'], $setting['value'], $setting['validationcode']);

	echo "<tbody id=\"tbody_error_$settingid\" style=\"display:" . (($valid === 1 OR $valid === true) ? 'none' : '') . "\"><tr><td class=\"alt1 smallfont\" colspan=\"2\"><div style=\"padding:4px; border:solid 1px red; background-color:white; color:black\"><strong>$vbphrase[error]</strong>:<div id=\"span_error_$settingid\">$valid</div></div></td></tr></tbody>";
}

function additional_options($settingid, $optioncode, $options)
{
	vB::getHooks()->invoke('hookAdminSettingsSelectOptions', array(
		'settingid' => $settingid,
		'optioncode' => $optioncode,
		'options' => &$options,
	));

	return $options;
}

function print_option_select_row($description, $name, $options, $value, $size=0)
{
	if (is_array($options) AND !empty($options))
	{
		//if this is a multi-select box then we need to convert the post return
		//to an array value.
		if($size > 0)
		{
			$name .= '[]';
		}

		//size controls how many options show.  0 doesn't set the size param
		//multi (the last param) allows allow multiple values to be selected
		//In theory we could show the select with multiple values visible (instead of drop down)
		//but we don't want to do that, so let's just use the size param to controls this.
		print_select_row($description, $name, $options, $value, true, $size, (bool) $size);
	}
	else
	{
		print_input_row($description, $name, $value);
	}
}

/**
* Updates the setting table based on data passed in then rebuilds the datastore.
* Only entries in the array are updated (allows partial updates).
*
* @param	array	Array of settings. Format: [setting_name] = new_value
*
*/
function save_settings($settings)
{
	global $vbulletin, $vbphrase;

	//a few variables to track changes for processing after all variables are updated.
	$rebuildstyle = false;
	$templatecachepathchanged = false;
	$oldtemplatepath = null;
	$newtemplatepath = null;
	$userContext = vB::getUserContext();
	$cleaner = vB::getCleaner();
	$canAdminAll = $userContext->hasAdminPermission('canadminsettingsall');
	$assertor = vB::getDbAssertor();
	$oldsettings = $assertor->assertQuery('vBAdmincp:getCurrentSettings',
		array('varname' => array_keys($settings)));

	foreach ($oldsettings as $oldsetting)
	{
		//check the setting and group permissions
		if ((!empty($oldsetting['adminperm']) AND !$userContext->hasAdminPermission($oldsetting['adminperm']))
			OR (!empty($oldsetting['groupperm']) AND !$userContext->hasAdminPermission($oldsetting['groupperm'])))
		{
			throw new vB_Exception_Api('no_permission');
		}

		switch ($oldsetting['varname'])
		{
			// **************************************************
			case 'bbcode_html_colors':
			{
				$settings['bbcode_html_colors'] = serialize($settings['bbcode_html_colors']);
			}
			break;

			// **************************************************
			case 'styleid':
			{
				$assertor->assertQuery('vBForum:style',
					array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_UPDATE,
						'userselect' => 1,
						vB_dB_Query::CONDITIONS_KEY => array(
							array('field' => 'styleid', 'value' => $settings['styleid'], 'operator' => vB_dB_Query::OPERATOR_EQ)
						)
					)
				);
			}
			break;

			// **************************************************
			case 'banemail':
			{
				vB::getDatastore()->build('banemail', $settings['banemail']);
				$settings['banemail'] = '';
			}
			break;

			// **************************************************
			case 'editormodes':
			{
				$vbulletin->input->clean_array_gpc('p', array('fe' => vB_Cleaner::TYPE_UINT, 'qr' => vB_Cleaner::TYPE_UINT, 'qe' => vB_Cleaner::TYPE_UINT));

				$settings['editormodes'] = serialize(array(
					'fe' => $vbulletin->GPC['fe'],
					'qr' => $vbulletin->GPC['qr'],
					'qe' => $vbulletin->GPC['qe']
				));
			}
			break;

			// **************************************************
			case 'attachresizes':
			{
				$vbulletin->input->clean_array_gpc('p', array(
					'attachresizes' => vB_Cleaner::TYPE_ARRAY_UINT,
				));

				$value = @unserialize($oldsetting['value']);
				$invalidate = array();
				if ($value[vB_Api_Filedata::SIZE_ICON] != $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_ICON])
				{
					$invalidate[] = vB_Api_Filedata::SIZE_ICON;
				}
				if ($value[vB_Api_Filedata::SIZE_THUMB] != $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_THUMB])
				{
					$invalidate[] = vB_Api_Filedata::SIZE_THUMB;
				}
				if ($value[vB_Api_Filedata::SIZE_SMALL] != $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_SMALL])
				{
					$invalidate[] = vB_Api_Filedata::SIZE_SMALL;
				}
				if ($value[vB_Api_Filedata::SIZE_MEDIUM] != $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_MEDIUM])
				{
					$invalidate[] = vB_Api_Filedata::SIZE_MEDIUM;
				}
				if ($value[vB_Api_Filedata::SIZE_LARGE] != $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_LARGE])
				{
					$invalidate[] = vB_Api_Filedata::SIZE_LARGE;
				}
				if (!empty($invalidate))
				{
					$assertor->update('vBForum:filedataresize', array('reload' => 1), array('resize_type' => $invalidate));
				}

				$settings['attachresizes'] = serialize(array(
					vB_Api_Filedata::SIZE_ICON   => $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_ICON],
					vB_Api_Filedata::SIZE_THUMB  => $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_THUMB],
					vB_Api_Filedata::SIZE_SMALL  => $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_SMALL],
					vB_Api_Filedata::SIZE_MEDIUM => $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_MEDIUM],
					vB_Api_Filedata::SIZE_LARGE  => $vbulletin->GPC['attachresizes'][vB_Api_Filedata::SIZE_LARGE]
				));
			}
			break;
			case 'thumbquality':
				if ($oldsetting['value'] != $settings['thumbquality'])
				{
					$assertor->update('vBForum:filedataresize', array('reload' => 1), vB_dB_Query::CONDITION_ALL);
				}
			break;

			// **************************************************
			case 'cookiepath':
			case 'cookiedomain':
			{
				if ($settings[$oldsetting['varname'] . '_other'] AND $settings[$oldsetting['varname'] . '_value'])
				{
					$settings[$oldsetting['varname']] = $settings[$oldsetting['varname'] . '_value'];
				}
			}
			break;

			case 'imagetype':
				// If they're not coming from AdminCP such that imagick_pdf_thumbnail is
				// not set, don't touch it.
				if (isset($settings['imagick_pdf_thumbnail']))
				{
					// Check pdf thumbnail-ability when option is changed to ImageMagick
					if ($settings['imagetype'] == 'Magick' AND $oldsetting['value'] != 'Magick')
					{
						// If the option is already enabled, maybe they manually enabled it,
						// so leave it alone.
						if (!$settings['imagick_pdf_thumbnail'])
						{
							/*
								We need to pull the vB_Image_ImageMagick instance.
								To get it through vB_Image::instance(), we need to
								make sure it gives us the imagick one not GD.
								Alternatively we could just instance it directly
								since its constructor is public...
							 */
							vB::getDatastore()->setOption('imagetype', 'Magick', false);
							$ghostScriptOkay = vB_Image::instance()->canThumbnailPdf();
							/*
								We currently don't really have a good way to check if the user
								manually set this or not. One annoying bit could be if they
								wanted to manually set this to "No" while switching over to
								imagemagick, and this re-enables it.
								We may end up reverting this depending on which we decide
								is more useful.
							 */
							if ($ghostScriptOkay)
							{
								$settings['imagick_pdf_thumbnail'] = 1;
							}
						}
					}
					else if ($settings['imagetype'] == 'GD')
					{
						/*
							Disable this option so that we can always check this when going
							*to* ImageMagick.
							If they switch back and forth between the 2 classes a lot and had
							to manually enable this option, then having this keep turning off
							would be annoying, but I don't think any admin will switch that
							frequently unless they're testing something.
						 */
						$settings['imagick_pdf_thumbnail'] = 0;
					}
				}
				break;

			// **************************************************
			default:
			{
				// Legacy Hook 'admin_options_processing' Removed //

				if ($oldsetting['optioncode'] == 'multiinput')
				{
					$store = array();
					foreach ($settings["$oldsetting[varname]"] AS $value)
					{
						if ($value != '')
						{
							$store[] = $value;
						}
					}
					$settings["$oldsetting[varname]"] = serialize($store);
				}
				else if (preg_match('#^(usergroup|forum)s?:([0-9]+|all|none)$#', $oldsetting['optioncode']))
				{
					// serialize the array of usergroup inputs
					if (!is_array($settings["$oldsetting[varname]"]))
					{
						 $settings["$oldsetting[varname]"] = array();
					}
					$settings["$oldsetting[varname]"] = array_map('intval', $settings["$oldsetting[varname]"]);
					$settings["$oldsetting[varname]"] = serialize($settings["$oldsetting[varname]"]);
				}
			}
		}
		$newvalue = validate_setting_value($settings["$oldsetting[varname]"], $oldsetting['datatype']);

		if ($canAdminAll AND isset($_POST['adminperm_' . $oldsetting['varname']]))
		{
			$newAdminPerm = substr($cleaner->clean($_POST['adminperm_' . $oldsetting['varname']], vB_Cleaner::TYPE_STR), 0, 32);
		}
		else
		{
			$newAdminPerm = $oldsetting['adminperm'];
		}
		// this is a strict type check because we want '' to be different from 0
		// some special cases below only use != checks to see if the logical value has changed
		if (
			$oldsetting['value'] === NULL OR
			(strval($oldsetting['value']) !== strval($newvalue)) OR
			(strval($oldsetting['adminperm']) !== strval($newAdminPerm))
		)
		{
			switch ($oldsetting['varname'])
			{
				case 'cache_templates_as_files':
				{
					if (!is_demo_mode())
					{
						$templatecachepathchanged = true;
					}
				}
				break;

				case 'template_cache_path':
				{

					if (!is_demo_mode())
					{
						$oldtemplatepath = strval($oldsetting['value']);
						$newtemplatepath = $newvalue;
					}
				}
				break;

				case 'languageid':
				{
					if ($oldsetting['value'] != $newvalue)
					{
						vB::getDatastore()->setOption('languageid', $newvalue, false);
						require_once(DIR . '/includes/adminfunctions_language.php');
						build_language($newvalue);
					}
				}
				break;

				case 'cpstylefolder':
				{
					$admindm =& datamanager_init('Admin', $vbulletin, vB_DataManager_Constants::ERRTYPE_CP);

					$admindm->set_existing(vB::getCurrentSession()->fetch_userinfo());
					$admindm->set('cssprefs', $newvalue);
					$admindm->save();
					unset($admindm);
				}
				break;

				case 'attachthumbssize':
				{
					if ($oldsetting['value'] != $newvalue)
					{
						$rebuildstyle = true;
					}
				}

				case 'storecssasfile':
				{
					if (!is_demo_mode() AND $oldsetting['value'] != $newvalue)
					{
						vB::getDatastore()->setOption('storecssasfile', $newvalue, false);
						$rebuildstyle = true;
					}
				}
				break;

				case 'cssfilelocation':
				{
					if (!is_demo_mode() AND $oldsetting['value'] != $newvalue)
					{
						if (vB::getDatastore()->getOption('storecssasfile'))
						{
							vB::getDatastore()->setOption('cssfilelocation', $newvalue, false);
							$rebuildstyle = true;
						}
					}
				}
				break;

				case 'loadlimit':
				{
					update_loadavg();
				}
				break;

				case 'tagcloud_usergroup':
				{
					build_datastore('tagcloud', serialize(''), 1);
				}
				break;

				case 'censorwords':
				case 'codemaxlines':
				case 'url_nofollow':
				case 'url_nofollow_whitelist':
				{
					if ($oldsetting['value'] != $newvalue)
					{
						if (vB::getDatastore()->getOption('templateversion') >= '3.6')
						{
							$assertor->assertQuery('truncateTable', array('table' => 'sigparsed'));
						}
					}

					// Legacy Hook 'admin_options_processing_censorcode' Removed //
				}
				break;

				default:
				{
					// Legacy Hook 'admin_options_processing_build' Removed //
				}
			}


			if (is_demo_mode() AND
				in_array($oldsetting['varname'], array(
					'cache_templates_as_files', 'template_cache_path', 'storecssasfile', 'attachfile', 'usefileavatar',
					'errorlogdatabase', 'errorlogsecurity', 'safeupload', 'tmppath'
				))
			)
			{
				continue;
			}

			$updateSetting = $assertor->assertQuery('setting', array(
				vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_UPDATE,
				'value' => $newvalue,
				'adminperm' => $newAdminPerm,
				vB_dB_Query::CONDITIONS_KEY => array('varname' => $oldsetting['varname']),
			));
		}
	}

	if (!isset($oldsetting))
	{
		return false;
	}

	vB::getDatastore()->build_options();

	if (defined('DEV_AUTOEXPORT') AND DEV_AUTOEXPORT)
	{
		require_once(DIR . '/includes/functions_filesystemxml.php');
		$xml = get_settings_export_xml('vbulletin');
		autoexport_write_file_with_backup(DIR . '/install/vbulletin-settings.xml', $xml);
	}


	//handle changes for cache_templates_as_files and template_cache_path
	//we do it here because there are interactions between them and we don't
	//want to redo the chache changes twice if both are changed.
	$api = vB_Api::instanceInternal('template');
	if ($templatecachepathchanged OR (!is_null($oldtemplatepath) AND !is_null($newtemplatepath)))
	{
		if (vB::getDatastore()->getOption('cache_templates_as_files'))
		{
			if (!is_null($oldtemplatepath))
			{
				//temporarily set the datastore path to the old value to clear it.
				vB::getDatastore()->setOption('template_cache_path', $oldtemplatepath, false);
				$api->deleteAllTemplateFiles();
				vB::getDatastore()->setOption('template_cache_path', $newtemplatepath, false);
			}
			$api->saveAllTemplatesToFile();
		}
		else
		{
			//we we changed directories and the cache is off, delete from the old directory
			if (!is_null($oldtemplatepath))
			{
				vB::getDatastore()->setOption('template_cache_path', $oldtemplatepath, false);
				$api->deleteAllTemplateFiles();
				vB::getDatastore()->setOption('template_cache_path', $newtemplatepath, false);
			}
			//otherwise delete from the current directory.
			else
			{
				$api->deleteAllTemplateFiles();
			}
		}
	}

	if ($rebuildstyle)
	{
		require_once(DIR . '/includes/adminfunctions_template.php');
		print_rebuild_style(-1, '', 1, 0, 0, 0);
	}
	return true;
}

/**
* Attempts to run validation code on a setting
*
* @param	string	Setting varname
* @param	mixed	Setting value
* @param	string	Setting validation code
*
* @return	mixed
*/
function exec_setting_validation_code($varname, $value, $validation_code, $raw_value = false)
{
	if ($raw_value === false)
	{
		$raw_value = $value;
	}

	if ($validation_code != '')
	{
		//this is a terrible hack, but there aren't a lot of options for converting the code
		//text to a function now that create_function is deprecated.  Longer term we should
		//convert this to a function name or class/function pair and move the code out the
		//db and into the filesystem
		eval("\$validation_function = function(&\$data, \$raw_data) { $validation_code };");

		$validation_result = $validation_function($value, $raw_value);

		if ($validation_result === false OR $validation_result === null)
		{
			$valid = fetch_error("setting_validation_error_$varname");

			/* It seems like this used to expect something like 'Could not find xxxxxx' if a phrase was not found,
			but now all the API returns is simply nothing, so I changed the condition. This was the old code for reference, just in case ....
			if ((preg_match('#^Could#i', $valid) AND preg_match("#'" . preg_quote("setting_validation_error_$varname", '#') . "'#i", $valid))) */

			if (!$valid)
			{
				$valid = fetch_error("you_did_not_enter_a_valid_value");
			}
			return $valid;
		}
		else
		{
			return $validation_result;
		}
	}

	return 1;
}

/**
* Validates the provided value of a setting against its datatype
*
* @param	mixed	(ref) Setting value
* @param	string	Setting datatype ('number', 'boolean' or other)
* @param	boolean	Represent boolean with 1/0 instead of true/false
* @param boolean  Query database for username type
*
* @return	mixed	Setting value
*/
function validate_setting_value(&$value, $datatype, $bool_as_int = true, $username_query = true)
{
	switch ($datatype)
	{
		case 'number':
			$value += 0;
			break;

		case 'integer':
			$value = intval($value);
			break;

		case 'arrayinteger':
			$key = array_keys($value);
			$size = sizeOf($key);
			for ($i = 0; $i < $size; $i++)
			{
				$value[$key[$i]] = intval($value[$key[$i]]);
			}
			break;

		case 'arrayfree':
			$key = array_keys($value);
			$size = sizeOf($key);
			for ($i = 0; $i < $size; $i++)
			{
				$value[$key[$i]] = trim($value[$key[$i]]);
			}
			break;

		case 'posint':
			$value = max(1, intval($value));
			break;

		case 'boolean':
			$value = ($bool_as_int ? ($value ? 1 : 0) : ($value ? true : false));
			break;

		case 'bitfield':
			if (is_array($value))
			{
				$bitfield = 0;
				foreach ($value AS $bitval)
				{
					$bitfield += $bitval;
				}
				$value = $bitfield;
			}
			else
			{
				$value += 0;
			}
			break;

		case 'username':
			$value = trim($value);
			if ($username_query)
			{
				$userinfo = vB::getDbAssertor()->getRow('user',
					array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT,
						'username' => htmlspecialchars_uni($value)
					)
				);
				if (empty($value))
				{
					$value =  0;
				}
				else if ($userinfo)
				{
					$value = $userinfo['userid'];
				}
				else
				{
					$value = false;
				}
			}
			break;

		default:
			$value = trim($value);
	}

	return $value;
}

/**
* Returns a list of valid settings for $vbulletin->options['cookiedomain'] based on $_SERVER['HTTP_HOST']
*
* @param	string	$_SERVER['HTTP_HOST']
* @param	string	Phrase to use for blank option
*
* @return	array
*/
function fetch_valid_cookiedomains($http_host, $blank_phrase)
{
	$cookiedomains = array('' => $blank_phrase);
	$domain = $http_host;

	while (substr_count($domain, '.') > 1)
	{
		$dotpos = strpos($domain, '.');
		$newdomain = substr($domain, $dotpos);
		$cookiedomains["$newdomain"] = $newdomain;
		$domain = substr($domain, $dotpos + 1);
	}

	return $cookiedomains;
}

/**
* Returns a list of valid settings for $vbulletin->options['cookiepath'] based on $vbulletin->script
*
* @param	string	$vbulletin->script
*
* @return	array
*/
function fetch_valid_cookiepaths($script)
{
	$cookiepaths = array('/' => '/');
	$curpath = '/';

	$path = preg_split('#/#', substr($script, 0, strrpos($script, '/')), -1, PREG_SPLIT_NO_EMPTY);

	for ($i = 0; $i < sizeof($path) - 1; $i++)
	{
		$curpath .= "$path[$i]/";
		$cookiepaths["$curpath"] = $curpath;
	}

	return $cookiepaths;
}


function get_settings_export_xml($product)
{
	$setting = array();
	$settinggroup = array();

	$groups = vB::getDbAssertor()->assertQuery('settinggroup',
		array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT, 'volatile' => 1),
		array('field' => array('displayorder', 'grouptitle'), 'direction' => array(vB_dB_Query::SORT_ASC, vB_dB_Query::SORT_ASC))
	);

	if($groups AND $groups->valid())
	{
		foreach ($groups AS $group)
		{
			$settinggroup["$group[grouptitle]"] = $group;
		}
	}

	$conditions = array('product' => $product);
	if($product == 'vbulletin')
	{
		$conditions['product'] = array('vbulletin', '');
	}

	$sets = vB::getDbAssertor()->select('setting', $conditions, array('field' => array('displayorder', 'varname')));

	if ($sets AND $sets->valid())
	{
		foreach ($sets AS $set)
		{
			$setting["$set[grouptitle]"][] = $set;
		}
	}
	unset($set);

	$xml = new vB_XML_Builder();
	$xml->add_group('settinggroups', array('product' => $product));

	foreach($settinggroup AS $grouptitle => $group)
	{
		$group = $settinggroup["$grouptitle"];

		//allow blank groups for the vbulletin product -- they are placeholders for products to put options in.
		if(!empty($setting["$grouptitle"]) OR ($product=='vbulletin' AND in_array($group['product'], array('vbulletin', ''))))
		{
			if (empty($group['adminperm']))
			{
				$xml->add_group('settinggroup',
				array(
					'name' => htmlspecialchars($group['grouptitle']),
					'displayorder' => $group['displayorder'],
					'product' => $group['product'],)
				);
			}
			else
			{
				$xml->add_group('settinggroup',
				array(
					'name' => htmlspecialchars($group['grouptitle']),
					'displayorder' => $group['displayorder'],
					'product' => $group['product'],
					'adminperm' => $group['adminperm'])
				);
			}

			if (!empty($setting["$grouptitle"]))
			{
				foreach($setting["$grouptitle"] AS $set)
				{
					$arr = array('varname' => $set['varname'], 'displayorder' => $set['displayorder']);

					if ($set['advanced'])
					{
						$arr['advanced'] = 1;
					}
					$xml->add_group('setting', $arr);

					if ($set['datatype'])
					{
						$xml->add_tag('datatype', $set['datatype']);
					}

					if ($set['optioncode'] != '')
					{
						$xml->add_tag('optioncode', $set['optioncode']);
					}

					if ($set['validationcode'])
					{
						$xml->add_tag('validationcode', $set['validationcode']);
					}

					if ($set['defaultvalue'] != '')
					{
						$xml->add_tag('defaultvalue', iif($set['varname'] == 'templateversion', vB::getDatastore()->getOption('templateversion'), $set['defaultvalue']));
					}

					if ($set['blacklist'])
					{
						$xml->add_tag('blacklist', 1);
					}

					if ($set['ispublic'])
					{
						$xml->add_tag('ispublic', 1);
					}

					if (!empty($set['adminperm']))
					{
						$xml->add_tag('adminperm', $set['adminperm']);
					}
					$xml->close_group();
				}
			}
			$xml->close_group();
		}
	}

	$xml->close_group();

	$doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";

	$doc .= $xml->output();
	$xml = null;
	return $doc;
}

/**
* Imports settings from an XML settings file
*
* Call as follows:
* $path = './path/to/install/vbulletin-settings.xml';
* xml_import_settings($xml);
*
* @param	mixed	Either XML string or boolean false to use $path global variable
*/
function xml_import_settings($xml = false)
{
	$vbphrase = vB_Api::instanceInternal('phrase')->fetch(array('please_wait', 'importing_settings'));
	print_dots_start('<b>' . $vbphrase['importing_settings'] . "</b>, $vbphrase[please_wait]", ':', 'dspan');

	$xmlobj = new vB_XML_Parser($xml, $GLOBALS['path']);
	if ($xmlobj->error_no() == 1)
	{
			print_dots_stop();
			print_stop_message2('no_xml_and_no_path');
	}
	else if ($xmlobj->error_no() == 2)
	{
			print_dots_stop();
			print_stop_message('please_ensure_x_file_is_located_at_y', 'vbulletin-settings.xml', $GLOBALS['path']);
	}

	if(!$arr = $xmlobj->parse())
	{
		print_dots_stop();
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}

	if (!$arr['settinggroup'])
	{
		print_dots_stop();
		print_stop_message2('invalid_file_specified');
	}

	$product = (empty($arr['product']) ? 'vbulletin' : $arr['product']);

	// delete old volatile settings and settings that might conflict with new ones...
	vB::getDbAssertor()->assertQuery('vBForum:deleteSettingGroupByProduct', array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_METHOD, 'product' => $product));

	vB::getDbAssertor()->assertQuery('vBForum:deleteSettingByProduct', array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_METHOD, 'product' => $product));

	// run through imported array
	if (!is_array($arr['settinggroup'][0]))
	{
		$arr['settinggroup'] = array($arr['settinggroup']);
	}
	foreach($arr['settinggroup'] AS $group)
	{
		// need check to make sure group product== xml product before inserting new settinggroup
		if (empty($group['product']) OR $group['product'] == $product)
		{
			// insert setting group
			/*insert query*/
			vB::getDbAssertor()->assertQuery('settinggroup',
				array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_INSERTIGNORE,
					'grouptitle' => $group['name'],
					'displayorder' => $group['displayorder'],
					'volatile' => 1,
					'product' => $product,
					'adminperm' => $group['adminperm'],
				)
			);
		}

		// build insert query for this group's settings
		$qBits = array();
		if (isset($group['setting']))
		{
			if (!is_array($group['setting'][0]))
			{
				$group['setting'] = array($group['setting']);
			}
			foreach($group['setting'] AS $setting)
			{
				$newvalue = vB::getDatastore()->getOption($setting['varname']);

				if ($newvalue === null)
				{
					$newvalue = $setting['defaultvalue'];
				}
				if (!defined('UPGRADE_COMPAT'))
				{
					$qBits[] = array($setting['varname'], $group['name'], trim($newvalue),
					trim($setting['defaultvalue']), trim($setting['datatype']), $setting['optioncode'],
					$setting['displayorder'], $setting['advanced'], 1, $setting['validationcode'], $setting['blacklist'],
					$product, $setting['ispublic'], $setting['adminperm']);
				}
				else
				{
					$qBits[] = array($setting['varname'], $group['name'], trim($newvalue),
					trim($setting['defaultvalue']), trim($setting['datatype']), $setting['optioncode'], $setting['displayorder'],
					$setting['advanced'], 1, $setting['ispublic'], $setting['adminperm']);
				}

			}

			if (!defined('UPGRADE_COMPAT'))
			{
				$fieldsArray = array('varname', 'grouptitle', 'value', 'defaultvalue', 'datatype', 'optioncode',
					'displayorder', 'advanced', 'volatile', 'validationcode', 'blacklist', 'product', 'ispublic', 'adminperm');
			}
			else
			{
				$fieldsArray = array('varname', 'grouptitle', 'value', 'defaultvalue', 'datatype',
					'optioncode', 'displayorder', 'advanced', 'volatile', 'ispublic', 'adminperm');
			}
			/*insert query*/
			$insertSettings = vB::getDbAssertor()->assertQuery('setting',
				array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_MULTIPLEINSERT,
					vB_dB_Query::FIELDS_KEY => $fieldsArray,
					vB_dB_Query::VALUES_KEY => $qBits
				)
			);
		}
	}

	// rebuild the options array
	vB::getDatastore()->build_options();

	// stop the 'dots' counter feedback
	print_dots_stop();

}

/**
* Restores a settings backup from an XML file
*
* Call as follows:
* $path = './path/to/install/vbulletin-settings.xml';
* xml_import_settings($xml);
*
* @param	mixed	Either XML string or boolean false to use $path global variable
* @param bool	Ignore blacklisted settings
*/
function xml_restore_settings($xml = false, $blacklist = true)
{
	$newsettings = array();

	$vbphrase = vB_Api::instanceInternal('phrase')->fetch(array('please_wait', 'importing_settings'));
	print_dots_start('<b>' . $vbphrase['importing_settings'] . "</b>, $vbphrase[please_wait]", ':', 'dspan');

	$xmlobj = new vB_XML_Parser($xml, $GLOBALS['path']);
	if ($xmlobj->error_no() == 1)
	{
			print_dots_stop();
			print_stop_message2('no_xml_and_no_path');
	}
	else if ($xmlobj->error_no() == 2)
	{
			print_dots_stop();
			print_stop_message('please_ensure_x_file_is_located_at_y', 'vbulletin-settings.xml', $GLOBALS['path']);
	}

	if(!$newsettings = $xmlobj->parse())
	{
		print_dots_stop();
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}


	if (!$newsettings['setting'])
	{
		print_dots_stop();
		print_stop_message2('invalid_file_specified');
	}

	$product = (empty($newsettings['product']) ? 'vbulletin' : $newsettings['product']);

	$db = vB::getDbAssertor();
	foreach(get_xml_list($newsettings['setting']) AS $setting)
	{
		// Loop to update all the settings
		$conditions = array(
			'varname' => $setting['varname'],
			'product' => $product
		);
		if ($blacklist)
		{
			$conditions['blacklist'] = 0;
		}

		//if this is nothing but whitespace we just set to blank string.
		//we might want to trim all values, but I'm not sure that's a great idea.
		//note that trim(0) is a string so this won't do anything strange with
		//zero values.
		if (trim($setting['value']) == '')
		{
			$setting['value'] = '';
		}

		$db->assertQuery('setting', array(
			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_UPDATE,
			'value' => $setting['value'],
			vB_dB_Query::CONDITIONS_KEY => $conditions
		));
	}

	unset($newsettings);

	// rebuild the options array
	vB::getDatastore()->build_options();

	// stop the 'dots' counter feedback
	print_dots_stop();

}

/**
* Fetches an array of style titles for use in select menus
*
* @param	string	Prefix for titles
* @param	boolean	Display top level style?
*
* @return	array
*/
function fetch_style_title_options_array($titleprefix = '', $displaytop = false)
{
	$stylecache = vB_Library::instance('Style')->fetchStyles(false, false);
	$out = array();

	foreach($stylecache AS $style)
	{
		$out["$style[styleid]"] = $titleprefix . construct_depth_mark($style['depth'], '--', iif($displaytop, '--', '')) . " $style[title]";
	}

	return $out;
}

/**
* Fetches information about GD
*
* @return	array
*/
function fetch_gdinfo()
{
	$gdinfo = array();

	if (function_exists('gd_info'))
	{
		$gdinfo = gd_info();
	}
	else if (function_exists('phpinfo') AND function_exists('ob_start'))
	{
		if (@ob_start())
		{
			eval('@phpinfo();');
			$info = @ob_get_contents();
			@ob_end_clean();
			preg_match('/GD Version[^<]*<\/td><td[^>]*>(.*?)<\/td><\/tr>/si', $info, $version);
			preg_match('/FreeType Linkage[^<]*<\/td><td[^>]*>(.*?)<\/td><\/tr>/si', $info, $freetype);
			$gdinfo = array(
				'GD Version'       => $version[1],
				'FreeType Linkage' => $freetype[1],
			);
		}
	}

	if (empty($gdinfo['GD Version']))
	{
		$vbphrase = vB_Api::instanceInternal('phrase')->fetch(array('n_a'));
		$gdinfo['GD Version'] = $vbphrase['n_a'];
	}
	else
	{
		$gdinfo['version'] = preg_replace('#[^\d\.]#', '', $gdinfo['GD Version']);
	}

	if (preg_match('#with (unknown|freetype|TTF)( library)?#si', trim($gdinfo['FreeType Linkage']), $freetype))
	{
		$gdinfo['freetype'] = $freetype[1];
	}

	return $gdinfo;
}

/**
* Fetches an array describing the bits in the requested bitfield
*
* @param	string	Represents the array key required... use x|y|z to fetch ['x']['y']['z']
*
* @return	array	Reference to the requested array from includes/xml/bitfield_{product}.xml
*/
function &fetch_bitfield_definitions($string)
{
	static $bitfields = null;

	if ($bitfields === null)
	{
		require_once(DIR . '/includes/class_bitfield_builder.php');
		$bitfields = vB_Bitfield_Builder::return_data();
	}

	$keys = "['" . implode("']['", preg_split('#\|#si', $string, -1, PREG_SPLIT_NO_EMPTY)) . "']";

	eval('$return =& $bitfields' . $keys . ';');

	return $return;
}

/**
* Attempts to fetch the text of a phrase from the given key.
* If the phrase is not found, the key is returned.
*
* @param	string	Phrase key
*
* @return	string
*/
function fetch_phrase_from_key($phrase_key)
{
	$vbphrase = vB_Api::instanceInternal('phrase')->fetch(array($phrase_key));
	return (isset($vbphrase["$phrase_key"])) ? $vbphrase["$phrase_key"] : $phrase_key;
}

/**
* Returns an array of options and phrase values from a piped list
* such as 0|no\n1|yes\n2|maybe
*
* @param	string	Piped data
*
* @return	array
*/
function fetch_piped_options($piped_data)
{
	$options = array();

	$option_lines = preg_split("#(\r\n|\n|\r)#s", $piped_data, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($option_lines AS $option)
	{
		if (preg_match('#^([^\|]+)\|(.+)$#siU', $option, $option_match))
		{
			$option_text = explode('(,)', $option_match[2]);
			foreach (array_keys($option_text) AS $idx)
			{
				$option_text["$idx"] = fetch_phrase_from_key(trim($option_text["$idx"]));
			}
			$options["$option_match[1]"] = implode(', ', $option_text);
		}
	}

	return $options;
}

/**
 * Called by exec_setting_validation_code(), verifies if this forum can change their 'logintype' setting
 * to the requested value.
 * @param	int    $data    Integer (or numeric string, note no hex) value 0-2, where:
 * 			                   0|login_only_email
 * 			                   1|login_only_username
 * 			                   2|login_both
 *
 * @return	mixed	Boolean true if validated without issues, String error message if validation failed.
 */
function validate_setting_logintype($data)
{
	/*
		See core/clientscripts/vbulletin_settings_validate.js's handle_validation() function.
		If you return anything (string, boolean, integer) that can loosely match '1', that'll be a success.
		So a string (that's not literal "1") will be considered an error, and it'll be displayed by JS.

		Loose equality makes me nervous, but I'll return boolean not integer on success just to be consistent with
		some other settings validation code I've seen.
	 */
	$data = intval($data);
	switch($data)
	{
		case 1:
			/*
				Username is the default, & we're not gonna have dupe usernames. They should always be able to use usernames for login.
			 */
			return true;
			break; // code standards.
		case 0:
			/*
				Here, we need to make sure that using email is OK.
			 */
			$requireuniqueemail = vB::getDatastore()->getOption('requireuniqueemail');
			if (!$requireuniqueemail)
			{
				return fetch_error('setting_validation_error_logintype_requireuniqueemail_conflict');
			}


			$duplicateEmails = vB::getDbAssertor()->getRows('vBAdminCP:checkDuplicateEmails');
			$sharedEmailsCount = count($duplicateEmails);
			if ($sharedEmailsCount > 0)
			{
				return fetch_error('setting_validation_error_logintype_nonunique_emails_found', $sharedEmailsCount);
			}

			return true;
			break;

		case 2:
			/*
				So here, some users might be "sharing emails" (that is 1 person has multiple users).
				In such a case, using an email is not really defined behavior FOR THAT USER, but we expect that forums using this setting & allowing
				multiple usernames per email is an extreme edge case, so we're leaving it to the end user to be smart enough to know to use a username
				and not an email to log-in if they're the unicorn that has multiple usernames per email on a forum that allowed this setting.

				Leaving this separate from the case 1 for context reasons.
			 */
			return true;
			break;
		default:
			// if they're not in the 0-2 range, something done trucked up. Let the usual error handler handle it...
			return false;
			break;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101123 $
|| #######################################################################
\*=========================================================================*/
