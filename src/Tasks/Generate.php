<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Robo\Task\Development\loadTasks;

/**
 * Building class for extensions
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks
 *
 * @since       1.0
 */
class Generate extends JTask implements TaskInterface
{
	use loadTasks;
	use TaskIO;
	use Generate\generateTasks;

	/**
	 * Additional params
	 *
	 * @var array|null
	 *
	 * @since   1.0
	 */
	protected $params = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   array  $params  Additional params
	 *
	 * @since   1.0
	 */
	public function __construct($params)
	{
		parent::__construct();

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
		$this->prepareSouceDirectory();

		$this->say('Not implemented yet');

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
	private function prepareSouceDirectory()
	{
		if (!file_exists($this->sourceFolder))
		{
			$this->say('Creating source folder');
			$this->_mkdir($this->sourceFolder);
		}
	}
}
