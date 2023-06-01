<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Result;

/**
 * Class Language
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Language extends Base
{
    protected $ext = null;

    protected $type = "com";

    protected $target = null;

    protected $adminLangPath = null;

    protected $frontLangPath = null;

    protected $hasAdminLang = true;

    protected $hasFrontLang = true;

    /**
     * Initialize Build Task
     *
     * @param   String  $extension  The extension (component, module etc.)
     *
     * @since   1.0
     */
    public function __construct($extension, $params = [])
    {
        parent::__construct($params);

        $this->adminLangPath = $this->getSourceFolder() . "/administrator/language";
        $this->frontLangPath = $this->getSourceFolder() . "/language";

        $this->ext = $extension;

        $this->type = substr($extension, 0, 3);
    }

    /**
     * Returns true
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        if (!$this->hasAdminLang && !$this->hasFrontLang) {
            // No Language files
            return Result::success($this);
        }

        $this->printTaskInfo("Building language for " . $this->ext . " | Type " . $this->type);

        // Make sure we have the language folders in our target
        $this->prepareDirectories();

        $dest = $this->getBuildFolder();

        if ($this->type == "mod") {
            $dest .= "/modules/" . $this->ext;
        } elseif ($this->type == "plg") {
            $a    = explode("_", $this->ext);
            $dest .= "/plugins/" . $a[1] . "/" . $a[2];
        } elseif ($this->type == "pkg") {
            $dest .= "/administrator/manifests/packages/" . $this->ext;
        } elseif ($this->type == "lib") {
            // Remove lib before - ugly hack
            $ex   = str_replace("lib_", "", $this->ext);
            $dest .= "/libraries/" . $ex;
        } elseif ($this->type == "tpl") {
            $a    = explode("_", $this->ext);
            $dest .= "/templates/" . $a[1];
        }

        if ($this->hasAdminLang) {
            $map = $this->copyLanguage("administrator/language", $dest);
            $this->addFiles('backendLanguage', $map);
        }

        if ($this->hasFrontLang) {
            $map = $this->copyLanguage("language", $dest);
            $this->addFiles('frontendLanguage', $map);
        }

        $this->printTaskSuccess("Finished building language for " . $this->ext . " | Type " . $this->type);

        return Result::success($this);
    }

    /**
     * Analyze the extension structure
     *
     * @return  void
     *
     * @since   1.0
     */
    private function analyze()
    {
        // Check for all languages here
        if (empty(glob($this->adminLangPath . "/*/*" . $this->ext . "*.ini"))) {
            $this->hasAdminLang = false;
        }

        if (empty(glob($this->frontLangPath . "/*/*" . $this->ext . "*.ini"))) {
            $this->hasFrontLang = false;
        }
    }

    /**
     * Prepare the directory structure
     *
     * @return  boolean
     *
     * @since   1.0
     */
    private function prepareDirectories()
    {
        if ($this->type == "com") {
            if ($this->hasAdminLang) {
                $this->taskFilesystemStack()
                    ->setVerbosityThreshold(self::VERBOSITY_VERY_VERBOSE)
                    ->mkdir($this->getBuildFolder() . "/administrator/language")
                    ->run();
            }

            if ($this->hasFrontLang) {
                $this->taskFilesystemStack()
                    ->setVerbosityThreshold(self::VERBOSITY_VERY_VERBOSE)
                    ->mkdir($this->getBuildFolder() . "/language")
                    ->run();
            }
        }

        if ($this->type == "mod") {
            $this->taskFilesystemStack()
                ->setVerbosityThreshold(self::VERBOSITY_VERY_VERBOSE)
                ->mkdir($this->getBuildFolder() . "/modules/" . $this->ext . "/language")
                ->run();
        }

        if ($this->type == "plg") {
            $a = explode("_", $this->ext);

            $this->taskFilesystemStack()
                ->setVerbosityThreshold(self::VERBOSITY_VERY_VERBOSE)
                ->mkdir($this->getBuildFolder() . "/plugins/" . $a[1] . "/" . $a[2] . "/administrator/language")
                ->run();
        }

        return true;
    }

    /**
     * Copy language files
     *
     * @param   string  $dir     The directory (administrator/language or language or mod_xy/language etc)
     * @param   String  $target  The target directory
     *
     * @return   array
     *
     * @since   1.0
     */
    public function copyLanguage($dir, $target)
    {
        // Equals administrator/language or language
        $path  = $this->getSourceFolder() . "/" . $dir;
        $files = [];

        $hdl = opendir($path);

        while ($entry = readdir($hdl)) {
            $p = $path . "/" . $entry;

            // Which languages do we have
            // Ignore hidden files
            if (substr($entry, 0, 1) != '.') {
                // Language folders
                if (!is_file($p)) {
                    // Make folder at destination
                    $this->taskFilesystemStack()
                        ->setVerbosityThreshold(self::VERBOSITY_VERY_VERBOSE)
                        ->mkdir($target . "/" . $dir . "/" . $entry)
                        ->run();

                    $fileHdl = opendir($p);

                    while ($file = readdir($fileHdl)) {
                        // Only copy language files for this extension (and sys files..)
                        if (substr($file, 0, 1) !== '.' && strpos($file, $this->ext . ".") !== false) {
                            $files[] = [$entry => $file];

                            // Copy file
                            $this->taskFilesystemStack()
                                ->setVerbosityThreshold(self::VERBOSITY_VERY_VERBOSE)
                                ->copy($p . "/" . $file, $target . "/" . $dir . "/" . $entry . "/" . $file)
                                ->run();
                        }
                    }

                    closedir($fileHdl);
                }
            }
        }

        closedir($hdl);

        return $files;
    }
}
