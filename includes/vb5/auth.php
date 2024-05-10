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

/**
 * Authentication/login related methods
 */
class vB5_Auth
{
	/**
	 * Sets cookies needed for authentication
	 *
	 * @param	array	$loginInfo - array of information returned from
	 *			the user::login api method
	 */
	public static function setLoginCookies(array $loginInfo, $loginType, $remember)
	{
		vB5_Cookie::set('sessionhash', $loginInfo['sessionhash'], 0, true);

		if ($loginType === 'cplogin')
		{
			vB5_Cookie::set('cpsession', $loginInfo['cpsession'], 0, true);
		}

		// in frontend we set these cookies only if rememberme is on
		if ($remember)
		{
			self::setRememberMeCookies($loginInfo['password'], $loginInfo['userid'], 365);
		}
	}

	public static function setRememberMeCookies($rememberMeToken, $userid, $expires = 30)
	{
		vB5_Cookie::set('password', $rememberMeToken, $expires, true);
		vB5_Cookie::set('userid', $userid, $expires, true);
	}

	/**
	 * Redirects the user back to where they were after logging in
	 */
	public static function doLoginRedirect()
	{
		$url = '';
		if (isset($_POST['url']) && $_POST['url'])
		{
			$url = base64_decode(trim($_POST['url']));
		}

		if ($url)
		{
			$parse = parse_url($url);
			if(
				!$parse OR
				empty($parse['scheme']) OR
				($parse['scheme'] != 'http' AND $parse['scheme'] != 'https')
			)
			{
				$url = NULL;
			}
		}

		$application = vB5_ApplicationAbstract::instance();
		if (!$url OR strpos($url, '/auth/') !== false OR strpos($url, '/register') !== false OR !$application->allowRedirectToUrl($url))
		{
			$url = vB5_Template_Options::instance()->get('options.frontendurl');
		}

		if (isset($_POST['associatefb']))
		{
			$joinchar = (strpos($url, '?') !== false ? '&' : '?');
			$url = $url . $joinchar . 'dofbredirect=1';
		}

		$templater = new vB5_Template('login_redirect');
		$templater->register('url', filter_var($url, FILTER_SANITIZE_STRING));
		echo $templater->render();
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
