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
* Abstracted human verification class
*
* @package 		vBulletin
* @version		$Revision: 100503 $
* @date 		$Date: 2019-01-18 17:07:41 -0800 (Fri, 18 Jan 2019) $
*
*/
class vB_HumanVerify
{
	/**
	* Constructor
	* Does nothing :p
	*
	* @return	void
	*/
	function __construct() {}

	/**
	* Singleton emulation: Select library
	*
	* @return	object
	*/
	public static function &fetch_library($library = '')
	{
		static $instance;

		if (!$instance)
		{
			if ($library)
			{
				// Override the defined vboption
				$chosenlib = $library;
			}
			else
			{
				$vboptions = vB::getDatastore()->getValue('options');
				$chosenlib = ($vboptions['hv_type'] ? $vboptions['hv_type'] : 'Disabled');
			}

			$selectclass = 'vB_HumanVerify_' . $chosenlib;
			$chosenlib = strtolower($chosenlib);
			require_once(DIR . '/includes/class_humanverify_' . $chosenlib . '.php');
			$instance = new $selectclass();
		}

		return $instance;
	}
}


/**
* Abstracted human verification class
*
* @package 		vBulletin
* @version		$Revision: 100503 $
* @date 		$Date: 2019-01-18 17:07:41 -0800 (Fri, 18 Jan 2019) $
*
* @abstract
*/
abstract class vB_HumanVerify_Abstract
{
	/**
	* Error string
	*
	* @var	string
	*/
	protected $error = '';

	/**
	* Last generated hash
	*
	* @var	string
	*/
	protected $hash = '';

	/**
	 * Deleted a Human Verification Token
	 *
	 * @param	string	The hash to delete
	 * @param	string	The Corresponding Option
	 * @param	integer	Whether the token has been viewd
	 *
	 * @return	boolean	Was anything deleted?
	 *
	*/
	protected function delete_token($hash, $answer = NULL, $viewed = NULL)
	{
		$data = array(
			'hash' => $hash,
		);

		if ($answer !== NULL)
		{
			$data['answer'] = $answer;
		}
		if ($viewed !== NULL)
		{
			$data['viewed'] = intval($viewed);
		}

		if ($this->hash == $hash)
		{
			$this->hash = '';
		}

		vB::getDbAssertor()->assertQuery('humanverify', array(
			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_DELETE,
			vB_dB_Query::CONDITIONS_KEY => $data,
		));

		return vB::getDbAssertor()->affected_rows() ? true : false;
	}

	/**
	 * Generates a Random Token and stores it in the database
	 *
	 * @param	boolean	Delete the previous hash generated
	 *
	 * @return	array	an array consisting of the hash, and the answer
	 *
	*/
	public function generate_token($deletehash = true)
	{
		$verify = array(
			'hash'   => md5(uniqid(vbrand(), true)),
			'answer' => $this->fetch_answer(),
		);

		if ($deletehash AND $this->hash)
		{
			$this->delete_token($this->hash);
		}
		$this->hash = $verify['hash'];

		vB::getDbAssertor()->assertQuery('humanverify', array(
			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_INSERT,
			'hash' => $verify['hash'],
			'answer' => $verify['answer'],
			'dateline' => vB::getRequest()->getTimeNow()
		));

		return $verify;
	}

	/**
	 * Verifies whether the HV entry was correct
	 *
	 * @param	array	An array consisting of the hash, and the inputted answer
	 *
	 * @return	boolean
	 *
	*/
	public function verify_token($input)
	{
		return true;
	}

	/**
	 * Returns any errors that occurred within the class
	 *
	 * @return	mixed
	 *
	*/
	public function fetch_error()
	{
		return $this->error;
	}

	/**
	 * Generates an expected answer
	 *
	 * @return	mixed
	 *
	*/
	protected function fetch_answer() {}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 100503 $
|| #######################################################################
\*=========================================================================*/
