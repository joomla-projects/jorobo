<?php

/**
 * @package    Jorobo
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Jorobo\Tasks\loadTasks;

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
}

// PSR-4 Autoload by composer
require_once JPATH_BASE . '/vendor/autoload.php';

/**
 * Sample RoboFile - adjust to your needs, extend your own
 *
 * @since   1.0.0
 */
class RoboFile extends \Robo\Tasks
{
    use loadTasks;

    /**
     * Initialize Robo
     */
    public function __construct()
    {
        $this->stopOnFail(true);
    }

    /**
     * Map into Joomla installation.
     *
     * @param   String  $target  The target joomla instance
     *
     * @return  void
     */
    public function map($target, $params = ['base' => JPATH_BASE])
    {
        (new \Joomla\Jorobo\Tasks\Map($target, $params))->run();
    }

    /**
     * Build the joomla extension package
     *
     * @param   array  $params  Additional params
     *
     * @return  void
     */
    public function build($params = ['dev' => false, 'base' => JPATH_BASE])
    {
        (new \Joomla\Jorobo\Tasks\Build($params))->run();
    }

    /**
     * Generate an extension skeleton - not implemented yet
     *
     * @param   array  $extensions  Extensions to build (com_xy, mod_xy)
     *
     * @return  void
     */
    public function generate($extensions, $params = ['base' => JPATH_BASE])
    {
        (new \Joomla\Jorobo\Tasks\Generate($extensions, $params))->run();
    }

    /**
     * Update copyright headers for this project. (Set the text up in the jorobo.ini)
     *
     * @return  void
     */
    public function headers($params = ['base' => JPATH_BASE])
    {
        (new \Joomla\Jorobo\Tasks\CopyrightHeader($params))->run();
    }

    /**
     * Bump Version placeholder __DEPLOY_VERSION__ in this project. (Set the version up in the jorobo.ini)
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function bump($params = ['base' => JPATH_BASE])
    {
        (new \Joomla\Jorobo\Tasks\BumpVersion($params))->run();
    }
}
