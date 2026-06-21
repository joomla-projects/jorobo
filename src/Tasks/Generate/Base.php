<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Generate;

use Joomla\Jorobo\Tasks\JTask;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\TaskException;

/**
 * Generate base class - contains methods / data used in multiple generateion tasks
 *
 * @package  Joomla\Jorobo\Generate\Base
 *
 * @since    1.0
 */
abstract class Base extends JTask
{
    private $replacements = [];
    private $fs;
    private $exclude = [];

    /**
     * @var int
     */
    protected $chmod = 0755;

    /**
     * Pattern replacer
     *
     * Search pattern: _#_<UPPERCASE_PLACEHOLDER>_#_ with modifier _#LC_<UPPERCASE-PLACEHOLDER>_#_ converted to lowercase
     *
     * Modifier:
     *   LC      String to lowercase
     *   UC      String to uppercase
     *   BI      Build In replacments
     *     DAY   date(d)
     *     MONTH date(m)
     *     YEAR  date(Y)
     *   X<A-Z>  Callback for supplied function
     *
     * @param string     $string      The string to replace
     * @param array      $replacement Associative array with key => value
     * @param array|null $callbacks   Associative array with key => function(string)
     *
     *
     * @return string|string[]|null  The modified string
     */
    private function patternreplace(string $string, array $replacement, ?array $callbacks = null)
    {
        do {
            $string = preg_replace_callback(
                '/_#([X]){0,1}([A-Z]{0,2}){0,1}_([A-Z-_]*)_#_/',
                function ($match) use ($replacement, $callbacks) {
                    if (is_array($callbacks)) {
                        if ($match[1] === 'X') {
                            if (isset($callbacks[$match[2]])) {
                                return $callbacks[$match[2]]($match[3]);
                            }
                        }
                    }

                    if ($match[2] === 'BI') {
                        switch ($match[3]) {
                            case 'DAY':
                                return date('d');
                            case 'MONTH':
                                return date('m');
                            case 'MONTH_NAME':
                                return date('F');
                            case 'YEAR':
                                return date('Y');
                            default:
                                throw new TaskException($this, 'Internal modifier ' . $match[2] . ' ' . $match[3] . ' not found.');
                        }
                    }

                    if (isset($replacement[$match[3]])) {
                        $value = $replacement[$match[3]];

                        if (empty($match[2])) {
                            return $value;
                        }

                        switch ($match[2]) {
                            case 'LC':
                                return strtolower($value);
                            case 'UC':
                                return strtoupper($value);
                            case 'LF':
                                return lcfirst($value);
                            case 'UF':
                                return ucfirst($value);
                            case 'CC': // camel Case
                            case 'PC': // Pascal Case
                                $separators = [
                                    "-",
                                    " ",
                                    "\t",
                                    "\r",
                                    "\n",
                                    "\f",
                                    "\v",
                                ];
                                $value = str_replace($separators, '', ucwords($value, implode('', $separators)));

                                // lower the first character for camel Case
                                if ($match[2] === 'CC') {
                                    return strtolower($value[0]) . substr($value, 1);
                                }

                                return $value;
                            default:
                                throw new TaskException($this, 'Modifier ' . $match[2] . ' not found.');
                        }
                    } else {
                        throw new TaskException($this, 'Replacment ' . $match[3] . ' not found.');
                    }
                },
                $string,
                -1,
                $count
            );
        } while ($count > 0);

        return $string;
    }

    /**
     * Add files to array
     *
     * @param   string  $type       Type (media, component etc.)
     * @param   array   $fileArray  File array
     *
     * @return  boolean
     *
     * @since   1.0
     */
    public function addFiles($type, $fileArray)
    {
        $method = 'add' . ucfirst($type) . "Files";

        if (method_exists($this, $method)) {
            $this->$method($fileArray);
        } else {
            $this->printTaskError('Missing method: ' . $method);
        }

        return true;
    }

    /**
     * Copies the files and maps them into an array
     *
     * @param   string  $path  Folder path
     * @param   string  $tar   Target path
     *
     * @return  array
     *
     * @since   1.0
     */
    protected function copyTarget($path, $tar)
    {
        $map = [];
        $hdl = opendir($path);

        while ($entry = readdir($hdl)) {
            $p = $path . "/" . $entry;

            // Ignore hidden files
            if (substr($entry, 0, 1) != '.') {
                if (
                    isset($this->getJConfig()->exclude)
                    && in_array($entry, explode(',', $this->getJConfig()->exclude))
                ) {
                    continue;
                }

                if (is_file($p)) {
                    $map[] = ["file" => $entry];
                    $this->taskFilesystemStack()
                        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                        ->copy($p, $tar . "/" . $entry)
                        ->run();
                } else {
                    $map[] = ["folder" => $entry];
                    $this->taskCopyDir([$p => $tar . "/" . $entry])
                        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE)
                        ->run();
                }
            }
        }

        closedir($hdl);

