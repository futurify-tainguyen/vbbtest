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
 * @package vBulletin
 */

/*
 *	Ported from the vB_vURL class.
 *
 * 	Note despite only supporting cUrl (and not intending to support anything else for the forseeable
 * 	future) we don't just convert the calling code to cUrl because this class allows us to set
 * 	defaults to vBulletin specific values, simply the interface by hiding options we don't need,
 * 	and adding logic to get around cUrl bugs/problems.  Most notably adding checking to prevent outgoing
 * 	connections to internal urls (including on redirect) which is a security hazard.  It also makes it
 * 	slightly easier to add additional implemenations down the road by providing an interface that we
 * 	can write to.
 *
 *	This make a large number of changes intended to simplify some
 *	old and overly complex code:
 *	1) We get rid of the "transport" concept.  The old code had a list of "transport" options and would
 *		try them in turn until one suceeded (or the list ran out).  The only one left on the list is curl
 *		so we're just coding for that.  In the unlikely event that we need to provide options, we should
 *		create an abstract base class and subclass implementations like other things and then pick *one*
 *		implementation based on autodetection of what underlying libraries are installed or some kind of
 *		user configuration.  Trying everything is just unnecesarily complicated.
 *
 *	2) Use get/post functions for ease of use.  Having to set *everything* via setOpt is tedious.  To
 *		the extent that the old class had some wierd cover functions that tried to avoid requiring it.
 *
 *	3) Eliminate the "return transfer" option.  We'll largely treat it as if it were true. I'm not sure
 *		everything worked properly if it wasn't and I don't think the code every calls it any other way.
 *
 *	4) Create a consistant return format.  Before if only the headers or the body were requested then
 *		the function would return only those as a scalar.  This means that the caller needs to know what
 *		options are set in order to handle the return or explicitly inspect the return to figure out
 *		what they actually got.
 *
 *	5) vUrl supports returning large requests as a file on disk via the "exec2" function.  We preserve the
 *		"store as a file" switch internally but do not currently return the file.  Instead we follow the
 *		practice of the vUrl "exec" function of loading the file on return from the request function.
 *		We need to sort out how we want to handle this case because I'm not sure that the vUrl case
 *		works correctly under all iterations of HEADERS/NO HEADERS exec/exec2.  Also it's up to the calling
 *		code to figure out if the limit was tripped to storing as a file.
 *
 *		When we implement this option it should be an explicit option.  We may want to consider *always*
 *		returning as a file instead of only for large returns so that the caller can handle all returns
 *		the same way.  (The actual uses of exec2 either ignore the file or immediately save a non file
 *		return to disk).  We may also consider only storing as a file when so requested and passing the
 *		filename as part of the option (instead of to the constructor) so that requests that are never
 *		going to use the tempfile don't need to worry about it.
 *
 */
class vB_Utility_Url
{
	use vB_Utility_Trait_NoSerialize;

	//constants for options.
	const URL = 1;
	const TIMEOUT = 2;
	const POST = 4;
	const HEADER = 8;
	const POSTFIELDS = 16;
	const ENCODING = 32;
	const USERAGENT = 64;
	//const RETURNTRANSFER = 128;
	const HTTPHEADER = 256;

	const CLOSECONNECTION = 1024;
	const FOLLOWLOCATION = 2048;
	const MAXREDIRS = 4096;
	const NOBODY = 8192;
	const CUSTOMREQUEST = 16384;
	const MAXSIZE = 32768;
	const DIEONMAXSIZE = 65536;
	const VALIDSSLONLY = 131072;

	//there used to be more, but they weren't used.
	const ERROR_MAXSIZE = 1;

	//some stuff to keep track of internal state in the callbacks.
	//used to determine if we should log to the file
	//these should be considered private even though PHP doesn't allow that.
	const STATE_HEADERS = 1;
	const STATE_LOCATION = 2;
 	const STATE_BODY = 3;

	//class vars
	private $errror = 0;

	private $bitoptions = 0;
	private $options = array();

	private $allowedports;
	private $tempfilename = '';
	private $response_text = '';
	private $response_header = '';

	private $string;
	private $ch;
	private $tempfilepointer;

	private $response_length = 0;
	private $max_limit_reached = false;

