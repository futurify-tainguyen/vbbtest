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
 * This class is used as a proxy for the actual node route. It enables us to hide
 * the node title and URL path until we verify permissions.
 */
class vB5_Route_Node extends vB5_Route
{
	public function getUrl()
	{
		$result = "/{$this->prefix}/{$this->arguments['nodeid']}";

		// putting /contentpageX before /pageX
		if (isset($this->arguments['contentpagenum']) AND is_numeric($this->arguments['contentpagenum']) AND $this->arguments['contentpagenum'] > 1)
		{
			$result .= '/contentpage' . intval($this->arguments['contentpagenum']);
		}

		if (isset($this->arguments['pagenum']) AND is_numeric($this->arguments['pagenum']) AND $this->arguments['pagenum'] > 1)
		{
			$result .= '/page' . intval($this->arguments['pagenum']);
		}

		return $result;
	}

	public function getCanonicalRoute()
	{
		if (!isset($this->canonicalRoute))
		{
			if (empty($this->arguments['nodeid']))
			{
				throw new vB_Exception_404('invalid_page');
			}

			$nodeApi = vB_Api::instanceInternal('node');

			try
			{
				// this method will return an error if the user does not have permission
				$node = $nodeApi->getNode($this->arguments['nodeid']);
			}
			catch(vB_Exception_Api $ex)
			{
				if($ex->has_error('no_permission'))
				{
					throw new vB_Exception_NodePermission($this->arguments['nodeid']);
				}
				else if($ex->has_error('invalid_node_id'))
				{
					throw new vB_Exception_404('invalid_page');
				}
				else
				{
					// otherwise, just let the caller catch the exception
					throw $ex;
				}
			}

			$contentLib = vB_Library_Content::getContentLib($node['contenttypeid']);
			if (!$contentLib->validate($node, vB_Library_Content::ACTION_VIEW, $node['nodeid'], array($node['nodeid'] => $node)))
			{
				throw new vB_Exception_NodePermission($node['nodeid']);
			}

			if (!empty($node['starter']))
			{
				$parent = $nodeApi->getNode($node['starter']);
				$parent['innerPost'] = $this->arguments['nodeid'];
			}
			else
			{
				$parent = $node;
			}

			if (isset($this->arguments['pagenum']))
			{
				$parent['pagenum'] = $this->arguments['pagenum'];
			}

			if (isset($this->arguments['contentpagenum']))
			{
				$parent['contentpagenum'] = $this->arguments['contentpagenum'];
			}

			$this->canonicalRoute = self::getRoute($node['routeid'], $parent, $this->queryParameters);
		}

		return $this->canonicalRoute;
	}
}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99787 $
|| #######################################################################
\*=========================================================================*/
