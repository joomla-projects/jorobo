<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Robo\Task\Development\loadTasks;

/**
 * Community Builder build class
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @since       1.0
 */
class CBPlugin extends Base implements TaskInterface
{
	use loadTasks;
	use TaskIO;
	use buildTasks;

	/**
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $plgName = null;

	/**
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $plgType = null;

	/**
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $source = null;

	/**
	 * @var  string
	 *
	 * @since  1.0
	 */
	protected $target = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $type    Type of the plugin
	 * @param   String  $name    Name of the plugin
	 * @param   String  $params  Optional params
	 *
	 * @since   1.0
	 */
	public function __construct($type, $name, $params)
	{
		parent::__construct();

		// Reset files - > new module
		$this->resetFiles();

		$this->plgName = $name;
		$this->plgType = $type;

		$this->source = $this->getSourceFolder() . "/components/com_comprofiler/plugin/" . $type . "/" . $name;
		$this->target = $this->getBuildFolder() . "/components/com_comprofiler/plugin/" . $type . "/" . $name;
	}

	/**
	 * Build the package
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say('Building CB Plugin: ' . $this->plgName . " (" . $this->plgType . ")");

		// Prepare directories
		$this->prepareDirectories();

		$files = $this->copyTarget($this->source, $this->target);

		// Build language files
		$language = $this->buildLanguage("plug_" . $this->plgType . "_" . $this->plgName);
		$language->run();

		// No XML
		$this->createInstaller($files);

		return true;
	}


	/**
	 * Prepare the directory structure
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function prepareDirectories()
	{
		$this->_mkdir($this->target);
	}

	/**
	 * Generate the installer xml file for the plugin
	 *
	 * @param   array  $files  The module files
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function createInstaller($files)
	{
		$this->say("Creating plugin installer");

		$xmlFile = $this->target . "/" . str_replace('plug_', '', $this->plgName) . ".xml";

		// Version & Date Replace
		$this->replaceInFile($xmlFile);
	}
}
