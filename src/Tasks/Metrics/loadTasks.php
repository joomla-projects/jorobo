<?php
/**
 * @package    JBuild
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       20.09.15
 *
 * @copyright  Copyright (C) 2008 - 2015 Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Jorobo\Tasks\Metrics;

use Joomla\Jorobo\Tasks\Metrics;

trait loadTasks
{
    /**
     * The metrics task
     *
     * @return Metrics
     */
    protected function taskMetrics($options = [])
    {
        return new Metrics($options);
    }

    public function metrics($options = [])
    {
        $this->taskMetrics($options)->run();
    }
}
