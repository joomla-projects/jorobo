<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Deploy
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Robo\Task\Development\loadTasks;

/**
 * Deploy project as Package file
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Deploy
 *
 * @since       1.0
 */
class Package extends Base implements TaskInterface
{
	use loadTasks;
	use TaskIO;

	/**
	 * The target Zip file of the package
	 *
	 * @var    string
	 *
	 * @since  1.0
	 */
	protected $target = null;

	/**
	 * @var    string
	 *
	 * @since  1.0
	 */
	protected $current = null;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	private $hasComponent = true;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	private $hasModules = true;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	private $hasTemplates = true;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	private $hasPlugins = true;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	private $hasLibraries = true;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	private $hasCBPlugins = true;

	/**
	 * Initialize Build Task
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->target = JPATH_BASE . "/dist/pkg-" . $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
		$this->current = JPATH_BASE . "/dist/current";
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
		// TODO improve DRY!
		$this->say('Creating package ' . $this->getJConfig()->extension . " " . $this->getJConfig()->version);

		// Start getting single archives
		if (file_exists(JPATH_BASE . '/dist/zips'))
		{
			$this->_deleteDir(JPATH_BASE . '/dist/zips');
		}

		$this->_mkdir(JPATH_BASE . '/dist/zips');
		$this->analyze();

		if ($this->hasComponent)
		{
			$this->createComponentZip();
		}

		if ($this->hasModules)
		{
			$this->createModuleZips();
		}

		if ($this->hasPlugins)
		{
			$this->createPluginZips();
		}

		if ($this->hasTemplates)
		{
			$this->createTemplateZips();
		}

		if ($this->hasLibraries)
		{
			$this->createLibraryZips();
		}

		$this->createPackageZip();
		$this->_symlink($this->target, JPATH_BASE . "/dist/pkg-" . $this->getExtensionName() . "-current.zip");

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
		if (!file_exists($this->current . "/administrator/components/com_" . $this->getExtensionName())
			&& !file_exists($this->current . "/components/com_" . $this->getExtensionName())
		)
		{
			$this->say("Extension has no component");
			$this->hasComponent = false;
		}

		if (!file_exists($this->current . "/modules"))
		{
			$this->hasModules = false;
		}

		if (!file_exists($this->current . "/plugins"))
		{
			$this->hasPlugins = false;
		}

		if (!file_exists($this->current . "/templates"))
		{
			$this->hasTemplates = false;
		}

		if (!file_exists($this->current . "/libraries"))
		{
			$this->hasLibraries = false;
		}

		if (!file_exists($this->current . "/components/com_comprofiler"))
		{
			$this->hasCBPlugins = false;
		}
	}

	/**
	 * Add files
	 *
	 * @param   \ZipArchive  $zip   The zip object
	 * @param   string       $path  Optional path
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function addFiles($zip, $path = null)
	{
		if (!$path)
		{
			$path = $this->current;
		}

		$source = str_replace('\\', '/', realpath($path));

		if (is_dir($source) === true)
		{
			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($files as $file)
			{
				$file = str_replace('\\', '/', $file);

				if (substr($file, 0, 1) == ".")
				{
					continue;
				}

				// Ignore "." and ".." folders
				if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
				{
					continue;
				}

				$file = str_replace('\\', '/', $file);

				if (is_dir($file) === true)
				{
					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
				}
				elseif (is_file($file) === true)
				{
					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
				}
			}
		}
		elseif (is_file($source) === true)
		{
			$zip->addFromString(basename($source), file_get_contents($source));
		}
	}

	/**
	 * Create a installable zip file for a component
	 *
	 * @TODO implement possibility for multiple components (without duplicate content)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createComponentZip()
	{
		$comZip = new \ZipArchive(JPATH_BASE . "/dist", \ZipArchive::CREATE);

		$tmpPath = '/dist/tmp/cbuild';

		if (file_exists(JPATH_BASE . $tmpPath))
		{
			$this->_deleteDir(JPATH_BASE . $tmpPath);
		}

		// Improve, should been a whitelist instead of a hardcoded copy
		$this->_mkdir(JPATH_BASE . $tmpPath);

		$this->_copyDir($this->current . '/administrator', JPATH_BASE . $tmpPath . '/administrator');
		$this->_remove(JPATH_BASE . $tmpPath . '/administrator/manifests');
		$this->_copyDir($this->current . '/language', JPATH_BASE . $tmpPath . '/language');
		$this->_copyDir($this->current . '/components', JPATH_BASE . $tmpPath . '/components');

		if (file_exists($this->current . '/media'))
		{
			$this->_copyDir($this->current . '/media', JPATH_BASE . $tmpPath . '/media');
		}

		$comZip->open(JPATH_BASE . '/dist/zips/com_' . $this->getExtensionName() . '.zip', \ZipArchive::CREATE);

		// Process the files to zip
		$this->addFiles($comZip, JPATH_BASE . $tmpPath);

		$comZip->addFile($this->current . "/" . $this->getExtensionName() . ".xml", $this->getExtensionName() . ".xml");
		$comZip->addFile($this->current . "/administrator/components/com_" . $this->getExtensionName() . "/script.php", "script.php");

		// Close the zip archive
		$comZip->close();
	}

	/**
	 * Create zips for libraries
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createLibraryZips()
	{
		$path = $this->current . "/libraries";

		// Get every module
		$hdl = opendir($path);

		while ($lib = readdir($hdl))
		{
			// Only folders
			$p = $path . "/" . $lib;

			if (substr($lib, 0, 1) == '.')
			{
				continue;
			}

			// Workaround for libraries without lib_
			if (substr($lib, 0, 3) != "lib")
			{
				$lib = 'lib_' . $lib;
			}

			if (!is_file($p))
			{
				$this->say("Packaging Library " . $lib);

				// Package file
				$zip = new \ZipArchive(JPATH_BASE . "/dist", \ZipArchive::CREATE);

				$zip->open(JPATH_BASE . '/dist/zips/' . $lib . '.zip', \ZipArchive::CREATE);

				$this->say("Library " . $p);

				// Process the files to zip
				$this->addFiles($zip, $p);

				// Close the zip archive
				$zip->close();
			}
		}

		closedir($hdl);
	}

	/**
	 * Create zips for modules
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createModuleZips()
	{
		$path = $this->current . "/modules";

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
				$this->say("Packaging Module " . $entry);

				// Package file
				$zip = new \ZipArchive(JPATH_BASE . "/dist", \ZipArchive::CREATE);

				$zip->open(JPATH_BASE . '/dist/zips/' . $entry . '.zip', \ZipArchive::CREATE);

				$this->say("Module " . $p);

				// Process the files to zip
				$this->addFiles($zip, $p);

				// Close the zip archive
				$zip->close();
			}
		}

		closedir($hdl);
	}

	/**
	 * Create zips for plugins
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createPluginZips()
	{
		$path = $this->current . "/plugins";

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
					if (substr($plugin, 0, 1) == '.')
					{
						continue;
					}

					// Only folders
					$p2 = $path . "/" . $type . "/" . $plugin;

					if (!is_file($p2))
					{
						$plg = "plg_" . $type . "_" . $plugin;

						$this->say("Packaging Plugin " . $plg);

						// Package file
						$zip = new \ZipArchive(JPATH_BASE . "/dist", \ZipArchive::CREATE);

						$zip->open(JPATH_BASE . '/dist/zips/' . $plg . '.zip', \ZipArchive::CREATE);

						// Process the files to zip
						$this->addFiles($zip, $p2);

						// Close the zip archive
						$zip->close();
					}
				}

				closedir($hdl2);
			}
		}

		closedir($hdl);
	}

	/**
	 * Create zips for templates
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createTemplateZips()
	{
		$path = $this->current . "/templates";

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
				$this->say("Packaging Template " . $entry);

				// Package file
				$zip = new \ZipArchive(JPATH_BASE . "/dist", \ZipArchive::CREATE);

				$zip->open(JPATH_BASE . '/dist/zips/tpl_' . $entry . '.zip', \ZipArchive::CREATE);

				$this->say("Template " . $p);

				// Process the files to zip
				$this->addFiles($zip, $p);

				// Close the zip archive
				$zip->close();
			}
		}

		closedir($hdl);
	}

	/**
	 * Create package zip (called latest)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createPackageZip()
	{
		$zip = new \ZipArchive($this->target, \ZipArchive::CREATE);

		// Instantiate the zip archive
		$zip->open($this->target, \ZipArchive::CREATE);

		// Process the files to zip
		$this->addFiles($zip, JPATH_BASE . '/dist/zips/');

		$pkgPath = $this->current . "/administrator/manifests/packages/pkg_" . $this->getExtensionName();

		$zip->addFile($pkgPath . ".xml", "pkg_" . $this->getExtensionName() . ".xml");
		$zip->addFile($this->current . "/administrator/manifests/packages/" . $this->getExtensionName() . "/script.php", "script.php");

		// If the package has language files, add those
		$pkgLanguagesPath = $pkgPath . "/language";
		$languages = glob($pkgLanguagesPath . "/*/*.pkg_" . $this->getExtensionName() . "*.ini");

		// Add all package language files
		foreach ($languages as $langPath)
		{
			$pathInZip = substr($langPath, strlen($pkgPath) + 1);
			$zip->addFile($langPath, $pathInZip);
		}

		// Close the zip archive
		$zip->close();
	}
}
