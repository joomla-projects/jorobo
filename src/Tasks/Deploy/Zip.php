<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Deploy
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Robo\Task\Development\loadTasks;

/**
 * Deploy project as Zip
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Deploy
 *
 * @since       1.0
 */
class Zip extends Base implements TaskInterface
{
	use loadTasks;
	use TaskIO;

	/**
	 * @var    string
	 *
	 * @since  1.0
	 */
	protected $target = null;

	/**
	 * @var    string
	 *
	 * @since  1.0
	 */
	private $zip = null;

	/**
	 * Initialize Build Task
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->target = JPATH_BASE . "/dist/" . $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
		$this->zip    = new \ZipArchive($this->target, \ZipArchive::CREATE);
	}

	/**
	 * Build the package
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say('Zipping ' . $this->getJConfig()->extension . " " . $this->getJConfig()->version);

		// Instantiate the zip archive
		$this->zip->open($this->target, \ZipArchive::CREATE);

		// Process the files to zip
		foreach (new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($this->getBuildFolder()), \RecursiveIteratorIterator::SELF_FIRST
		) as $subfolder)
		{
			if ($subfolder->isFile())
			{
				// Set all separators to forward slashes for comparison
				$usefolder = str_replace('\\', '/', $subfolder->getPath());

				// Drop the folder part as we don't want them added to archive
				$addpath = str_ireplace($this->getBuildFolder(), '', $usefolder);

				// Remove preceding slash
				$findfirst = strpos($addpath, '/');

				if ($findfirst == 0 && $findfirst !== false)
				{
					$addpath = substr($addpath, 1);
				}

				if (strlen($addpath) > 0 || empty($addpath))
				{
					$addpath .= '/';
				}

				$options = array('add_path' => $addpath, 'remove_all_path' => true);
				$this->zip->addGlob($usefolder . '/*.*', GLOB_BRACE, $options);
			}
		}

		// Close the zip archive
		$this->zip->close();

		return true;
	}
}
