<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Robo\Task\Development\loadTasks;

/**
 * Build Cli
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @since       1.0
 */
class Cli extends Base implements TaskInterface
{
	use loadTasks;
	use TaskIO;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $source = null;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $target = null;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
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
	 * @return  boolean
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
