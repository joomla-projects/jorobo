<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Robo\Result;

/**
 * Deploy project as Zip
 *
 * @package  Joomla\Jorobo\Tasks\Deploy
 *
 * @since    1.0
 */
class Zip extends Base
{
    protected $target = null;

    private $zip = null;

    /**
     * Initialize Build Task
     *
     * @since   1.0
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        $this->target = $this->params['base'] . "/dist/" . $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
        $this->zip    = new \ZipArchive();
    }

    /**
     * Build the package
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo('Zipping ' . $this->getJConfig()->extension . " " . $this->getJConfig()->version);

        // Instantiate the zip archive
        if ($this->zip->open($this->target, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return Result::error($this, 'Could not open ' . $this->target);
        }

        $buildFolder = str_replace('\\', '/', realpath($this->getBuildFolder()));
        $iterator    = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($buildFolder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        // Process the files to zip
        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            $this->zip->addFile($path, substr($path, strlen($buildFolder) + 1));
        }

        // Close the zip archive
        if (!$this->zip->close()) {
            return Result::error($this, 'Could not write zip: ' . $this->zip->getStatusString());
        }

        return Result::success($this);
    }
}
