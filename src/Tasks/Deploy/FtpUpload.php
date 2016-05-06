<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Codeception\Module\FTP;
use Robo\Result;
use Robo\Contract\TaskInterface;

/**
 * Deploy project via FTP - needs zip or pkg deployment to be done before
 *
 * @since  0.3.0
 */
class FtpUpload extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * Should we upload a package or a zip (defaults to zip)
	 *
	 * @var     string
	 */
	protected $target = "zip";

	/**
	 * Path to the package we deploy
	 *
	 * @var    string
	 */
	protected $filepath = null;

	/**
	 * Filename of the package
	 *
	 * @var    string
	 */
	protected $filename = null;

	/**
	 * Build the package
	 *
	 * @return  bool
	 */
	public function run()
	{
		$this->say('Uploading ' . $this->getConfig()->extension . $this->getConfig()->version . " via FTP");

		// Todo Move filepath and name to config
		$this->filename = $this->getExtensionName() . "-" . $this->getConfig()->version . ".zip";
		$this->filepath = JPATH_BASE . "/dist/" . $this->filename;

		// Check if we have a package
		if (in_array("package", explode(" ", $this->getConfig()->target)))
		{
			$this->target = "package";
			$this->filename = "pkg-" . $this->getExtensionName() . "-" . $this->getConfig()->version . ".zip";
			$this->filepath = JPATH_BASE . "/dist/" . $this->filename;
		}

		try
		{
			if ($this->getConfig()->ftp->ssl == "true")
			{
				$con = ftp_connect($this->getConfig()->ftp->host);
			}
			else
			{
				$con = ftp_connect($this->getConfig()->ftp->host);
			}

			$login_result = ftp_login($con, $this->getConfig()->ftp->user, $this->getConfig()->ftp->password);

			// Set passive ftp
			ftp_pasv($con, true);

			if (!$login_result)
			{
				return Result::error($this, 'Failed logging in');
			}

			ftp_chdir($con, $this->getConfig()->ftp->target);

			$this->say('Uploading ' . $this->filepath);

			if (!ftp_put($con, $this->filename, $this->filepath, FTP_BINARY))
			{
				return Result::error($this, 'Failed uploading package');
			}

			$this->say("Upload finished");
		}
		catch (\Exception $e)
		{
			return Result::error($this, 'Error: ' . $e->getMessage());
		}

		return true;
	}
}
