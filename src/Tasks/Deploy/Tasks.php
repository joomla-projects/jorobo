<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Robo\Collection\CollectionBuilder;

trait Tasks
{
    /**
     * Build extension
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function deployZip($params = [])
    {
        return $this->task(Zip::class, $params);
    }

    /**
     * Build extension
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function deployPackage($params = [])
    {
        return $this->task(Package::class, $params);
    }

    /**
     * Build extension
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function deployRelease($params = [])
    {
        return $this->task(Release::class, $params);
    }

    /**
     * Deploy to FTP
     * (Depends on package or zip deploy task)
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function deployFtp($params = [])
    {
        return $this->task(FtpUpload::class, $params);
    }
}
