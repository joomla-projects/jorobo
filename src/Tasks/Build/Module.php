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
 * Class Module
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Module extends Base
{
    use Tasks;

    protected $modName = null;

    protected $source = null;

    protected $target = null;

    /**
     * Initialize Build Task
     *
     * @param   string  $modName  Name of the module
     * @param   array   $params   Optional params
     *
     * @since   1.0
     */
    public function __construct($modName, $params = [])
    {
        parent::__construct($params);

        // Reset files - > new module
        $this->resetFiles();

        $this->modName = $modName;

        $this->source = $this->getSourceFolder() . "/modules/" . $modName;
        $this->target = $this->getBuildFolder() . "/modules/" . $modName;
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
        $this->printTaskInfo('Building module: ' . $this->modName);

        // Prepare directories
        $this->prepareDirectories();

        $files = $this->copyTarget($this->source, $this->target);

        // Build media (relative path)
        $media = $this->buildMedia("media/" . $this->modName, $this->modName);
        $media->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run();

        $this->addFiles('media', $media->getResultFiles());

        // Build language files for the module
        if (is_dir($this->getSourceFolder() . '/language')) {
            $language = $this->buildLanguage($this->modName)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
                ->run();
        }

        // Update XML and script.php
        $this->createInstaller($files);

        $this->printTaskSuccess('Finished building module: ' . $this->modName);

        return Result::success($this);
    }

    /**
     * Prepare the directory structure
     *
     * @return  void
     *
     * @since   1.0
     */
    private function prepareDirectories()
    {
        $this->taskFilesystemStack()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->mkdir($this->target)
            ->run();
    }

    /**
     * Generate the installer xml file for the module
     *
     * @param   array  $files  The module files
     *
     * @return  void
     *
     * @since   1.0
     */
    private function createInstaller($files)
    {
        $this->printTaskInfo("Creating module installer");

        $xmlFile = $this->target . "/" . $this->modName . ".xml";

        // Version & Date Replace
        $this->replaceInFile($xmlFile);

        // Files and folders
        $f = $this->generateModuleFileList($files, $this->modName);

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##MODULE_FILES##')
            ->to($f)
            ->run();

        // Language files
        $f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##LANGUAGE_FILES##')
            ->to($f)
            ->run();

        // Media files
        $f = $this->generateFileList($this->getFiles('media'));

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##MEDIA_FILES##')
            ->to($f)
            ->run();
    }
}
