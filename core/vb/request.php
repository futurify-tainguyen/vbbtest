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

abstract class vB_Request
{
	use vB_Trait_NoSerialize;

	/**
	 * UNIX timestamp at the creation of request
	 *
	 * @var int
	 */
	protected $timeNow;

	protected $ipAddress;
	protected $altIp;
	protected $sessionHost;
	protected $userAgent;
	protected $referrer;

	protected $languageid = 0;

	/**
	 * @var string
	 */
	protected $sessionClass; // this property is set by each subclass
	/**
	 *
	 * @var vB_Session
	 */
	protected $session = NULL;

	// This constructor can only be used by subclasses
	protected function __construct()
	{
		/* It seems that vB_Request can be called multiple times,
		so we check if SESSION_HOST has already been defined.
		Since SESSION_HOST, IPADDRESS & ALT_IP are all created together
		its safe to assume if one exists, they all do (and vice versa) */
		if (!defined('SESSION_HOST'))
		{
			// Get initial stuff
			$proxy = false;
			$webip = $this->fetchIp();
			$altip = $this->fetchAltIp();

			$this->config = vB::getConfig();

			if (isset($this->config['Misc']['proxyiplist']))
			{
				// Get the defined proxy list
				$proxylist = array_map('trim', explode(',', $this->config['Misc']['proxyiplist']));

				// Check all the entries
				foreach ($proxylist AS $proxyip)
				{
					if (strtolower($proxyip) == 'all' // Any IP allowed, nothing more to check.
					OR $webip == $proxyip // We matched our current IP, thats good enough.
					OR (
						substr($proxyip, -1) == '*' // A wildcard was defined, so lets check it.
						AND substr($webip, 0, strlen($proxyip)-1) == substr($proxyip, 0, -1)
						)
					)
					{
						$proxy = true;

						if (isset($this->config['Misc']['proxyipheader'])
						AND isset($_SERVER[$this->config['Misc']['proxyipheader']]))
						{
							$tempip = $_SERVER[$this->config['Misc']['proxyipheader']];
							if ($this->filter_ip($tempip))
							{
								$altip = $tempip;
							}
						}

						break; // Our work is done, its break time.
					}
				}
			}

			if ($proxy)
			{
				define('ALT_IP', $webip);
				define('IPADDRESS', $altip);
			}
			else
			{
				define('IPADDRESS', $webip);
				define('ALT_IP', $altip);
			}

			$this->altIp = ALT_IP;
			$this->ipAddress = IPADDRESS;
			$this->sessionHost = substr(IPADDRESS, 0, 15); // IPv4 Only

			define('SESSION_HOST', $this->sessionHost);
		}
		else
		{
			$this->altIp = ALT_IP;
			$this->ipAddress = IPADDRESS;
			$this->sessionHost = SESSION_HOST;
		}

		// define some useful contants related to environment
		if (!isset($_SERVER['HTTP_USER_AGENT']))
		{
			$_SERVER['HTTP_USER_AGENT'] = 'vBulletin';
		}

		if (empty($this->userAgent))
		{
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}

		if (!defined('USER_AGENT'))
		{
			define('USER_AGENT', $this->userAgent);
		}

		if (empty($this->referrer))
		{
			$this->referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		}

		if (!defined('REFERRER'))
		{
			define('REFERRER', $this->referrer);
		}

		$this->timeNow = time();
	}


	/**
	* Validate the IP address (both ipv4 & ipv6)
	*
	* @return	string
	*/
	public function filter_ip($ip)
	{
		return filter_var($ip, FILTER_VALIDATE_IP);
	}

	/**
	 * Returns the name of session class associated to the request
	 * @return string
	 */
	public function getSessionClass()
	{
		return $this->sessionClass;
	}

	/**
	 * Creates a session based on client input
	 */
	public function createSession()
	{
		// we declare it here and not in subclasses so that it can be used in tests as well
		$sessionClass = $this->sessionClass;

		//func_get_args cannot be used directly as a parameter in php 5.2
		$args =  func_get_args();

		$session = call_user_func_array(array($sessionClass, 'createSession'), $args);
		$this->setSession($session);
	}

