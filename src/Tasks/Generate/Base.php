<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Generate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Joomla\Jorobo\Tasks\JTask;

/**
 * Generate base class - contains methods / data used in multiple generateion tasks
 *
 * @package  Joomla\Jorobo\Generate\Base
 */
class Base extends JTask implements TaskInterface
{
	use TaskIO;

	/**
	 * Returns true - should never be called on this
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
