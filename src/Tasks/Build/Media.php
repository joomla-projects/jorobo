<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Contract\VerbosityThresholdInterface;
use Robo\Result;

/**
 * Class Media
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Media extends Base
{
    protected $source = null;

    protected $target = null;

    protected $type = "com";

    protected $extName = null;

    /**
     * Initialize Build Task
     *
     * @param   String  $folder   The target directory
     * @param   String  $extName  The extension name
     *
     * @since   1.0
     */
    public function __construct($folder, $extName, $params = [])
    {
        parent::__construct($params);

        $this->source  = $this->getSourceFolder() . "/" . $folder;
        $this->extName = $extName;

        $this->type = substr($extName, 0, 3);

        $target = $this->getBuildFolder() . "/" . $folder;

        if ($this->type == 'mod') {
            $target = $this->getBuildFolder() . "/modules/" . $extName . "/" . $folder;
        } elseif ($this->type == 'plg') {
            $a = explode("_", $this->extName);

            $target = $this->getBuildFolder() . "/plugins/" . $a[1] . "/" . $a[2] . "/" . $folder;
        } elseif ($this->type == 'lib') {
            // Remove lib before - ugly hack
            $ex = str_replace("lib_", "", $this->extName);

            $target = $this->getBuildFolder() . "/libraries/" . $ex . "/" . $folder;
        }

        $this->target = $target;
    }

    /**
     * Runs the media build task
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo("Building media folder " . $this->source . " for " . $this->extName);

        if (!file_exists($this->source)) {
            $this->printTaskInfo("Folder " . $this->source . " does not exist!");

            return Result::success($this);
        }

        $this->prepareDirectory();

        $map = $this->copyTarget($this->source, $this->target);

        $this->setResultFiles($map);

        $this->printTaskSuccess("Finished building media folder " . $this->source . " for " . $this->extName);

        return Result::success($this);
    }

    /**
     * Prepare the directory structure
     *
     * @return  void
     *
     * @since   1.0
     */
    private function prepareDirectory()
    {
        $this->taskFilesystemStack()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->mkdir($this->target)
            ->run();
    }
}
