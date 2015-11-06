<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use joomla_projects\jorobo\Tasks\loadTasks;

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

// PSR-4 Autoload by composer
require_once JPATH_BASE . '/vendor/autoload.php';

/**
 * Sample RoboFile - adjust to your needs, extend your own
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
	 * @param   String   $target    The target joomla instance
	 *
	 * @return  void
	 */
	public function map($target)
	{
		(new \joomla_projects\jorobo\Tasks\Map($target))->run();
	}

	/**
	 * Build the joomla extension package
	 *
	 * @param   array  $params  Additional params
	 *
	 * @return  void
	 */
	public function build($params = ['dev' => false])
	{
		(new \joomla_projects\jorobo\Tasks\Build($params))->run();
	}

	/**
	 * Generate an extension skeleton - not implemented yet
	 *
	 * @param   array  $extensions  Extensions to build (com_xy, mod_xy)
	 *
	 * @return  void
	 */
	public function generate($extensions)
	{
		(new \joomla_projects\jorobo\Tasks\Generate($extensions))->run();
	}
}
