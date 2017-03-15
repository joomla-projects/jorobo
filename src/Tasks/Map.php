<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Map extension into an Joomla installation
 *
 * @package  Joomla\Jorobo\Tasks\Component
 */
class Map extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * The target folder
	 *
	 * @var    null|String  $target
	 *
	 * @since  1.0
	 */
	protected $target = null;

	/**
	 * Admin folders
	 *
	 * @var    array  $adminFolders
	 *
	 * @since  1.0
	 */
	protected $adminFolders = array('components', 'language', 'modules');

	/**
	 * Initialize Map Task
	 *
	 * @param   String  $target  The target directory
	 *
	 * @since   1.0
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
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say('Mapping ' . $this->getJConfig()->extension . " to " . $this->target);
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

		$this->say("Finished symlinking into Joomla!");

		return true;
	}

	/**
	 * Process Administrator files
	 *
	 * @return  void
	 *
	 * @since   1.0
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
	 *
	 * @since   1.0
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
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function processLanguage($src, $toDir)
	{
		if (is_dir($src))
		{
			$dirHandle = opendir($src);

			while (false !== ($element = readdir($dirHandle)))
			{
				if (substr($element, 0, 1) != '.')
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
	 *
	 * @since   1.0
	 */
	private function processLibraries($src, $toDir)
	{
		$this->linkSubdirectories($src, $toDir . "/libraries");
	}

	/**
	 * Process media
	 *
	 * @param   String  $toDir  The target
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function processMedia($src, $toDir)
	{
		$this->linkSubdirectories($src, $toDir . "/media");
	}

	/**
	 * Link subdirectories into folder
	 *
	 * @param   string  $src  The source
	 * @param   string  $to   The target
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function linkSubdirectories($src, $to)
	{
		if (is_dir($src))
		{
			$dirHandle = opendir($src);

			while (false !== ($element = readdir($dirHandle)))
			{
				if (substr($element, 0, 1) != '.')
				{
					if (is_dir($src . "/" . $element))
					{
						$this->symlink($src . "/" . $element, $to . '/' . $element);
					}
				}
			}
		}
	}


	/**
	 * Process Cli
	 *
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function processCli($src, $toDir)
	{
		$this->linkSubdirectories($src, $toDir . "/cli");
	}

	/**
	 * Process Module
	 *
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function processModules($src, $toDir)
	{
		$this->linkSubdirectories($src, $toDir . "/modules");
	}

	/**
	 * Process Plugins
	 *
	 * @param   String  $toDir  - The target
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function processPlugins($src, $toDir)
	{
		// Plugin folder /plugins
		if (is_dir($src))
		{
			$dirHandle = opendir($src);

			while (false !== ($type = readdir($dirHandle)))
			{
				if (substr($type, 0, 1) != '.')
				{
					if (is_dir($src . "/" . $type))
					{
						$this->linkSubdirectories($src . "/" . $type, $toDir . '/plugins/' . $type);
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
	 *
	 * @since   1.0
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
	 *
	 * @since   1.0
	 */
	private function symlink($source, $target)
	{
		if (file_exists($target))
		{
			if (is_dir($target))
			{
				$this->_deleteDir($target);
			}
			else
			{
				unlink($target);
			}
		}

		try
		{
			$this->taskFileSystemStack()
				->symlink($source, $target)
				->run();
		}
		catch (Exception $e)
		{
			$this->say('Error symlinking: ' . $e->message());
		}
	}
}
