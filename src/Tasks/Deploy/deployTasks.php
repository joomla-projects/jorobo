<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JBuild\Tasks\Deploy;

use JBuild\Tasks\Build\Component;
use JBuild\Tasks\Build\Media;

trait deployTasks
{
	/**
	 * Build extension
	 *
	 * @return  Zip
	 */
	protected function deployZip()
	{
		return new Zip;
	}

	/**
	 * Build extension
	 *
	 * @return  Package
	 */
	protected function deployPackage()
	{
		return new Package();
	}
}
