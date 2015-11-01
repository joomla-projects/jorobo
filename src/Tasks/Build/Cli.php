<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks\Build;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use JBuild\Tasks\JTask;

/**
 * Build Cli
 *
 * @package  JBuild\Tasks\Build
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
	 */
	private function prepareDirectory()
	{
		$this->_mkdir($this->target);
	}
}
