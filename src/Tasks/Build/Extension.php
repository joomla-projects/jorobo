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
 * The supervisor
 *
 * @package  Joomla\Jorobo\Tasks\Build
 */
class Extension extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use buildTasks;

	/**
	 * @var   array
	 */
	protected $params = null;

	private $hasComponent = true;

	private $hasModules = true;

	private $hasPackage = true;

	private $hasPlugins = true;

	private $hasLibraries = true;

	private $hasCBPlugins = true;

	private $hasTemplates = true;

	private $modules = array();

	private $plugins = array();

	private $libraries = array();

	private $templates = array();

	/**
	 * Community Builder plugins
	 *
	 * @var    array
	 *
	 * @since  1.0
	 */
	private $cbplugins = array();

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $params  The target directory
	 *
	 * @since   1.0
	 */
	public function __construct($params)
	{
		parent::__construct();
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
		$this->say('Building Extension package');

		$this->analyze();

		// Build component
		if ($this->hasComponent)
		{
			$this->buildComponent($this->params)->run();
		}

		// Modules
		if ($this->hasModules)
		{
			$path = $this->getSourceFolder() . "/modules";

			// Get every module
			$hdl = opendir($path);

			while ($entry = readdir($hdl))
			{
				// Only folders
				$p = $path . "/" . $entry;

				if (substr($entry, 0, 1) == '.')
				{
					continue;
				}

				if (!is_file($p))
				{
					// Module folder
					$this->modules[] = $entry;
					$this->buildModule($entry, $this->params)->run();
				}
			}

			closedir($hdl);
		}

		// Plugins
		if ($this->hasPlugins)
		{
			$path = $this->getSourceFolder() . "/plugins";

			// Get every plugin
			$hdl = opendir($path);

			while ($entry = readdir($hdl))
			{
				// Only folders
				$p = $path . "/" . $entry;

				if (substr($entry, 0, 1) == '.')
				{
					continue;
				}

				if (!is_file($p))
				{
					// Plugin type folder
					$type = $entry;

					$hdl2 = opendir($p);

					while ($plugin = readdir($hdl2))
					{
						// Only folders
						$p2 = $path . "/" . $entry;

						if (substr($plugin, 0, 1) == '.')
						{
							continue;
						}

						if (!is_file($p2))
						{
							$this->plugins[] = "plg_" . $type . "_" . $plugin;
							$this->buildPlugin($type, $plugin, $this->params)->run();
						}
					}

					closedir($hdl2);
				}
			}

			closedir($hdl);
		}

		if ($this->hasLibraries)
		{
			$path = $this->getSourceFolder() . "/libraries";

			// Get every library
			$hdl = opendir($path);

			while ($entry = readdir($hdl))
			{
				// Only folders
				$p = $path . "/" . $entry;

				if (substr($entry, 0, 1) == '.')
				{
					continue;
				}

				if (!is_file($p))
				{
					// Library folder
					$this->libraries[] = $entry;
					$this->buildLibrary($entry, $this->params, $this->hasComponent)->run();
				}
			}

			closedir($hdl);
		}

		if ($this->hasCBPlugins)
		{
			$path = $this->getSourceFolder() . "/components/com_comprofiler/plugin";

			// Get every plugin
			$hdl = opendir($path);

			while ($entry = readdir($hdl))
			{
				// Only folders
				$p = $path . "/" . $entry;

				if (substr($entry, 0, 1) == '.')
				{
					continue;
				}

				if (!is_file($p))
				{
					// Plugin type folder
					$type = $entry;

					$hdl2 = opendir($p);

					while ($plugin = readdir($hdl2))
					{
						// Only folders
						$p2 = $path . "/" . $entry;

						if (substr($plugin, 0, 1) == '.')
						{
							continue;
						}

						if (!is_file($p2))
						{
							$this->plugins[] = "plug_" . $plugin;
							$this->buildCBPlugin($type, $plugin, $this->params)->run();
						}
					}

					closedir($hdl2);
				}
			}

			closedir($hdl);
		}

		// Templates
		if ($this->hasTemplates)
		{
			$path = $this->getSourceFolder() . "/templates";

			// Get every module
			$hdl = opendir($path);

			while ($entry = readdir($hdl))
			{
				// Only folders
				$p = $path . "/" . $entry;

				if (substr($entry, 0, 1) == '.')
				{
					continue;
				}

				if (!is_file($p))
				{
					// Template folder
					$this->templates[] = $entry;
					$this->buildTemplate($entry, $this->params)->run();
				}
			}

			closedir($hdl);
		}

		// Build component
		if ($this->hasPackage)
		{
			$this->buildPackage($this->params)->run();
		}

		// Replacements (date, version etc.) in every php file
		$dir = new \RecursiveDirectoryIterator($this->getBuildFolder(), \RecursiveDirectoryIterator::SKIP_DOTS);
		$it = new \RecursiveIteratorIterator($dir);

		foreach ($it as $file)
		{
			if (in_array(pathinfo($file, PATHINFO_EXTENSION), array('php', 'js')))
			{
				$this->replaceInFile($file);
			}
		}

		return true;
	}

	/**
	 * Analyze the extension structure
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function analyze()
	{
		// Check if we have component, module, plugin etc.
		if (!file_exists($this->getSourceFolder() . "/administrator/components/com_" . $this->getExtensionName())
			&& !file_exists($this->getSourceFolder() . "/components/com_" . $this->getExtensionName()))
		{
			$this->say("Extension has no component");
			$this->hasComponent = false;
		}

		if (!file_exists($this->getSourceFolder() . "/modules"))
		{
			$this->hasModules = false;
		}

		if (!file_exists($this->getSourceFolder() . "/plugins"))
		{
			$this->hasPlugins = false;
		}

		if (!file_exists($this->getSourceFolder() . "/templates"))
		{
			$this->hasTemplates = false;
		}

		if (!file_exists($this->getSourceFolder() . "/libraries"))
		{
			$this->hasLibraries = false;
		}

		if (!file_exists($this->getSourceFolder() . "/administrator/manifests/packages"))
		{
			$this->hasPackage = false;
		}

		if (!file_exists($this->getSourceFolder() . "/components/com_comprofiler"))
		{
			$this->hasCBPlugins = false;
		}
	}
}
