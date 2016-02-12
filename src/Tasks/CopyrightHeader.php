<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Contract\TaskInterface;

/**
 * Generate / Update copyright headers in project files
 *
 * @package  Joomla\Jorobo\Tasks
 */
class CopyrightHeader extends JTask implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;
	use Generate\generateTasks;

	/**
	 * @var array|null
	 */
	protected $params = null;

	/**
	 * Initialize Build Task
	 *
	 * @param   array  $params  Additional params
	 */
	public function __construct($params)
	{
		parent::__construct();

		$this->params = $params;
	}

	/**
	 * Generate / Update copyright headers
	 *
	 * @return  bool
	 */
	public function run()
	{
		$this->say("Updating / adding copyright headers");
		$text = $this->replaceInText(trim($this->getConfig()->header->text));
		$exclude = explode(",", trim($this->getConfig()->header->exclude));

		$path = realpath($this->getConfig()->source);
		$fileTypes = explode(",", trim($this->getConfig()->header->files));

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $dir => $filename)
		{
			if (substr($filename, 0, 1) == '.')
			{
				continue;
			}

			$file = new \SplFileInfo($filename);

			// Skip directories in exclude list
			if ($exclude && in_array($file->getPath(), $exclude))
			{
				continue;
			}

			if (!in_array($file->getExtension(), $fileTypes))
			{
				continue;
			}

			// Remove previous / any doctype headers at the beginning of the file
			// Todo: needs check for class headers (as long as namespace / use is there, this is no issue)
			$this->removeHeader($file);
			$this->addHeader($file, $text);
		}

		$this->say("Finished updating copyright headers");
	}

	/**
	 * Replace placeholders in the copyright header
	 * Todo separate and make configurable and extentable
	 *
	 * @param   $text  -
	 *
	 * @return  mixed
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
	 * @param   \SplFileInfo  $file  - Target
	 *
	 * @return  void
	 */
	protected function removeHeader(\SplFileInfo $file)
	{
		$content = file_get_contents($file->getRealPath());

		$lines = explode(PHP_EOL, $content);

		foreach ($lines as $i => $l)
		{
			$l = trim($l);

			if (strpos($l, "<?php") === 0 || $l == "")
			{
				continue;
			}

			if (strpos($l, "/**") !== false || strpos($l, "*") !== false || strpos($l, "*/") !== false )
			{
				unset($lines[$i]);

				continue;
			}

			break;
		}

		file_put_contents($file->getRealPath(), implode(PHP_EOL, $lines));
	}

	/**
	 * Adds copyright headers in file
	 *
	 * @param   \SplFileInfo  $file  - Target
	 *
	 * @return  void
	 */
	protected  function addHeader(\SplFileInfo $file, $text)
	{
		$content = file_get_contents($file->getRealPath());

		$lines = explode(PHP_EOL, $content);
		$text = explode("\n", $text);

		foreach ($lines as $i => $l)
		{
			$l = trim($l);

			if (strpos($l, "<?php") === 0)
			{
				continue;
			}

			array_splice($lines, $i, 0, $text);

			break;
		}

		file_put_contents($file->getRealPath(), implode(PHP_EOL, $lines));
	}
}
