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
 * Class Template
 *
 * @package  Joomla\Jorobo\Tasks\Build
 */
class Template extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use buildTasks;

	protected $templateName = null;

	protected $source = null;

	protected $target = null;

	/**
	 * Initialize Build Task
	 *
	 * @param String $templateName  Name of the template
	 * @param String $params   Optional params
	 *
	 * @since   1.0
	 */
	public function __construct($templateName, $params)
	{
		parent::__construct();

		// Reset files - > new template
		$this->resetFiles();

		$this->templateName = $templateName;

		$this->source = $this->getSourceFolder() . "/templates/" . $templateName;
		$this->target = $this->getBuildFolder() . "/templates/" . $templateName;
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
		$this->say('Building template: ' . $this->templateName);

		// Prepare directories
		$this->prepareDirectories();

		$files = $this->copyTarget($this->source, $this->target);

		// Build media (relative path)
		$media = $this->buildMedia("media/" . $this->templateName, $this->templateName);
		$media->run();

		$this->addFiles('media', $media->getResultFiles());

		// Build language files for the component
		$language = $this->buildLanguage('tpl_' . $this->templateName);
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
	 * Generate the installer xml file for the template
	 *
	 * @param   array  $files  The template files
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function createInstaller($files)
	{
		$this->say("Creating template installer");

		$xmlFile = $this->target . "/templateDetails.xml";

		// Version & Date Replace
		$this->replaceInFile($xmlFile);

		// Files and folders
		$f = $this->generateFileList($files);

		$this->taskReplaceInFile($xmlFile)
			->from('##TEMPLATE_FILES##')
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
