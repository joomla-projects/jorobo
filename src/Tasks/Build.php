<?php
/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Class Build
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class Build extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use Build\buildTasks;
	use Deploy\deployTasks;

	/**
	 * @var    array|null
	 *
	 * @since  1.0
	 */
	protected $params = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   array  $params  Additional  params
	 *
	 * @since   1.0
	 */
	public function __construct($params)
	{
		parent::__construct($params);

		$this->params = $params;
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
		$this->say('Building ' . $this->getJConfig()->extension . " " . $this->getJConfig()->version);

		if (!$this->checkFolders())
		{
			return false;
		}

		// Create directory
		$this->prepareDistDirectory();

		// Build extension
		$this->buildExtension($this->params)->run();

		// Create symlink to current folder
		if ($this->isWindows())
		{
			$this->_exec('mklink /J ' . JPATH_BASE . '\dist\current ' . $this->getWindowsPath($this->getBuildFolder()));
		}
		else
		{
			$this->_symlink($this->getBuildFolder(), JPATH_BASE . "/dist/current");
		}

		// Support multiple deployment methods, separated by spaces
		$deploy = explode(" ", $this->getJConfig()->target);

		if (count($deploy))
		{
			foreach ($deploy as $d)
			{
				$task = 'deploy' . ucfirst($d);

				$this->{$task}()->run();
			}
		}

		return true;
	}

	/**
	 * Cleanup the given directory
	 *
	 * @param   string  $dir  The dir
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function cleanup($dir)
	{
		// Clean building directory
		$this->_cleanDir($dir);
	}

	/**
	 * Prepare the directories
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function prepareDistDirectory()
	{
		$build = $this->getBuildFolder();

		if (!file_exists($build))
		{
			$this->_mkdir($build);
		}

		$this->cleanup($build);
	}

	/**
	 * Check if local OS is Windows
	 *
	 * @return  boolean
	 *
	 * @since   3.7.3
	 */
	private function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Return the correct path for Windows (needed by CMD)
	 *
	 * @param   string  $path  Linux path
	 *
	 * @return  string
	 *
	 * @since   3.7.3
	 */
	private function getWindowsPath($path)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $path);
	}
}
