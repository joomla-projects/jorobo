<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Joomla\Github\Github;
use Joomla\Registry\Registry;
use Robo\Result;

/**
 * Generate the joomla.asset.json file for the webasset manager
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class AssetJSON extends JTask
{
    use \Robo\Task\Development\Tasks;
    use Generate\Tasks;

    /**
     * Generate joomla.asset.json file
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo("Generating joomla.asset.json for webasset manager");

        $folders = glob($this->getSourceFolder() . '/media/*', GLOB_ONLYDIR);

        foreach ($folders as $folder) {
            $extension = basename($folder);

            if (file_exists($folder . '/joomla.asset.json')) {
                $this->printTaskInfo('Updating joomla.asset.json for ' . $extension);
                $assetFile = json_decode(file_get_contents($folder . '/joomla.asset.json'));
            } else {
                $this->printTaskInfo('Generating joomla.asset.json for ' . $extension);

                $assetFile              = new \stdClass();
                $assetFile->{'$schema'} = 'https://developer.joomla.org/schemas/json-schema/web_assets.json';
                $assetFile->name        = $extension;
                $assetFile->version     = $this->getJConfig()->version;
                $assetFile->description = '';
                $assetFile->license     = 'GPL-2.0-or-later';
                $assetFile->assets      = [];
            }

            foreach (['js', 'css'] as $type) {
                if (is_dir($folder . '/' . $type)) {
                    $files = glob($folder . '/' . $type . '/*.' . $type);

                    foreach ($files as $file) {
                        if (str_ends_with($file, '.min.' . $type) && file_exists(str_replace('.min.' . $type, '.' . $type, $file))) {
                            continue;
                        }

                        $name = str_replace(['.min.' . $type, '.' . $type], '', basename($file));

                        $found = false;
                        foreach ($assetFile->assets as $asset) {
                            if ($asset->type == ($type == 'js' ? 'script' : 'style') && $asset->name == $extension . '.' . $name) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $entry       = new \stdClass();
                            $entry->name = $extension . '.' . $name;
                            $entry->type = $type == 'js' ? 'script' : 'style';
                            $uri         = $extension . '/' . basename($file);

                            if (!str_ends_with($entry->name, '.min.' . $type) && file_exists(substr($file, 0, -(strlen($type) + 1)) . '.min.' . $type)) {
                                $uri = $extension . '/' . substr(basename($file), 0, -(strlen($type) + 1)) . '.min.' . $type;
                            }

                            $entry->uri = $uri;

                            if ($type == 'js') {
                                $entry->dependencies = [];
                                $entry->attributes   = (object)['type' => 'module'];
                            }

                            $assetFile->assets[] = $entry;
                        }
                    }
                }
            }

            file_put_contents($folder . '/joomla.asset.json', json_encode($assetFile, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }

        $this->printTaskSuccess('Finished!!');

        return Result::success($this);
    }
}
