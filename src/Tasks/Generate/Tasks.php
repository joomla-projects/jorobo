<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

trait Tasks
{
    /**
     * Generate a component skeleton
     *
     * @param   string  $title   The component name (e.g. com_component)
     * @param   array   $params  Opt params
     *
     * @return  null
     *
     * @since   1.0
     */
    protected function generateComponent($title, $params = [])
    {
        return new Component($title, $params);
    }

    /**
     * Generate a module skeleton
     *
     * @param   string  $title   The module name (e.g. mod_login)
     * @param   array   $params  Opt params
     *
     * @return  null
     *
     * @since   1.0
     */
    protected function generateModule($title, $params = [])
    {
        return new Module($title, $params);
    }

    /**
     * Generate a package skeleton
     *
     * @param   string  $title   The package name (e.g. weblinks)
     * @param   array   $params  Opt params
     *
     * @return  null
     *
     * @since   1.0
     */
    protected function generatePackage($title, $params = [])
    {
        return new Package($title, $params);
    }

    /**
     * Generate a plugin skeleton
     *
     * @param   string  $title   The plugin name (e.g. plg_system_joomla)
     * @param   array   $params  Opt params
     *
     * @return  null
     *
     * @since   1.0
     */
    protected function generatePlugin($title, $params = [])
    {
        return new Plugin($title, $params);
    }

    /**
     * Generate a template skeleton
     *
     * @param   string  $title   The template name (e.g. cassiopeia)
     * @param   array   $params  Opt params
     *
     * @return  null
     *
     * @since   1.0
     */
    protected function generateTemplate($title, $params = [])
    {
        return new Template($title, $params);
    }
}
