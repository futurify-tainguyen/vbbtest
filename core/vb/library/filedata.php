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
* Class to manage FileData.  At the moment the only thing it does is to move filedata between the database and filesystem.
*
* @package	vBulletin
* @version	$Revision: 99909 $
* @date		$Date: 2018-11-06 18:13:41 -0800 (Tue, 06 Nov 2018) $
*/
class vB_Library_Filedata extends vB_Library
{
	/**
	 * Storage Type
	 *
	 * @var  string
	 */
	protected $storage = null;
	protected $filePath = false;

	/**
	 * Supported types of storage
	 *
	 * These should match the legacy defines at the top of core/includes/functions_file.php
	 *
	 * @constant	int
	 */
	const ATTACH_AS_DB = 0;
	const ATTACH_AS_FILES_OLD = 1;
	const ATTACH_AS_FILES_NEW = 2;

	/**
	 *	Standard Constructor
	 */
	protected function __construct()
	{
		parent::__construct();

		$datastore = vB::getDatastore();
		$this->storage = $datastore->getOption('attachfile');
		$this->filePath = $datastore->getOption('attachpath');
		$this->assertor = vB::getDbAssertor();
	}

	/**
	 * moves one record from the database to the filesystem
	 *
	 * @param	array $filedata (optional) -- filedata record
	 * @param int	$filedataid (optional)
	 *
	 * @return array has either 'success' or 'error'
	 */
	public function moveToFs(&$filedata = false, $filedataid = false, $resize = array())
	{
		/*three non-obvious issues:
		first, we normally get attachment.  It seems strange, but that's because this is inside a loop
		in admincp, so we have the record. Since we have the data, let's use it.
		second, it would appear wiser to do the looping here.  But we have to limit the number of records to prevent timeouts,
		and pass that to admincp page, and then reload. The admincp is already written to handle all this
		third, it would seem we would want to clear the old data.  But that would be bad. Because of the looping per "second", we
		complete all the moves, and then after confirmation wipe the old. Otherwise we could get caught in an invalid state with
		some records in the filesystem and some in the database.  Very bad.
		*/

		if ($this->storage == self::ATTACH_AS_FILES_OLD OR $this->storage == self::ATTACH_AS_FILES_NEW)
		{
			throw new vB_Exception_Api('invalid_request');
		}

		$fileRec = $this->cleanFileParams($filedata, $filedataid);

		//We can skip most of the data cleaning.  We aren't saving new records, we're just moving between filesystem and database
		$path = $this->fetchAttachmentPath($fileRec['userid'], self::ATTACH_AS_FILES_NEW);
		if(!$path)
		{
			throw new vB_Exception_Api('file_not_found');
		}

		$filename = $this->getAttachmentFileInternal($path, $fileRec['filedataid'], vB_Api_Filedata::SIZE_FULL);
		file_put_contents($filename, $fileRec['filedata']);

		$resizeSizes = array();
		if (is_array($resize) AND !empty($resize))
		{
			foreach($resize AS $resizeRec)
			{
				$resizeName = $this->getAttachmentFileInternal($path, $fileRec['filedataid'], $resizeRec['resize_type']);
				file_put_contents($resizeName, $resizeRec['resize_filedata']);

				$resizeSizes[$resizeRec['resize_type']] = filesize($resizeName);
			}
		}

		return array(
			'success' => true,
			'filesize' => filesize($filename),
			'resize_sizes' => $resizeSizes
		);
	}

	/**
	 * Delete the filedata records
	 *
	 * Will also remove the files from disk if that option is being used.
	 *
	 * @param array|int $filedataids -- ids to delete.  Takes either a single id or a list.
	 */
	public function deleteFileData($filedataids)
	{
		$db = vB::getDbAssertor();
		//we shouldn't really shouldn't see ATTACH_AS_FILES_OLD, but if we do we handle it the same was as NEW
		if ($this->storage == self::ATTACH_AS_FILES_OLD OR $this->storage == self::ATTACH_AS_FILES_NEW)
		{
			$filedata = $db->select('filedata', array('filedataid' => $filedataids), false, array('userid', 'filedataid'));
			foreach($filedata AS $row)
			{
				$this->deleteFileDataFiles($row['userid'], $row['filedataid']);
			}
		}

		$db->delete('filedata', array('filedataid' => $filedataids));
		$db->delete('filedataresize', array('filedataid' => $filedataids));
	}

