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
        $this->zip->open($this->target, \ZipArchive::CREATE);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->getBuildFolder()),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // Process the files to zip
        foreach ($iterator as $subfolder) {
            if ($subfolder->isFile()) {
                // Set all separators to forward slashes for comparison
                $usefolder = str_replace('\\', '/', $subfolder->getPath());

                // Drop the folder part as we don't want them added to archive
                $addpath = str_ireplace($this->getBuildFolder(), '', $usefolder);

                // Remove preceding slash
                $findfirst = strpos($addpath, '/');

                if ($findfirst == 0 && $findfirst !== false) {
                    $addpath = substr($addpath, 1);
                }

                if (strlen($addpath) > 0 || empty($addpath)) {
                    $addpath .= '/';
                }

                $options = ['add_path' => $addpath, 'remove_all_path' => true];
                $this->zip->addGlob($usefolder . '/*.*', GLOB_BRACE, $options);
            }
        }

        // Close the zip archive
        $this->zip->close();

        return Result::success($this);
    }
}
