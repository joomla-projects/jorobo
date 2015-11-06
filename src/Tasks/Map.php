<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomla_projects\jorobo\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Map extension into an Joomla installation
 *
 * @package  joomla_projects\jorobo\Tasks\Component
 */
class Map extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * @var   null|String  $target  - The target folder
	 */
	protected $target = null;

	/**
	 * @var   array  $adminFolders  - Admin folders
	 */
	protected $adminFolders = array('components', 'language', 'modules');

	/**
	 * Initialize Map Task
	 *
	 * @param   String  $target  The target directory
	 */
	public function __construct($target)
	{
		parent::__construct();

		$this->target = $target;
	}

	/**
	 * Maps all parts of an extension into a Joomla! installation
	 *
	 * @return  bool
	 */
	public function run()
	{

		$this->say('Mapping ' . $this->getConfig()->extension . " to " . $this->target);
		$this->say('OS: ' . $this->getOs() . " | Basedir: " . $this->getSourceFolder());

		if (!$this->checkFolders())
		{
			return false;
		}

		$dirHandle = opendir($this->getSourceFolder());

		// Get all main dirs
		while (false !== ($element = readdir($dirHandle)))
		{
			if (substr($element, 0, 1) == '.')
			{
				continue;
			}

			$method = 'process' . ucfirst($element);

			if (method_exists($this, $method))
			{
				$this->$method($this->getSourceFolder() . "/" . $element, $this->target);
			}
			else
			{
				$this->say('Missing method: ' . $method);
			}
		}

		closedir($dirHandle);

		// Get lib_compojoom (TODO move into separate file)
		$libDir = dirname(dirname($this->getSourceFolder())) . "/lib_compojoom/source";

		$libHandle = opendir($libDir);

		if ($libHandle === false)
		{
			$this->printTaskError('Can not open ' . $libDir . ' for parsing');

			return false;
		}

		$this->say("Syncing library " . $libDir);

		while (false !== ($element = readdir($libHandle)))
		{
			if  (substr($element, 0, 1) == '.')
			{
				continue;
			}

			$method = 'process' . ucfirst($element);

			if (method_exists($this, $method))
			{
				$this->$method($libDir . "/" . $element, $this->target);
			}
			else
			{
				$this->say('Missing method: ' . $method);
			}
		}

		closedir($libHandle);

		$this->say("Finished symlinking into Joomla!");

		return true;
	}

	/**
	 * Process Administrator files
	 *
	 * @return  void
	 */
	private function processAdministrator()
	{
		$sourceFolder = $this->getSourceFolder();
		$this->processComponents($sourceFolder . '/administrator/components', $this->target . '/administrator');
		$this->processLanguage($sourceFolder . '/administrator/language', $this->target . '/administrator');
		$this->processModules($sourceFolder . '/administrator/modules', $this->target . '/administrator/modules');
	}


	/**
	 * Process components
	 *
	 * @param   String  $src  - The source
	 * @param   String  $to   - The target
	 *
	 * @return  void
	 */
	private function processComponents($src, $to)
	{
		// Component directory
		if (is_dir($src))
		{
			$dirHandle = opendir($src);

			while (false !== ($element = readdir($dirHandle)))
			{
				if (false !== strpos($element, 'com_'))
				{
					$this->symlink($src . '/' . $element, $to . '/components/' . $element);
				}
			}
		}
	}

	/**
	 * Process components
	 *
	 * @param   String  $toDir     - The target
	 *
	 * @return  void
	 */
	private function processLanguage($src, $toDir)
	{
		if (is_dir($src))
		{
			$dirHandle = opendir($src);

			while (false !== ($element = readdir($dirHandle)))
			{
				if (substr($element, 0, 1) == '.')
				{
					if (is_dir($src . "/" . $element))
					{
						$langDirHandle = opendir($src . '/' . $element);

						while (false !== ($file = readdir($langDirHandle)))
						{
							if (is_file($src . '/' . $element . '/' . $file))
							{
								$this->say($file);
								$this->symlink($src . '/' . $element . '/' . $file, $toDir . '/language/' . $element . '/' . $file);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Process Libraries
	 *
	 * @param   String  $toDir  The target
	 *
	 * @return  void
	 */
	private function processLibraries($toDir)
	{
		$this->mapDir('libraries', $this->getSourceFolder(), $toDir);
	}

	/**
	 * Process media
	 *
	 * @param   String  $toDir  The target
	 *
	 * @return  void
	 */
	private function processMedia($toDir)
	{
		$this->mapDir('media', $this->getSourceFolder(), $toDir);
	}

	/**
	 * Process Cli
	 *
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 */
	private function processCli($toDir)
	{
		$this->mapDir('cli', $this->getSourceFolder(), $toDir);
	}

	/**
	 * Process Module
	 *
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 */
	private function processModules($src, $toDir)
	{
		$this->mapDir('modules', $src, $toDir);
	}

	/**
	 * Process Plugins
	 *
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 */
	private function processPlugins($src, $toDir)
	{
		if (is_dir($this->getSourceFolder()))
		{
			$dirHandle = opendir($this->getSourceFolder());

			// Plugin folder
			while (false !== ($element = readdir($dirHandle)))
			{
				if (substr($element, 0, 1) != '.')
				{
					$plgDirHandle = opendir($this->getSourceFolder() . "/" . $element);

					while (false !== ($plugin = readdir($plgDirHandle)))
					{
						if  (substr($element, 0, 1) != '.')
						{
							if (is_dir($this->getSourceFolder() . "/" . $element . "/" . $plugin))
							{
								$this->symlink(
									$this->getSourceFolder() . '/' . $element . "/" . $plugin,
									$toDir . '/plugins/' . $element . '/' . $plugin
								);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Process components
	 *
	 * @param   String  $type   - The type
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 */
	private function mapDir($type, $toDir)
	{
		// Check if dir exists
		if (is_dir($this->getSourceFolder()))
		{
			$dirHandle = opendir($this->getSourceFolder());

			while (false !== ($element = readdir($dirHandle)))
			{
				if (substr($element, 0, 1) != '.')
				{
					$this->symlink($this->getSourceFolder() . '/' . $element, $toDir . '/' . $type . '/' . $element);
				}
			}
		}
	}

	/**
	 * Symlinks files / folders
	 *
	 * @param   String  $source  - The source
	 * @param   String  $target  - The target
	 *
	 * @return  void
	 */
	private function symlink($source, $target)
	{
		$this->say('Source: ' . $source);
		$this->say('Target: ' . $target);

		if (file_exists($target))
		{
			$this->say("DELETING TARGET: " . $target);
			$this->_deleteDir($target);
		}

		try
		{
			$this->taskFileSystemStack()
				->symlink($source, $target)
				->run();
		}
		catch (Exception $e)
		{
			$this->say('ERROR: ' . $e->message());
		}
	}
}
