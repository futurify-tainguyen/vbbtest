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

class vB_vURL_cURL
{
	use vB_Trait_NoSerialize;

	/**
	* String that holds the cURL callback data
	*
	* @var	string
	*/
	var $response_text = '';

	/**
	* String that holds the cURL callback data
	*
	* @var	string
	*/
	var $response_header = '';

	/**
	* cURL Handler
	*
	* @var	resource
	*/
	var $ch = null;

	/**
	* vB_vURL object
	*
	* @var	object
	*/
	var $vurl = null;

	/**
	* Filepointer to the temporary file
	*
	* @var	resource
	*/
	var $fp = null;

	/**
	* Length of the current response
	*
	* @var	integer
	*/
	var $response_length = 0;

	/**
	* Private variable when we request headers. Values are one of VURL_STATE_* constants.
	*
	* @var	int
	*/
	var $__finished_headers = VURL_STATE_HEADERS;

	/**
	* If the current result is when the max limit is reached
	*
	* @var	integer
	*/
	var $max_limit_reached = false;

	/**
	* Constructor
	*
	* @param	object	Instance of a vB_vURL Object
	*/
	public function __construct(&$vurl)
	{
		if (!is_a($vurl, 'vB_vURL'))
		{
			throw new Exception('Direct Instantiation of ' . __CLASS__ . ' prohibited.');
		}
		$this->vurl =& $vurl;
	}

	/**
	* Callback for handling headers
	*
	* @param	resource	cURL object
	* @param	string		Request
	*
	* @return	integer		length of the request
	*/
	public function curl_callback_header(&$ch, $string)
	{
		if (trim($string) !== '')
		{
			$this->response_header .= $string;
		}
		return strlen($string);
	}

	/**
	* Callback for handling the request body
	*
	* @param	resource	cURL object
	* @param	string		Request
	*
	* @return	integer		length of the request
	*/
	public function curl_callback_response(&$ch, $response)
	{
		$chunk_length = strlen($response);

		/* We receive both headers + body */
		if ($this->vurl->bitoptions & VURL_HEADER)
		{
			if ($this->__finished_headers != VURL_STATE_BODY)
			{
				if ($this->vurl->bitoptions & VURL_FOLLOWLOCATION AND preg_match('#(?<=\r\n|^)Location:#i', $response))
				{
					$this->__finished_headers = VURL_STATE_LOCATION;
				}

				if ($response === "\r\n")
				{
					if ($this->__finished_headers == VURL_STATE_LOCATION)
					{
						// found a location -- still following it; reset the headers so they only match the new request
						$this->response_header = '';
						$this->__finished_headers = VURL_STATE_HEADERS;
					}
					else
					{
						// no location -- we're done
						$this->__finished_headers = VURL_STATE_BODY;
					}
				}

				return $chunk_length;
			}
		}

		// no filepointer and we're using or about to use more than 100k
		if (!$this->fp AND $this->response_length + $chunk_length >= 1024*100)
		{
			if ($this->fp = @fopen($this->vurl->tmpfile, 'wb'))
			{
				fwrite($this->fp, $this->response_text);
				unset($this->response_text);
			}
		}

		if ($this->fp AND $response)
		{
			fwrite($this->fp, $response);
		}
		else
		{
			$this->response_text .= $response;

		}

		$this->response_length += $chunk_length;

		if (!empty($this->vurl->options[VURL_MAXSIZE]) AND $this->response_length > $this->vurl->options[VURL_MAXSIZE])
		{
			$this->max_limit_reached = true;
			$this->vurl->set_error(VURL_ERROR_MAXSIZE);
			return false;
		}

		return $chunk_length;
	}

	/**
	* Clears all previous request info
	*/
	public function reset()
	{
		$this->response_text = '';
		$this->response_header = '';
		$this->response_length = 0;
		$this->__finished_headers = VURL_STATE_HEADERS;
		$this->max_limit_reached = false;
		$this->closeTempFile();
	}

