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

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
* Class that provides payment verification and form generation functions
*
* @package	vBulletin
* @version	$Revision: 99787 $
* @date		$Date: 2018-10-24 17:13:06 -0700 (Wed, 24 Oct 2018) $
*/
class vB_PaidSubscriptionMethod_ccbill extends vB_PaidSubscriptionMethod
{
	/**
	* The variable indicating if this payment provider supports recurring transactions
	*
	* @var	bool
	*/
	var $supports_recurring = false;

	/**
	* Perform verification of the payment, this is called from the payment gateway
	*
	* @return	bool	Whether the payment is valid
	*/
	function verify_payment()
	{
		$this->registry->input->clean_array_gpc('r', array(
			'clientAccnum'         => vB_Cleaner::TYPE_STR,
			'clientSubacc'         => vB_Cleaner::TYPE_STR,
			'subscription_id'      => vB_Cleaner::TYPE_STR,
			'hash'                 => vB_Cleaner::TYPE_STR,
			'typeId'               => vB_Cleaner::TYPE_INT,
			'secretword'           => vB_Cleaner::TYPE_STR,
			'reasonForDeclineCode' => vB_Cleaner::TYPE_STR,
			'initialPrice'         => vB_Cleaner::TYPE_NUM,
		));

		if (!$this->test())
		{
			$this->error = 'Payment processor not configured';
			return false;
		}

		$this->transaction_id = $this->registry->GPC['subscription_id'];

		// reasonForDeclineCode will be set upon denial but CCBill can offer the user other payment options after a decline and submit again
		if (empty($this->registry->GPC['reasonForDeclineCode']) AND $this->registry->GPC['secretword'] == $this->settings['secretword'] AND preg_match('#^64\.38\.#', $this->registry->ipaddress))	// check REMOTE_ADDR = 64.38.*
		{
			$this->paymentinfo = vB::getDbAssertor()->getRow('vBForum:getPaymentinfo', array('hash' => $this->registry->GPC['item_number']));

			// lets check the values
			if (!empty($this->paymentinfo))
			{
				$sub = vB::getDbAssertor()->getRow('vBForum:subscription', array('subscriptionid' => $this->paymentinfo['subscriptionid']));
				$this->paymentinfo['currency'] = 'usd';
				$this->paymentinfo['amount'] = floatval($this->registry->GPC['initialPrice']);
				$this->type = 1;
				return true;
			}
		}
		return false;
	}

	/**
	* Test that required settings are available, and if we can communicate with the server (if required)
	*
	* @return	bool	If the vBulletin has all the information required to accept payments
	*/
	function test()
	{
		return (!empty($this->settings['clientAccnum']) AND !empty($this->settings['clientSubacc']) AND !empty($this->settings['formName']));
	}

	/**
	* Generates HTML for the subscription form page
	*
	* @param	string		Hash used to indicate the transaction within vBulletin
	* @param	string		The cost of this payment
	* @param	string		The currency of this payment
	* @param	array		Information regarding the subscription that is being purchased
	* @param	array		Information about the user who is purchasing this subscription
	* @param	array		Array containing specific data about the cost and time for the specific subscription period
	*
	* @return	array		Compiled form information
	*/
	function generate_form_html($hash, $cost, $currency, $subinfo, $userinfo, $timeinfo)
	{
		global $vbphrase, $vbulletin, $show;

		$form['action'] = 'https://bill.ccbill.com/jpost/signup.cgi';
		$form['method'] = 'post';

		// load settings into array so the template system can access them
		$settings =& $this->settings;
		$settings['email'] = htmlspecialchars_uni($this->registry->userinfo['email']);
		$subinfo['ccbillsubid'] = $timeinfo['ccbillsubid'];

		$templater = vB_Template::create('subscription_payment_ccbill');
			$templater->register('hash', $hash);
			$templater->register('settings', $settings);
			$templater->register('subinfo', $subinfo);
		$form['hiddenfields'] .= $templater->render();
		return $form;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
