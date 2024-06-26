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
abstract class vB_Utility_Geoip
{
	use vB_Utility_Trait_NoSerialize;

	//use a map because key lookups are faster than value lookups
	private $euCountries = array(
		'AT' => 1, //Austria
		'BE' => 1, //Belgium
		'BG' => 1, //Bulgaria
		'HR' => 1, //Croatia
		'CY' => 1, //Cyprus
		'CZ' => 1, //Czech Republic
		'DK' => 1, //Denmark
		'EE' => 1, //Estonia
		'FI' => 1, //Finland
		'FR' => 1, //France
		'DE' => 1, //Germany
		'GR' => 1, //Greece
		'HU' => 1, //Hungary
		'IE' => 1, //Ireland
		'IT' => 1, //Italy
		'LV' => 1, //Latvia
		'LT' => 1, //Lithuania
		'LU' => 1, //Luxembourg
		'MT' => 1, //Malta
		'NL' => 1, //Netherlands
		'PL' => 1, //Poland
		'PT' => 1, //Portugal
		'RO' => 1, //Romania
		'SK' => 1, //Slovakia
		'SI' => 1, //Slovenia
		'ES' => 1, //Spain
		'SE' => 1, //Sweden
		'GB' => 1, //United Kingdom

	 	// EU candidate countries
		'AL' => 1, //Albania
		'ME' => 1, //Montenegro
		'RS' => 1, //Serbia
		'MK' => 1, //The former Yugoslav Republic of Macedonia
		'TR' => 1, //Turkey

		// Other Affected European countries
		'IS' => 1, //Iceland
		'LI' => 1, //Liechtenstein
		'MC' => 1, //Monaco
		'NO' => 1, //Norway
		'CH' => 1, //Switzerland
		'UA' => 1, //Ukraine

		//Some GeoIp providers will return this is some cases.
		'EU' => 1, //Europe
	 );

	public function __construct($data)
	{
	}

	public function isEu($ipaddress)
	{
		$code = $this->getCountryCode($ipaddress);
		return isset($this->euCountries[$code]);
	}

	abstract protected function getCountryCode($ipaddress);
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
