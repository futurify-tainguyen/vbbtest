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
 * vB_Api_Vb4_visitormessage
 *
 * @package vBApi
 * @access public
 */
class vB_Api_Vb4_visitormessage extends vB_Api
{
	public function message($message, $userid)
	{
		$cleaner = vB::getCleaner();
		$message = $cleaner->clean($message, vB_Cleaner::TYPE_STR);
		$userid = $cleaner->clean($userid, vB_Cleaner::TYPE_STR);

		$parentid = vB_Api::instanceInternal('node')->fetchVMChannel();
		$data = array(
			'title' => '(Untitled)',
			'parentid' => $parentid,
			'channelid' => '',
			'nodeid' => '',
			'setfor' => $userid,
			'rawtext' => $message,
		);
		$result = vB_Api::instanceInternal('content_text')->add($data, array('wysiwyg' => false));
		if (!empty($result['errors'])) {
			return array('response' => array('postpreview' => array('invalidid')));
		}
		return array('response' => array('errormessage' => array('visitormessagethanks')));
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