	/**
	 *	Handles setting adding a newly created session to the request object
	 *
	 * 	Adds the session to the request
	 *	Sets the session language to the request langauge if we have one
	 *	Registers the session with the vB class
	 *
	 *	@param vB_Session $session
	 */
	protected function setSession($session)
	{
		$this->session = $session;
		if ($this->languageid)
		{
			$this->session->set('languageid', $this->languageid);
		}
		vB::setCurrentSession($this->session);
	}

	/**
	 *	Creates a session for a specific user
	 *
	 *	Used to create session for a particular user based on the current
	 *	request information.  Useful for creating a session after the user logs in.
	 *	This will overwrite the current Session in this request class and the
	 *	vB current session.
	 *
	 *	@param $userid integer  The user to create the session for.
	 *	@return $session vB_Session The session created.  Not that this will be a subclass
	 *		of the abstract vB_Session Class
	 */
	public function createSessionForUser($userid)
	{
		//refactored from vB_User login code

		//if we currently have a session, get rid of it.
		$currentSession = vB::getCurrentSession();
		if ($currentSession)
		{
			$currentSession->delete();
		}

		$sessionClass = $this->getSessionClass();

		//these are references so we need to set to locals.
		$db = &vB::getDbAssertor();
		$store = &vB::getDatastore();
		$config = &vB::getConfig();
		$this->session = call_user_func(array($sessionClass, 'getSession'), $userid, '', $db, $store, $config);
		vB::setCurrentSession($this->session);
		return $this->session;
	}


	public function getTimeNow()
	{
		return $this->timeNow;
	}

	public function getIpAddress()
	{
		return $this->ipAddress;
	}

	public function getAltIp()
	{
		return $this->altIp;
	}

	public function getSessionHost()
	{
		return $this->sessionHost;
	}

	public function getUserAgent()
	{
		return $this->userAgent;
	}

	public function getReferrer()
	{
		return $this->referrer;
	}

	/**
	 * Fetches a value from $_SERVER or $_ENV
	 *
	 * @param string $name
	 * @return string
	 */
	protected function fetchServerValue($name)
	{
		if (isset($_SERVER[$name]) AND $_SERVER[$name])
		{
			return $_SERVER[$name];
		}

		if (isset($_ENV[$name]) AND $_ENV[$name])
		{
			return $_ENV[$name];
		}

		return false;
	}

	/**
	* Fetches the IP address of the current visitor
	*
	* @return	string
	*/
	protected function fetchIp()
	{
		$main_ip = '';

		if (defined('IPADDRESS'))
		{
			$main_ip = IPADDRESS;
		}
		else if (!empty($this->ipAddress))
		{
			// Unit Tests Preset the IP
			$main_ip = $this->ipAddress;
		}
		else if (isset($_SERVER['REMOTE_ADDR']))
		{
			// Set from a web page, but not from CLI
			$main_ip = $_SERVER['REMOTE_ADDR'];
		}

		return $main_ip;
	}

