<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

use Joomla\Jorobo\Tasks\JTask;
use Robo\Contract\TaskInterface;

/**
 * Generate base class - contains methods / data used in multiple generateion tasks
 *
 * @package  Joomla\Jorobo\Generate\Base
 *
 * @since    1.0
 */
abstract class Base extends JTask implements TaskInterface
{
    use \Robo\Common\TaskIO;
}
