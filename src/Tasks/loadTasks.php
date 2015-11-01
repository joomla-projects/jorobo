<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks;

use JBuild\Tasks\Metrics\metricsTasks;

trait loadTasks
{
    use metricsTasks;

    /**
     * Map Task
     *
     * @param   String  $target  - The target directory
     *
     * @return  Map
     */
    protected function taskMap($target)
    {
        return new Map($target);
    }

    /**
     * The build task
     *
     * @param   array  $params  - Opt params
     *
     * @return  Build
     */
    protected function taskBuild($params)
    {
        return new Build($params);
    }

    /**
     * The generate task
     *
     * @param   array  $params  - Opt params
     *
     * @return  Build
     */
    protected function taskGenerate($params)
    {
        return new Generate($params);
    }
}
