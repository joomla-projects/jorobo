<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Build;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use Joomla\Jorobo\Tasks\JTask;

/**
 * Class Module
 *
 * @package  Joomla\Jorobo\Tasks\Build
 */
class Module extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use buildTasks;

	protected $modName = null;

	protected $source = null;

	protected $target = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $modName  Name of the module
	 * @param   String  $params   Optional params
	 *
	 * @since   1.0
	 */
	public function __construct($modName, $params)
	{
		parent::__construct();

		// Reset files - > new module
		$this->resetFiles();

		$this->modName = $modName;

		$this->source = $this->getSourceFolder() . "/modules/" . $modName;
		$this->target = $this->getBuildFolder() . "/modules/" . $modName;
	}

	/**
	 * Build the package
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say('Building module: ' . $this->modName);

		// Prepare directories
		$this->prepareDirectories();

		$files = $this->copyTarget($this->source, $this->target);

		// Build media (relative path)
		$media = $this->buildMedia("media/" . $this->modName, $this->modName);
		$media->run();

		$this->addFiles('media', $media->getResultFiles());

		// Build language files for the component
		$language = $this->buildLanguage($this->modName);
		$language->run();

		// Update XML and script.php
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
	 * Generate the installer xml file for the module
	 *
	 * @param   array  $files  The module files
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function createInstaller($files)
	{
		$this->say("Creating module installer");

		$xmlFile = $this->target . "/" . $this->modName . ".xml";

		// Version & Date Replace
		$this->replaceInFile($xmlFile);

		// Files and folders
		$f = $this->generateModuleFileList($files, $this->modName);

		$this->taskReplaceInFile($xmlFile)
			->from('##MODULE_FILES##')
			->to($f)
			->run();

		// Language files
		$f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

		$this->taskReplaceInFile($xmlFile)
			->from('##LANGUAGE_FILES##')
			->to($f)
			->run();

		// Media files
		$f = $this->generateFileList($this->getFiles('media'));

		$this->taskReplaceInFile($xmlFile)
			->from('##MEDIA_FILES##')
			->to($f)
			->run();
	}
}
