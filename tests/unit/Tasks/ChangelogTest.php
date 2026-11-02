<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tests\Tasks;

use Joomla\Jorobo\Tasks\Changelog;
use Joomla\Jorobo\Tasks\JTask;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class ChangelogTest extends TestCase
{
    use TestTasksTrait;

    private string $tmpDir;

    // ── lifecycle ────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        $this->initTestTasksTrait();
        $this->resetJConfig();

        $this->tmpDir = sys_get_temp_dir() . '/jorobo-changelog-' . uniqid('', true);
        mkdir($this->tmpDir . '/src', 0777, true);

        file_put_contents($this->tmpDir . '/jorobo.ini', implode("\n", [
            'extension = testextension',
            'version = 2.0.0',
            'source = src',
            'target = package',
            '[github]',
            'changelog_source = commits',
        ]));

        $dir = escapeshellarg($this->tmpDir);
        exec("git -C {$dir} init -q");
        exec("git -C {$dir} config user.email \"test@example.com\"");
        exec("git -C {$dir} config user.name \"Test User\"");
    }

    protected function tearDown(): void
    {
        // Git creates its object files read-only, which prevents deleting them on Windows
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tmpDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            chmod($file->getPathname(), 0666);
        }

        (new Filesystem())->remove($this->tmpDir);
        $this->resetJConfig();
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function resetJConfig(): void
    {
        $prop = (new \ReflectionClass(JTask::class))->getProperty('jConfig');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function gitCommit(string $message): void
    {
        $dir = escapeshellarg($this->tmpDir);
        $msg = escapeshellarg($message);
        exec("git -C {$dir} commit --allow-empty -m {$msg}");
    }

    private function gitTag(string $name): void
    {
        exec('git -C ' . escapeshellarg($this->tmpDir) . ' tag ' . escapeshellarg($name));
    }

    private function newChangelog(): Changelog
    {
        return new Changelog(['base' => $this->tmpDir]);
    }

    private function invokePrivate(object $obj, string $method, mixed ...$args): mixed
    {
        $m = (new \ReflectionClass($obj))->getMethod($method);
        $m->setAccessible(true);

        return $m->invoke($obj, ...$args);
    }

    private function loadXml(): \SimpleXMLElement
    {
        return simplexml_load_file($this->tmpDir . '/changelog.xml');
    }

    // ── detectType ───────────────────────────────────────────────────────────

    #[DataProvider('provideDetectTypeCases')]
    public function testDetectType(string $subject, string $expected): void
    {
        $this->assertSame($expected, $this->invokePrivate($this->newChangelog(), 'detectType', $subject));
    }

    public static function provideDetectTypeCases(): array
    {
        return [
            'fix colon'           => ['fix: correct offset',           'Bug Fix'],
            'fix paren'           => ['fix(api): handle null',          'Bug Fix'],
            'fix space'           => ['fix correct pagination',         'Bug Fix'],
            'bugfix colon'        => ['bugfix: resolve #42',            'Bug Fix'],
            'bug colon'           => ['bug: regression in list',        'Bug Fix'],
            'feat colon'          => ['feat: dark mode',                'Feature'],
            'feat paren'          => ['feat(ui): new button',           'Feature'],
            'feature colon'       => ['feature: import wizard',         'Feature'],
            'security colon'      => ['security: patch XSS',            'Security Fix'],
            'security paren'      => ['security(auth): fix bypass',     'Security Fix'],
            'lang colon'          => ['lang: update de-DE',             'Language fix'],
            'i18n colon'          => ['i18n: fix typo in en-GB',        'Language fix'],
            'translation colon'   => ['translation: add fr-FR',         'Language fix'],
            'merge message'       => ['Merge branch main',              'Note'],
            'generic message'     => ['Codestyle',                      'Note'],
            'update message'      => ['update readme',                  'Note'],
            'fix at word end'     => ['Fixing something',               'Note'],
            'mixed case feat'     => ['FEAT: uppercase prefix',         'Feature'],
        ];
    }

    // ── run() – no commits ───────────────────────────────────────────────────

    public function testRunSucceedsWithNoCommitsAndDoesNotCreateFile(): void
    {
        $result = $this->newChangelog()->run();

        $this->assertTrue($result->wasSuccessful());
        $this->assertFileDoesNotExist($this->tmpDir . '/changelog.xml');
    }

    public function testRunSucceedsWhenNoCommitsExistAfterLatestTag(): void
    {
        $this->gitCommit('initial commit');
        $this->gitTag('1.0.0');

        $result = $this->newChangelog()->run();

        $this->assertTrue($result->wasSuccessful());
        $this->assertFileDoesNotExist($this->tmpDir . '/changelog.xml');
    }

    // ── run() – creates changelog from scratch ───────────────────────────────

    public function testRunCreatesChangelogXmlWhenFileIsAbsent(): void
    {
        $this->gitCommit('feat: add something');

        $result = $this->newChangelog()->run();

        $this->assertTrue($result->wasSuccessful());
        $this->assertFileExists($this->tmpDir . '/changelog.xml');
    }

    public function testChangelogContainsCorrectMetadataFromConfig(): void
    {
        $this->gitCommit('feat: new thing');
        $this->newChangelog()->run();

        $entry = $this->loadXml()->changelog[0];

        $this->assertSame('testextension', (string) $entry->element);
        $this->assertSame('component', (string) $entry->type);
        $this->assertSame('2.0.0', (string) $entry->version);
        $this->assertSame(date('Y-m-d'), (string) $entry->date);
    }

    public function testEachCommitProducesOneItem(): void
    {
        $this->gitCommit('feat: first');
        $this->gitCommit('fix: second');
        $this->gitCommit('update readme');
        $this->newChangelog()->run();

        $this->assertCount(3, $this->loadXml()->changelog[0]->item);
    }

    public function testItemTypesMatchDetectedConventionalPrefixes(): void
    {
        $this->gitCommit('fix: a bug');
        $this->gitCommit('feat: a feature');
        $this->newChangelog()->run();

        $items = $this->loadXml()->changelog[0]->item;
        $types = array_map(fn($i) => (string) $i->type, iterator_to_array($items, false));

        $this->assertContains('Bug Fix', $types);
        $this->assertContains('Feature', $types);
    }

    public function testAllCommitsWithoutTagAreCollected(): void
    {
        $this->gitCommit('feat: first');
        $this->gitCommit('fix: second');
        $this->gitCommit('note: third');
        $this->newChangelog()->run();

        $notes = array_map(
            fn($i) => (string) $i->note,
            iterator_to_array($this->loadXml()->changelog[0]->item, false)
        );

        $this->assertContains('feat: first', $notes);
        $this->assertContains('fix: second', $notes);
        $this->assertContains('note: third', $notes);
    }

    // ── run() – scoped to commits since tag ──────────────────────────────────

    public function testOnlyCommitsSinceLatestTagAreIncluded(): void
    {
        $this->gitCommit('old commit before tag');
        $this->gitCommit('another old commit');
        $this->gitTag('1.0.0');

        $this->gitCommit('feat: new thing after tag');
        $this->gitCommit('fix: bug fix after tag');
        $this->newChangelog()->run();

        $notes = array_map(
            fn($i) => (string) $i->note,
            iterator_to_array($this->loadXml()->changelog[0]->item, false)
        );

        $this->assertCount(2, $notes);
        $this->assertContains('feat: new thing after tag', $notes);
        $this->assertContains('fix: bug fix after tag', $notes);
        $this->assertNotContains('old commit before tag', $notes);
        $this->assertNotContains('another old commit', $notes);
    }

    // ── run() – merge commits ────────────────────────────────────────────────

    public function testMergeCommitsAreIncluded(): void
    {
        $dir = escapeshellarg($this->tmpDir);

        exec("git -C {$dir} commit --allow-empty -m \"initial\"");
        exec("git -C {$dir} checkout -b feature-branch -q");
        exec("git -C {$dir} commit --allow-empty -m \"feat: branch feature\"");
        exec("git -C {$dir} checkout - -q");
        exec("git -C {$dir} merge feature-branch --no-ff -m \"Merge branch feature-branch\"");

        $this->newChangelog()->run();

        $notes = array_map(
            fn($i) => (string) $i->note,
            iterator_to_array($this->loadXml()->changelog[0]->item, false)
        );

        $this->assertContains('Merge branch feature-branch', $notes);
        $this->assertContains('feat: branch feature', $notes);
    }

    // ── run() – updates existing changelog ───────────────────────────────────

    public function testNewEntryIsPrependedToExistingChangelog(): void
    {
        file_put_contents($this->tmpDir . '/changelog.xml', implode('', [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<changelogs>',
            '<changelog>',
            '<element>testextension</element><type>component</type>',
            '<version>1.0.0</version><date>2020-01-01</date>',
            '</changelog>',
            '</changelogs>',
        ]));

        $this->gitCommit('fix: something new');
        $this->newChangelog()->run();

        $xml = $this->loadXml();
        $this->assertCount(2, $xml->changelog);
        $this->assertSame('2.0.0', (string) $xml->changelog[0]->version, 'Newest version must come first');
        $this->assertSame('1.0.0', (string) $xml->changelog[1]->version);
    }

    public function testExistingBlockForSameVersionIsReplaced(): void
    {
        file_put_contents($this->tmpDir . '/changelog.xml', implode('', [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<changelogs>',
            '<changelog>',
            '<element>testextension</element><type>component</type>',
            '<version>2.0.0</version><date>2020-01-01</date>',
            '<item><type>Note</type><note>old stale entry</note></item>',
            '</changelog>',
            '</changelogs>',
        ]));

        $this->gitCommit('feat: replacement feature');
        $this->newChangelog()->run();

        $xml = $this->loadXml();
        $this->assertCount(1, $xml->changelog, 'Version block must not be duplicated');

        $notes = array_map(fn($i) => (string) $i->note, iterator_to_array($xml->changelog[0]->item, false));
        $this->assertNotContains('old stale entry', $notes);
        $this->assertContains('feat: replacement feature', $notes);
    }

    public function testOlderVersionBlocksAreKeptWhenAddingNewEntry(): void
    {
        file_put_contents($this->tmpDir . '/changelog.xml', implode('', [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<changelogs>',
            '<changelog>',
            '<element>testextension</element><type>component</type>',
            '<version>1.9.0</version><date>2020-06-01</date>',
            '<item><type>Feature</type><note>old feature</note></item>',
            '</changelog>',
            '<changelog>',
            '<element>testextension</element><type>component</type>',
            '<version>1.0.0</version><date>2019-01-01</date>',
            '</changelog>',
            '</changelogs>',
        ]));

        $this->gitCommit('fix: latest fix');
        $this->newChangelog()->run();

        $xml = $this->loadXml();
        $this->assertCount(3, $xml->changelog);

        $versions = array_map(fn($c) => (string) $c->version, iterator_to_array($xml->changelog, false));
        $this->assertSame(['2.0.0', '1.9.0', '1.0.0'], $versions);
    }

    // ── XML correctness ──────────────────────────────────────────────────────

    public function testGeneratedFileIsWellFormedXml(): void
    {
        $this->gitCommit('feat: something');
        $this->newChangelog()->run();

        $dom = new \DOMDocument();
        $this->assertTrue(
            $dom->load($this->tmpDir . '/changelog.xml'),
            'changelog.xml must be parseable as well-formed XML'
        );
    }

    public function testSpecialCharactersInCommitMessagesAreEscaped(): void
    {
        $this->gitCommit('fix: handle <html> & "entities" correctly');
        $this->newChangelog()->run();

        // File must remain parseable
        $xml = $this->loadXml();
        $this->assertNotFalse($xml);

        $note = (string) $xml->changelog[0]->item[0]->note;
        $this->assertSame('fix: handle <html> & "entities" correctly', $note);
    }

    public function testRootElementIsChangelogs(): void
    {
        $this->gitCommit('fix: something');
        $this->newChangelog()->run();

        $xml = $this->loadXml();
        $this->assertSame('changelogs', $xml->getName());
    }

    public function testItemsHaveTypeAndNoteChildren(): void
    {
        $this->gitCommit('fix: a bug');
        $this->newChangelog()->run();

        $item = $this->loadXml()->changelog[0]->item[0];
        $this->assertNotEmpty((string) $item->type);
        $this->assertNotEmpty((string) $item->note);
    }
}
