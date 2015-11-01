<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks\Build;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

use JBuild\Tasks\JTask;

/**
 * Class Component
 *
 * @package  JBuild\Tasks\Build
 */
class Component extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use buildTasks;

	protected $adminPath = null;

	protected $frontPath = null;

	protected $hasAdmin = true;

	protected $hasFront = true;

	protected $hasCli = true;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $params  The target directory
	 */
	public function __construct($params)
	{
		parent::__construct();

		// Reset files - > new component
		$this->resetFiles();

		$this->adminPath = $this->getSourceFolder() . "/administrator/components/com_" . $this->getExtensionName();
		$this->frontPath = $this->getSourceFolder() . "/components/com_" . $this->getExtensionName();
	}

	/**
	 * Build the package
	 *
	 * @return  bool
	 */
	public function run()
	{
		$this->say('Building component');

		// Analyize extension structure
		$this->analyze();

		// Prepare directories
		$this->prepareDirectories();

		if ($this->hasAdmin)
		{
			$adminFiles = $this->copyTarget($this->adminPath, $this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName());

			$this->addFiles('backend', $adminFiles);
		}

		if ($this->hasFront)
		{
			$frontendFiles = $this->copyTarget($this->frontPath, $this->getBuildFolder() . "/components/com_" . $this->getExtensionName());

			$this->addFiles('frontend', $frontendFiles);
		}

		// Build media (relative path)
		$media = $this->buildMedia("media/com_" . $this->getExtensionName(), 'com_' . $this->getExtensionName());
		$media->run();

		$this->addFiles('media', $media->getResultFiles());

		// Build language files for the component
		$language = $this->buildLanguage("com_" . $this->getExtensionName());
		$language->run();

		// Cli
		if ($this->hasCli)
		{
			$this->buildCli()->run();
		}

		// Update XML and script.php
		$this->createInstaller();

		// Copy XML and script.php to root
		$adminFolder = $this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName();
		$xmlFile     = $adminFolder . "/" . $this->getExtensionName() . ".xml";
		$scriptFile  = $adminFolder . "/script.php";

		$this->_copy($xmlFile, $this->getBuildFolder() . "/" . $this->getExtensionName() . ".xml");
		$this->_copy($scriptFile, $this->getBuildFolder() . "/script.php");

		if (file_exists($scriptFile))
		{
			$this->_copy($scriptFile, $this->getBuildFolder() . "/script.php");
		}

		// Copy Readme
		if (JPATH_BASE . "/docs/README.md")
		{
			$this->_copy(JPATH_BASE . "/docs/README.md", $this->getBuildFolder() . "/README");
		}

		return true;
	}

	/**
	 * Analyze the component structure
	 *
	 * @return  void
	 */
	private function analyze()
	{
		if (!file_exists($this->adminPath))
		{
			$this->hasAdmin = false;
		}

		if (!file_exists($this->frontPath))
		{
			$this->hasFront = false;
		}

		if (!file_exists($this->sourceFolder . "/cli"))
		{
			$this->hasCli = false;
		}
	}

	/**
	 * Prepare the directory structure
	 *
	 * @return  void
	 */
	private function prepareDirectories()
	{
		if ($this->hasAdmin)
		{
			$this->_mkdir($this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName());
		}

		if ($this->hasFront)
		{
			$this->_mkdir($this->getBuildFolder() . "/components/com_" . $this->getExtensionName());
		}
	}

	/**
	 * Generate the installer xml file for the component
	 *
	 * @return  void
	 */
	private function createInstaller()
	{
		$this->say("Creating component installer");

		$adminFolder = $this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName();
		$xmlFile     = $adminFolder . "/" . $this->getExtensionName() . ".xml";
		$scriptFile  = $adminFolder . "/script.php";
		$helperFile  = $adminFolder . "/helpers/defines.php";

		// Version & Date Replace
		$this->taskReplaceInFile($xmlFile)
			->from(array('##DATE##', '##YEAR##', '##VERSION##'))
			->to(array($this->getDate(), date('Y'), $this->getConfig()->version))
			->run();

		if (file_exists($scriptFile))
		{
			$this->taskReplaceInFile($scriptFile)
				->from(array('##DATE##', '##YEAR##', '##VERSION##'))
				->to(array($this->getDate(), date('Y'), $this->getConfig()->version))
				->run();
		}

		if (file_exists($helperFile))
		{
			$this->taskReplaceInFile($helperFile)
				->from(array('##DATE##', '##YEAR##', '##VERSION##'))
				->to(array($this->getDate(), date('Y'), $this->getConfig()->version))
				->run();
		}

		// Files and folders
		if ($this->hasAdmin)
		{
			$f = $this->generateFileList($this->getFiles('backend'));

			$this->taskReplaceInFile($xmlFile)
				->from('##BACKEND_COMPONENT_FILES##')
				->to($f)
				->run();

			// Language files
			$f = $this->generateLanguageFileList($this->getFiles('backendLanguage'));

			$this->taskReplaceInFile($xmlFile)
				->from('##BACKEND_LANGUAGE_FILES##')
				->to($f)
				->run();
		}

		if ($this->hasFront)
		{
			$f = $this->generateFileList($this->getFiles('frontend'));

			$this->taskReplaceInFile($xmlFile)
				->from('##FRONTEND_COMPONENT_FILES##')
				->to($f)
				->run();

			// Language files
			$f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

			$this->taskReplaceInFile($xmlFile)
				->from('##FRONTEND_LANGUAGE_FILES##')
				->to($f)
				->run();
		}

		// Media files
		$f = $this->generateFileList($this->getFiles('media'));

		$this->taskReplaceInFile($xmlFile)
			->from('##MEDIA_FILES##')
			->to($f)
			->run();
	}
}
