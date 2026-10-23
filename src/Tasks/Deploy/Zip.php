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

        $buildFolder = $this->determineZipRoot(str_replace('\\', '/', realpath($this->getBuildFolder())));

        if ($buildFolder === null) {
            $this->zip->close();

            return Result::error(
                $this,
                'The build contains several extensions but no manifest in its root. Use the "package" target instead of "zip".'
            );
        }

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

    /**
     * Determine the folder which has to become the root of the zip file
     *
     * Joomla expects the manifest in the root of an installable zip. Components (and file extensions) get their
     * manifest copied to the root of the build folder, while modules, plugins, templates and libraries are built
     * into their Joomla folder (e.g. plugins/system/foo), which already is a self-contained installable folder.
     *
     * @param   string  $buildFolder  The build folder
     *
     * @return  ?string  The folder to zip or null if there is no single installable extension
     *
     * @since   __DEPLOY_VERSION__
     */
    private function determineZipRoot($buildFolder)
    {
        if (count(glob($buildFolder . '/*.xml')) > 0) {
            return $buildFolder;
        }

        $candidates = array_merge(
            glob($buildFolder . '/modules/*', GLOB_ONLYDIR),
            glob($buildFolder . '/administrator/modules/*', GLOB_ONLYDIR),
            glob($buildFolder . '/plugins/*/*', GLOB_ONLYDIR),
            glob($buildFolder . '/templates/*', GLOB_ONLYDIR),
            glob($buildFolder . '/libraries/*', GLOB_ONLYDIR)
        );

        if (count($candidates) !== 1) {
            return null;
        }

        return $candidates[0];
    }
}
