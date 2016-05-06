<?php
/**
 * @package     JoRobo
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks\Deploy;

use Joomla\Registry\Registry;
use Joomla\Github\Github;
use Joomla\Http\Http;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;


/**
 * Release build package to github
 *
 * @since  0.5.0
 */
class Release extends Base implements TaskInterface
{
	use \Robo\Task\Development\loadTasks;
	use \Robo\Common\TaskIO;

	/**
	 * Release the build package on GitHub
	 *
	 * @return  bool
	 */
	public function run()
	{
		$version = $this->getConfig()->version;
		$remote = $this->getConfig()->github->remote;
		$branch = $this->getConfig()->github->branch;
		$owner = $this->getConfig()->github->owner;
		$repository = $this->getConfig()->github->repository;

		$this->say('Creating package ' . $this->getConfig()->extension . " " . $this->getConfig()->version);

		$latest_release = $this->getLatestReleases();
		$pulls = $this->getAllRepoPulls();

		$changes = $this->getChanges($latest_release, $pulls);

		$this->changelogUpdate($changes);

		$this->taskGitStack()
			->add('CHANGELOG.md')
			->commit("Prepare for release version " . $version)
			->push($remote, $branch)
			->run();

		$this->say("Creating github tag: $version");

		$this->taskGitStack()
			->stopOnFail()
			->tag($version)
			->push($remote, $version)
			->run();

		$this->say("Tag created: $version and published at $owner/$repository");

		$this->say("Creating the release at: https://github.com/$owner/$repository/releases/tag/$version");

		$github = $this->getGithub();
		$changesInRelease = "# Changelog: \n\n" . implode("\n* ", $changes);

		$response = $github->repositories->releases->create(
			$owner,
			$repository,
			(string) $version,
			'',
			$this->getConfig()->extension . " " . $version,
			$changesInRelease,
			false,
			true
		);

		$this->say(print_r($repository, true));

		$this->uploadToGithub($version, $this->getConfig()->github->token, $response->upload_url);
	}


	/**
	 * Get the Changes
	 *
	 * @param   bool   $latest_release  - Latest release
	 * @param   array  $pulls           - Pulls
	 *
	 * @return array
	 */
	private function getChanges($latest_release = false, $pulls)
	{
		$changes = array();

		foreach ($pulls as $pull)
		{
			if (!$latest_release || strtotime($pull->merged_at) > strtotime($latest_release->published_at))
			{
				if($this->getConfig()->github->changelog_source == "pr")
				{
					$changes[] = $pull->title;
				}

				$message = explode(PHP_EOL, $pull->commit->message);
				$changes[] = $message[0];
			}
		}

		return $changes;
	}

	/**
	 * Get the latest release
	 *
	 * @return  false|array
	 */
	protected function getLatestReleases()
	{
		$github = $this->getGithub();
		$owner = $this->getConfig()->github->owner;
		$repository = $this->getConfig()->github->repository;

		$this->say('Get latest Release commit ' . $owner . "/" . $repository);

		try
		{
			$latest_release = $github->repositories->releases->get(
				$owner,
				$repository,
				'latest'
			);
		}
		catch (\Exception $e)
		{
			$this->say($owner . "/" . $repository . " has no Release");

			return false;
		}

		return $latest_release;
	}

	/**
	 * Get all repository pulls for the changelog
	 *
	 * @param   string     $state   - The state of the PR (default closed)
	 * @param   string     $sha     - The sha sum (opt)
	 * @param   string     $path    - The path (opt)
	 * @param   string     $author  - The author (opt)
	 * @param   Date|null  $since   - Changes since (opt)
	 * @param   Date|null  $until   - Changes until (opt)
	 *
	 * @return  mixed
	 */
	protected function getAllRepoPulls($state = 'closed', $sha = '', $path = '', $author = '', Date $since = null, Date $until = null)
	{
		$github = $this->getGithub();

		if (!isset($this->allClosedPulls))
		{
			if($this->getConfig()->github->changelog_source == "pr")
			{
				$this->allClosedPulls = $github->pulls->getList(
					$this->getConfig()->github->owner,
					$this->getConfig()->github->repository,
					$state
				);
			}
			else
			{
				$this->allClosedPulls = $github->repositories->commits->getList(
					$this->getConfig()->github->owner,
					$this->getConfig()->github->repository,
					$sha,
					$path,
					$author,
					$since,
					$until
				);
			}
		}

		return $this->allClosedPulls;
	}

	/**
	 * Updates changelog with the changes since the last release
	 *
	 * @return  void
	 */
	protected function changelogUpdate($changes)
	{
		if (!empty($changes))
		{
			$this->taskChangelog()
				->changes($changes)
				->version($this->getConfig()->version)
				->run();
		}
	}

	/**
	 * Get Github
	 *
	 * @return  Github
	 */
	protected function getGithub()
	{
		$options = new Registry;
		$options->set('gh.token', (string) $this->getConfig()->github->token);

		return new Github($options);
	}

	/**
	 * Upload build Zip- or Packagefile to GitHub
	 *
	 * @param   string  $version      The release version
	 * @param   string  $githubToken  The github access token
	 * @param   string  $upload_url   The upload URL
	 *
	 * @return  void
	 */
	protected function uploadToGithub($version, $githubToken, $upload_url)
	{
		$deploy = explode(' ', $this->getConfig()->target);

		$zipfile = $this->getExtensionName() . '-' . $this->getConfig()->version . '.zip';

		if (in_array('package', $deploy))
		{
			$zipfile = 'pkg-' . $zipfile;
		}

		$zipfilepath =  JPATH_BASE . '/dist/' . $zipfile;

		$filesize = filesize($zipfilepath);

		$this->say('Uploading the Extension package to the Github release: ' . $version);

		$uploadUrl = str_replace("{?name,label}", "?access_token=" . $githubToken . "&name=" . $zipfile . "&size=" . $filesize, $upload_url);

		$request = curl_init($uploadUrl);

		curl_setopt($request, CURLOPT_POST, true);
		curl_setopt($request, CURLOPT_VERBOSE, true);

		curl_setopt($request, CURLOPT_HTTPHEADER, array(
			'Authorization: token ' . $githubToken,
		));

		curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-type: application/zip'));

		curl_setopt($request, CURLOPT_POSTFIELDS, file_get_contents($zipfilepath));

		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($request);

		curl_close($request);

		$this->say(print_r($result, true));
	}
}

