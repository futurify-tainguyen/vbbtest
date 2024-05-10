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
 * vB_Library
 *
 * @package vBForum
 * @access public
 */
class vB_Library
{
	use vB_Trait_NoSerialize;

	protected static $instance = array();

	protected function __construct()
	{

	}

	/**
	 * Returns singleton instance of self.
	 *
	 * @return vB_PageCache		- Reference to singleton instance of the cache handler
	 */
	public static function instance($class)
	{
		/*
			Class names are not case sensitive in PHP, but vars & array keys are.
			Make sure that we get a single instance of the requested class regardless of letter case.
		 */
		//$class = ucfirst(strtolower($class));
		//$className = 'vB_Library_' . $class;
		list($className, $package) = self::getLibraryClassNameInternal($class);
		if (!isset(self::$instance[$className]))
		{
			self::$instance[$className] = new $className();
		}

		return self::$instance[$className];
	}

	protected static function getLibraryClassNameInternal($controller)
	{
		/*
			Based on vB_Api::getApiClassNameInternal()
			Keep this in sync with vB_Api::getApiClassNameInternal()
		 */
		$values = explode(':', $controller);
		$package = '';
		if(count($values) == 1)
		{
			$c = 'vB_Library_' . ucfirst(strtolower($controller));
		}
		else
		{
			list($package, $controller) = $values;
			// todo: class names might have uppercases to help with readability, but
			// productid/package-name may be in lowercase.
			// Iff productid is always in lowercase this strtolower is OK to do,
			// but if we allow uppercases & allow case to define uniqueness of products,
			// this is NOT ok.
			// This is here to do stuff like call TwitterLogin:ExternalLogin API for the
			// twitterlogin package.
			$package = strtolower($package);
			$c = ucfirst($package) . '_Library_' . ucfirst(strtolower($controller));

			/*
				From vB_Api::getApiClass()
			 */
			$products = vB::getDatastore()->getValue('products');
			if(empty($products[$package]))
			{
				// todo: new phrase for library_class_... ?
				throw new vB_Exception_Api('api_class_product_x_is_disabled', array($controller, $package));
			}
		}

		return array($c, $package);
	}

	public static function getContentInstance($contenttypeid)
	{
		$contentType = vB_Types::instance()->getContentClassFromId($contenttypeid);
		$className = 'Content_' . $contentType['class'];

		return self::instance($className);
	}

	public static function clearCache()
	{
		self::$instance = array();
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
