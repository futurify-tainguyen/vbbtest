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
 * vB_Utility_Geoip
 *
 * @package vBulletin
 */
class vB_Utility_Geoip_Ipstack extends vB_Utility_Geoip
{
	private $urlLoader;
	private $key;
	private $useSecure = false;

	public function __construct($data)
	{
		$this->urlLoader = $data['urlLoader'];
		$this->key = $data['key'];
	}

	protected function getCountryCode($address)
	{
		//if we don't have the key set, let's not bug IP Stack about this request,
		//it's not going to work anyway.
		if(!$this->key)
		{
			throw new Exception('Application key not provided for IP Stack request');
		}

		$query = array(
			'access_key' => $this->key,
			'fields' => 'country_code',
		);

		//unfortunately IP stack doesn't permit https on free accounts.
		//this is terrible, but nothing we can do about it.
		$url = 'http';
		if($this->useSecure)
		{
			$url .= 's';
		}

		$url .= '://api.ipstack.com/' . urlencode($address) . '?' . http_build_query($query);
		$data = $this->urlLoader->get($url);

		$data = json_decode($data['body'], true);

		if(!$data)
		{
			throw new Exception('Invalid response from IP Stack');
		}

		if(isset($data['error']))
		{
			throw new Exception('IPStack error: ' . $data['error']['info']);
		}
		return $data['country_code'];
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
