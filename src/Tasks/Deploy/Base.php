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
use Joomla\Jorobo\Tasks\JTask;
use Robo\Task\Development\loadTasks;

/**
 * Deployment base - contains methods / data used in multiple build tasks
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Deploy
 *
 * @since       1.0
 */
class Base extends JTask implements TaskInterface
{
	use loadTasks;
	use TaskIO;

	/**
	 * Returns true
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function run()
	{
		return true;
	}
}
