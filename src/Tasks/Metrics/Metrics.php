<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Metrics;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Class Metrics
 *
 * @package Joomla\Jorobo\Tasks
 */
class Metrics extends \Joomla\Jorobo\Tasks\JTask
{
	private $command = 'vendor/bin/phpqa';

	private $options = [];

	use \Robo\Task\Base\loadTasks;

	/**
	 * Initialize Metrics Task
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __construct($options = [])
	{
		parent::__construct();

		$this->options = $options;
	}

	/**
	 * Rund the metrics
	 *
	 * @return  \Robo\Result
	 *
	 * @since    1.0
	 */
	public function run()
	{
		$task = $this->taskExec($this->command);

		if ($this->options['verbose'])
		{
			$task->option('verbose');
		}

		$task->option('analyzedDir', $this->getSourceFolder());
		$task->option('buildDir', './docs/phpqa');
		$task->option('ignoredDirs', 'plugins/payment,administrator/components/com_matukio/includes');
		$task->option('report');

		if ($this->options['quiet'])
		{
			ob_start();
			$task->run();
			ob_end_clean();
		}
		else
		{
			$task->run();
		}
	}
}
