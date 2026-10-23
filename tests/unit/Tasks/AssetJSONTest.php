<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tests\Tasks;

use Joomla\Jorobo\Tasks\AssetJSON;
use Joomla\Jorobo\Tasks\JTask;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class AssetJSONTest extends TestCase
{
    use TestTasksTrait;

    private string $tmpDir;

    // ── lifecycle ────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        $this->initTestTasksTrait();
        $this->resetJConfig();

        $this->tmpDir = sys_get_temp_dir() . '/jorobo-assetjson-' . uniqid('', true);
        mkdir($this->tmpDir . '/src/media', 0777, true);

        file_put_contents($this->tmpDir . '/jorobo.ini', <<<'INI'
            extension = testextension
            version = 3.0.0
            source = src
            target = package
            INI);
    }

    protected function tearDown(): void
    {
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

    private function newTask(): AssetJSON
    {
        return new AssetJSON(['base' => $this->tmpDir]);
    }

    private function mediaDir(string $extension): string
    {
        return $this->tmpDir . '/src/media/' . $extension;
    }

    private function createMediaFolder(string $extension): void
    {
        mkdir($this->mediaDir($extension), 0777, true);
    }

    private function createJsFile(string $extension, string $filename): void
    {
        $dir = $this->mediaDir($extension) . '/js';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/' . $filename, '/* js */');
    }

    private function createCssFile(string $extension, string $filename): void
    {
        $dir = $this->mediaDir($extension) . '/css';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/' . $filename, '/* css */');
    }

    private function readAssetJson(string $extension): \stdClass
    {
        return json_decode(file_get_contents($this->mediaDir($extension) . '/joomla.asset.json'));
    }

    // ── run() – basic ────────────────────────────────────────────────────────

    public function testRunReturnsSuccess(): void
    {
        $this->createMediaFolder('com_test');

        $this->assertTrue($this->newTask()->run()->wasSuccessful());
    }

    public function testRunSucceedsWithNoMediaSubfolders(): void
    {
        $this->assertTrue($this->newTask()->run()->wasSuccessful());
    }

    // ── new joomla.asset.json ─────────────────────────────────────────────────

    public function testRunCreatesAssetJsonFile(): void
    {
        $this->createMediaFolder('com_test');
        $this->newTask()->run();

        $this->assertFileExists($this->mediaDir('com_test') . '/joomla.asset.json');
    }

    public function testNewAssetJsonHasCorrectSchema(): void
    {
        $this->createMediaFolder('com_test');
        $this->newTask()->run();

        $json = $this->readAssetJson('com_test');
        $this->assertSame('https://developer.joomla.org/schemas/json-schema/web_assets.json', $json->{'$schema'});
    }

    public function testNewAssetJsonContainsExtensionNameAndVersion(): void
    {
        $this->createMediaFolder('com_test');
        $this->newTask()->run();

        $json = $this->readAssetJson('com_test');
        $this->assertSame('com_test', $json->name);
        $this->assertSame('3.0.0', $json->version);
        $this->assertSame('GPL-2.0-or-later', $json->license);
    }

    // ── JS entries ───────────────────────────────────────────────────────────

    public function testJsFileAddedAsScriptEntry(): void
    {
        $this->createJsFile('com_test', 'app.js');
        $this->newTask()->run();

        $assets = $this->readAssetJson('com_test')->assets;
        $this->assertCount(1, $assets);
        $this->assertSame('script', $assets[0]->type);
        $this->assertSame('com_test.app', $assets[0]->name);
    }

    public function testJsEntryHasModuleAttribute(): void
    {
        $this->createJsFile('com_test', 'app.js');
        $this->newTask()->run();

        $entry = $this->readAssetJson('com_test')->assets[0];
        $this->assertSame('module', $entry->attributes->type);
        $this->assertIsArray($entry->dependencies);
    }

    // ── CSS entries ───────────────────────────────────────────────────────────

    public function testCssFileAddedAsStyleEntry(): void
    {
        $this->createCssFile('com_test', 'styles.css');
        $this->newTask()->run();

        $assets = $this->readAssetJson('com_test')->assets;
        $this->assertCount(1, $assets);
        $this->assertSame('style', $assets[0]->type);
        $this->assertSame('com_test.styles', $assets[0]->name);
    }

    // ── minified file handling ────────────────────────────────────────────────

    public function testMinifiedUriUsedWhenMinifiedJsExists(): void
    {
        $this->createJsFile('com_test', 'app.js');
        $this->createJsFile('com_test', 'app.min.js');
        $this->newTask()->run();

        $entry = $this->readAssetJson('com_test')->assets[0];
        $this->assertSame('com_test/app.min.js', $entry->uri);
    }

    public function testOnlyOneEntryCreatedWhenBothMinifiedAndNonMinifiedExist(): void
    {
        $this->createJsFile('com_test', 'app.js');
        $this->createJsFile('com_test', 'app.min.js');
        $this->newTask()->run();

        $this->assertCount(1, $this->readAssetJson('com_test')->assets);
    }

    public function testPlainUriUsedWhenNoMinifiedJsExists(): void
    {
        $this->createJsFile('com_test', 'app.js');
        $this->newTask()->run();

        $entry = $this->readAssetJson('com_test')->assets[0];
        $this->assertSame('com_test/app.js', $entry->uri);
    }

    // ── updating existing file ────────────────────────────────────────────────

    public function testRunUpdatesExistingAssetJson(): void
    {
        $this->createMediaFolder('com_test');
        $existing = json_encode([
            '$schema' => 'https://developer.joomla.org/schemas/json-schema/web_assets.json',
            'name'    => 'com_test',
            'version' => '1.0.0',
            'license' => 'GPL-2.0-or-later',
            'assets'  => [],
        ]);
        file_put_contents($this->mediaDir('com_test') . '/joomla.asset.json', $existing);

        $this->createJsFile('com_test', 'new.js');
        $this->newTask()->run();

        $assets = $this->readAssetJson('com_test')->assets;
        $this->assertCount(1, $assets);
        $this->assertSame('com_test.new', $assets[0]->name);
    }

    public function testRunDoesNotAddDuplicateEntries(): void
    {
        $this->createJsFile('com_test', 'app.js');
        $task = $this->newTask();
        $task->run();

        $this->resetJConfig();
        $this->newTask()->run();

        $this->assertCount(1, $this->readAssetJson('com_test')->assets);
    }

    // ── multiple media folders ────────────────────────────────────────────────

    public function testRunProcessesMultipleMediaFolders(): void
    {
        $this->createMediaFolder('com_alpha');
        $this->createMediaFolder('com_beta');
        $this->newTask()->run();

        $this->assertFileExists($this->mediaDir('com_alpha') . '/joomla.asset.json');
        $this->assertFileExists($this->mediaDir('com_beta') . '/joomla.asset.json');
    }
}