	/**
	* Fetches an alternate IP address of the current visitor, attempting to detect proxies etc.
	*
	* @return	string
	*/
	protected function fetchAltIp()
	{
		$alt_ip = '';

		if (defined('ALT_IP'))
		{
			$alt_ip = ALT_IP;
		}
		else if (!empty($this->altIp))
		{
			// Unit Tests Preset the IP
			$alt_ip = $this->altIp;
		}
		else
		{
			// Set from a web page, but not from CLI
			if (isset($_SERVER['REMOTE_ADDR']))
			{
				$alt_ip = $_SERVER['REMOTE_ADDR'];
			}

			/* Removed checks for HTTP_FROM & HTTP_CLIENT_IP, these are largely obsolete.
			You can add them back into the config file if you really want to use them still */
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
			{
				// try to avoid using an internal IP address, its probably a proxy
				$ranges = array(
					'10.0.0.0/8' => array(ip2long('10.0.0.0'), ip2long('10.255.255.255')),
					'127.0.0.0/8' => array(ip2long('127.0.0.0'), ip2long('127.255.255.255')),
					'169.254.0.0/16' => array(ip2long('169.254.0.0'), ip2long('169.254.255.255')),
					'172.16.0.0/12' => array(ip2long('172.16.0.0'), ip2long('172.31.255.255')),
					'192.168.0.0/16' => array(ip2long('192.168.0.0'), ip2long('192.168.255.255')),
				);

				foreach ($matches[0] AS $ip)
				{
					$ip_long = ip2long($ip);
					if ($ip_long === false)
					{
						continue;
					}

					$private_ip = false;
					foreach ($ranges AS $range)
					{
						if ($ip_long >= $range[0] AND $ip_long <= $range[1])
						{
							$private_ip = true;
							break;
						}
					}

					if (!$private_ip)
					{
						$alt_ip = $ip;
						break;
					}
				}
			}
		}

		return $alt_ip;
	}

	/**
	* Browser detection system - returns whether or not the visiting browser is the one specified
	*
	* @param	string	Browser name (opera, ie, mozilla, firebord, firefox... etc. - see $is array)
	* @param	float	Minimum acceptable version for true result (optional)
	*
	* @return	boolean
	*/
	public function isBrowser($browser, $version = 0)
	{
		static $is;
		if (!is_array($is))
		{
			$useragent = strtolower($this->getUserAgent()); //strtolower($_SERVER['HTTP_USER_AGENT']);
			$is = array(
				'opera'     => 0,
				'ie'        => 0,
				'mozilla'   => 0,
				'firebird'  => 0,
				'firefox'   => 0,
				'camino'    => 0,
				'konqueror' => 0,
				'safari'    => 0,
				'webkit'    => 0,
				'webtv'     => 0,
				'netscape'  => 0,
				'mac'       => 0
			);

			// detect opera
				# Opera/7.11 (Windows NT 5.1; U) [en]
				# Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.0) Opera 7.02 Bork-edition [en]
				# Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 4.0) Opera 7.0 [en]
				# Mozilla/4.0 (compatible; MSIE 5.0; Windows 2000) Opera 6.0 [en]
				# Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC) Opera 5.0 [en]
			if (strpos($useragent, 'opera') !== false)
			{
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			}

			// detect internet explorer
				# Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Q312461)
				# Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.0.3705)
				# Mozilla/4.0 (compatible; MSIE 5.22; Mac_PowerPC)
				# Mozilla/4.0 (compatible; MSIE 5.0; Mac_PowerPC; e504460WanadooNL)
			if (strpos($useragent, 'msie ') !== false AND !$is['opera'])
			{
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}

			// Detect IE11(+)
				# Mozilla/5.0 (IE 11.0; Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C; rv:11.0)
			if (strpos($useragent, 'trident') !== false AND !$is['opera'] AND !$is['ie'])
			{
				// Trident = IE, So look for rv number
				preg_match('#rv:([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}

			// detect macintosh
			if (strpos($useragent, 'mac') !== false)
			{
				$is['mac'] = 1;
			}

			// detect safari
				# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/74 (KHTML, like Gecko) Safari/74
				# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/51 (like Gecko) Safari/51
				# Mozilla/5.0 (Windows; U; Windows NT 6.0; en) AppleWebKit/522.11.3 (KHTML, like Gecko) Version/3.0 Safari/522.11.3
				# Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1C28 Safari/419.3
				# Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3
			if (strpos($useragent, 'applewebkit') !== false)
			{
				preg_match('#applewebkit/([0-9\.]+)#', $useragent, $regs);
				$is['webkit'] = $regs[1];

				if (strpos($useragent, 'safari') !== false)
				{
					preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
					$is['safari'] = $regs[1];
				}
			}

			// detect konqueror
				# Mozilla/5.0 (compatible; Konqueror/3.1; Linux; X11; i686)
				# Mozilla/5.0 (compatible; Konqueror/3.1; Linux 2.4.19-32mdkenterprise; X11; i686; ar, en_US)
				# Mozilla/5.0 (compatible; Konqueror/2.1.1; X11)
			if (strpos($useragent, 'konqueror') !== false)
			{
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}

			// detect mozilla
				# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.4b) Gecko/20030504 Mozilla
				# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.2a) Gecko/20020910
				# Mozilla/5.0 (X11; U; Linux 2.4.3-20mdk i586; en-US; rv:0.9.1) Gecko/20010611
			if (strpos($useragent, 'gecko') !== false AND !$is['safari'] AND !$is['konqueror'] AND !$is['ie'])
			{
				// See bug #26926, this is for Gecko based products without a build
				$is['mozilla'] = 20090105;
				if (preg_match('#gecko/(\d+)#', $useragent, $regs))
				{
					$is['mozilla'] = $regs[1];
				}

				// detect firebird / firefox
					# Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.3a) Gecko/20021207 Phoenix/0.5
					# Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4b) Gecko/20030516 Mozilla Firebird/0.6
					# Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4a) Gecko/20030423 Firebird Browser/0.6
					# Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.6) Gecko/20040206 Firefox/0.8
				if (strpos($useragent, 'firefox') !== false OR strpos($useragent, 'firebird') !== false OR strpos($useragent, 'phoenix') !== false)
				{
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];

					if ($regs[1] == 'firefox')
					{
						$is['firefox'] = $regs[3];
					}
				}

				// detect camino
					# Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-US; rv:1.0.1) Gecko/20021104 Chimera/0.6
				if (strpos($useragent, 'chimera') !== false OR strpos($useragent, 'camino') !== false)
				{
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}

			// detect web tv
			if (strpos($useragent, 'webtv') !== false)
			{
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}

			// detect pre-gecko netscape
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs))
			{
				$is['netscape'] = "$regs[1].$regs[2]";
			}
		}

		// sanitize the incoming browser name
		$browser = strtolower($browser);
		if (substr($browser, 0, 3) == 'is_')
		{
			$browser = substr($browser, 3);
		}

		// return the version number of the detected browser if it is the same as $browser
		if ($is["$browser"])
		{
			// $version was specified - only return version number if detected version is >= to specified $version
			if ($version)
			{
				if ($is["$browser"] >= $version)
				{
					return $is["$browser"];
				}
			}
			else
			{
				return $is["$browser"];
			}
		}

		// if we got this far, we are not the specified browser, or the version number is too low
		return 0;
	}

