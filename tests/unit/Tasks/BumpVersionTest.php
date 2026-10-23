<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tests\Tasks;

use Joomla\Jorobo\Tasks\BumpVersion;
use Joomla\Jorobo\Tasks\JTask;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class BumpVersionTest extends TestCase
{
    use TestTasksTrait;

    private string $tmpDir;

    // ── lifecycle ────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        $this->initTestTasksTrait();
        $this->resetJConfig();

        $this->tmpDir = sys_get_temp_dir() . '/jorobo-bump-' . uniqid('', true);
        mkdir($this->tmpDir . '/src', 0777, true);

        $this->writeIni();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tmpDir);
        $this->resetJConfig();
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function writeIni(string $exclude = ''): void
    {
        file_put_contents($this->tmpDir . '/jorobo.ini', <<<INI
            extension = testextension
            version = 2.0.0
            source = src
            target = package
            [header]
            files = php,xml
            exclude = "{$exclude}"
            text = "/* header */"
            INI);
    }

    private function resetJConfig(): void
    {
        $prop = (new \ReflectionClass(JTask::class))->getProperty('jConfig');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function newTask(): BumpVersion
    {
        return new BumpVersion(['base' => $this->tmpDir]);
    }

    private function invokeProtected(object $obj, string $method, mixed ...$args): mixed
    {
        $m = (new \ReflectionClass($obj))->getMethod($method);
        $m->setAccessible(true);

        return $m->invoke($obj, ...$args);
    }

    private function writeSourceFile(string $relativePath, string $content): \SplFileInfo
    {
        $fullPath = $this->tmpDir . '/src/' . $relativePath;
        $dir      = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($fullPath, $content);

        return new \SplFileInfo($fullPath);
    }

    private function readSourceFile(string $relativePath): string
    {
        return file_get_contents($this->tmpDir . '/src/' . $relativePath);
    }

    private function extensionXml(string $version): string
    {
        return <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <extension type="component" version="5.0" method="upgrade">
                <version>{$version}</version>
            </extension>
            XML;
    }

    // ── updatePlain ───────────────────────────────────────────────────────────

    public function testUpdatePlainReplacesDeployVersionToken(): void
    {
        $file = $this->writeSourceFile('class.php', '<?php' . PHP_EOL . 'const VERSION = \'__DEPLOY_VERSION__\';');

        $this->invokeProtected($this->newTask(), 'updatePlain', $file);

        $this->assertStringContainsString('2.0.0', $this->readSourceFile('class.php'));
        $this->assertStringNotContainsString('__DEPLOY_VERSION__', $this->readSourceFile('class.php'));
    }

    public function testUpdatePlainReturnsTrueWhenTokenFound(): void
    {
        $file   = $this->writeSourceFile('has-token.php', '<?php' . PHP_EOL . '// @version __DEPLOY_VERSION__');
        $result = $this->invokeProtected($this->newTask(), 'updatePlain', $file);

        $this->assertTrue($result);
    }

    public function testUpdatePlainReturnsFalseWhenNoToken(): void
    {
        $file   = $this->writeSourceFile('no-token.php', '<?php' . PHP_EOL . '// nothing special');
        $result = $this->invokeProtected($this->newTask(), 'updatePlain', $file);

        $this->assertFalse($result);
    }

    public function testUpdatePlainDoesNotModifyFileWithoutToken(): void
    {
        $original = '<?php' . PHP_EOL . 'class Foo {}';
        $file     = $this->writeSourceFile('unchanged.php', $original);

        $this->invokeProtected($this->newTask(), 'updatePlain', $file);

        $this->assertSame($original, $this->readSourceFile('unchanged.php'));
    }

    // ── updateXML ─────────────────────────────────────────────────────────────

    public function testUpdateXmlUpdatesVersionInExtensionElement(): void
    {
        $file = $this->writeSourceFile('manifest.xml', $this->extensionXml('1.0.0'));

        $this->invokeProtected($this->newTask(), 'updateXML', $file);

        $xml = simplexml_load_file($file->getRealPath());
        $this->assertSame('2.0.0', (string) $xml->version);
    }

    public function testUpdateXmlReturnsTrueWhenVersionChanged(): void
    {
        $file   = $this->writeSourceFile('old.xml', $this->extensionXml('1.0.0'));
        $result = $this->invokeProtected($this->newTask(), 'updateXML', $file);

        $this->assertTrue($result);
    }

    public function testUpdateXmlReturnsFalseWhenVersionAlreadyCurrent(): void
    {
        $file   = $this->writeSourceFile('current.xml', $this->extensionXml('2.0.0'));
        $result = $this->invokeProtected($this->newTask(), 'updateXML', $file);

        $this->assertFalse($result);
    }

    public function testUpdateXmlReturnsFalseForNonExtensionRoot(): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<config><version>1.0.0</version></config>';
        $file = $this->writeSourceFile('config.xml', $xml);

        $result = $this->invokeProtected($this->newTask(), 'updateXML', $file);

        $this->assertFalse($result);
    }

    // ── run() ────────────────────────────────────────────────────────────────

    public function testRunReturnsSuccess(): void
    {
        $this->writeSourceFile('dummy.php', '<?php' . PHP_EOL . 'echo 1;');

        $this->assertTrue($this->newTask()->run()->wasSuccessful());
    }

    public function testRunReplacesTokenInPhpFile(): void
    {
        $this->writeSourceFile('versioned.php', '<?php' . PHP_EOL . '$v = \'__DEPLOY_VERSION__\';');

        $this->newTask()->run();

        $content = $this->readSourceFile('versioned.php');
        $this->assertStringContainsString('2.0.0', $content);
        $this->assertStringNotContainsString('__DEPLOY_VERSION__', $content);
    }

    public function testRunUpdatesXmlManifest(): void
    {
        $this->writeSourceFile('manifest.xml', $this->extensionXml('1.0.0'));

        $this->newTask()->run();

        $xml = simplexml_load_file($this->tmpDir . '/src/manifest.xml');
        $this->assertSame('2.0.0', (string) $xml->version);
    }

    public function testRunSkipsFilesWithoutToken(): void
    {
        $original = '<?php' . PHP_EOL . 'class Untouched {}';
        $this->writeSourceFile('clean.php', $original);

        $this->newTask()->run();

        $this->assertSame($original, $this->readSourceFile('clean.php'));
    }

    public function testRunSkipsFilesInExcludedDirectory(): void
    {
        $this->resetJConfig();
        $this->writeIni('vendor');

        $original = '<?php' . PHP_EOL . 'const V = \'__DEPLOY_VERSION__\';';
        $this->writeSourceFile('vendor/lib.php', $original);

        $this->newTask()->run();

        $this->assertSame($original, $this->readSourceFile('vendor/lib.php'));
    }

    public function testRunReturnsChangedFileCountInMessage(): void
    {
        $this->writeSourceFile('a.php', '<?php' . PHP_EOL . '// __DEPLOY_VERSION__');
        $this->writeSourceFile('b.php', '<?php' . PHP_EOL . '// __DEPLOY_VERSION__');

        $result = $this->newTask()->run();

        $this->assertStringContainsString('2', $result->getMessage());
    }
}
