<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Application;
use Robo\Contract\TaskInterface;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Robo;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class JTask - Base class for our tasks
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
abstract class JTask extends \Robo\Tasks implements TaskInterface, VerbosityThresholdInterface
{
    use \Robo\Common\TaskIO;

    /**
     * The Jorobo config object
     *
     * @var    \stdClass
     *
     * @since  1.0
     */
    protected static $jConfig = null;

    /**
     * Operating system
     *
     * @var    string
     *
     * @since  1.0
     */
    protected $os = '';

    /**
     * The file extension (OS Support)
     *
     * @var    string
     *
     * @since  1.0
     */
    protected $fileExtension = '';

    /**
     * The source folder
     *
     * @var    string
     *
     * @since  1.0
     */
    protected $sourceFolder = '';

    /**
     * Build parameters
     *
     * @var    array
     *
     * @since  1.0
     */
    protected $params = [];

    /**
     * Construct
     *
     * @param   array      $params  Opt params
     * @param   ConsoleIO  $io      IO object
     *
     * @since   1.0
     */
    public function __construct($params = [], $io = null)
    {
        $this->params         = (array) $params;
        $this->params['base'] = $this->params['base'] ?? \JPATH_BASE;
        $this->logger         = Robo::logger();

        if (is_a($io, '\Robo\Symfony\ConsoleIO')) {
            $this->io = $io;
        }

        // Registers the application to run Robo commands
        $app    = new Application('Joomla\Jorobo\Tasks\JTask', '1.0.0');
        Robo::register($app, $this);

        $this->loadConfiguration($params);
        $this->determineOperatingSystem();
        $this->determineSourceFolder();
    }

    /**
     * Function to check if folders are existing / writable (Code Base etc.)
     *
     * @return  boolean
     *
     * @since   1.0
     */
    public function checkFolders()
    {
        $dirHandle = opendir($this->getSourceFolder());

        if ($dirHandle === false) {
            $this->printTaskError('Can not open ' . $this->getSourceFolder() . ' for parsing');

            return false;
        }

        return true;
    }

    /**
     * Get the operating system
     *
     * @return string
     *
     * @since   1.0
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Get the build config
     *
     * @return  \stdClass
     *
     * @since   1.0
     */
    public function getJConfig()
    {
        return self::$jConfig;
    }

    /**
     * Get the source folder path
     *
     * @return  string  absolute path
     *
     * @since   1.0
     */
    public function getSourceFolder()
    {
        return $this->sourceFolder;
    }

    /**
     * Get the extension name
     *
     * @return  string
     *
     * @since   1.0
     */
    public function getExtensionName()
    {
        return strtolower($this->getJConfig()->extension);
    }

    /**
     * Get the destination / build folder
     *
     * @return   string
     *
     * @since   1.0
     */
    public function getBuildFolder()
    {
        return $this->getJConfig()->buildFolder;
    }

    /**
     * Sets the source folder
     *
     * @return  void
     *
     * @since   1.0
     */
    private function determineSourceFolder()
    {
        $this->sourceFolder = $this->params['base'] . "/" . $this->getJConfig()->source;

        if (!is_dir($this->sourceFolder)) {
            $this->printTaskError('Warning - Directory: ' . $this->sourceFolder . ' is not available');
        }
    }

    /**
     * Sets the operating system
     *
     * @return  void
     *
     * @since   1.0
     */
    private function determineOperatingSystem()
    {
        $this->os = strtoupper(substr(PHP_OS, 0, 3));

        if ($this->os === 'WIN') {
            $this->fileExtension = '.exe';
        }
    }

    /**
     * Load config
     *
     * @param   array  $params  Optional Params
     *
     * @return  boolean|void
     *
     * @since   1.0
     * @throws  FileNotFoundException
     */
    private function loadConfiguration($params)
    {
        if (!is_null(self::$jConfig)) {
            return true;
        }

        // Load config as object
        $jConfig = json_decode(json_encode(parse_ini_file($this->params['base'] . '/jorobo.ini', true)), false);

        if (!$jConfig) {
            $this->printTaskError('Error: Config file jorobo.ini not available');

            throw new FileNotFoundException('Config file jorobo.ini not available');
        }

        // Are we building a git / dev release?
        if ($this->isDevelopmentVersion($params)) {
            $res = $this->_exec('git rev-parse --short HEAD');

            $version = "git" . trim($res->getMessage());

            if ($version) {
                $this->printTaskInfo("Changing version to development version " . $version);
                $jConfig->version = $version;
            }
        }

        $jConfig->buildFolder = $this->params['base'] . $this->determineTarget($jConfig);
        $jConfig->params      = $params;

        self::$jConfig = $jConfig;

        // Date set
        date_default_timezone_set('UTC');
    }

    /**
     * Check if we are building a dev release
     *
     * @param   array  $params  Robo.li Params
     *
     * @return  boolean
     *
     * @since   1.0
     */
    private function isDevelopmentVersion($params)
    {
        return isset($params['dev']) ? $params['dev'] : false;
    }

    /**
     * Get target
     *
     * @param   object  $jConfig  The JoRobo config
     *
     * @return  string
     *
     * @since   1.0
     */
    private function determineTarget($jConfig)
    {
        if (!isset($jConfig->extension)) {
            return 'unnamed';
        }

        $target = "/dist/" . $jConfig->extension;

        if (!empty($jConfig->version)) {
            $target = "/dist/" . $jConfig->extension . "-" . $jConfig->version;

            return $target;
        }

        return $target;
    }
}
