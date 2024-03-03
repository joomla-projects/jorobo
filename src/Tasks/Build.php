<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Contract\VerbosityThresholdInterface;
use Robo\Result;

/**
 * Class Build
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class Build extends JTask
{
    use \Robo\Task\Development\Tasks;
    use Build\Tasks;
    use Deploy\Tasks;

    /**
     * Build the package
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo('Building ' . $this->getJConfig()->extension . " " . $this->getJConfig()->version);

        if (!$this->checkFolders()) {
            return Result::error($this, 'checkFolders failed');
        }

        // Create directory
        $this->prepareDistDirectory();

        // Build extension
        $this->buildExtension($this->params)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG)
            ->run();

        // Create symlink to current folder
        if ($this->isWindows()) {
            if (is_dir($this->params['base'] . "\dist\current")) {
                rmdir($this->params['base'] . "\dist\current");
            }
            $this->taskExec('mklink /J "' . $this->params['base'] . '\dist\current" "' . $this->getWindowsPath($this->getBuildFolder()) . '"')
                ->run();
        } else {
            if (is_dir($this->params['base'] . "/dist/current")) {
                unlink($this->params['base'] . "/dist/current");
            }
            $this->taskFilesystemStack()
                ->symlink($this->getBuildFolder(), $this->params['base'] . "/dist/current")
                ->run();
        }

        // Support multiple deployment methods, separated by spaces
        $deploy = explode(" ", $this->getJConfig()->target);

        if (count($deploy)) {
            foreach ($deploy as $d) {
                $task = 'deploy' . ucfirst($d);

                $this->{$task}($this->params)->run();
            }
        }

        return Result::success($this, 'Build successful');
    }

    /**
     * Cleanup the given directory
     *
     * @param   string  $dir  The dir
     *
     * @return  void
     *
     * @since   1.0
     */
    private function cleanup($dir)
    {
        // Clean building directory
        $this->_cleanDir($dir);
    }

    /**
     * Prepare the directories
     *
     * @return  void
     *
     * @since   1.0
     */
    private function prepareDistDirectory()
    {
        $build = $this->getBuildFolder();

        if (!file_exists($build)) {
            $this->_mkdir($build);
        }

        $this->cleanup($build);
    }

    /**
     * Check if local OS is Windows
     *
     * @return  boolean
     *
     * @since   3.7.3
     */
    private function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Return the correct path for Windows (needed by CMD)
     *
     * @param   string  $path  Linux path
     *
     * @return  string
     *
     * @since   3.7.3
     */
    private function getWindowsPath($path)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
