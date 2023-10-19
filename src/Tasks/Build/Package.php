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
 * Class Package
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Package extends Base
{
    use Tasks;

    /**
     * Initialize Build Task
     *
     * @param   array  $params  The target directory
     *
     * @since   1.0
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // Reset files -> new package
        $this->resetFiles();
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
        $this->printTaskInfo('Building package ' . $this->getExtensionName());

        // Build language files for the package
        if (is_dir($this->getSourceFolder() . '/administrator/language')) {
            $language = $this->buildLanguage("pkg_" . $this->getExtensionName())
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
                ->run();
        }

        // Update XML and script.php
        $this->createInstaller();

        $this->printTaskSuccess('Finished building package ' . $this->getExtensionName());

        return Result::success($this);
    }

    /**
     * Generate the installer xml file for the package
     *
     * @return  void
     *
     * @since   1.0
     */
    private function createInstaller()
    {
        $this->printTaskInfo("Creating package installer");

        // Copy XML and script.php
        $sourceFolder = $this->getSourceFolder() . "/administrator/manifests/packages";
        $targetFolder = $this->getBuildFolder() . "/administrator/manifests/packages";
        $xmlFile      = $targetFolder . "/pkg_" . $this->getExtensionName() . ".xml";
        $scriptFile   = $targetFolder . "/" . $this->getExtensionName() . "/script.php";

        $this->taskFilesystemStack()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->copy($sourceFolder . "/pkg_" . $this->getExtensionName() . ".xml", $xmlFile)
            ->run();

        // Version & Date Replace
        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from(['##DATE##', '##YEAR##', '##VERSION##'])
            ->to([$this->getDate(), date('Y'), $this->getJConfig()->version])
            ->run();

        if (is_file($sourceFolder . "/" . $this->getExtensionName() . "/script.php")) {
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->copy($sourceFolder . "/" . $this->getExtensionName() . "/script.php", $scriptFile)
                ->run();

            $this->taskReplaceInFile($scriptFile)
                ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                ->from(['##DATE##', '##YEAR##', '##VERSION##'])
                ->to([$this->getDate(), date('Y'), $this->getJConfig()->version])
                ->run();
        }

        // Language files
        $f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##LANGUAGE_FILES##')
            ->to($f)
            ->run();
    }
}