	public function deleteFileDataFiles($userid, $filedataid)
	{
		//for some reason we attempt to delete all types for a file instead of
		//checking the types we think we have.  I'm not certain why, but I don't
		//want to change it without better understanding.  It's a little slower
		//but we don't really do this *alot*
		$resizeTypes = array(
			vB_Api_Filedata::SIZE_FULL,
			vB_Api_Filedata::SIZE_THUMB,
			vB_Api_Filedata::SIZE_ICON,
			vB_Api_Filedata::SIZE_SMALL,
			vB_Api_Filedata::SIZE_MEDIUM,
			vB_Api_Filedata::SIZE_LARGE,
		);

		foreach($resizeTypes AS $resizeType)
		{
			$resizeName = $this->getExistingFile($userid, $filedataid, $resizeType);
			if($resizeName)
			{
				@unlink($resizeName);
			}
		}
	}

	public function trimEmptyFiledataDirectories()
	{
		//we don't want to delete the cach directory itself, so we'll loop through the
		//top level dirs seperately
		$iterator = new DirectoryIterator($this->filePath);
		foreach($iterator AS $item)
		{
			if(!$item->isDot() AND $item->isDir())
			{
				$this->trimEmptyDirectories($item->getPathname());
			}
		}
	}

	/**
	 * Attempts to recursively trim empty subdirectories
	 *
	 * Goes through a directory tree and attempts to delete any empty subdirectories
	 * Does depth first recursively so that we remove a directory that starts out
	 * with children if those children are removed
	 *
	 * @param $directory -- directory to trim
	 * @return bool -- true if we deleted the directory
	 */
	//this is probably suitable for promoting to a more general application.
	private function trimEmptyDirectories($directory)
	{
		$iterator = new DirectoryIterator($directory);
		$empty = true;
		foreach($iterator AS $item)
		{

			if($item->isDot())
			{
				continue;
			}

			if($item->isDir())
			{
				//if we can't remove the child dir then we can't remove this dir
				if(!$this->trimEmptyDirectories($item->getPathname()))
				{
					$empty = false;
				}
			}
			else
			{
				//and if we have a non dir child, we can't delete this either
				$empty = false;
			}
		}

		if($empty)
		{
			return @rmdir($directory);
		}

		return false;
	}

	/**
	 * moves one record from the filesystem to the database
	 *
	 * @param	mixed		optional filedata record
	 * @param 	int		optional $filedataid
	 *
	 * @return 	array	has either 'success' or 'error'
	 */
	public function moveToDb(&$filedata = false, $filedataid = false, $resize = array())
	{
		if ($this->storage == self::ATTACH_AS_DB)
		{
			throw new vB_Exception_Api('invalid_request');
		}

		//this should always be the case, but let's not break if it isn't
		if(!is_array($resize))
		{
			$resize = array();
		}

		//see introductory comments in moveToFs above
		$fileRec = $this->cleanFileParams($filedata, $filedataid);

		//We can skip most of the data cleaning.  We aren't saving new records, we're just moving between filesystem and database
		$filename = $this->getExistingFile($fileRec['userid'], $fileRec['filedataid'], vB_Api_Filedata::SIZE_FULL);
		if (!$filename)
		{
			throw new vB_Exception_Api('file_not_found');
		}

		$fileContents = file_get_contents($filename);
		$this->assertor->assertQuery('filedata', array(
			vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_UPDATE,
			vB_dB_Query::CONDITIONS_KEY => array('filedataid' => $fileRec['filedataid']),
			'filedata' => $fileContents
		));

		$resizeSizes = array();
		foreach($resize AS $resizeRec)
		{
			$resizeName = $this->getExistingFile($fileRec['userid'], $fileRec['filedataid'], $resizeRec['resize_type']);
			if (!$resizeName)
			{
				throw new vB_Exception_Api('file_not_found');
			}

			$fileContents = file_get_contents($resizeName);
			$this->assertor->assertQuery('filedataresize', array(
				vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_UPDATE,
				vB_dB_Query::CONDITIONS_KEY => array(
					'filedataid' => $resizeRec['filedataid'],
					'resize_type' => $resizeRec['resize_type'],
				),
				'resize_filedata' => $fileContents
			));

			$resizeSizes[$resizeRec['resize_type']] = filesize($resizeName);
		}

		return array(
			'success' => true,
			'filesize' => filesize($filename),
			'resize_sizes' => $resizeSizes
		);
	}

	protected function cleanFileParams(&$filedata = false, $filedataid = false)
	{
		if (empty($filedata) OR empty($filedata['filedataid']))
		{
			if (empty($filedataid) OR !is_int($filedataid))
			{
				throw new vB_Exception_Api('invalid_request');
			}
			$fileRec = $this->assertor->getRow('filedata', array('filedataid' => $filedataid));
		}
		else
		{
			$fileRec = &$filedata;
		}

		if (empty($fileRec) OR !empty($fileRec['errors']) OR empty($fileRec['filedataid']))
		{
			throw new vB_Exception_Api('invalid_request');
		}

		return $fileRec;
	}

	public function getAttachmentFile($userid, $filedataid, $resizeType = vB_Api_Filedata::SIZE_FULL)
	{
		$path = $this->fetchAttachmentPath($userid, $this->storageType, false);
		return $this->getAttachmentFileInternal($path, $fieldataid, $resizeType);
	}

