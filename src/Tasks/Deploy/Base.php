<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomla_projects\jorobo\Tasks\Deploy;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use joomla_projects\jorobo\Tasks\JTask;

/**
 * Deployment base - contains methods / data used in multiple build tasks
 */
class Base extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * Returns true
	 *
	 * @return  bool
	 */
	public function run()
	{
		return true;
	}
}
