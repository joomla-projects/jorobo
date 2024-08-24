<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Result;

/**
 * Bump the version of a Joomla extension
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class BumpVersion extends JTask
{
    use \Robo\Task\Development\Tasks;

    /**
     * Maps all parts of an extension into a Joomla! installation
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo('Updating ' . $this->getJConfig()->extension . " to " . $this->getJConfig()->version);

        // Reusing the header config here
        $excludeList = $this->getJConfig()->header->exclude;

        if ($excludeList !== '') {
            $exclude = explode(",", trim($excludeList));
        }

        $path      = realpath($this->getJConfig()->source);
        $fileTypes = explode(",", trim($this->getJConfig()->header->files));

        $changedFiles = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $filename) {
            if (substr($filename, 0, 1) == '.') {
                continue;
            }

            $file = new \SplFileInfo($filename);

            if (!in_array($file->getExtension(), $fileTypes)) {
                continue;
            }

            // Skip directories in exclude list
            if (isset($exclude) && count($exclude)) {
                $relative = str_replace(realpath($path), "", $file->getPath());

                // It is possible to have multiple exclude directories
                foreach ($exclude as $e) {
                    if (stripos($relative, $e) !== false) {
                        $this->printTaskInfo("Excluding " . $filename);
                        continue 2;
                    }
                }
            }

            if ($file->getExtension() === 'xml') {
                if ($this->updateXML($file)) {
                    $changedFiles++;
                }

                continue;
            }

            if ($this->updatePlain($file)) {
                $changedFiles++;
            }
        }

        return Result::success($this, 'Updated ' . $changedFiles . ' files');
    }

    protected function updatePlain($file): bool
    {
        $fileContents = file_get_contents($file->getRealPath());

        if (preg_match('#__DEPLOY_VERSION__#', $fileContents)) {
            $fileContents = preg_replace('#__DEPLOY_VERSION__#', $this->getJConfig()->version, $fileContents);

            $this->printTaskInfo('Updating file: ' . $file->getRealPath());

            file_put_contents($file->getRealPath(), $fileContents);

            return true;
        }

        return false;
    }

    protected function updateXML($file): bool
    {
        $xml = simplexml_load_file($file->getRealPath());

        if ($xml->getName() !== 'extension') {
            return false;
        }

        $newVersion = $this->getJConfig()->version;

        if ((string)$xml->version === $newVersion) {
            return false;
        }

        $xml->version = $newVersion;

        $this->printTaskInfo('Updating file: ' . $file->getRealPath());

        $xml->asXML($file->getRealPath());

        return true;
    }
}