	public function getCachePageForGuestTime()
	{
		return vB::getDatastore()->getOption('guestcacheminutes');
	}

	public function getUseEarlyFlush()
	{
		return vB::getDatastore()->getOption('useearlyflush')
			AND !preg_match("#(google|bingbot|yahoo! slurp|facebookexternalhit)#si", $this->getUserAgent())
			AND !vB5_Frontend_ExplainQueries::isActive();
	}

	/**
	 *	These are mostly only meaningful for web requests, but we need to
	 *	keep a consistant interface for requests so provide a trivial
	 *	default here.
	 */
	public function getVbUrlScheme()
	{
		return "http";
	}

	public function getVbHttpHost()
	{
		return '';
	}

	public function getVbUrlPath()
	{
		return '';
	}

	public function getVbUrlQuery()
	{
		return '';
	}

	public function getVbUrlQueryRaw()
	{
		return '';
	}

	public function getVbUrlClean()
	{
		return '';
	}

	public function getVbUrlWebroot()
	{
		return '';
	}

	public function getVbUrlBasePath()
	{
		return '';
	}

	public function getScriptPath()
	{
		return '';
	}

	public function setLanguageid($languageid)
	{
		$this->languageid = $languageid;
	}

	public function getLanguageid()
	{
		return $this->languageid;
	}

}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
