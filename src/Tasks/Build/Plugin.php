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
 * Class Plugin
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Plugin extends Base
{
    use Tasks;

    protected $plgName = null;

    protected $plgType = null;

    protected $source = null;

    protected $target = null;

    /**
     * Initialize Build Task
     *
     * @param   string  $type    Type of the plugin
     * @param   string  $name    Name of the plugin
     * @param   array   $params  Optional params
     *
     * @since   1.0
     */
    public function __construct($type, $name, $params = [])
    {
        parent::__construct($params);

        // Reset files - > new module
        $this->resetFiles();

        $this->plgName = $name;
        $this->plgType = $type;

        $this->source = $this->getSourceFolder() . "/plugins/" . $type . "/" . $name;
        $this->target = $this->getBuildFolder() . "/plugins/" . $type . "/" . $name;
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
        $this->printTaskInfo('Building plugin: ' . $this->plgName . " (" . $this->plgType . ")");

        // Prepare directories
        $this->prepareDirectories();

        $files = $this->copyTarget($this->source, $this->target);

        // Build media (relative path)
        $media = $this->buildMedia("media/plg_" . $this->plgType . "_" . $this->plgName, 'plg_' . $this->plgType . "_" . $this->plgName);
        $media->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run();

        $this->addFiles('media', $media->getResultFiles());

        // Build language files
        if (is_dir($this->getSourceFolder() . '/administrator/language')) {
            $this->buildLanguage("plg_" . $this->plgType . "_" . $this->plgName)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
                ->run();
        }

        // Update XML and script.php
        $this->createInstaller($files);

        $this->printTaskSuccess('Finished building plugin: ' . $this->plgName . " (" . $this->plgType . ")");

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
     * Generate the installer xml file for the plugin
     *
     * @param   array  $files  The module files
     *
     * @return  void
     *
     * @since   1.0
     */
    private function createInstaller($files)
    {
        $this->printTaskInfo("Creating plugin installer");

        $xmlFile = $this->target . "/" . $this->plgName . ".xml";

        // Version & Date Replace
        $this->replaceInFile($xmlFile);

        // Files and folders
        $f = $this->generatePluginFileList($files, $this->plgName);

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##FILES##')
            ->to($f)
            ->run();

        // Language files
        $f = $this->generateLanguageFileList($this->getFiles('backendLanguage'));

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
