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
 * Build Library
 *
 * @package  Joomla\Jorobo\Tasks\Build
 */
class Library extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use buildTasks;

	protected $source = null;

	protected $target = null;

	protected $libName = null;

	protected $hasComponent = false;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $libName       Name of the library to build
	 * @param   String  $params        Optional params
	 * @param   bool    $hasComponent  has the extension a component (then we need to build differnet)
	 *
	 * @since   1.0
	 */
	public function __construct($libName, $params, $hasComponent)
	{
		parent::__construct();

		// Reset files -> new lib
		$this->resetFiles();

		$this->libName = $libName;
		$this->hasComponent = $hasComponent;

		$this->source = $this->getSourceFolder() . "/libraries/" . $libName;
		$this->target = $this->getBuildFolder() . "/libraries/" . $libName;
	}

	/**
	 * Runs the library build tasks, just copying files currently
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say("Building library folder " . $this->libName);

		if (!file_exists($this->source))
		{
			$this->say("Folder " . $this->source . " does not exist!");

			return true;
		}

		$this->prepareDirectory();

		// Libaries are problematic.. we have libraries/name/libraries/name in the end for the build script
		$tar = $this->target;

		if (!$this->hasComponent)
		{
			$tar = $this->target . "/libraries/" . $this->libName;
		}

		$files = $this->copyTarget($this->source, $tar);

		$lib = $this->libName;

		// Workaround for libraries without lib_
		if (substr($this->libName, 0, 3) != "lib")
		{
			$lib = 'lib_' . $this->libName;
		}

		// Build media (relative path)
		$media = $this->buildMedia("media/" . $lib, $lib);
		$media->run();

		$this->addFiles('media', $media->getResultFiles());

		// Build language files for the component
		$language = $this->buildLanguage($lib);
		$language->run();

		// Copy XML
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
	private function prepareDirectory()
	{
		$this->_mkdir($this->target);
	}

	/**
	 * Generate the installer xml file for the library
	 *
	 * @param   array  $files  The library files
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function createInstaller($files)
	{
		$this->say("Creating library installer");

		$xmlFile = $this->target . "/" . $this->libName . ".xml";

		// Version & Date Replace
		$this->replaceInFile($xmlFile);

		// Files and folders
		$f = $this->generateFileList($files);

		$this->taskReplaceInFile($xmlFile)
			->from('##LIBRARYFILES##')
			->to($f)
			->run();

		// Language backend files
		$f = $this->generateLanguageFileList($this->getFiles('backendLanguage'));

		$this->taskReplaceInFile($xmlFile)
			->from('##BACKEND_LANGUAGE_FILES##')
			->to($f)
			->run();

		// Language frontend files
		$f = $this->generateLanguageFileList($this->getFiles('frontendLanguage'));

		$this->taskReplaceInFile($xmlFile)
			->from('##FRONTEND_LANGUAGE_FILES##')
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
