<?php
/**
 * @package    JBuild
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       20.09.15
 *
 * @copyright  Copyright (C) 2008 - 2015 Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace JBuild\Tasks\Metrics;

use Robo\Exception\TaskException;

trait loadTasks
{
    /**
     * The metrics task
     *
     * @param $subCommand
     * @return CodeStyle
     * @throws TaskException
     */
    protected function taskMetrics($subCommand)
    {
        switch (strtolower($subCommand))
        {
            case 'codestyle':
                return new CodeStyle;

            default:
                throw new TaskException(__CLASS__, "Unknown metric $subCommand");
        }
    }

    /**
     * Check the codestyle - not implemented yet
     * @param string $style
     */
    public function metricsCodestyle($style = 'Joomla')
    {
        $this->taskMetrics('CodeStyle')
            ->standard($style)
            ->run();
    }

}
