<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace joomla_projects\jorobo\Tasks\Deploy;

use joomla_projects\jorobo\Tasks\Build\Component;
use joomla_projects\jorobo\Tasks\Build\Media;

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

	/**
	 * Build extension
	 *
	 * @return  Release
	 */
	protected function deployRelease()
	{
		return new Release();
	}
}
