<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Collection\CollectionBuilder;

trait Tasks
{
    /**
     * Build extension
     *
     * @param   array  $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildExtension($params = [])
    {
        return $this->task(Extension::class, $params);
    }

    /**
     * Build component
     *
     * @param   array  $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildComponent($params = [])
    {
        return $this->task(Component::class, $params);
    }

    /**
     * Build media folder
     *
     * @param   string   $source   The media folder (an extension could have multiple)
     * @param   string   $extName  The extension name (e.g. mod_xy)
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildMedia($source, $extName, $params = [])
    {
        return $this->task(Media::class, $source, $extName, $params);
    }

    /**
     * Build language folder
     *
     * @param   string  $extension  The extension (not the whole, but mod_xy or plg_)
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildLanguage($extension, $params = [])
    {
        return $this->task(Language::class, $extension, $params);
    }

    /**
     * Build a library
     *
     * @param   String  $libName       Name of the module
     * @param   array   $params        Opt params
     * @param   bool    $hasComponent  has the extension a component (then we need to build different)
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildLibrary($libName, $params, $hasComponent)
    {
        return $this->task(Library::class, $libName, $params, $hasComponent);
    }

    /**
     * Build a Module
     *
     * @param   String  $modName  Name of the module
     * @param   array   $params   Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildModule($modName, $params = [])
    {
        return $this->task(Module::class, $modName, $params);
    }

    /**
     * Build package
     *
     * @param   array  $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildPackage($params = [])
    {
        return $this->task(Package::class, $params);
    }

    /**
     * Build a Plugin
     *
     * @param   String  $type    Type of the plugin
     * @param   String  $name    Name of the plugin
     * @param   array   $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildPlugin($type, $name, $params = [])
    {
        return $this->task(Plugin::class, $type, $name, $params);
    }

    /**
     * Build a File extension
     *
     * @param   String  $name    Name of the plugin
     * @param   array   $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildFile($name, $params = [])
    {
        return $this->task(File::class, $name, $params);
    }

    /**
     * Build a Template
     *
     * @param   String  $templateName  Name of the template
     * @param   array   $params        Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function buildTemplate($templateName, $params = [])
    {
        return $this->task(Template::class, $templateName, $params);
    }
}
