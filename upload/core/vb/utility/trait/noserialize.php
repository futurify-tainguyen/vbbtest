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

/*
 *	This is a duplicate of the main NoSerialize trait intended to
 *	keep to the rule that there can be no explicit dependacies between
 *	the Utility directory and the rest of vBulletin.  It's perhaps overkill
 *	but it doesn't cost much and keeping the dependencies clean is
 *	important.
 */

trait vB_Utility_Trait_NoSerialize
{
	public function __sleep()
	{
		throw new Exception('Serialization not supported');
	}

	public function __wakeup()
	{
		throw new Exception('Serialization not supported');
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 101013 $
|| #######################################################################
\*=========================================================================*/
