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
 *	@package vBUtility
 */

/**
 *	@package vBUtility
 */
abstract class vB_Utility_Password_Algorithm
{
	use vB_Utility_Trait_NoSerialize;

	/**
	 *	Create an password algorithm object for the given scheme.
	 *
	 *	@param string $scheme -- the requested password scheme (algorithm + any parameters the argorithm expects
	 *		such as repetitions.
	 *	@return object An object of type vB_Password_Algorithm
	 *	@throws vB_Password_Exception_InvalidScheme
	 */
	public static function instance($scheme)
	{
		$algorithm = explode(':', $scheme, 2);
		$class = 'vB_Utility_Password_Algorithm_' . ucfirst($algorithm[0]);

		if (class_exists($class))
		{
			return new $class($scheme);
		}

		throw new vB_Utility_Password_Exception_InvalidScheme();
	}

	//hide the constructor, everything should go through the instance function
	protected function __construct($scheme)
	{
	}

	/**
	 *	Hash the password according to the password algorithm
	 *
	 * 	Will also generate the salt if a salt is not provided.  Salts are paired with
	 *
	 *	@param string $password -- The password to encode.  It should already have any front end encoding applied.
	 *	@param string $salt -- The salt to use when encoding the hash.  If a salt is not provided, then it will be generated
	 *
	 *	@return array.  An array containing
	 *		password -- the new password hash
	 *		salt -- the salt used to generate the password.  If the salt is provided then the salt returned will be equivilant to
	 *			the salt passed in.  In the case of the blowfish algorithm the salt is a 22 character string, but the algorithm only
	 *			uses the top two bits of the last character.  The result is that the salt may differ in the last character so long as
	 *			the top two bits are the same -- this function will return the canonical version of the salt passed to it in cases like
	 *			that.  Since the salts should all be generated by a previous call to this function, which will always return the canonical
	 *			form, this should not be a problem in actual use.
	 */
	abstract public function generateToken($password);
	abstract public function verifyPassword($password, $token);
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
