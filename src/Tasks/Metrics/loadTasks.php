<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Metrics;

use Joomla\Jorobo\Tasks\Metrics;

trait loadTasks
{
	/**
	 * The metrics task
	 *
	 * @return  Metrics
	 *
	 * @since   1.0
	 */
	protected function taskMetrics($options = [])
	{
		return new \Joomla\Jorobo\Tasks\Metrics\Metrics($options);
	}

	/**
	 * The metrics task
	 *
	 * @return  Metrics
	 *
	 * @since   1.0
	 */
	public function metrics($options = [])
	{
		$this->taskMetrics($options)->run();
	}
}
