<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tasks;

use Robo\Result;

/**
 * Generate or update a Joomla-format changelog.xml from local git history
 *
 * Collects all commits and merges since the most recent git tag and prepends
 * a new <changelog> block to changelog.xml (created if absent) in the
 * repository root.
 *
 * @package  Joomla\Jorobo\Tasks
 *
 * @since    1.0
 */
class Changelog extends JTask
{
    /**
     * @return  Result
     *
     * @since   1.0
     */
    public function run(): Result
    {
        $this->printTaskInfo('Generating changelog');

        $changelogPath = $this->params['base'] . '/changelog.xml';
        $latestTag     = $this->getLatestTag();

        $this->printTaskInfo(
            $latestTag !== null
                ? 'Collecting commits since tag: ' . $latestTag
                : 'No tags found – collecting all commits'
        );

        $entries = $this->collectEntries($latestTag);

        if (empty($entries)) {
            $this->printTaskInfo('No commits found – changelog unchanged');

            return Result::success($this, 'No changes to add');
        }

        $this->updateChangelog($changelogPath, $entries);

        return Result::success($this, 'Changelog updated with ' . count($entries) . ' entries');
    }

    /**
     * Returns the most recent git tag, or null when none exist.
     *
     * @return  string|null
     *
     * @since   1.0
     */
    private function getLatestTag(): ?string
    {
        $base = escapeshellarg($this->params['base']);
        exec("git -C {$base} describe --tags --abbrev=0 2>/dev/null", $out, $code);

        return ($code === 0 && !empty($out)) ? trim($out[0]) : null;
    }

    /**
     * Collects all commits (merges and regular) since the given tag.
     *
     * @param   string|null  $sinceTag  Collect everything after this tag; null = entire history
     *
     * @return  list<array{type: string, note: string}>
     *
     * @since   1.0
     */
    private function collectEntries(?string $sinceTag): array
    {
        $base  = escapeshellarg($this->params['base']);
        $range = $sinceTag !== null ? escapeshellarg("{$sinceTag}..HEAD") : '';

        // Merges first (PR-level entries), then individual commits
        exec("git -C {$base} log {$range} --pretty=format:%s --merges 2>/dev/null", $merges);
        exec("git -C {$base} log {$range} --pretty=format:%s --no-merges 2>/dev/null", $commits);

        $entries = [];

        foreach (array_merge($merges, $commits) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $entries[] = ['type' => $this->detectType($line), 'note' => $line];
        }

        return $entries;
    }

    /**
     * Maps a commit subject to a Joomla changelog item type.
     *
     * Recognises conventional-commit prefixes and falls back to "Note".
     *
     * @param   string  $subject  Commit subject line
     *
     * @return  string  One of: Bug Fix, Feature, Security Fix, Language fix, Note
     *
     * @since   1.0
     */
    private function detectType(string $subject): string
    {
        $lower = strtolower($subject);

        if (preg_match('/^(fix|bugfix|bug)[\(:\s]/', $lower)) {
            return 'Bug Fix';
        }

        if (preg_match('/^feat(ure)?[\(:\s]/', $lower)) {
            return 'Feature';
        }

        if (preg_match('/^security[\(:\s]/', $lower)) {
            return 'Security Fix';
        }

        if (preg_match('/^(lang|i18n|translation)[\(:\s]/', $lower)) {
            return 'Language fix';
        }

        return 'Note';
    }

    /**
     * Writes (or prepends to) the Joomla-format changelog.xml file.
     *
     * A new <changelog> block for the current version is inserted as the first
     * child so the file stays in reverse-chronological order.  If a block for
     * this version already exists it is replaced.
     *
     * @param   string                                   $path     Absolute path to changelog.xml
     * @param   list<array{type: string, note: string}>  $entries  Entries for the new block
     *
     * @return  void
     *
     * @since   1.0
     */
    private function updateChangelog(string $path, array $entries): void
    {
        $version   = $this->getJConfig()->version;
        $extension = $this->getJConfig()->extension;
        $type      = $this->getJConfig()->type ?? 'component';
        $date      = date('Y-m-d');

        $dom               = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        if (file_exists($path)) {
            libxml_use_internal_errors(true);
            $dom->load($path);
            libxml_clear_errors();
            libxml_use_internal_errors(false);

            $root = $dom->documentElement;

            if ($root === null) {
                $root = $dom->createElement('changelogs');
                $dom->appendChild($root);
            } else {
                $this->removeVersionBlock($root, $version);
            }
        } else {
            $this->printTaskInfo('Creating changelog.xml');
            $root = $dom->createElement('changelogs');
            $dom->appendChild($root);
        }

        $changelog = $dom->createElement('changelog');
        $this->addTextNode($dom, $changelog, 'element', $extension);
        $this->addTextNode($dom, $changelog, 'type', $type);
        $this->addTextNode($dom, $changelog, 'version', $version);
        $this->addTextNode($dom, $changelog, 'date', $date);

        foreach ($entries as $entry) {
            $item = $dom->createElement('item');
            $this->addTextNode($dom, $item, 'type', $entry['type']);
            $this->addTextNode($dom, $item, 'note', $entry['note']);
            $changelog->appendChild($item);
        }

        // Prepend so that newest versions appear first
        $root->insertBefore($changelog, $root->firstChild);

        $dom->save($path);

        $this->printTaskInfo('changelog.xml written to ' . $path);
    }

    /**
     * Removes the <changelog> block for the given version if it already exists.
     *
     * @param   \DOMElement  $root     The <changelogs> root element
     * @param   string       $version  Version string to look for
     *
     * @return  void
     *
     * @since   1.0
     */
    private function removeVersionBlock(\DOMElement $root, string $version): void
    {
        $toRemove = null;

        foreach ($root->getElementsByTagName('changelog') as $node) {
            $versionNodes = $node->getElementsByTagName('version');

            if ($versionNodes->length > 0 && $versionNodes->item(0)->textContent === $version) {
                $toRemove = $node;
                break;
            }
        }

        if ($toRemove !== null) {
            $this->printTaskInfo("Replacing existing entry for version {$version}");
            $root->removeChild($toRemove);
        }
    }

    /**
     * Appends a child element containing a properly-escaped text node.
     *
     * @param   \DOMDocument  $dom     Owner document
     * @param   \DOMNode      $parent  Element to append to
     * @param   string        $tag     Tag name of the new child
     * @param   string        $text    Text content (special characters are escaped automatically)
     *
     * @return  void
     *
     * @since   1.0
     */
    private function addTextNode(\DOMDocument $dom, \DOMNode $parent, string $tag, string $text): void
    {
        $el = $dom->createElement($tag);
        $el->appendChild($dom->createTextNode($text));
        $parent->appendChild($el);
    }
}
