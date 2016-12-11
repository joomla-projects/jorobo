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
 */
class FtpUpload extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * Should we upload a package or a zip (defaults to zip)
	 *
	 * @var    string
	 *
	 * @since  1.0
	 */
	protected $target = "zip";

	/**
	 * Path to the package we deploy
	 *
	 * @var    string
	 *
	 * @since  1.0
	 */
	protected $filepath = null;

	/**
	 * Filename of the package
	 *
	 * @var    string
	 *
	 * @since  1.0
	 */
	protected $filename = null;

	/**
	 * Build the package
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	public function run()
	{
		$this->say('Uploading ' . $this->getJConfig()->extension . $this->getJConfig()->version . " via FTP");

		// Todo Move filepath and name to config
		$this->filename = $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
		$this->filepath = JPATH_BASE . "/dist/" . $this->filename;

		// Check if we have a package
		if (in_array("package", explode(" ", $this->getJConfig()->target)))
		{
			$this->target = "package";
			$this->filename = "pkg-" . $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
			$this->filepath = JPATH_BASE . "/dist/" . $this->filename;
		}

		try
		{
			if ($this->getJConfig()->ftp->ssl == "true")
			{
				$con = ftp_ssl_connect($this->getJConfig()->ftp->host);
			}
			else
			{
				$con = ftp_connect($this->getJConfig()->ftp->host);
			}

			$login_result = ftp_login($con, $this->getJConfig()->ftp->user, $this->getJConfig()->ftp->password);

			// Set passive ftp
			ftp_pasv($con, true);

			if (!$login_result)
			{
				return Result::error($this, 'Failed logging in');
			}

			ftp_chdir($con, $this->getJConfig()->ftp->target);

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