	/**
	* Constructor
	*
	* @param vB_Utility_String $string -- the properly configured string object.
	*	@param array|int $allowedports -- ports in addition to 80 and 443 that we allow outgoing connections to.
	*	@param string $tempfilename -- the tempfile that we will create and use internally for large connections
	*		we don't want to directly access the config to create this filename internally.
	*/
	public function __construct($string, $allowedports, $tempfilename)
	{
		$this->string = $string;
		$this->allowedports = $allowedports;
		$this->tempfilename = $tempfilename;

		$this->reset();
	}

	/**
	 * This deals with the case that people forget to either unlink or move the file.
	 */
	//this behavior is copied from the original vurl implementation.  It's a bit
	//problematic because we call might not be aware that the file we return will
	//magically go away if they don't immediately do something with it.  On the other
	//hand, we don't necesarily want to leave the file around either
	//Leaving the way it was.
	public function __destruct()
	{
		$this->deleteTempFile();
	}

	/**
	* Set Error
	*
	* @param int $errorcode
	*/
	private function setError($errorcode)
	{
		$this->error = $errorcode;
	}

	/**
	* Return Error
	*
	* @return	int errorcode
	*/
	public function getError()
	{
		return $this->error;
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
	* On/Off options
	*
	* @param		integer	one of the option constants
	* @param		mixed		option to set
	*
	*/
	public function setOption($option, $extra)
	{
		switch ($option)
		{
			case self::POST:
			case self::HEADER:
			case self::NOBODY:
			case self::FOLLOWLOCATION:
			case self::CLOSECONNECTION:
			case self::VALIDSSLONLY:
				if ($extra == 1 OR $extra == true)
				{
					$this->bitoptions = $this->bitoptions | $option;
				}
				else
				{
					$this->bitoptions = $this->bitoptions & ~$option;
				}
				break;
			case self::TIMEOUT:
				if ($extra == 1 OR $extra == true)
				{
					$this->options[self::TIMEOUT] = intval($extra);
				}
				else
				{
					$this->options[self::TIMEOUT] = 15;
				}
				break;
			case self::POSTFIELDS:
				if ($extra == 1 OR $extra == true)
				{
					$this->options[self::POSTFIELDS] = $extra;
				}
				else
				{
					$this->options[self::POSTFIELDS] = '';
				}
				break;
			case self::ENCODING:
			case self::USERAGENT:
			case self::URL:
			case self::CUSTOMREQUEST:
				$this->options["$option"] = $extra;
				break;
			case self::HTTPHEADER:
				if (is_array($extra))
				{
					$this->options[self::HTTPHEADER] = $extra;
				}
				else
				{
					$this->options[self::HTTPHEADER] = array();
				}
				break;
			case self::MAXSIZE:
			case self::MAXREDIRS:
			case self::DIEONMAXSIZE:
				$this->options["$option"]	= intval($extra);
				break;
		}
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
		if ($this->bitoptions & self::HEADER)
		{
			if ($this->__finished_headers != self::STATE_BODY)
			{
				if ($this->bitoptions & self::FOLLOWLOCATION AND preg_match('#(?<=\r\n|^)Location:#i', $response))
				{
					$this->__finished_headers = self::STATE_LOCATION;
				}

				if ($response === "\r\n")
				{
					if ($this->__finished_headers == self::STATE_LOCATION)
					{
						// found a location -- still following it; reset the headers so they only match the new request
						$this->response_header = '';
						$this->__finished_headers = self::STATE_HEADERS;
					}
					else
					{
						// no location -- we're done
						$this->__finished_headers = self::STATE_BODY;
					}
				}

				return $chunk_length;
			}
		}

		// no filepointer and we're using or about to use more than 100k
		// if we don't have a tempfile, then just store as a string.
		if ($this->tempfilename AND !$this->tempfilepointer AND $this->response_length + $chunk_length >= 1024*100)
		{
			if ($this->tempfilepointer = @fopen($this->tempfilename, 'wb'))
			{
				fwrite($this->tempfilepointer, $this->response_text);
				unset($this->response_text);
			}
		}

		if ($this->tempfilepointer AND $response)
		{
			fwrite($this->tempfilepointer, $response);
		}
		else
		{
			$this->response_text .= $response;
		}

		$this->response_length += $chunk_length;

		if (!empty($this->options[self::MAXSIZE]) AND $this->response_length > $this->options[self::MAXSIZE])
		{
			$this->max_limit_reached = true;
			$this->setError(self::ERROR_MAXSIZE);
			return false;
		}

		return $chunk_length;
	}

	/**
	 *	Perform a GET request
	 *
	 *	@param string $url
	 *	@return false | array
	 *		-- array headers -- the httpheaders return.  Empty if the HEADER is not set
	 *		-- string body -- the body of the request. Empty if NOBODY is set
	 *		Returns false on error
	 */
	public function get($url)
	{
		$this->setOption(self::URL, $url);
		$this->setOption(self::POST, 0);

		$result = $this->exec();
		if($result)
		{
			return $this->formatReponse();
		}
		return false;
	}

	/**
	 *	Perform a POST request
	 *	@param string $url
	 *	@param array|string $postdata -- the data as either an array or "query param" string
	 *
	 *	@return false | array
	 *		-- array headers -- the httpheaders return.  Empty if the HEADER is not set
	 *		-- string body -- the body of the request. Empty if NOBODY is set
	 *		Returns false on error
	 */
	public function post($url, $postdata)
	{
		$this->setOption(self::URL, $url);
		$this->setOption(self::POST, 1);
		$this->setOption(self::POSTFIELDS, $postdata);

		$result = $this->exec();
		if($result)
		{
			return $this->formatReponse();
		}
		return false;
	}

	/**
	 *	Perform a POST request using a JSON post body
	 *
	 *	This performs as post using a custom JSON request (popular with REST APIs) instead of
	 *	a standard x-www-form-urlencoded format
	 *
	 *	@param string $url
	 *	@param string $postdata -- the JSON encoded request.
	 *
	 *	@return false | array
	 *		-- array headers -- the httpheaders return.  Empty if the HEADER is not set
	 *		-- string body -- the body of the request. Empty if NOBODY is set
	 *		Returns false on error
	 */
	public function postJson($url, $postdata)
	{
		$this->setOption(self::URL, $url);

		$this->setOption(self::CUSTOMREQUEST, 'POST');
		$this->setOption(self::POSTFIELDS, $postdata);

		// Set HTTP Header for POST request
		$this->setOption(self::HTTPHEADER, array(
			'Content-Type: application/json',
		));

		$result = $this->exec();
		if($result)
		{
			return $this->formatReponse();
		}
		return false;
	}

	private function formatReponse()
	{
		$response = array(
			'headers' => array(),
			'body' => '',
		);

		if ($this->bitoptions & self::HEADER)
		{
			$response['headers'] = $this->buildHeaders($this->response_header);
		}

		if (!($this->bitoptions & self::NOBODY))
		{
			$response['body'] = (isset($this->response_text) ? $this->response_text : "");
			if (empty($response['body']) AND file_exists($this->tempfilename))
			{
				$response['body'] = file_get_contents($this->tempfilename);
			}
		}

		//for now we're not going to return the body file so we'll delete it now.
		$this->deleteTempFile();

		return $response;
	}

	private function buildHeaders($data)
	{
		$returnedheaders = explode("\r\n", $data);
		$headers = array();
		foreach ($returnedheaders AS $line)
		{
			@list($header, $value) = explode(': ', $line, 2);
			if (preg_match('#^http/(1\.[012]) ([12345]\d\d) (.*)#i', $header, $httpmatches))
			{
				$headers['http-response']['version'] = $httpmatches[1];
				$headers['http-response']['statuscode'] = $httpmatches[2];
				$headers['http-response']['statustext'] = $httpmatches[3];
			}
			else if (!empty($header))
			{
				$headers[strtolower($header)] = $value;
			}
		}

		return $headers;
	}

	/**
	* Performs fetching of the file if possible
	*
	* @return	boolean
	*/
	private function exec()
	{
		$urlinfo = $this->string->parseUrl($this->options[self::URL]);

		if(!$this->validateUrl($urlinfo))
		{
			return false;
		}

		if (!function_exists('curl_init') OR ($this->ch = curl_init()) === false)
		{
			return false;
		}

		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->options[self::TIMEOUT]);
		if (!empty($this->options[self::CUSTOMREQUEST]))
		{
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->options[self::CUSTOMREQUEST]);

			//if we set a post this way, we still need to send the post fields.
			//documentation suggests that this is the correct way to send posts with non
			//standard post bodies (such as JSON or XML)
			if(strcasecmp($this->options[self::CUSTOMREQUEST], 'post') === 0)
			{
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->options[self::POSTFIELDS]);
			}
		}
		else if ($this->bitoptions & self::POST)
		{
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->options[self::POSTFIELDS]);
		}
		else
		{
			curl_setopt($this->ch, CURLOPT_POST, 0);
		}

		curl_setopt($this->ch, CURLOPT_HEADER, ($this->bitoptions & self::HEADER) ? 1 : 0);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->options[self::HTTPHEADER]);
		if ($this->bitoptions & self::NOBODY)
		{
			curl_setopt($this->ch, CURLOPT_NOBODY, 1);
		}

		//never use CURLOPT_FOLLOWLOCATION -- we need to make sure we are as careful with the
		//urls returned from the server as we are about the urls we initially load.
		//we'll loop internally up to the recommended tries.
		$redirect_tries = 1;

		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 0);
		if ($this->bitoptions & self::FOLLOWLOCATION)
		{
			$redirect_tries = $this->options[self::MAXREDIRS];
		}

		//sanity check to avoid an infinite loop
		if ($redirect_tries < 1)
		{
			$redirect_tries = 1;
		}

		if ($this->options[self::ENCODING])
		{
			// this will work on versions of cURL after 7.10, though was broken on PHP 4.3.6/Win32
			@curl_setopt($this->ch, CURLOPT_ENCODING, $this->options[self::ENCODING]);
		}

		curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, array(&$this, 'curl_callback_response'));
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array(&$this, 'curl_callback_header'));

		if (!($this->bitoptions & self::VALIDSSLONLY))
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
					return false;
				}
			}

			$result = $this->execCurl($url, $isHttps);

			//if we don't have another iteration of the loop to go, skip the effort here.
			if (($i > 1) AND in_array(curl_getinfo($this->ch, CURLINFO_HTTP_CODE), $redirectCodes))
			{
				$url = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL);
				$urlinfo = $this->string->parseUrl($url);

				if(!$this->validateUrl($urlinfo))
				{
					$this->closeTempFile();
					return false;
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
		if (($this->bitoptions & self::FOLLOWLOCATION) && in_array(curl_getinfo($this->ch, CURLINFO_HTTP_CODE), $redirectCodes))
		{
			$this->closeTempFile();
			return false;
		}

		//close the connection and clean up the file.
		curl_close($this->ch);
		$this->closeTempFile();

		if ($result !== false OR (!$this->options[self::DIEONMAXSIZE] AND $this->max_limit_reached))
		{
			return true;
		}

		return false;
	}

	private function closeTempFile()
	{
		if ($this->tempfilepointer)
		{
			fclose($this->tempfilepointer);
			$this->tempfilepointer = null;
		}
	}

	private function deleteTempFile()
	{
		if (file_exists($this->tempfilename))
		{
			@unlink($this->tempfilename);
		}
	}

	public function reset()
	{
		$this->bitoptions = 0;
		$this->error = 0;

		$this->options = array(
			self::TIMEOUT    => 15,
			self::POSTFIELDS => '',
			self::ENCODING   => '',
			self::USERAGENT  => '',
			self::URL        => '',
			self::HTTPHEADER => array(),
			self::MAXREDIRS  => 5,
			self::USERAGENT  => 'vBulletin via PHP',
			self::DIEONMAXSIZE => 1,
		);
	}

	/**
	 * Clears all previous request info
	 */
	private function resetPageLoad()
	{
		$this->response_text = '';
		$this->response_header = '';
		$this->response_length = 0;
		$this->__finished_headers = self::STATE_HEADERS;
		$this->max_limit_reached = false;
		$this->closeTempFile();
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
		$this->resetPageLoad();
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$result = curl_exec($this->ch);

		//this violates the "utilites" rule of depending on things outside of the utility directory.
		//However we don't really want this in the PHAR file because this is something an end
		//user might want to edit, we can't hide it.  We need to figure out a proper policy for
		//that sort of dependancy (probably should pass the path into the constructor).
		//Howere the better solution is to remove this logic entirely. It's probably not needed
		//(best understanding is it's due to problems with SSL certs on really old cUrl installs)
		//But it's insufficiently unclear what the consequences are.
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
	 *	@param $urlinfo -- The parsed url info from vB_Utility_String::parseUrl -- scheme, port, host
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
		$allowedPorts = $this->allowedports;
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
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
