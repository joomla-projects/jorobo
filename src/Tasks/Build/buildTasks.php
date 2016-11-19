<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Joomla\Jorobo\Tasks\Build\Component;
use Joomla\Jorobo\Tasks\Build\Media;

trait buildTasks
{
	/**
	 * Build extension
	 *
	 * @param   array  $params  - Opt params
	 *
	 * @return  Extension
	 *
	 * @since   1.0
	 */
	protected function buildExtension($params)
	{
		return new Extension($params);
	}

	/**
	 * Build component
	 *
	 * @param   array  $params  - Opt params
	 *
	 * @return  Component
	 *
	 * @since   1.0
	 */
	protected function buildComponent($params)
	{
		return new Component($params);
	}

	/**
	 * Build media folder
	 *
	 * @param   array   $source   The media folder (an extension could have multiple)
	 * @param   string  $extName  The extension name (e.g. mod_xy)
	 *
	 * @return  Media
	 *
	 * @since   1.0
	 */
	protected function buildMedia($source, $extName)
	{
		return new Media($source, $extName);
	}

	/**
	 * Build language folder
	 *
	 * @param   string  $extension  - The extension (not the whole, but mod_xy or plg_)
	 *
	 * @return  Language
	 *
	 * @since   1.0
	 */
	protected  function buildLanguage($extension)
	{
		return new Language($extension);
	}

	/**
	 * Build a library
	 *
	 * @param   String  $libName       Name of the module
	 * @param   array   $params        Opt params
	 * @param   bool    $hasComponent  has the extension a component (then we need to build differnet)
	 *
	 * @return  Library
	 *
	 * @since   1.0
	 */
	protected function buildLibrary($libName, $params, $hasComponent)
	{
		return new Library($libName, $params, $hasComponent);
	}

	/**
	 * Build cli folder
	 *
	 * @return  Cli
	 *
	 * @since   1.0
	 */
	protected function buildCli()
	{
		return new Cli;
	}

	/**
	 * Build a Module
	 *
	 * @param   String  $modName  Name of the module
	 * @param   array   $params   Opt params
	 *
	 * @return  Module
	 *
	 * @since   1.0
	 */
	protected function buildModule($modName, $params)
	{
		return new Module($modName, $params);
	}

	/**
	 * Build package
	 *
	 * @param   array  $params  - Opt params
	 *
	 * @return  Package
	 *
	 * @since   1.0
	 */
	protected function buildPackage($params)
	{
		return new Package($params);
	}

	/**
	 * Build a Plugin
	 *
	 * @param   String  $type    Type of the plugin
	 * @param   String  $name    Name of the plugin
	 * @param   array   $params  Opt params
	 *
	 * @return  Plugin
	 *
	 * @since   1.0
	 */
	protected function buildPlugin($type, $name, $params)
	{
		return new Plugin($type, $name, $params);
	}

	/**
	 * Build a CBPlugin
	 *
	 * @param   String  $type    Type of the plugin
	 * @param   String  $name    Name of the plugin
	 * @param   array   $params  Opt params
	 *
	 * @return  CBPlugin
	 *
	 * @since   1.0
	 */
	protected function buildCBPlugin($type, $name, $params)
	{
		return new CBPlugin($type, $name, $params);
	}

	/**
	 * Build a Template
	 *
	 * @param   String  $templateName  Name of the template
	 * @param   array   $params        Opt params
	 *
	 * @return  Template
	 *
	 * @since   1.0
	 */
	protected function buildTemplate($templateName, $params)
	{
		return new Template($templateName, $params);
	}
}
