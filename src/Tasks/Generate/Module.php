<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Generate a module skeleton
 *
 * @package  Joomla\Jorobo\Tasks\Generate
 */
class Module extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	protected $adminPath = null;

	protected $frontPath = null;

	protected $hasAdmin = true;

	protected $hasFront = true;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $params  The target directory
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __construct($params)
	{
		parent::__construct();

		$this->adminPath = $this->getSourceFolder() . "/administrator/components/com_" . $this->getExtensionName();
		$this->frontPath = $this->getSourceFolder() . "/components/com_" . $this->getExtensionName();
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
		$media = $this->buildMedia("media/com_" . $this->getExtensionName());
		$media->run();

		$this->addFiles('media', $media->getResultFiles());

		$language = $this->buildLanguage("com_matukio");
		$language->run();

		return true;
	}

	/**
	 * Analyze the component structure
	 *
	 * @return  void
	 *
	 * @since   1.0
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
		if ($this->hasAdmin)
		{
			$this->_mkdir($this->getBuildFolder() . "/administrator/components/com_" . $this->getExtensionName());
		}

		if ($this->hasFront)
		{
			$this->_mkdir($this->getBuildFolder() . "/components/com_" . $this->getExtensionName());
		}
	}
}