	/**
	* Performs fetching of the file if possible
	*
	* @return	integer		Returns one of two constants, VURL_NEXT or VURL_HANDLED
	*/
	public function exec()
	{
		$urlinfo = @vB_String::parseUrl($this->vurl->options[VURL_URL]);

		if(!$this->validateUrl($urlinfo))
		{
			return VURL_NEXT;
		}

		if (!function_exists('curl_init') OR ($this->ch = curl_init()) === false)
		{
			return VURL_NEXT;
		}


		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->vurl->options[VURL_TIMEOUT]);
		if (!empty($this->vurl->options[VURL_CUSTOMREQUEST]))
		{
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->vurl->options[VURL_CUSTOMREQUEST]);
		}
		else if ($this->vurl->bitoptions & VURL_POST)
		{
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->vurl->options[VURL_POSTFIELDS]);
		}
		else
		{
			curl_setopt($this->ch, CURLOPT_POST, 0);
		}
		curl_setopt($this->ch, CURLOPT_HEADER, ($this->vurl->bitoptions & VURL_HEADER) ? 1 : 0);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->vurl->options[VURL_HTTPHEADER]);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, ($this->vurl->bitoptions & VURL_RETURNTRANSFER) ? 1 : 0);
		if ($this->vurl->bitoptions & VURL_NOBODY)
		{
			curl_setopt($this->ch, CURLOPT_NOBODY, 1);
		}

		//never use CURLOPT_FOLLOWLOCATION -- we need to make sure we are as careful with the
		//urls returned from the server as we are about the urls we initially load.
		//we'll loop internally up to the recommended tries.
		$redirect_tries = 1;

		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 0);
		if ($this->vurl->bitoptions & VURL_FOLLOWLOCATION)
		{
			$redirect_tries = $this->vurl->options[VURL_MAXREDIRS];
		}

		//sanity check to avoid an infinite loop
		if ($redirect_tries < 1)
		{
			$redirect_tries = 1;
		}

		if ($this->vurl->options[VURL_ENCODING])
		{
			// this will work on versions of cURL after 7.10, though was broken on PHP 4.3.6/Win32
			@curl_setopt($this->ch, CURLOPT_ENCODING, $this->vurl->options[VURL_ENCODING]);
		}

		curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, array(&$this, 'curl_callback_response'));
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array(&$this, 'curl_callback_header'));

		if (!($this->vurl->bitoptions & VURL_VALIDSSLONLY))
		{
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		$url = $this->rebuildUrl($urlinfo);

		$redirectCodes = array(301, 302, 307, 308);
		for ($i = $redirect_tries; $i > 0; $i--)
		{
			$isHttps = ($urlinfo['scheme'] == 'https');
			if ($isHttps)
			{
				// curl_version crashes if no zlib support in cURL (php <= 5.2.5)
				$curlinfo = curl_version();
				if (empty($curlinfo['ssl_version']))
				{
					curl_close($this->ch);
					return VURL_NEXT;
				}
			}

			$result = $this->execCurl($url, $isHttps);

			//if we don't have another iteration of the loop to go, skip the effort here.
			if (($i > 1) AND in_array(curl_getinfo($this->ch, CURLINFO_HTTP_CODE), $redirectCodes))
			{
				$url = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL);
				$urlinfo = @vB_String::parseUrl($url);

				if(!$this->validateUrl($urlinfo))
				{
					$this->closeTempFile();
					return VURL_NEXT;
				}
				$url = $this->rebuildUrl($urlinfo);
			}
			else
			{
				//if we don't have a redirect, skip the loop
				break;
			}
		}

		//if we are following redirects and still have a redirect code, its because we hit our limit without finding a real page
		//we want the fallback code to mimic the behavior of curl in this case
		if (($this->vurl->bitoptions & VURL_FOLLOWLOCATION) && in_array(curl_getinfo($this->ch, CURLINFO_HTTP_CODE), $redirectCodes))
		{
			$this->closeTempFile();
			return VURL_NEXT;
		}

		//close the connection and clean up the file.
		curl_close($this->ch);
		$this->closeTempFile();

		if ($result !== false OR (!$this->vurl->options[VURL_DIEONMAXSIZE] AND $this->max_limit_reached))
		{
			return VURL_HANDLED;
		}

		return VURL_NEXT;
	}


	private function closeTempFile()
	{
		if ($this->fp)
		{
			fclose($this->fp);
			$this->fp = null;
		}
	}

	/**
	 *	Actually load the url from the interweb
	 *	@param string $url
	 *	@params boolean $isHttps
	 *
	 *	@return string|false The result of curl_exec
	 */
	private function execCurl($url, $isHttps)
	{
		$this->reset();
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$result = curl_exec($this->ch);

		//curle_ssl_cacert problem with the ca cert (path? access rights?)
		if ($isHttps AND $result === false AND curl_errno($this->ch) == '60')
		{
			curl_setopt($this->ch, CURLOPT_CAINFO, DIR . '/includes/cacert.pem');
			$result = curl_exec($this->ch);
		}

		return $result;
	}

	/**
	 *	Rebuild the a url from the info components
	 *
	 *	This ensures that we know for certain that the url we validated
	 *	is the the one that we are fetching.  Due to bugs in parse_url
	 *	it's possible to slip something through the validation function
	 *	because it appears in the wrong component.  So we validate the
	 *	hostname that appears in the array but the actual url will be
	 *	interpreted differently by curl -- for example:
	 *
	 *	http://127.0.0.1:11211#@orange.tw/xxx
	 *
	 *	The host name is '127.0.0.1' and port is 11211 but parse_url will return
	 *	host orange.tw and no port value.
	 *
	 *	the expectation is that the values passed to this function passed validateUrl
	 *
	 *	@param $urlinfo -- The parsed url info from vB_String::parseUrl -- scheme, port, host
	 */
	private function rebuildUrl($urlinfo)
	{
		$url = '';

		$url .= $urlinfo['scheme'];
		$url .= '://';

		$url .= $urlinfo['host'];

		//note that we intentionally skip the port here.  We *only* want to use
		//the default port for the scheme ever.  There is no point is setting it
		//explicitly.  We also deliberately strip username/password data if passed.
		//That's far more likely to be an attempt to hack than it is a legitimate
		//url to fetch.
		if (!empty($urlinfo['path']))
		{
			$url .= $urlinfo['path'];
		}

		if (!empty($urlinfo['query']))
		{
			$url .= '?';
			$url .= $urlinfo['query'];
		}

		//not sure if this is needed since it shouldn't get passed to the
		//server.  But it's harmless and it feels like we should attempt
		//to preserve the original as much as is possible.
		if (!empty($urlinfo['fragement']))
		{
			$url .= '#';
			$url .= $urlinfo['fragement'];
		}

		return $url;
	}

	/**
	 *	Determine if the url is safe to load
	 *
	 *	@param $urlinfo -- The parsed url info from vB_String::parseUrl -- scheme, port, host
	 * 	@return boolean
	 */
	private function validateUrl($urlinfo)
	{
		// VBV-11823, only allow http/https schemes
		if (!isset($urlinfo['scheme']) OR !in_array(strtolower($urlinfo['scheme']), array('http', 'https')))
		{
			return false;
		}

		// VBV-11823, do not allow localhost and 127.0.0.0/8 range by default
		if (!isset($urlinfo['host']) OR preg_match('#localhost|127\.(\d)+\.(\d)+\.(\d)+#i', $urlinfo['host']))
		{
			return false;
		}

		if (empty($urlinfo['port']))
		{
			if ($urlinfo['scheme'] == 'https')
			{
				$urlinfo['port'] = 443;
			}
			else
			{
				$urlinfo['port'] = 80;
			}
		}

		// VBV-11823, restrict detination ports to 80 and 443 by default
		// allow the admin to override the allowed ports in config.php (in case they have a proxy server they need to go to).
		$config = vB::getConfig();
		$allowedPorts = isset($config['Misc']['uploadallowedports']) ? $config['Misc']['uploadallowedports'] : array();
		if (!is_array($allowedPorts))
		{
			$allowedPorts = array(80, 443, $allowedPorts);
		}
		else
		{
			$allowedPorts = array_merge(array(80, 443), $allowedPorts);
		}

		if (!in_array($urlinfo['port'], $allowedPorts))
		{
			return false;
		}

		return true;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 100353 $
|| #######################################################################
\*=========================================================================*/
