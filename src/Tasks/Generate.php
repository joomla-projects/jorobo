<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomla_projects\jorobo\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Building class for extensions
 *
 * @package  joomla_projects\jorobo\Tasks
 */
class Generate extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use Generate\generateTasks;

	/**
	 * @var array|null
	 */
	protected $params = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   array  $params  Additional params
	 */
	public function __construct($params)
	{
		parent::__construct();

		$this->params = $params;
	}

	/**
	 * Build the package
	 *
	 * @return  bool
	 */
	public function run()
	{
		$this->prepareSouceDirectory();

		$this->say('Not implemented yet');
	}

	/**
	 * Cleanup the given directory
	 *
	 * @param   string  $dir  The dir
	 *
	 * @return  void
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
	 */
	private function prepareSouceDirectory()
	{
		if (!file_exists($this->sourceFolder))
		{
			$this->say('Creating source folder');
			$this->_mkdir($this->sourceFolder);
		}
	}
}
