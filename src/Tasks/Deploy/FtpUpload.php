<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Robo\Result;

/**
 * Deploy project via FTP - needs zip or pkg deployment to be done before
 *
 * @package  Joomla\Jorobo\Tasks\Deploy
 *
 * @since    1.0
 */
class FtpUpload extends Base
{
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
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo('Uploading ' . $this->getJConfig()->extension . $this->getJConfig()->version . " via FTP");

        // Todo Move filepath and name to config
        $this->filename = $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
        $this->filepath = $this->params['base'] . "/dist/" . $this->filename;

        // Check if we have a package
        if (in_array("package", explode(" ", $this->getJConfig()->target))) {
            $this->target   = "package";
            $this->filename = "pkg-" . $this->getExtensionName() . "-" . $this->getJConfig()->version . ".zip";
            $this->filepath = $this->params['base'] . "/dist/" . $this->filename;
        }

        try {
            $ftp  = $this->getJConfig()->ftp;
            $port = !empty($ftp->port) ? (int) $ftp->port : 21;

            // parse_ini_file() turns "true" into "1" and "false" into ""
            if (filter_var($ftp->ssl ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $con = ftp_ssl_connect($ftp->host, $port);
            } else {
                $con = ftp_connect($ftp->host, $port);
            }

            if ($con === false) {
                return Result::error($this, 'Could not connect to ' . $ftp->host . ':' . $port);
            }

            if (!ftp_login($con, $ftp->user, $ftp->password)) {
                return Result::error($this, 'Failed logging in');
            }

            // Set passive ftp (only possible after login)
            ftp_pasv($con, true);

            if (!ftp_chdir($con, $ftp->target)) {
                return Result::error($this, 'Could not change to directory ' . $ftp->target);
            }

            $this->printTaskInfo('Uploading ' . $this->filepath);

            if (!ftp_put($con, $this->filename, $this->filepath, FTP_BINARY)) {
                return Result::error($this, 'Failed uploading package');
            }

            $this->printTaskInfo("Upload finished");
        } catch (\Exception $e) {
            return Result::error($this, 'Error: ' . $e->getMessage());
        }

        return Result::success($this);
    }
}
