<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

trait Tasks
{
    /**
     * Build extension
     *
     * @return  Zip
     *
     * @since   1.0
     */
    protected function deployZip($params = [])
    {
        return new Zip($params);
    }

    /**
     * Build extension
     *
     * @return  Package
     *
     * @since   1.0
     */
    protected function deployPackage($params = [])
    {
        return new Package($params);
    }

    /**
     * Build extension
     *
     * @return  Release
     *
     * @since   1.0
     */
    protected function deployRelease($params = [])
    {
        return new Release($params);
    }

    /**
     * Deploy to FTP
     * (Depends on package or zip deploy task)
     *
     * @return  FtpUpload
     *
     * @since   1.0
     */
    protected function deployFtp($params = [])
    {
        return new FtpUpload($params);
    }
}
