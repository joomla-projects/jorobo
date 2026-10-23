<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Tests\Tasks;

use Joomla\Jorobo\Tasks\CopyrightHeader;
use Joomla\Jorobo\Tasks\JTask;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class CopyrightHeaderTest extends TestCase
{
    use TestTasksTrait;

    private string $tmpDir;

    protected function setUp(): void
    {
        $this->initTestTasksTrait();
        $this->resetJConfig();

        $this->tmpDir = sys_get_temp_dir() . '/jorobo-header-' . uniqid('', true);
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
            version = 1.0.0
            source = src
            target = package
            [header]
            files = php,js
            exclude = "{$exclude}"
            text = "
            /**
             * @copyright ##YEAR##
             */
            "
            INI);
    }

    private function resetJConfig(): void
    {
        $prop = (new \ReflectionClass(JTask::class))->getProperty('jConfig');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function newTask(): CopyrightHeader
    {
        return new CopyrightHeader(['base' => $this->tmpDir]);
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

    // ── replaceInText ────────────────────────────────────────────────────────

    public function testReplaceInTextSubstitutesYear(): void
    {
        $result = $this->invokeProtected($this->newTask(), 'replaceInText', 'Copyright ##YEAR##');

        $this->assertSame('Copyright ' . date('Y'), $result);
    }

    public function testReplaceInTextSubstitutesDate(): void
    {
        $result = $this->invokeProtected($this->newTask(), 'replaceInText', 'Built on ##DATE##');

        $this->assertSame('Built on ' . date('Y-m-d'), $result);
    }

    public function testReplaceInTextSubstitutesBothPlaceholders(): void
    {
        $result = $this->invokeProtected($this->newTask(), 'replaceInText', '##YEAR## / ##DATE##');

        $this->assertSame(date('Y') . ' / ' . date('Y-m-d'), $result);
    }

    public function testReplaceInTextLeavesTextWithoutPlaceholdersUnchanged(): void
    {
        $result = $this->invokeProtected($this->newTask(), 'replaceInText', 'No placeholders here');

        $this->assertSame('No placeholders here', $result);
    }

    // ── removeHeader ─────────────────────────────────────────────────────────

    public function testRemoveHeaderStripsExistingDocblock(): void
    {
        $file = $this->writeSourceFile('strip.php', implode(PHP_EOL, [
            '<?php',
            '/**',
            ' * @copyright Old',
            ' */',
            'class Foo {}',
        ]));

        $this->invokeProtected($this->newTask(), 'removeHeader', $file);

        $content = file_get_contents($file->getRealPath());
        $this->assertStringNotContainsString('/**', $content);
        $this->assertStringNotContainsString('@copyright Old', $content);
        $this->assertStringContainsString('<?php', $content);
        $this->assertStringContainsString('class Foo {}', $content);
    }

    public function testRemoveHeaderPreservesFileWithoutDocblock(): void
    {
        $original = implode(PHP_EOL, ['<?php', 'class Bar {}']);
        $file     = $this->writeSourceFile('nodoc.php', $original);

        $this->invokeProtected($this->newTask(), 'removeHeader', $file);

        $this->assertSame($original, file_get_contents($file->getRealPath()));
    }

    public function testRemoveHeaderHandlesEmptyLinesBetweenTagAndDocblock(): void
    {
        $file = $this->writeSourceFile('emptylines.php', implode(PHP_EOL, [
            '<?php',
            '',
            '/**',
            ' * @package Something',
            ' */',
            'echo "hi";',
        ]));

        $this->invokeProtected($this->newTask(), 'removeHeader', $file);

        $content = file_get_contents($file->getRealPath());
        $this->assertStringNotContainsString('@package', $content);
        $this->assertStringContainsString('echo "hi";', $content);
    }

    // ── addHeader ─────────────────────────────────────────────────────────────

    public function testAddHeaderInsertsTextAfterPhpOpenTag(): void
    {
        $file   = $this->writeSourceFile('add.php', implode(PHP_EOL, ['<?php', 'class Baz {}']));
        $header = "/**\n * @copyright Test\n */";

        $this->invokeProtected($this->newTask(), 'addHeader', $file, $header);

        $content = file_get_contents($file->getRealPath());
        $this->assertStringContainsString('<?php', $content);
        $this->assertStringContainsString('@copyright Test', $content);
        $this->assertStringContainsString('class Baz {}', $content);
        $this->assertLessThan(strpos($content, 'class Baz'), strpos($content, '@copyright'));
    }

    public function testAddHeaderPreservesCodeBelowPhpTag(): void
    {
        $file = $this->writeSourceFile('preserve.php', implode(PHP_EOL, [
            '<?php',
            'namespace MyNs;',
            'class Qux {}',
        ]));

        $this->invokeProtected($this->newTask(), 'addHeader', $file, "/**\n * Header\n */");

        $content = file_get_contents($file->getRealPath());
        $this->assertStringContainsString('namespace MyNs;', $content);
        $this->assertStringContainsString('class Qux {}', $content);
    }

    // ── run() ────────────────────────────────────────────────────────────────

    public function testRunReturnsSuccess(): void
    {
        $this->writeSourceFile('dummy.php', '<?php' . PHP_EOL . 'echo 1;');

        $this->assertTrue($this->newTask()->run()->wasSuccessful());
    }

    public function testRunAddsHeaderToPhpFile(): void
    {
        $this->writeSourceFile('target.php', implode(PHP_EOL, ['<?php', 'class Example {}']));

        $this->newTask()->run();

        $content = $this->readSourceFile('target.php');
        $this->assertStringContainsString('/**', $content);
        $this->assertStringContainsString('@copyright', $content);
    }

    public function testRunAddsHeaderToJsFile(): void
    {
        $this->writeSourceFile('target.js', 'const x = 1;');

        $this->newTask()->run();

        $this->assertStringContainsString('@copyright', $this->readSourceFile('target.js'));
    }

    public function testRunSkipsFilesWithUnconfiguredExtensions(): void
    {
        $original = 'This is a plain text file.';
        $this->writeSourceFile('readme.txt', $original);

        $this->newTask()->run();

        $this->assertSame($original, $this->readSourceFile('readme.txt'));
    }

    public function testRunSkipsFilesInExcludedDirectory(): void
    {
        $this->resetJConfig();
        $this->writeIni('vendor');

        $original = implode(PHP_EOL, ['<?php', 'class Vendored {}']);
        $this->writeSourceFile('vendor/lib.php', $original);

        $this->newTask()->run();

        $this->assertSame($original, $this->readSourceFile('vendor/lib.php'));
    }

    public function testRunReplacesYearPlaceholderInAddedHeader(): void
    {
        $this->writeSourceFile('dated.php', implode(PHP_EOL, ['<?php', 'class Dated {}']));

        $this->newTask()->run();

        $content = $this->readSourceFile('dated.php');
        $this->assertStringContainsString(date('Y'), $content);
        $this->assertStringNotContainsString('##YEAR##', $content);
    }

    public function testRunReplacesExistingHeaderWithConfiguredOne(): void
    {
        $this->writeSourceFile('replace.php', implode(PHP_EOL, [
            '<?php',
            '/**',
            ' * @copyright Old Corp',
            ' */',
            'class Replace {}',
        ]));

        $this->newTask()->run();

        $content = $this->readSourceFile('replace.php');
        $this->assertStringNotContainsString('Old Corp', $content);
        $this->assertStringContainsString('@copyright', $content);
        $this->assertStringContainsString('class Replace {}', $content);
    }
}
