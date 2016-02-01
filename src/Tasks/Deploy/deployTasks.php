<?php
/**
 * @package     Jorobo
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Jorobo\Tasks\Deploy;

use Joomla\Jorobo\Tasks\Build\Component;
use Joomla\Jorobo\Tasks\Build\Media;

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
