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
use Robo\Contract\TaskInterface;

/**
 * Class CodeStyle
 *
 * @package JBuild\Tasks\Metrics
 */
class CodeStyle implements  TaskInterface
{
    private $options = [
        'standard' => 'joomla',
    ];

    use IO;

    /**
     * Compute or check for metrics
     *
     * @return  bool
     */
    public function run()
    {
        $this->say('Not implemented yet');
    }

    /**
     * @param string $standard
     * @return $this
     */
    public function standard($standard = 'Joomla')
    {
        $this->options['standard'] = strtolower($standard);

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
