<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks\Metrics;

use JBuild\Tasks\Metrics;
use Robo\Tasks;

trait metricsTasks # extends Tasks
{
    /**
     * The metrics task
     *
     * @param   array  $params  - Opt params
     *
     * @return  Metrics
     */
    protected function taskMetrics($params)
    {
        return new Metrics($params);
    }

    /**
     * Calculate all available metrics
     */
    public function metrics($params)
    {
        $this->taskMetrics($params)->run();
    }

    /**
     * Check the codestyle
     */
    public function metricsCodestyle($style = 'Joomla')
    {
        $params = [
            'standard' => $style
        ];

        $this->taskMetrics($params)->codestyle(false);
    }

    /**
     * Measure the mess
     */
    public function metricsMessdetect()
    {
        $this->taskMetrics([])->messdetect(false);
    }

    /**
     * Perform all available checks
     */
    public function check()
    {
        $this->checkCodestyle();
        $this->checkMessdetect();
    }

    /**
     * Check the codestyle
     */
    public function checkCodestyle($style = 'Joomla')
    {
        $params = [
            'standard' => $style
        ];

        $this->taskMetrics($params)->codestyle(true);
    }

    /**
     * Check the mess
     */
    public function checkMessdetect()
    {
        $this->taskMetrics([])->messdetect(true);
    }
}
