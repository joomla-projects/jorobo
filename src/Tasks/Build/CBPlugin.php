<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use Joomla\Jorobo\Tasks\JTask;

/**
 * Community Builder build class
 *
 * @package  Joomla\Jorobo\Tasks\Build
 */
class CBPlugin extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use buildTasks;

	protected $plgName = null;

	protected $plgType = null;

	protected $source = null;

	protected $target = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $type    Type of the plugin
	 * @param   String  $name    Name of the plugin
	 * @param   String  $params  Optional params
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
	 * @return  bool
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
	 */
	private function createInstaller($files)
	{
		$this->say("Creating plugin installer");

		$xmlFile = $this->target . "/" . str_replace('plug_', '', $this->plgName) . ".xml";

		// Version & Date Replace
		$this->taskReplaceInFile($xmlFile)
			->from(array('##DATE##', '##YEAR##', '##VERSION##'))
			->to(array($this->getDate(), date('Y'), $this->getConfig()->version))
			->run();
	}
}
