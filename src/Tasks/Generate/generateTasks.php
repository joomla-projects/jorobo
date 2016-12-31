<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Generate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

/**
 * Generate tasks trait
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Generate
 *
 * @since       1.0
 */
trait generateTasks
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
	protected function generateComponent($title, $params = array())
	{
		return null;
	}

	/**
	 * Generate a module skeleton
	 *
	 * @param   string  $title   The component name (e.g. com_component)
	 * @param   array   $params  Opt params
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	protected function generateModule($title, $params = array())
	{
		return null;
	}

	/**
	 * Generate a plugin skeleton
	 *
	 * @param   string  $title   The component name (e.g. com_component)
	 * @param   array   $params  Opt params
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	protected function generatePlugin($title, $params = array())
	{
		return null;
	}
}
