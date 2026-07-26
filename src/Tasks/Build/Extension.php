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
 * The supervisor
 *
 * @package  Joomla\Jorobo\Tasks\Build
 *
 * @since    1.0
 */
class Extension extends Base
{
    use Tasks;

    /**
     * @var   array
     */
    protected $params = null;

    private $hasComponents = true;

    private $hasModules = true;

    private $hasAdminModules = false;

    private $hasPackage = true;

    private $hasPlugins = true;

    private $hasLibraries = true;

    private $hasFile = true;

    private $hasTemplates = true;

    private $modules = [];

    private $adminModules = [];

    private $plugins = [];

    private $libraries = [];

    private $templates = [];

    /**
     * Build the package
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo('Building ' . $this->getJConfig()->extension . ' extension package');

        $this->analyze();

        // Build component
        if ($this->hasComponents) {
            $path = $this->getSourceFolder() . "/administrator/components";

            // Get every component
            $dir = new \DirectoryIterator($path);

            foreach ($dir as $component) {
                if (
                    $component->isDot()
                    || substr($component->getFilename(), 0, 4) != 'com_'
                    || !is_dir($path . '/' . $component->getFilename())
                ) {
                    continue;
                }

                $this->buildComponent(substr($component->getFilename(), 4), $this->params)->run();
            }
        }

        // Frontend Modules
        if ($this->hasModules) {
            $path = $this->getSourceFolder() . "/modules";

            // Get every module
            $dir = new \DirectoryIterator($path);

            foreach ($dir as $module) {
                if (
                    $module->isDot()
                    || substr($module->getFilename(), 0, 4) != 'mod_'
                    || !is_dir($path . '/' . $module->getFilename())
                ) {
                    continue;
                }

                $this->modules[] = $module->getFilename();
                $this->buildModule($module->getFilename(), $this->params)->run();
            }
        }

        // Backend Modules
        if ($this->hasAdminModules) {
            $path               = $this->getSourceFolder() . "/administrator/modules";
            $params             = $this->params;
            $params['basepath'] = $path;

            // Get every module
            $dir = new \DirectoryIterator($path);

            foreach ($dir as $module) {
                if (
                    $module->isDot()
                    || substr($module->getFilename(), 0, 4) != 'mod_'
                    || !is_dir($path . '/' . $module->getFilename())
                ) {
                    continue;
                }

                $this->adminModules[] = $module->getFilename();
                $this->buildModule($module->getFilename(), $params)->run();
            }
        }

        // Plugins
        if ($this->hasPlugins) {
            $path = $this->getSourceFolder() . "/plugins";

            // Get every plugin
            $hdl = opendir($path);

            while ($entry = readdir($hdl)) {
                // Only folders
                $p = $path . "/" . $entry;

                if (substr($entry, 0, 1) == '.') {
                    continue;
                }

                if (!is_file($p)) {
                    // Plugin type folder
                    $type = $entry;

                    $hdl2 = opendir($p);

                    while ($plugin = readdir($hdl2)) {
                        // Only folders
                        $p2 = $path . "/" . $entry;

                        if (substr($plugin, 0, 1) == '.') {
                            continue;
                        }

                        if (!is_file($p2)) {
                            $this->plugins[] = "plg_" . $type . "_" . $plugin;
                            $this->buildPlugin($type, $plugin, $this->params)->run();
                        }
                    }

                    closedir($hdl2);
                }
            }

            closedir($hdl);
        }

        if ($this->hasLibraries) {
            $path = $this->getSourceFolder() . "/libraries";

            // Get every library
            $hdl = opendir($path);

            while ($entry = readdir($hdl)) {
                // Only folders
                $p = $path . "/" . $entry;

                if (substr($entry, 0, 1) == '.') {
                    continue;
                }

                if (!is_file($p)) {
                    // Library folder
                    $this->libraries[] = $entry;
                    $this->buildLibrary($entry, $this->params, $this->hasComponents)->run();
                }
            }

            closedir($hdl);
        }

        // Templates
        if ($this->hasTemplates) {
            $path = $this->getSourceFolder() . "/templates";

            // Get every module
            $hdl = opendir($path);

            while ($entry = readdir($hdl)) {
                // Only folders
                $p = $path . "/" . $entry;

                if (substr($entry, 0, 1) == '.') {
                    continue;
                }

                if (!is_file($p)) {
                    // Template folder
                    $this->templates[] = $entry;
                    $this->buildTemplate($entry, $this->params)->run();
                }
            }

            closedir($hdl);
        }

        // Build file
        if ($this->hasFile) {
            $this->buildFile($this->params)->run();
        }

        // Build component
        if ($this->hasPackage) {
            $this->buildPackage($this->params)->run();
        }

        // Replacements (date, version etc.) in every php file
        $this->printTaskInfo('Doing replacements in files');
        $dir = new \RecursiveDirectoryIterator($this->getBuildFolder(), \RecursiveDirectoryIterator::SKIP_DOTS);
        $it  = new \RecursiveIteratorIterator($dir);

        foreach ($it as $file) {
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['php', 'js'])) {
                $this->replaceInFile($file);
            }
        }

        $this->printTaskSuccess('Finished Building ' . $this->getJConfig()->extension . ' extension package');

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
        // Check if we have component, module, plugin etc.
        $folders = glob($this->getSourceFolder() . "/administrator/components/com_*", GLOB_ONLYDIR);

        if (count($folders) === 0) {
            $this->hasComponents = false;
        }

        if (file_exists($this->getSourceFolder() . "/administrator/modules")) {
            $this->hasAdminModules = true;
        }

        if (!file_exists($this->getSourceFolder() . "/modules")) {
            $this->hasModules = false;
        }

        if (!file_exists($this->getSourceFolder() . "/plugins")) {
            $this->hasPlugins = false;
        }

        if (!file_exists($this->getSourceFolder() . "/templates")) {
            $this->hasTemplates = false;
        }

        if (!file_exists($this->getSourceFolder() . "/libraries")) {
            $this->hasLibraries = false;
        }

        if (!file_exists($this->getSourceFolder() . "/administrator/manifests/files")) {
            $this->hasFile = false;
        }

        if (!file_exists($this->getSourceFolder() . "/administrator/manifests/packages")) {
            $this->hasPackage = false;
        }
    }
}
