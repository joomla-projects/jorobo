<?php
/**
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Jorobo\Tasks\Build;

use Robo\Common\TaskIO;
use Robo\Contract\TaskInterface;
use Robo\Task\Development\loadTasks;

/**
 * Class Language
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @since       1.0
 */
class Language extends Base implements TaskInterface
{
	use loadTasks;
	use TaskIO;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $ext = null;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $type = "com";

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $target = null;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $adminLangPath = null;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $frontLangPath = null;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	protected $hasAdminLang = true;

	/**
	 * @var   boolean
	 *
	 * @since  1.0
	 */
	protected $hasFrontLang = true;


	/**
	 * Initialize Build Task
	 *
	 * @param   String  $extension  The extension (component, module etc.)
	 *
	 * @since   1.0
	 */
	public function __construct($extension)
	{
		parent::__construct();

		$this->adminLangPath = $this->getSourceFolder() . "/administrator/language";
		$this->frontLangPath = $this->getSourceFolder() . "/language";

		$this->ext = $extension;

		$this->type = substr($extension, 0, 3);
	}

	/**
	 * Returns true
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function run()
	{
		if ($this->type != "plu")
		{
			$this->analyze();
		}

		if (!$this->hasAdminLang && !$this->hasFrontLang)
		{
			// No Language files
			return true;
		}

		$this->say("Building language for " . $this->ext . " | Type " . $this->type);

		// Make sure we have the language folders in our target
		$this->prepareDirectories();

		$dest = $this->getBuildFolder();

		if ($this->type == "mod")
		{
			$dest .= "/modules/" . $this->ext;
		}
		elseif ($this->type == "plg")
		{
			$a     = explode("_", $this->ext);
			$dest .= "/plugins/" . $a[1] . "/" . $a[2];
		}
		elseif ($this->type == "pkg")
		{
			$dest .= "/administrator/manifests/packages/" . $this->ext;
		}
		elseif ($this->type == "lib")
		{
			// Remove lib before - ugly hack
			$ex    = str_replace("lib_", "", $this->ext);
			$dest .= "/libraries/" . $ex;
		}
		elseif ($this->type == "plu")
		{
			$a = explode("_", $this->ext);

			$this->say("plug: " . $this->ext);
			$this->say("/components/com_comprofiler/plugin/" . $a[1] . "/plug_" . $a[3]);

			$dest .= "/components/com_comprofiler/plugin/" . $a[1] . "/plug_" . $a[3];

			$this->ext = "plg_plug_" . $a[3];
			$this->hasFrontLang = false;
		}
		elseif ($this->type == "tpl")
		{
			$a     = explode("_", $this->ext);
			$dest .= "/templates/" . $a[1];
		}

		if ($this->hasAdminLang)
		{
			$map = $this->copyLanguage("administrator/language", $dest);
			$this->addFiles('backendLanguage', $map);
		}

		if ($this->hasFrontLang)
		{
			$map = $this->copyLanguage("language", $dest);
			$this->addFiles('frontendLanguage', $map);
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
		// Check for all languages here
		if (empty(glob($this->adminLangPath . "/*/*." . $this->ext . "*.ini")))
		{
			$this->hasAdminLang = false;
		}

		if (empty(glob($this->frontLangPath . "/*/*." . $this->ext . "*.ini")))
		{
			$this->hasFrontLang = false;
		}
	}

	/**
	 * Prepare the directory structure
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	private function prepareDirectories()
	{
		if ($this->type == "com")
		{
			if ($this->hasAdminLang)
			{
				$this->_mkdir($this->getBuildFolder() . "/administrator/language");
			}

			if ($this->hasFrontLang)
			{
				$this->_mkdir($this->getBuildFolder() . "/language");
			}
		}

		if ($this->type == "mod")
		{
			$this->_mkdir($this->getBuildFolder() . "/modules/" . $this->ext . "/language");
		}

		if ($this->type == "plg")
		{
			$a = explode("_", $this->ext);

			$this->_mkdir($this->getBuildFolder() . "/plugins/" . $a[1] . "/" . $a[2] . "/administrator/language");
		}

		if ($this->type == "plug")
		{
			$a = explode("_", $this->ext);

			$this->_mkdir($this->getBuildFolder() . "/components/com_comprofiler/plugin/" . $a[1] . "/" . $this->ext . "/administrator/language");
		}

		return true;
	}

	/**
	 * Copy language files
	 *
	 * @param   string  $dir     The directory (administrator/language or language or mod_xy/language etc)
	 * @param   String  $target  The target directory
	 *
	 * @return   array
	 *
	 * @since   1.0
	 */
	public function copyLanguage($dir, $target)
	{
		// Equals administrator/language or language
		$path  = $this->getSourceFolder() . "/" . $dir;
		$files = array();

		$hdl = opendir($path);

		while ($entry = readdir($hdl))
		{
			$p = $path . "/" . $entry;

			// Which languages do we have
			// Ignore hidden files
			if (substr($entry, 0, 1) != '.')
			{
				// Language folders
				if (!is_file($p))
				{
					// Make folder at destination
					$this->_mkdir($target . "/" . $dir . "/" . $entry);

					$fileHdl = opendir($p);

					while ($file = readdir($fileHdl))
					{
						// Only copy language files for this extension (and sys files..)
						if (substr($file, 0, 1) != '.' && strpos($file, $this->ext . "."))
						{
							$files[] = array($entry => $file);

							// Copy file
							$this->_copy($p . "/" . $file, $target . "/" . $dir . "/" . $entry . "/" . $file);
						}
					}

					closedir($fileHdl);
				}
			}
		}

		closedir($hdl);

		return $files;
	}
}