	//this will try to get an existing file, even if it's not in the current file structure format.  The intent here
	//is to find existing files even if we wouldn't *write* a file like that in now in order to be compatible with
	//something the site may have done previously.  This should not be used when writing files.
	private function getExistingFile($userid, $filedataid, $resizeType)
	{
		//note that we don't care about the current storage type.  We might not even be *using* attachments
		//as files depending on what we are doing
		$newPath = $this->fetchAttachmentPath($userid, self::ATTACH_AS_FILES_NEW);
		if($newPath)
		{
			$fileName = $this->getAttachmentFileInternal($newPath, $filedataid, $resizeType);
		}

		if (!$newPath OR !file_exists($fileName))
		{
			$oldPath = $this->fetchAttachmentPath($userid, self::ATTACH_AS_FILES_OLD, false);

			// In some cases, we may have a mix of ATTACH_AS_FILES_NEW and ATTACH_AS_FILES_OLD. See VBV-13339.
			if($oldPath)
			{
				$fileName = $this->getAttachmentFileInternal($oldPath, $filedataid, $resizeType);
			}
			else
			{
				return false;
			}

			if (!file_exists($fileName))
			{
				// In some cases, when files were saved as ATTACH_AS_FILES_OLD, the resized images were incorrectly
				// stored with no dot between the filedataid and the resize type. See VBV-13339.
				// Note that we'll also now check this case when the full file size is requested.  That should never
				// produce a hit but if it does we probably want to pull the file and it's just easier to handle
				// everything consistently.
				$fileName = $oldPath . '/' . $filedataid . $resizeType;

				if (!file_exists($fileName))
				{
					return false;
				}
			}
		}

		return $fileName;
	}

	private function getAttachmentFileInternal($path, $filedataid, $resizeType)
	{
		$resizeType = $this->sanitizeFiletype($resizeType);
		$extension = ($resizeType == vB_Api_Filedata::SIZE_FULL ? 'attach' : $resizeType);
		return $path . '/' . $filedataid . '.' . $extension;
	}

	/**
	 * Get the path for a user and make sure it exists
	 *
	 * @param	int	$userid
	 * @param	int	$storageType -- Attachment storage type to use to generate the path
	 * 	needed because
	 * @param bool $create -- create the path if it doesn't exist.
	 *
	 * @return string	path to user's storage.
	 */
	public function fetchAttachmentPath($userid, $storageType, $create=true)
	{
		// Allow userid to be 0 since vB2 allowed guests to post attachments
		$userid = intval($userid);

		$attachPath = $this->filePath;

		if ($storageType == self::ATTACH_AS_FILES_NEW) // expanded paths
		{
			$path = $attachPath . '/' . implode('/', preg_split('//', $userid,  -1, PREG_SPLIT_NO_EMPTY));
		}
		else
		{
			$path = $attachPath . '/' . $userid;
		}

		if (is_dir($path))
		{
			return $path;
		}
		else if (file_exists($path))
		{
			throw new vB_Exception_Api('attachpathfailed');
		}

		if ($create AND vB_Library_Functions::vbMkdir($path))
		{
			return $path;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Ensures that Sent in thumbnail type is valid
	 *
	 * @param	mixed	Image size to get
	 *
	 * @return	string	Valid image size to get
	 */
	public function sanitizeFiletype($type)
	{
		//The reference to the constants here is awkward as we don't want libraries depending on the
		//API layer.  However constants are dependencies that are sneakily hidden and difficult to
		//change (since part of the point is to use them everywhere so you *can* change them in one
		//place).  We need this function in the library class for a lot of reasons so we'll need
		//to live with this until we can sort out a general strategy on constants.
		if ($type == 1 OR $type === true OR $type === 'thumbnail')
		{
			$type = vB_Api_Filedata::SIZE_THUMB;
		}

		$options = vB::getDatastore()->get_value('options');
		$sizes = @unserialize($options['attachresizes']);
		if (!isset($sizes[$type]) OR empty($sizes[$type]))
		{
			$type = vB_Api_Filedata::SIZE_FULL;
		}

		switch ($type)
		{
			case vB_Api_Filedata::SIZE_ICON:
			case vB_Api_Filedata::SIZE_THUMB:
			case vB_Api_Filedata::SIZE_SMALL:
			case vB_Api_Filedata::SIZE_MEDIUM:
			case vB_Api_Filedata::SIZE_LARGE:
				break;
			default:
				$type = vB_Api_Filedata::SIZE_FULL;
		}

		return $type;
	}

}

/*=========================================================================*\
|| #######################################################################
|| # NulleD By - prowebber.ru
|| # CVS: $RCSfile$ - $Revision: 99909 $
|| #######################################################################
\*=========================================================================*/