        return $map;
    }

    protected function copyDir($src, $dst, $parent = '')
    {
        $dir = @opendir($src);
        if (false === $dir) {
            throw new TaskException($this, "Cannot open source directory '" . $src . "'");
        }

        if (!is_dir($dst)) {
            $this->io->say('Create directory ' . $dst);
            mkdir($dst, $this->chmod, true);
        }

        $finfo = finfo_open(FILEINFO_MIME);

        while (false !== ($file = readdir($dir))) {
            // Support basename and full path exclusion.
            if ($this->excluded($file, $src, $parent)) {
                continue;
            }
            $srcFile  = $src . '/' . $file;
            $destFile = $dst . '/' . $file;
            try {
                $destFile = $this->replace($destFile);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' (Path: ' . $destFile . ')');
            }

            if (is_dir($srcFile)) {
                $this->copyDir($srcFile, $destFile, $parent . $file . DIRECTORY_SEPARATOR);
            } else {
                $isText   = false;
                $fileType = finfo_file($finfo, $srcFile);
                if (
                    str_starts_with($fileType, 'text')
                    || str_starts_with($fileType, 'application/json')
                ) {
                    $isText = true;
                }

                if (file_exists($destFile) && $isText) {
                    if (substr($srcFile, -4) === '.xml') {
                        $this->io->say('XML file ' . $destFile . ' exists patching content.');
                    } else {
                        $this->io->say('Textfile ' . $destFile . ' exists appending content.');
                    }

                    $content = file_get_contents($srcFile);
                    try {
                        $content = $this->replace($content);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage() . ' (File: ' . $srcFile . ')');
                    }

                    if (substr($srcFile, -4) === '.xml') {
                        $content = $this->patchXML(file_get_contents($destFile), $content);
                        file_put_contents($destFile, $content);
                    } elseif (substr($srcFile, -4) === '.php') {
                        $content = $this->patchPHP(file_get_contents($destFile), $content);
                        file_put_contents($destFile, $content);
                    } else {
                        $this->fs->appendToFile($destFile, $content);
                    }
                } else {
                    $this->io->say('Copy file to ' . $destFile);
                    $this->fs->copy($srcFile, $destFile, false);
                    // detect if we have a plaintext file
                    if ($isText) {
                        $this->io->say('Replacing content in textfile ' . $destFile);
                        $content = file_get_contents($destFile);
                        try {
                            $content = $this->replace($content);
                        } catch (\Exception $e) {
                            throw new \Exception($e->getMessage() . ' (File: ' . $srcFile . ')');
                        }
                        file_put_contents($destFile, $content);
                    }
                }
            }
        }
        closedir($dir);
    }

    private function patchXML($content, $patch)
    {
        $DOMParent                     = new \DOMDocument();
        $DOMParent->formatOutput       = true;
        $DOMParent->preserveWhiteSpace = false;
        $DOMParent->loadXML($content);
        $xpathParent = new \DOMXpath($DOMParent);

        $DOMChild                     = new \DOMDocument();
        $DOMChild->formatOutput       = true;
        $DOMChild->preserveWhiteSpace = false;
        $DOMChild->loadXML($patch);
        $xpathChild = new \DOMXpath($DOMChild);

        //$node = $DOMChild->documentElement;
        $node = $xpathChild->query("/patch")->item(0);
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $taskNode) {
                /** @var DOMNode $taskNode */
                if ($taskNode->nodeName === 'add') {
                    $target = $taskNode->attributes->getNamedItem('target')->value;
                    foreach ($taskNode->childNodes as $childNode) {
                        /** @var DOMNode $childNode */
                        $node = $DOMParent->importNode($childNode, true);

                        $xpathParent->query($target)->item(0)->appendChild($node);
                    }
                }
            }
        }

        return $DOMParent->saveXML();
    }

    private function patchPHP($content, $patch)
    {
        $patch   = explode(chr(10), $patch);
        $content = explode(chr(10), $content);

        $inPatch      = false;
        $currentPatch = [];
        $task         = '';
        $landmark     = '';
        foreach ($patch as $pLine) {
            if (strpos($pLine, '// PATCH+') !== false) {
                $inPatch           = true;
                [$task, $landmark] = explode('=', trim(substr($pLine, strpos('// PATCH+', $pLine) + 10)));
                continue;
            }
            if (strpos($pLine, '// PATCH-') !== false) {
                foreach ($content as $k => $cLine) {
                    if (strpos($cLine, $landmark) !== false) {
                        if ($task === 'INSERT') {
                            array_splice(
                                $content,
                                $k,
                                0,
                                $currentPatch
                            );
                        } elseif ($task === 'APPEND') {
                            array_splice(
                                $content,
                                $k + 1,
                                0,
                                $currentPatch
                            );
                        }
                        break;
                    }
                }

                $inPatch      = false;
                $currentPatch = [];
                $task         = '';
                $landmark     = '';
                continue;
            }
            if ($inPatch) {
                $currentPatch[] = $pLine;
            }
        }

        return implode(chr(10), $content);
    }

    private function replace($string)
    {
        return $this->patternreplace($string, $this->replacements);
    }

    /**
     * Check to see if the current item is excluded.
     *
     * @param string $file
     * @param string $src
     * @param string $parent
     *
     * @return bool
     */
    protected function excluded($file, $src, $parent)
    {
        return
            ($file == '.') ||
            ($file == '..') ||
            in_array($file, $this->exclude) ||
            in_array($this->simplifyForCompare($parent . $file), $this->exclude) ||
            in_array($this->simplifyForCompare($src . DIRECTORY_SEPARATOR . $file), $this->exclude);
    }

    /**
     * Avoid problems comparing paths on Windows that may have a
     * combination of DIRECTORY_SEPARATOR and /.
     *
     * @param string $item
     *
     * @return string
     */
    protected function simplifyForCompare($item)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $item);
    }
}
