<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Psr\Log\LogLevel;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Result;

/**
 * Class Component
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Component extends Base
{
    use Tasks;

    protected $adminPath = null;

    protected $apiPath = null;

    protected $frontPath = null;

    protected $hasAdmin = true;

    protected $hasApi = true;

    protected $hasFront = true;

    protected $hasMedia = false;

    /**
     * Initialize Build Task
     *
     * @param   array  $params  The target directory
     *
     * @since   1.0
     */
    public function __construct($params)
    {
        parent::__construct($params);

        // Reset files - > new component
        $this->resetFiles();

        $this->adminPath = $this->getSourceFolder() . "/administrator/components/com_" . $this->getExtensionName();
        $this->apiPath   = $this->getSourceFolder() . "/api/components/com_" . $this->getExtensionName();
        $this->frontPath = $this->getSourceFolder() . "/components/com_" . $this->getExtensionName();
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
        $this->printTaskInfo('Building com_' . $this->getExtensionName() . ' component');

        // Analyze extension structure
        $this->analyze();

        // Prepare directories
        $this->prepareDirectories();

        if ($this->hasAdmin) {
            $this->logger->log(LogLevel::INFO, 'Copy admin files', $this->getTaskContext());
            $adminFiles = $this->copyTarget($this->adminPath, $this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName());

            $this->addFiles('backend', $adminFiles);
        }

        if ($this->hasApi) {
            $this->logger->log(LogLevel::INFO, 'Copy API files', $this->getTaskContext());
            $apiFiles = $this->copyTarget($this->apiPath, $this->getBuildFolder() . "/api/components/com_" . $this->getExtensionName());

            $this->addFiles('api', $apiFiles);
        }

        if ($this->hasFront) {
            $this->logger->log(LogLevel::INFO, 'Copy frontend files', $this->getTaskContext());
            $frontendFiles = $this->copyTarget($this->frontPath, $this->getBuildFolder() . "/components/com_" . $this->getExtensionName());

            $this->addFiles('frontend', $frontendFiles);
        }

        // Build media (relative path)
        if ($this->hasMedia) {
            $this->logger->log(LogLevel::INFO, 'Copy media files', $this->getTaskContext());
            $media = $this->buildMedia("media/com_" . $this->getExtensionName(), 'com_' . $this->getExtensionName(), $this->params);
            $media->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
                ->run();

            $this->addFiles('media', $media->getResultFiles());
        }

        // Build language files for the component
        if (is_dir($this->getSourceFolder() . '/administrator/language')) {
            $language = $this->buildLanguage("com_" . $this->getExtensionName(), $this->params)
                ->setVerbosityThreshold(self::VERBOSITY_VERBOSE);
            $language->run();
        }

        // Update XML and script.php
        $this->createInstaller();

        // Copy XML and script.php to root
        $this->logger->log(LogLevel::INFO, 'Copy manifest and (optional) script file', $this->getTaskContext());
        $adminFolder = $this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName();
        $xmlFile     = $adminFolder . "/" . $this->getExtensionName() . ".xml";
        $scriptFile  = $adminFolder . "/script.php";

        $this->taskFilesystemStack()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->copy($xmlFile, $this->getBuildFolder() . "/" . $this->getExtensionName() . ".xml")
            ->run();

        if (file_exists($scriptFile)) {
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->copy($scriptFile, $this->getBuildFolder() . "/script.php")
                ->run();
        }

        // Copy Readme
        if (is_file($this->params['base'] . "/docs/README.md")) {
            $this->logger->log(LogLevel::INFO, 'Copy README from /docs folder', $this->getTaskContext());
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->copy($this->params['base'] . "/docs/README.md", $this->getBuildFolder() . "/README")
                ->run();
        }

        $this->printTaskSuccess('Finished building com_' . $this->getExtensionName() . ' component');

        return Result::success($this, "Component build");
    }

    /**
     * Analyze the component structure
     *
     * @return  void
     *
     * @since   1.0
     */
    private function analyze()
    {
        if (!file_exists($this->adminPath)) {
            $this->hasAdmin = false;
        }

        if (!file_exists($this->apiPath)) {
            $this->hasApi = false;
        }

        if (!file_exists($this->frontPath)) {
            $this->hasFront = false;
        }

        if (file_exists($this->getSourceFolder() . "/media/com_" . $this->getExtensionName())) {
            $this->hasMedia = true;
        }
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
        if ($this->hasAdmin) {
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->mkdir($this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName())
                ->run();
        }

        if ($this->hasApi) {
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->mkdir($this->getBuildFolder() . "/api/components/com_" . $this->getExtensionName())
                ->run();
        }

        if ($this->hasFront) {
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->mkdir($this->getBuildFolder() . "/components/com_" . $this->getExtensionName())
                ->run();
        }
    }

    /**
     * Generate the installer xml file for the component
     *
     * @return  void
     *
     * @since   1.0
     */
    private function createInstaller()
    {
        $this->printTaskInfo('Creating component installer');

        $adminFolder = $this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName();
        $xmlFile     = $adminFolder . "/" . $this->getExtensionName() . ".xml";
        $configFile  = $adminFolder . "/config.xml";
        $scriptFile  = $adminFolder . "/script.php";

        // Version & Date Replace
        $this->replaceInFile($xmlFile);
        $this->replaceInFile($scriptFile);
        $this->replaceInFile($configFile);

        // Files and folders
        if ($this->hasAdmin) {
            $f = $this->generateFileList($this->getFiles('backend'));

            $this->taskReplaceInFile($xmlFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from('##BACKEND_COMPONENT_FILES##')
                ->to($f)
                ->run();

            // Language files
            $f = $this->generateLanguageFileList($this->getFiles('backendLanguage'));

            $this->taskReplaceInFile($xmlFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from('##BACKEND_LANGUAGE_FILES##')
                ->to($f)
                ->run();
        }

        if ($this->hasApi) {
            $f = $this->generateFileList($this->getFiles('api'));

            $this->taskReplaceInFile($xmlFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from('##API_COMPONENT_FILES##')
                ->to($f)
                ->run();
        }

        if ($this->hasFront) {
            $f = $this->generateFileList($this->getFiles('frontend'));

            $this->taskReplaceInFile($xmlFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from('##FRONTEND_COMPONENT_FILES##')
                ->to($f)
                ->run();

            // Language files
            $f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

            $this->taskReplaceInFile($xmlFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from('##FRONTEND_LANGUAGE_FILES##')
                ->to($f)
                ->run();
        }

        // Media files
        if ($this->hasMedia) {
            $f = $this->generateFileList($this->getFiles('media'));

            $this->taskReplaceInFile($xmlFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from('##MEDIA_FILES##')
                ->to($f)
                ->run();
        }
    }
}
