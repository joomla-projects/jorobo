<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JBuild\Tasks;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

/**
 * Class Metrics
 *
 * @package JBuild\Tasks
 */
class Metrics extends JTask
{
	private $command = 'vendor/bin/phpqa';
	private $options = [];

	use \Robo\Task\Base\loadTasks;

	public function __construct($options = [])
	{
		parent::__construct();

		$this->options = $options;
	}

	/**
	 * @return \Robo\Result
	 */
	public function run()
	{
		$task = $this->taskExec($this->command);

		if ($this->options['verbose'])
		{
			$task->option('verbose');
		}

		$task->option('analyzedDir', $this->getSourceFolder());
		$task->option('buildDir', './build');

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
