<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Result;

/**
 * Generate / Update copyright headers in project files
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class CopyrightHeader extends JTask
{
    use \Robo\Task\Development\Tasks;
    use Generate\Tasks;

    /**
     * Generate / Update copyright headers
     *
     * @return  Result
     *
     * @since   1.0
     */
    public function run()
    {
        $this->printTaskInfo("Updating / adding copyright headers");

        if (!$this->checkFolders()) {
            return Result::error($this, 'checkFolders failed');
        }

        $text        = $this->replaceInText(trim($this->getJConfig()->header->text));
        $excludeList = $this->getJConfig()->header->exclude;

        if ($excludeList !== '') {
            $exclude = array_filter(array_map('trim', explode(",", $excludeList)), static function ($e) {
                return $e !== '';
            });
        }

        $path      = $this->getSourceFolder();
        $fileTypes = array_map('trim', explode(",", $this->getJConfig()->header->files));

        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $filter    = new \RecursiveCallbackFilterIterator($directory, function ($current) {
            return substr($current->getFilename(), 0, 1) !== '.';
        });

        foreach (new \RecursiveIteratorIterator($filter) as $filename) {
            $file = new \SplFileInfo($filename);

            if (!in_array($file->getExtension(), $fileTypes)) {
                continue;
            }

            // Skip directories in exclude list
            if (isset($exclude) && count($exclude)) {
                $relative = str_replace(realpath($path), "", $file->getPath());

                // It is possible to have multiple exclude directories
                foreach ($exclude as $e) {
                    if (stripos($relative, $e) !== false) {
                        $this->printTaskInfo("Excluding " . $filename);
                        continue 2;
                    }
                }
            }

            // Remove previous / any doctype headers at the beginning of the file
            // Todo: needs check for class headers (as long as namespace / use / defined _jexec is there, this is no issue)
            $this->removeHeader($file);
            $this->addHeader($file, $text);
        }

        return Result::success($this, "Finished updating copyright headers");
    }

    /**
     * Replaces placeholders in the copyright header
     * Todo separate and make configurable and extensible
     *
     * @param   string  $text  The header text with placeholders
     *
     * @return  mixed
     *
     * @since   1.0
     */
    protected function replaceInText($text)
    {
        $text = str_replace("##YEAR##", date('Y'), $text);
        $text = str_replace("##DATE##", date('Y-m-d'), $text);

        return $text;
    }

    /**
     * Remove copyright headers in file (If any)
     *
     * @param   \SplFileInfo  $file  Target
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function removeHeader(\SplFileInfo $file)
    {
        $content = file_get_contents($file->getRealPath());

        $lines = explode(PHP_EOL, $content);

        $inComment = false;

        foreach ($lines as $i => $l) {
            $l = trim($l);

            if (!$inComment && (strpos($l, "<?php") === 0 || $l == "")) {
                continue;
            }

            // Only remove comment blocks, not code which happens to contain a "*" (e.g. "import * as x from 'y'")
            if (!$inComment && strpos($l, "/*") !== 0) {
                break;
            }

            // The comment ends on this line, if "*/" follows (skipping the opening "/*" of a new comment)
            $offset    = $inComment ? 0 : 2;
            $inComment = strpos($l, "*/", min($offset, strlen($l))) === false;

            unset($lines[$i]);
        }

        file_put_contents($file->getRealPath(), implode(PHP_EOL, $lines));
    }

    /**
     * Adds copyright headers in file
     *
     * @param   \SplFileInfo  $file  Target
     * @param   string        $text  The header text with placeholders
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function addHeader(\SplFileInfo $file, $text)
    {
        $content = file_get_contents($file->getRealPath());

        $lines = explode(PHP_EOL, $content);
        $text  = explode("\n", $text);

        foreach ($lines as $i => $l) {
            $l = trim($l);

            if (strpos($l, "<?php") === 0) {
                continue;
            }

            array_splice($lines, $i, 0, $text);

            break;
        }

        file_put_contents($file->getRealPath(), implode(PHP_EOL, $lines));
    }
}
