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
 * Class Template
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Template extends Base
{
    use Tasks;

    protected $templateName = null;

    protected $source = null;

    protected $target = null;

    /**
     * Initialize Build Task
     *
     * @param   string  $templateName  Name of the template
     * @param   array   $params        Optional params
     *
     * @since   1.0
     */
    public function __construct($templateName, $params = [])
    {
        parent::__construct($params);

        // Reset files - > new template
        $this->resetFiles();

        $this->templateName = $templateName;

        $this->source = $this->getSourceFolder() . "/templates/" . $templateName;
        $this->target = $this->getBuildFolder() . "/templates/" . $templateName;
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
        $this->printTaskInfo('Building template: ' . $this->templateName);

        // Prepare directories
        $this->prepareDirectories();

        $files = $this->copyTarget($this->source, $this->target);

        // Build media (relative path)
        $media = $this->buildMedia("media/" . $this->templateName, $this->templateName);
        $media->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run();

        $this->addFiles('media', $media->getResultFiles());

        // Build language files for the component
        if (is_dir($this->getSourceFolder() . '/language')) {
            $language = $this->buildLanguage('tpl_' . $this->templateName)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
                ->run();
        }

        // Update XML and script.php
        $this->createInstaller($files);

        $this->printTaskSuccess('Finished building template: ' . $this->templateName);

        return Result::success($this, "Template build");
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
        $this->_mkdir($this->target);
    }

    /**
     * Generate the installer xml file for the template
     *
     * @param   array  $files  The template files
     *
     * @return  void
     *
     * @since   1.0
     */
    private function createInstaller($files)
    {
        $this->printTaskInfo("Creating template installer");

        $xmlFile = $this->target . "/templateDetails.xml";

        // Version & Date Replace
        $this->replaceInFile($xmlFile);

        // Files and folders
        $f = $this->generateFileList($files);

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##TEMPLATE_FILES##')
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
