<?php
/**
 * @package    JBuild
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       03.10.15
 *
 * @copyright  Copyright (C) 2008 - 2015 Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace JBuild\Tasks\Metrics;
use Robo\Common\IO;

/**
 * Metrics class for extensions
 *
 * @package  JBuild\Tasks
 */
class Metrics
{
    use IO;
    use loadTasks;

    /**
     * Calculate all available metrics - not implemented yet
     */
    public function metrics($params)
    {
    }

    /**
     * Measure the mess - not implemented yet
     */
    public function metricsMessdetect()
    {
        $this->taskMetrics([])->messdetect(false);
    }

    /**
     * Perform all available checks - not implemented yet
     */
    public function check()
    {
        $this->checkCodestyle();
        $this->checkMessdetect();
    }

    /**
     * Check the codestyle - not implemented yet
     */
    public function checkCodestyle($style = 'Joomla')
    {
        $params = [
            'standard' => $style
        ];

        $this->taskMetrics($params)->codestyle(true);
    }

    /**
     * Check the mess - not implemented yet
     */
    public function checkMessdetect()
    {
        $this->taskMetrics([])->messdetect(true);
    }
}
