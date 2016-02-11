<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	 */
	protected function taskMetrics($options = [])
	{
		return new Metrics($options);
	}

	public function metrics($options = [])
	{
		$this->taskMetrics($options)->run();
	}
}
