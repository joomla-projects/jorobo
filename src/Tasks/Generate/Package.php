<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

use Robo\Result;

/**
 * Generate a package skeleton
 *
 * @package  Joomla\Jorobo\Tasks\Generate
 *
 * @since    1.0
 */
class Package extends Base
{
    use \Robo\Task\Development\Tasks;

    public function run()
    {
        // TODO: Implement run() method.
        return Result::success($this, 'Package build');
    }
}
