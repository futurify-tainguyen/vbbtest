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
 * vB_Library_Worker
 *
 * @package vBLibrary
 * @access public
 */

class vB_Library_Worker extends vB_Library
{
	/*
		Used to offload time-consuming tasks from the current connection (usually the main
		connection from browser) to avoid blocking subsequent requests.
	 */

	// String This forum's URL. Used to spawn a child thread to offload
	//	FCM requests from the initial request.
	protected $my_url_prefix = '';

	/**
	 * Constructor
	 *
	 */
	protected function __construct()
	{
		$options = vB::getDatastore()->getValue('options');

		if(!empty($options['frontendurl']))
		{
			$this->my_url_prefix = $options['frontendurl'] . "/worker/";
		}

		return true;
	}

	public function testWorkerConnection()
	{
		$check = $this->callWorker("test");
		$decoded = array(
			'error' => "unknown error",
		);
		if (!empty($check['body']))
		{
			$decoded = json_decode($check['body'], true);
		}

		return $decoded;
	}

	public function callWorker($action, $postData = array())
	{
		if (empty($this->my_url_prefix))
		{
			return array(
				'error' => "missing_my_url",
			);
		}
		if (empty($action))
		{
			return array(
				'error' => "missing_action",
			);
		}
		$action = ltrim($action, '/');
		$url = $this->my_url_prefix . $action;

		$httpHeaders = array(
			'Content-Type: application/x-www-form-urlencoded',
		);

		$postFields = http_build_query($postData);

		/*
			Delegate task to an offshoot connection.
			This is to avoid incurring the processing time for the FCMs
			(mostly the wait time for the curl request to the google server
			for a number of FCMs) blocking subsequent AJAX requests due
			to "Connection: Keep-Alive".
		 */
		$vurl = new vB_vURL();
		$vurl->set_option(VURL_URL, $url);
		$vurl->set_option(VURL_USERAGENT, 'vBulletin/' . SIMPLE_VERSION);
		$vurl->set_option(VURL_POST, 1);
		$vurl->set_option(VURL_POSTFIELDS, $postFields);
		$vurl->set_option(VURL_HTTPHEADER, $httpHeaders);
		$vurl->set_option(VURL_RETURNTRANSFER, 1); // We want to examine the return, not output it directly.
		$vurl->set_option(VURL_HEADER, 1); // We want the headers to examine as well.
		$vurl->set_option(VURL_TIMEOUT, 3); // Timeout of 3s
		$vurl->set_option(VURL_CLOSECONNECTION, 1);

		$result = $vurl->exec();

		return $result;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
