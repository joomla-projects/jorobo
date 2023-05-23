<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Joomla\Jorobo\Tasks\JTask;

/**
 * Deployment base - contains methods / data used in multiple build tasks
 *
 * @package  Joomla\Jorobo\Tasks\Deploy
 *
 * @since    1.0
 */
abstract class Base extends JTask
{
    use \Robo\Task\Development\Tasks;
}
