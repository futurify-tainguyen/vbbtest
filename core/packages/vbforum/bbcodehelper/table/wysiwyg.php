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
* Implementation of table BB code parsing for the WYSIWYG editor.
*
* @package	vBulletin
*/
class vBForum_BBCodeHelper_Table_Wysiwyg extends vBCms_BBCodeHelper_Table
{
	/**
	* Prefix to apply to all classes used by the table/tr/td tags.
	* This prevents people from using completely arbitrary classes.
	*
	* @var	string
	*/
	protected $table_class_prefix = 'wysiwyg_table_';

	/**
	* Whether the output should include non-significant whitespace to aid
	* in formatting the HTML output. This will have no difference on the
	* displayed output.
	*
	* @var	bool
	*/
	protected $add_formatting_whitespace = false;

	/**
	*	Whether to wrap the output table with a div for markup purposes
	*
	*/
	protected $wrap_table = false;

	/**
	* Helper method to allow modification of the paramaters for a table tag
	* before they are used in child tags or outputted.
	*
	* @param	array	Table parameters (in format of resolveNamedParams)
	*
	* @return	array	Table parameters modified if necessary
	*/
	protected function modifyTableParams(array $table_params)
	{
		//do this before we call the parent class so that we don't have any 
		//units added that we need to get rid of.
		foreach ($this->table_param_list AS $name => $def)
		{
			if(!empty($def['wysiwygattr']) AND !empty($def['css']))
			{
				$cssname = $def['css'];
				if (!empty($table_params['css'][$cssname]))
				{
					$table_params['attributes'][$name] = $table_params['css'][$cssname];
					unset($table_params['css'][$cssname]);
				}
			}
		}		

		$table_params = parent::modifyTableParams($table_params);

		// tables will always have the wysiwyg_dashes class in the wysiwyg editor
		if (empty($table_params['attributes']['class']))
		{
			$table_params['attributes']['class'] = 'wysiwyg_dashes';
		}
		else
		{
			$table_params['attributes']['class'] = 'wysiwyg_dashes ' . $table_params['attributes']['class'];
		}

		return $table_params;
	}

	/**
	* Helper method to modify the cell content before it is placed in the HTML.
	*
	* @param	string	Cell content
	*
	* @return	string	Modified cell content
	*/
	protected function modifyCellContent($content)
	{
		$content = parent::modifyCellContent($content);

		if ($this->parser->is_wysiwyg('ie'))
		{
			$content = "<p>$content</p>";
			return str_replace('<p></p>', '', $content);
		}
		else if ($content === '')
		{
			// need to put something in the cell for FF
			return '<br _moz_dirty="" type="_moz" />';
		}
		else
		{
			return $content;
		}
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
