<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Contract\TaskInterface;
use Robo\Result;

/**
 * Build Cli
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Cli extends Base implements TaskInterface
{
    protected $source = null;

    protected $target = null;

    protected $fileMap = null;

    /**
     * Initialize Build Task
     *
     * @since   1.0
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        $this->source = $this->getSourceFolder() . "/cli";
        $this->target = $this->getBuildFolder() . "/cli";
    }

    /**
     * Runs the cli build tasks, just copying files currently
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->say("Copying CLI files " . $this->source);

        if (!file_exists($this->source)) {
            return Result::success($this, "Folder " . $this->source . " does not exist!");
        }

        $this->prepareDirectory();

        $map = $this->copyTarget($this->source, $this->target);

        $this->setResultFiles($map);

        return Result::success($this, "Cli build");
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
        $this->_mkdir($this->target);
    }
}
