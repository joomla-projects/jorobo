<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use Joomla\Jorobo\Tasks\JTask;

/**
 * Build Cli
 *
 * @package  Joomla\Jorobo\Tasks\Build
 */
class Cli extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	protected $source = null;

	protected $target = null;

	protected $fileMap = null;

	/**
	 * Initialize Build Task
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->source = $this->getSourceFolder() . "/cli";
		$this->target = $this->getBuildFolder() . "/cli";
	}

	/**
	 * Runs the cli build tasks, just copying files currently
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say("Copying CLI files " . $this->source);

		if (!file_exists($this->source))
		{
			$this->say("Folder " . $this->source . " does not exist!");

			return true;
		}

		$this->prepareDirectory();

		$map = $this->copyTarget($this->source, $this->target);

		$this->setResultFiles($map);

		return true;
	}

	/**
	 * Prepare the directory structure
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function prepareDirectory()
	{
		$this->_mkdir($this->target);
	}
}
