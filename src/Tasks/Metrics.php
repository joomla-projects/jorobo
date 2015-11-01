<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Metrics class for extensions
 *
 * @package  JBuild\Tasks
 */
class Metrics extends JTask implements TaskInterface
{
    /**
     * Compute or check for metrics
     *
     * @return  bool
     */
    public function run($bail = false)
    {
        $this->say('Not implemented yet');
        $this->say(print_r($this->getConfig()->params, true));

        $this->codestyle($bail);
        $this->messdetect($bail);
    }

    public function codestyle($bail = false)
    {
        if ((bool) $bail)
        {
            $this->say('Checking code style according to ' . $this->getConfig()->params['standard'] . ' standard');
        }
        else
        {
            $this->say('Generating code style report according to ' . $this->getConfig()->params['standard'] . ' standard');
        }
    }

    public function messdetect($bail = false)
    {
        if ((bool) $bail)
        {
            $this->say('Checking for mess');
        }
        else
        {
            $this->say('Generating mess report');
        }
    }
}
