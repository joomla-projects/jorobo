<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

trait loadTasks
{
	/**
	 * Map Task
	 *
	 * @param   String  $target  - The target directory
	 *
	 * @return  Map
	 *
	 * @since   1.0
	 */
	protected function taskMap($target)
	{
		return new Map($target);
	}

	/**
	 * The build task
	 *
	 * @param   array  $params  - Opt params
	 *
	 * @return  Build
	 *
	 * @since   1.0
	 */
	protected function taskBuild($params)
	{
		return new Build($params);
	}

	/**
	 * The generate task
	 *
	 * @param   array  $params  - Opt params
	 *
	 * @return  Generate
	 *
	 * @since   1.0
	 */
	protected function taskGenerate($params)
	{
		return new Generate($params);
	}

	/**
	 * The CopyrightHeader task
	 *
	 * @param   array  $params  - Opt params
	 *
	 * @return  CopyrightHeader
	 *
	 * @since   1.0
	 */
	protected function taskCopyrightHeaders($params)
	{
		return new CopyrightHeader($params);
	}

	/**
	 * Bump the __DEPLOY_VERSION__ task
	 *
	 * @return  BumpVersion
	 *
	 * @since   1.0
	 */
	protected function taskBumbVersion()
	{
		return new BumpVersion();
	}
}
