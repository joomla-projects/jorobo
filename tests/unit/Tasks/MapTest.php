<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tests\Tasks;

use Joomla\Jorobo\Tasks\JTask;
use Joomla\Jorobo\Tasks\Map;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class MapTest extends TestCase
{
    use TestTasksTrait;

    private string $tmpDir;
    private string $joomlaDir;

    // ── lifecycle ────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        $this->initTestTasksTrait();
        $this->resetJConfig();

        $base            = sys_get_temp_dir() . '/jorobo-map-' . uniqid('', true);
        $this->tmpDir    = $base . '/repo';
        $this->joomlaDir = $base . '/joomla';

        mkdir($this->tmpDir . '/src', 0777, true);
        $this->createJoomlaTargetDirs();

        file_put_contents($this->tmpDir . '/jorobo.ini', <<<'INI'
            extension = testextension
            version = 1.0.0
            source = src
            target = package
            INI);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(dirname($this->tmpDir));
        $this->resetJConfig();
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function resetJConfig(): void
    {
        $prop = (new \ReflectionClass(JTask::class))->getProperty('jConfig');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function newTask(): Map
    {
        return new Map($this->joomlaDir, ['base' => $this->tmpDir]);
    }

    private function createJoomlaTargetDirs(): void
    {
        foreach ([
            '/administrator/components',
            '/administrator/modules',
            '/administrator/language/en-GB',
            '/components',
            '/modules',
            '/plugins/system',
            '/media',
            '/libraries',
        ] as $dir) {
            mkdir($this->joomlaDir . $dir, 0777, true);
        }
    }

    private function mkSourceDir(string $relative): void
    {
        mkdir($this->tmpDir . '/src/' . $relative, 0777, true);
    }

    private function isLinked(string $path): bool
    {
        return file_exists($path) || is_link($path);
    }

    // ── error handling ────────────────────────────────────────────────────────

    public function testRunReturnsErrorWhenSourceFolderDoesNotExist(): void
    {
        // Remove the src/ directory so checkFolders() fails
        rmdir($this->tmpDir . '/src');

        $result = $this->newTask()->run();

        $this->assertFalse($result->wasSuccessful());
    }

    // ── success ───────────────────────────────────────────────────────────────

    public function testRunReturnsSuccessWithValidDirectories(): void
    {
        $result = $this->newTask()->run();

        $this->assertTrue($result->wasSuccessful());
    }

    // ── component mapping ─────────────────────────────────────────────────────

    public function testRunSymlinksFrontendComponent(): void
    {
        $this->mkSourceDir('components/com_test');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/components/com_test'));
    }

    public function testRunSymlinksAdminComponent(): void
    {
        $this->mkSourceDir('administrator/components/com_test');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/administrator/components/com_test'));
    }

    public function testRunDoesNotSymlinkNonComponentFolder(): void
    {
        $this->mkSourceDir('components/not_a_component');

        $this->newTask()->run();

        $this->assertFalse($this->isLinked($this->joomlaDir . '/components/not_a_component'));
    }

    // ── module mapping ────────────────────────────────────────────────────────

    public function testRunSymlinksFrontendModule(): void
    {
        $this->mkSourceDir('modules/mod_test');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/modules/mod_test'));
    }

    public function testRunSymlinksAdminModule(): void
    {
        $this->mkSourceDir('administrator/modules/mod_test');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/administrator/modules/mod_test'));
    }

    // ── plugin mapping ────────────────────────────────────────────────────────

    public function testRunSymlinksPlugin(): void
    {
        $this->mkSourceDir('plugins/system/plg_test');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/plugins/system/plg_test'));
    }

    // ── media mapping ─────────────────────────────────────────────────────────

    public function testRunSymlinksMediaDirectory(): void
    {
        $this->mkSourceDir('media/com_test');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/media/com_test'));
    }

    // ── library mapping ───────────────────────────────────────────────────────

    public function testRunSymlinksLibrary(): void
    {
        $this->mkSourceDir('libraries/mylib');

        $this->newTask()->run();

        $this->assertTrue($this->isLinked($this->joomlaDir . '/libraries/mylib'));
    }

    // ── re-mapping ────────────────────────────────────────────────────────────

    public function testRunOverwritesExistingSymlink(): void
    {
        $this->mkSourceDir('modules/mod_test');
        // Create a stale target first
        mkdir($this->joomlaDir . '/modules/mod_test', 0777, true);

        $result = $this->newTask()->run();

        $this->assertTrue($result->wasSuccessful());
        $this->assertTrue($this->isLinked($this->joomlaDir . '/modules/mod_test'));
    }
}
