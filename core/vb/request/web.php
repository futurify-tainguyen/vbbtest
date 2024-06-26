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

class vB_Request_Web extends vB_Request
{

	/*
	 *	The cookie salt constant is deprecated and has been left in place
	 *	only to support existing plugins that might use it.  It should not
	 *	be used in core vb code and plugin developers should change their
	 *	code to use the static variable instead.  This value will be removed
	 *	from a future version of vB  *CODE WHICH USES THE
	 *	CONSTANT IS NOT COMPATIBLE WITH vBCloud*
	 */
	const COOKIE_SALT = "ruT5Pjv6TOXdc4zMGVXdp7GmINV";

	public static $COOKIE_SALT = "ruT5Pjv6TOXdc4zMGVXdp7GmINV";
	protected $vBUrlScheme = 'http';
	protected $vBUrlPath;
	protected $vBUrlQuery;
	protected $vBUrlQueryRaw;
	protected $vBUrlClean;
	protected $vBUrlWebroot;
	protected $vBUrlBasePath;
	protected $vBHttpHost;
	protected $scriptPath;

	public function __construct()
	{
		parent::__construct();

		$this->resolveRequestUrl();

		// TODO: how should we determine this?
		if (!defined('LOCATION_BYPASS'))
		{
			define('LOCATION_BYPASS', 1);
		}

		$this->sessionClass = 'vB_Session_Web';
	}

	/**
	 * Overrides the default cookie salt. Required for vB Cloud.
	 * @param 	string 	$salt
	 */
	public static function setCookieSalt($salt)
	{
		self::$COOKIE_SALT = $salt;
	}

	public function getVbHttpHost()
	{
		return $this->vBHttpHost;
	}

	public function getVbUrlScheme()
	{
		return $this->vBUrlScheme;
	}

	public function getVbUrlPath()
	{
		return $this->vBUrlPath;
	}

	public function getVbUrlQuery()
	{
		return $this->vBUrlQuery;
	}

	public function getVbUrlQueryRaw()
	{
		return $this->vBUrlQueryRaw;
	}

	public function getVbUrlClean()
	{
		return $this->vBUrlClean;
	}

	public function getVbUrlWebroot()
	{
		return $this->vBUrlWebroot;
	}

	public function getVbUrlBasePath()
	{
		return $this->vBUrlBasePath;
	}

	public function getScriptPath()
	{
		return $this->scriptPath;
	}

