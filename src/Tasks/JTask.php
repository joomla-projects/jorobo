<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace joomla_projects\jorobo\Tasks;

use Robo\Contract\TaskInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class JTask - Base class for our tasks
 *
 * @package  joomla_projects\jorobo\Tasks
 */
abstract class JTask extends \Robo\Tasks implements TaskInterface
{
	/**
	 * The config object
	 *
	 * @var    array|null
	 */
	protected static $config = null;

	/**
	 * Operating sytem
	 *
	 * @var    string
	 */
	protected $os = '';

	/**
	 * The file extension (OS Support)
	 *
	 * @var    string
	 */
	protected $fileExtension = '';

	/**
	 * The source folder
	 *
	 * @var    string
	 */
	protected $sourceFolder = '';


	/**
	 * Construct
	 *
	 * @param   array  $params  Opt params
	 */
	public function __construct($params = array())
	{
		$this->loadConfiguration($params);
		$this->determineOperatingSystem();
		$this->determineSourceFolder();
	}

	/**
	 * Function to check if folders are existing / writable (Code Base etc.)
	 *
	 * @return  bool
	 */
	public function checkFolders()
	{
		$dirHandle = opendir($this->getSourceFolder());

		if ($dirHandle === false)
		{
			$this->printTaskError('Can not open ' . $this->getSourceFolder() . ' for parsing');

			return false;
		}

		return true;
	}

	/**
	 * Get the operating system
	 *
	 * @return string
	 */
	public function getOs()
	{
		return $this->os;
	}

	/**
	 * Get the build config
	 *
	 * @return  object
	 */
	public function getConfig()
	{
		return self::$config;
	}

	/**
	 * Get the source folder path
	 *
	 * @return  string  absolute path
	 */
	public function getSourceFolder()
	{
		return $this->sourceFolder;
	}

	/**
	 * Get the extension name
	 *
	 * @return   string
	 */
	public function getExtensionName()
	{
		return strtolower($this->getConfig()->extension);
	}

	/**
	 * Get the destination / build folder
	 *
	 * @return   string
	 */
	public function getBuildFolder()
	{
		return $this->getConfig()->buildFolder;
	}

	/**
	 * Sets the source folder
	 */
	private function determineSourceFolder()
	{
		$this->sourceFolder = JPATH_BASE . "/" . $this->getConfig()->source;

		if (!is_dir($this->sourceFolder))
		{
			$this->say('Warning - Directory: ' . $this->sourceFolder . ' is not available');
		}
	}

	/**
	 * Sets the operating system
	 */
	private function determineOperatingSystem()
	{
		$this->os = strtoupper(substr(PHP_OS, 0, 3));

		if ($this->os === 'WIN')
		{
			$this->fileExtension = '.exe';
		}
	}

	/**
	 * Load config
	 *
	 * @param $params
	 * @return bool
	 */
	private function loadConfiguration($params)
	{
		if (!is_null(self::$config))
		{
			return true;
		}

		// Load config as object
		$config = json_decode(json_encode(parse_ini_file(JPATH_BASE . '/jorobo.ini', true)), false);

		if (!$config)
		{
			$this->say('Error: Config file jbuild.ini not available');

			throw new FileNotFoundException('Config file jbuild.ini not available');
		}

		// Are we building a git / dev release?
		if ($this->isDevelopmentVersion($params))
		{
			$res = $this->_exec('git rev-parse --short HEAD');

			$version = trim($res->getMessage());

			if ($version)
			{
				$this->say("Changing version to development version " . $version);
				$config->version = $version;
			}
		}

		$config->buildFolder = JPATH_BASE . $this->determineTarget($config);
		$config->params      = $params;

		self::$config = $config;

		// Date set
		date_default_timezone_set('UTC');
	}

	/**
	 * @param $params
	 * @return mixed
	 */
	private function isDevelopmentVersion($params)
	{
		return isset($params['dev']) ? $params['dev'] : false;
	}

	/**
	 * @param $config
	 * @return string
	 */
	private function determineTarget($config)
	{
		if (!isset($config->extension))
		{
			return 'unnamed';
		}

		$target = "/dist/" . $config->extension;

		if (!empty($config->version))
		{
			$target = "/dist/" . $config->extension . "-" . $config->version;
			return $target;
		}

		return $target;
	}
}
