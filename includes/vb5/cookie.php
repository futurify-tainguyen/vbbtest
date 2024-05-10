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

class vB5_Cookie
{
	protected static $enabled = null;
	protected static $cookiePrefix = null;
	protected static $path = null;
	protected static $domain = null;
	protected static $secure = null;

	const TYPE_UINT = 1;
	const TYPE_STRING = 2;

	public static function set($name, $value, $expireDays = 0, $httpOnly = true)
	{
		if (!self::$enabled)
		{
			return;
		}

		if ($expireDays == 0)
		{
			$expire = 0;
		}
		else
		{
			$expire = time() + ($expireDays * 86400);
		}

		$name = self::$cookiePrefix . $name;

		if (!setcookie($name, $value, $expire, self::$path, self::$domain, self::$secure, $httpOnly))
		{
			throw new Exception('Unable to set cookies');
		}
	}

	public static function get($name, $type)
	{

		$name = self::$cookiePrefix . $name;

		$value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;

		switch($type)
		{
			case self::TYPE_UINT:
				$value = intval($value);
				$value = $value < 0 ? 0 : $value;
				break;

			case self::TYPE_STRING:
				$value = strval($value);
				break;

			default:
				throw new Exception('Invalid cookie clean type');
				break;
		}

		return $value;
	}

	public static function delete($name)
	{
		self::set($name, '', -1);
	}

	/**
	 * Deletes all cookies starting with cookiePrefix
	 */
	public static function deleteAll()
	{
		$prefix_length = strlen(self::$cookiePrefix);
		foreach ($_COOKIE AS $key => $val)
		{
			if ($prefix_length>0)
			{
				$index = strpos($key, self::$cookiePrefix);
			}
			else
			{
				$index = 0;
			}
			if ($index == 0 AND $index !== false)
			{
				$key = substr($key, $prefix_length);
				if (trim($key) == '')
				{
					continue;
				}
				// self::set will add the cookie prefix
				self::delete($key);
			}
		}
	}

	public static function isEnabled()
	{
		return self::$enabled;
	}

	public static function loadConfig($options)
	{
		$config = vB5_Config::instance();

		// these could potentially all be config options
		self::$enabled = ($config->cookie_enabled !== false);
		self::$cookiePrefix = $config->cookie_prefix;

		self::$path = $options['cookiepath'];
		self::$domain = $options['cookiedomain'];

		//if the site is on https, set cookies to secure.  Otherwise we can't without breaking things.
		//note that we should not trigger on the current url because
		//a) If we have only the logins on https, that will break the site (login page sets the session cookie
		//	as secure only, nothing else will ever see the session)
		//b) We can't always reliably detect if the current link is https because it can be offloaded to a proxy.
		$frontendurl = $options['frontendurl'];
		self::$secure = (stripos($frontendurl, 'https:') !== false);
	}

	/**
	 * Returns the value for an array stored in a cookie
	 * Ported from functions.php fetch_bbarray_cookie
	 *
	 * @param	string	Name of the cookie
	 * @param	mixed	ID of the data within the cookie
	 *
	 * @return	mixed
	 */
	public static function fetchBbarrayCookie($cookiename, $id)
	{
		$cookieValue = null;
		$cookie = self::get($cookiename, self::TYPE_STRING);
		if ($cookie != '')
		{
			$decodedCookie = json_decode(self::convertBbarrayCookie($cookie), true);
			$cookieValue = empty($decodedCookie["$id"]) ? null : $decodedCookie["$id"];
		}

		return $cookieValue;
	}

	/**
	 * Replaces all those none safe characters so we dont waste space in
	 * array cookie values with URL entities
	 * Ported from functions.php convert_bbarray_cookie
	 *
	 * @param	string	Cookie array
	 * @param	string	Direction ('get' or 'set')
	 *
	 * @return	array
	 */
	protected static function convertBbarrayCookie($cookie, $dir = 'get')
	{
		if ($dir == 'set')
		{
			$cookie = str_replace(array('"', ':', ';'), array('.', '-', '_'), $cookie);
		}
		else
		{
			$cookie = str_replace(array('.', '-', '_'), array('"', ':', ';'), $cookie);
		}
		return $cookie;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
