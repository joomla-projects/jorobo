<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Bump the version of an Joomla extension
 *
 * @package  Joomla\Jorobo\Tasks\Component
 */
class BumpVersion extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Maps all parts of an extension into a Joomla! installation
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say('Updating ' . $this->getJConfig()->extension . " to " . $this->getJConfig()->version);

		// Reusing the header config here
		$exclude = explode(",", trim($this->getJConfig()->header->exclude));

		$path = realpath($this->getJConfig()->source);
		$fileTypes = explode(",", trim($this->getJConfig()->header->files));

		$changedFiles = 0;

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $filename)
		{
			if (substr($filename, 0, 1) == '.')
			{
				continue;
			}

			$file = new \SplFileInfo($filename);

			if (!in_array($file->getExtension(), $fileTypes))
			{
				continue;
			}

			// Skip directories in exclude list
			if (isset($exclude) && count($exclude))
			{
				$relative = str_replace(realpath($path), "", $file->getPath());

				// It is possible to have multiple exclude directories
				foreach ($exclude as $e)
				{
					if (stripos($relative, $e) !== false)
					{
						$this->say("Excluding " . $filename);
						continue 2;
					}
				}
			}

			// Load the file
			$fileContents = file_get_contents($file->getRealPath());

			if (preg_match('#__DEPLOY_VERSION__#', $fileContents))
			{
				$fileContents = preg_replace('#__DEPLOY_VERSION__#', $this->getJConfig()->version, $fileContents);

				$this->say('Updating file: ' . $file->getRealPath());

				file_put_contents($file->getRealPath(), $fileContents);

				$changedFiles++;
			}
		}

		$this->say('Updated ' . $changedFiles . ' files');

		return true;
	}
}
