<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

/**
 * Building class for extensions
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class Generate extends JTask
{
    use \Robo\Task\Development\Tasks;
    use Generate\Tasks;

    /**
     * Additional params
     *
     * @var array|null
     *
     * @since   1.0
     */
    protected $params = null;

    /**
     * Build the package
     *
     * @return  void
     *
     * @since   1.0
     */
    public function run()
    {
        $this->prepareSourceDirectory();

        $this->say('Not implemented yet');
    }

    /**
     * Cleanup the given directory
     *
     * @param   string  $dir  The dir
     *
     * @return  void
     *
     * @since   1.0
     */
    private function cleanup($dir)
    {
        // Clean building directory
        $this->_cleanDir($dir);
    }

    /**
     * Prepare the directories
     *
     * @return  void
     *
     * @since   1.0
     */
    private function prepareSourceDirectory()
    {
        if (!file_exists($this->sourceFolder)) {
            $this->say('Creating source folder');
            $this->_mkdir($this->sourceFolder);
        }
    }
}
