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
 * vB_Api_Vb4_register
 *
 * @package vBApi
 * @access public
 */
class vB_Api_Vb4_login extends vB_Api
{
	/**
	 * Login with facebook logged user
	 *
	 * @param  [string] $signed_request [fb info]
	 * @return [array]                  [response -> errormessage and session params]
	 */
	public function facebook($signed_request, $devicetoken = null)
	{
		$cleaner = vB::getCleaner();
		$signed_request = $cleaner->clean($signed_request, vB_Cleaner::TYPE_STR);

		$user_api = vB_Api::instance('user');
		$loginInfo = $user_api->loginExternal('facebook', array('signedrequest' => $signed_request));

		if (empty($loginInfo) || isset($loginInfo['errors']))
		{
			//the api doesn't allow us to be that specific about our errors here.
			//and the app gets very cranky if the login returns an unexpected error code
			return array('response' => array('errormessage' => array('badlogin_facebook')));
		}

		// Update may throw an exception if devicetoken is longer than allowed by DB.
		try
		{
			// Take care of push notification device token registration (if there is one)
			$updateTokenResult = vB_Library::instance('fcmessaging')->updateDeviceToken($devicetoken);
		}
		catch (Exception $e)
		{
			// todo
		}

		$result = array(
			'session' => array(
				'dbsessionhash' => $loginInfo['login']['sessionhash'],
				'userid' => $loginInfo['login']['userid'],
			),
			'response' => array(
				'errormessage' => array('redirect_login')
			),
		);

		return $result;
	}


	/**
	 * Login. Wraps user.login (because we need to do some mapi specific tasks)
	 *
	 * @param  [string] $signed_request [fb info]
	 * @return [array]                  [response -> errormessage and session params]
	 */
	public function login(
		$vb_login_username,
		$vb_login_password = null,
		$vb_login_md5password = null,
		$vb_login_md5password_utf = null,
		$devicetoken = null,
		$privacyconsent = null
	)
	{
		$userAPI = vB_Api::instanceInternal('user');
		$loginResult = $userAPI->login(
			$vb_login_username,
			$vb_login_password,
			$vb_login_md5password,
			$vb_login_md5password_utf
			// logintype = null
		);

		if (!empty($loginResult['userid']) AND $privacyconsent !== null)
		{
			$userAPI = vB_Api::instance('user');
			if ($privacyconsent)
			{
				$check = $userAPI->save(
					$loginResult['userid'],
					null,
					array('privacyconsent' => 1),
					null,
					null,
					null
				);
			}
			else
			{
				$userinfo = $userAPI->fetchUserinfo();
				/*
					Spec was to proceed with regular login if user has given privacy consent before,
					and to return a "badlogin_no_privacy_consent" error if no consent was given.
					I'll interpret the second condition as either "unknown" or "previously withdrawn consent".
				 */
				if (empty($userinfo['privacyconsent']) OR $userinfo['privacyconsent'] == -1)
				{
					// Log this session out.
					$userAPI->logout();
					// throw exception to get around api hijacking login_login responses.
					// Note that this exception gets caught into vB_Api::map_vb5_errors_to_vb4().
					throw new Exception('badlogin_no_privacy_consent');
				}
			}

		}

		// Update may throw an exception if devicetoken is longer than allowed by DB.
		try
		{
			// Take care of push notification device token registration (if there is one)
			$updateTokenResult = vB_Library::instance('fcmessaging')->updateDeviceToken($devicetoken);
		}
		catch (Exception $e)
		{
			// todo
		}

		// This will be transformed into the output that mobile client expects by VB_Api::map_vb5_output_to_vb4()
		// called by core/api.php
		return $loginResult;
	}

	public function logout()
	{
		// Take care of push notification device token removal.
		// Do it before we lose session.
		vB_Library::instance('fcmessaging')->removeDeviceToken();

		$logoutResult = vB_Api::instanceInternal('user')->logout();


		// This will be transformed into the output that mobile client expects by VB_Api::map_vb5_output_to_vb4()
		// called by core/api.php
		return $logoutResult;
	}
}
/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
