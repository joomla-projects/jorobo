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
 * Class Media
 *
 * @package     Joomla\Jorobo
 * @subpackage  Tasks\Build
 *
 * @since       1.0
 */
class Media extends Base implements TaskInterface
{
	use loadTasks;
	use TaskIO;

	/**
	 * @var   string
	 *
	 * @since  1.0
	 */
	protected $source = null;

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
	protected $fileMap = null;

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
	protected $extName = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   String  $folder   The target directory
	 * @param   String  $extName  The extension name
	 *
	 * @since   1.0
	 */
	public function __construct($folder, $extName)
	{
		parent::__construct();

		$this->source = $this->getSourceFolder() . "/" . $folder;
		$this->extName = $extName;

		$this->type = substr($extName, 0, 3);

		$target = $this->getBuildFolder() . "/" . $folder;

		if ($this->type == 'mod')
		{
			$target = $this->getBuildFolder() . "/modules/" . $extName . "/" . $folder;
		}
		elseif ($this->type == 'plg')
		{
			$a = explode("_", $this->extName);

			$target = $this->getBuildFolder() . "/plugins/" . $a[1] . "/" . $a[2] . "/" . $folder;
		}
		elseif ($this->type == 'lib')
		{
			// Remove lib before - ugly hack
			$ex = str_replace("lib_", "", $this->extName);

			$target = $this->getBuildFolder() . "/libraries/" . $ex . "/" . $folder;
		}

		$this->target = $target;
	}

	/**
	 * Runs the media build task
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say("Building media folder " . $this->source . " for " . $this->extName);

		if (!file_exists($this->source))
		{
			$this->say("Folder " . $this->source . " does not exist!");

			return true;
		}

		$this->prepareDirectory();

		$map = $this->copyTarget($this->source, $this->target);

		$this->setResultFiles($map);

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
}
