<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Collection\CollectionBuilder;

trait Tasks
{
    /**
     * Map Task
     *
     * @param   String  $target  The target directory
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function taskMap($target, $params = [])
    {
        return $this->task(Map::class, $target, $params);
    }

    /**
     * The build task
     *
     * @param   array  $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function taskBuild($params = [])
    {
        return $this->task(Build::class, $params);
    }

    /**
     * The generate task
     *
     * @param   array  $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function taskGenerate($params = [])
    {
        return $this->task(Generate::class, $params);
    }

    /**
     * The CopyrightHeader task
     *
     * @param   array  $params  Opt params
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function taskCopyrightHeaders($params = [])
    {
        return $this->task(CopyrightHeader::class, $params);
    }

    /**
     * Bump the __DEPLOY_VERSION__ task
     *
     * @return  CollectionBuilder
     *
     * @since   1.0
     */
    protected function taskBumpVersion($params = [])
    {
        return $this->task(BumpVersion::class, $params);
    }
}
