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

use JBuild\Tasks\JTask;
use Robo\Common\ExecOneCommand;
use Robo\Common\TaskIO;
use Robo\Contract\CommandInterface;

/**
 * Class CodeStyle
 *
 * @package JBuild\Tasks\Metrics
 */
class CodeStyle extends JTask implements CommandInterface
{
    private $options = [
        'standard' => 'joomla',
    ];

    protected $command = './vendor/bin/phpcs';

    use TaskIO;
    use ExecOneCommand;

    /**
     * Compute or check for metrics
     *
     * @return  bool
     */
    public function run()
    {
        foreach ($this->options as $option => $value)
        {
            $this->option($option, $value);
        }

        $this->printTaskInfo("Running PHP CodeSniffer\n". $this->command . $this->arguments);

        return $this->executeCommand($this->getCommand());
    }

    /**
     * Select the standard to be used.
     *
     * PHPCS, MySource, Zend, Squiz, PSR2, PEAR and PSR1
     *
     * @param string $standard
     * @return $this
     */
    public function standard($standard = 'Joomla')
    {
        $this->options['standard'] = strtolower($standard);

        return $this;
    }

    /**
     * Pass option to executable. Options are prefixed with `--` , value can be provided in second parameter
     *
     * @param $option
     * @param null $value
     * @return $this
     */
    public function option($option, $value = null)
    {
        if (!empty($option) and $option[0] != '-') {
            $option = "--$option";
        }

        if ($option == '--standard' && $value == 'joomla')
        {
            $value = 'vendor/greencape/joomla-cs/Joomla';
        }
        $this->arguments .= ' ' . $option;
        $this->arguments .= empty($value) ? '' : '=' . $value;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getCommand()
    {
        return $this->command . $this->arguments . ' -- ' . $this->getSourceFolder();
    }
}
