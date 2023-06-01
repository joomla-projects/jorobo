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
 * Build Library
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Library extends Base
{
    use Tasks;

    protected $source = null;

    protected $target = null;

    protected $libName = null;

    protected $hasComponent = false;

    /**
     * Initialize Build Task
     *
     * @param   string  $libName       Name of the library to build
     * @param   array   $params        Optional params
     * @param   bool    $hasComponent  Has the extension a component (then we need to build different)
     *
     * @since   1.0
     */
    public function __construct($libName, $params, $hasComponent)
    {
        parent::__construct($params);

        // Reset files -> new lib
        $this->resetFiles();

        $this->libName      = $libName;
        $this->hasComponent = $hasComponent;

        $this->source = $this->getSourceFolder() . "/libraries/" . $libName;
        $this->target = $this->getBuildFolder() . "/libraries/" . $libName;
    }

    /**
     * Runs the library build tasks, just copying files currently
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo("Building library " . $this->libName);

        if (!file_exists($this->source)) {
            return Result::error($this, "Folder " . $this->source . " does not exist!");
        }

        $this->prepareDirectory();

        // Libaries are problematic.. we have libraries/name/libraries/name in the end for the build script
        $tar = $this->target;

        if (!$this->hasComponent) {
            $tar = $this->target . "/libraries/" . $this->libName;
        }

        $files = $this->copyTarget($this->source, $tar);

        $lib = $this->libName;

        // Workaround for libraries without lib_
        if (substr($this->libName, 0, 3) != "lib") {
            $lib = 'lib_' . $this->libName;
        }

        // Build media (relative path)
        $media = $this->buildMedia("media/" . $lib, $lib);
        $media->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run();

        $this->addFiles('media', $media->getResultFiles());

        // Build language files for the component
        $language = $this->buildLanguage($lib)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run();

        // Copy XML
        $this->createInstaller($files);

        $this->printTaskSuccess('Finished building library ' . $this->libName);

        return Result::success($this, "Library build");
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

    /**
     * Generate the installer xml file for the library
     *
     * @param   array  $files  The library files
     *
     * @return  void
     *
     * @since   1.0
     */
    private function createInstaller($files)
    {
        $this->printTaskInfo("Creating library installer");

        $xmlFile = $this->target . "/" . $this->libName . ".xml";

        // Version & Date Replace
        $this->replaceInFile($xmlFile);

        // Files and folders
        $f = $this->generateFileList($files);

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##LIBRARYFILES##')
            ->to($f)
            ->run();

        // Language backend files
        $f = $this->generateLanguageFileList($this->getFiles('backendLanguage'));

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##BACKEND_LANGUAGE_FILES##')
            ->to($f)
            ->run();

        // Language frontend files
        $f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

        $this->taskReplaceInFile($xmlFile)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
            ->from('##FRONTEND_LANGUAGE_FILES##')
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
