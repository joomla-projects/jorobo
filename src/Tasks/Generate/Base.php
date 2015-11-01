<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks\Generate;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use JBuild\Tasks\JTask;

/**
 * Generate base class - contains methods / data used in multiple generateion tasks
 *
 * @package  JBuild\Generate\Base
 */
class Base extends JTask implements TaskInterface
{
	use \Robo\Common\TaskIO;

	/**
	 * Returns true - should never be called on this
	 *
	 * @return  bool
	 */
	public function run()
	{
		return true;
	}
}
