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
 * Class Package
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Package extends Base implements TaskInterface
{
    use buildTasks;

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
        $this->say('Building package');

        // Build language files for the package
        $language = $this->buildLanguage("pkg_" . $this->getExtensionName());
        $language->run();

        // Update XML and script.php
        $this->createInstaller();

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
        $this->say("Creating package installer");

        // Copy XML and script.php
        $sourceFolder = $this->getSourceFolder() . "/administrator/manifests/packages";
        $targetFolder = $this->getBuildFolder() . "/administrator/manifests/packages";
        $xmlFile      = $targetFolder . "/pkg_" . $this->getExtensionName() . ".xml";
        $scriptFile   = $targetFolder . "/" . $this->getExtensionName() . "/script.php";

        $this->_copy($sourceFolder . "/pkg_" . $this->getExtensionName() . ".xml", $xmlFile);

        // Version & Date Replace
        $this->taskReplaceInFile($xmlFile)
            ->from(['##DATE##', '##YEAR##', '##VERSION##'])
            ->to([$this->getDate(), date('Y'), $this->getJConfig()->version])
            ->run();

        if (is_file($sourceFolder . "/" . $this->getExtensionName() . "/script.php")) {
            $this->_copy($sourceFolder . "/" . $this->getExtensionName() . "/script.php", $scriptFile);

            $this->taskReplaceInFile($scriptFile)
                ->from(['##DATE##', '##YEAR##', '##VERSION##'])
                ->to([$this->getDate(), date('Y'), $this->getJConfig()->version])
                ->run();
        }

        // Language files
        $f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

        $this->taskReplaceInFile($xmlFile)
            ->from('##LANGUAGE_FILES##')
            ->to($f)
            ->run();
    }
}