	/**
	 * Resolves information about the request URL.
	 *
	 * Extracted from class vB_Input_Cleaner
	 */
	// Several constants were removed as they were not referenced in the code. If needed, add a class property
	// todo: remove constants from legacy code
	protected function resolveRequestUrl()
	{
		// Ports which will not be appended to the URL
		$ignore_ports = array(80, 443);
		$config = vB::getConfig();
		$backend_ports = @$config['Misc']['backendports'];
		if (!empty($backend_ports))
		{
			$ignore_ports = array_merge($ignore_ports, $backend_ports);
		}

		// Numerical port this request came from, may be a backend port
		$rawport = 80;

		// Will contain the port portion of the built URL, default empty
		$port = '';

		if (!empty($_SERVER['SERVER_PORT']))
		{
			$rawport = intval($_SERVER['SERVER_PORT']);
			$port = in_array($rawport, $ignore_ports) ? '' : ':' . $rawport;
		}

		// resolve the request scheme
		$isHttps = (($rawport == 443) OR (!empty($_SERVER['HTTPS']) AND ($_SERVER['HTTPS'] !== 'off')));
		$scheme =  $isHttps ? 'https://' : 'http://';

		$host = $this->fetchServerValue('HTTP_HOST');
		$name = $this->fetchServerValue('SERVER_NAME');

		// If host exists use it, otherwise fallback to servername.
		$host = ( !empty($host) ? $host : $name );

		// resolve the query
		$query = ($query = $this->fetchServerValue('QUERY_STRING')) ? '?' . $query : '';

		// resolve the path and query
		if (!($scriptpath = $this->fetchServerValue('REQUEST_URI')))
		{
			if (!($scriptpath = $this->fetchServerValue('UNENCODED_URL')))
			{
				$scriptpath = $this->fetchServerValue('HTTP_X_REWRITE_URL');
			}
		}

		if ($scriptpath)
		{
			// already have the query
			if ($scriptpath)
			{
				$query = '';
			}
		}
		else
		{
			// server hasn't provided a URI, try to resolve one
			if (!$scriptpath = $this->fetchServerValue('PATH_INFO'))
			{
				if (!$scriptpath = $this->fetchServerValue('REDIRECT_URL'))
				{
					if (!($scriptpath = $this->fetchServerValue('URL')))
					{
						if (!($scriptpath = $this->fetchServerValue('PHP_SELF')))
						{
							$scriptpath = $this->fetchServerValue('SCRIPT_NAME');
						}
					}
				}
			}
		}

		// build the URL
		$url = $scheme . $host . '/' . ltrim($scriptpath, '/\\') . $query;

		// store a literal version
		$vbUrl = $url;
		if (!defined('VB_URL'))
		{
			define('VB_URL', $vbUrl);
		}

		$vbUrlRelativePath = '';

		// Set URL info
		$url_info = @vB_String::parseUrl($vbUrl);
		$url_info['path'] = '/' . ltrim($url_info['path'], '/\\');
		$url_info['query_raw'] = (isset($url_info['query']) ? $url_info['query'] : '');
		$url_info['query'] = self::stripSessionhash($url_info['query_raw']);
		$url_info['query'] = trim($url_info['query'], '?&') ? $url_info['query'] : '';

		$url_info['scheme'] = substr($scheme, 0, strlen($scheme)-3);

		/*
			values seen in the wild:

			CGI+suexec:
			SCRIPT_NAME: /vb4/admincp/index.php
			ORIG_SCRIPT_NAME: /cgi-sys/php53-fcgi-starter.fcgi

			CGI #1:
			SCRIPT_NAME: /index.php
			ORIG_SCRIPT_NAME: /search/foo

			CGI #2:
			SCRIPT_NAME: /index.php/search/foo
			ORIG_SCRIPT_NAME: /index.php

		*/

		if (substr(PHP_SAPI, -3) == 'cgi' AND (isset($_SERVER['ORIG_SCRIPT_NAME']) AND !empty($_SERVER['ORIG_SCRIPT_NAME'])))
		{
			if (substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['ORIG_SCRIPT_NAME'])) == $_SERVER['ORIG_SCRIPT_NAME'])
			{
				// cgi #2 above
				$url_info['script'] = $_SERVER['ORIG_SCRIPT_NAME'];
			}
			else
			{
				// cgi #1 and CGI+suexec above
				$url_info['script'] = $_SERVER['SCRIPT_NAME'];
			}
		}
		else
		{
			$url_info['script'] = (isset($_SERVER['ORIG_SCRIPT_NAME']) AND !empty($_SERVER['ORIG_SCRIPT_NAME'])) ? $_SERVER['ORIG_SCRIPT_NAME'] : $_SERVER['SCRIPT_NAME'];
		}
		$url_info['script'] = '/' . ltrim($url_info['script'], '/\\');


		// define constants
		$this->vBUrlScheme = $url_info['scheme'];

		$vBUrlScriptPath = rtrim(dirname($url_info['script']), '/\\') . '/';

		$this->vBUrlPath = urldecode($url_info['path']);
		if (!defined('VB_URL_PATH'))
		{
			define('VB_URL_PATH',        $this->vBUrlPath);
		}

		$this->vBUrlQuery = $url_info['query'] ? $url_info['query'] : '';
		if (!defined('VB_URL_QUERY'))
		{
			define('VB_URL_QUERY',       $this->vBUrlQuery);
		}

		$this->vBUrlQueryRaw = $url_info['query_raw'];
		if (!defined('VB_URL_QUERY_RAW'))
		{
			define('VB_URL_QUERY_RAW',   $this->vBUrlQueryRaw);
		}

		$cleaner = vB::get_cleaner();

		$this->vBUrlClean = $cleaner->xssClean(self::stripSessionhash($vbUrl));
		if (!defined('VB_URL_CLEAN'))
		{
			define('VB_URL_CLEAN',       $this->vBUrlClean);
		}

		$this->vBUrlWebroot = $cleaner->xssClean($this->vBUrlScheme . '://' . $url_info['host'] . $port);

		$this->vBUrlBasePath = $cleaner->xssClean($this->vBUrlScheme . '://' . $url_info['host'] . $port . $vBUrlScriptPath . $vbUrlRelativePath);
		if (!defined('VB_URL_BASE_PATH'))
		{
			define('VB_URL_BASE_PATH',   $this->vBUrlBasePath);
		}

		$this->scriptPath = $cleaner->xssClean($this->addQuery($this->vBUrlPath));

		// legacy constants
		if (!defined('SCRIPT'))
		{
			define('SCRIPT', $_SERVER['SCRIPT_NAME']);
		}

		if (!defined('SCRIPTPATH'))
		{
			define('SCRIPTPATH', $this->scriptPath);
		}

		if (!empty($url_info) AND !empty($url_info['host']))
		{
			$this->vBHttpHost = $url_info['host'];
			if (!defined('VB_HTTP_HOST'))
			{
				define('VB_HTTP_HOST', $this->vBHttpHost);
			}
		}
	}

	/**
	* Strips out the s=gobbledygook& rubbish from URLs
	* Extracted from vB_Input_Cleaner
	*
	* @param	string	The URL string from which to remove the session stuff
	*
	* @return	string
	*/
	public static function stripSessionhash($string)
	{
		$string = preg_replace('/(s|sessionhash)=[a-z0-9]{32}?&?/', '', $string);
		return $string;
	}

	/**
	 * Adds a query string to a path, fixing the query characters.
	 *
	 * @param 	string		The path to add the query to
	 * @param 	string		The query string to add to the path
	 *
	 * @return	string		The resulting string
	 */
	public function addQuery($path, $query = false)
	{
		if (false === $query)
		{
			$query = $this->vBUrlQuery;
		}

		if (!$query OR !($query = trim($query, '?&')))
		{
			return $path;
		}

		return $path . '?' . $query;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
