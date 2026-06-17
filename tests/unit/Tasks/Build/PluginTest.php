<?php

namespace JoRobo;

use Joomla\Jorobo\Tasks\Build\Tasks;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class PluginTest extends TestCase
{
    use TestTasksTrait;
    use Tasks;

    public function setUp(): void
    {
        $this->initTestTasksTrait();

        if (!is_file(JPATH_BASE . '/test-weblinks/jorobo.ini')) {
            $fs = new Filesystem();
            $fs->copy(JPATH_BASE . '/test-weblinks/jorobo.dist.ini', JPATH_BASE . '/test-weblinks/jorobo.ini');
        }
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(JPATH_BASE . '/test-weblinks/dist');
    }

    public function testBuildPlugin()
    {
        $result = $this->buildPlugin('system', 'weblinks', ['base' => JPATH_BASE . '/test-weblinks'])
            ->run();
        $this->assertTrue($result->wasSuccessful(), $result->getMessage());
        $this->assertDirectoryExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0/plugins/system/weblinks');
        $this->assertFileExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0/plugins/system/weblinks/weblinks.xml');
    }

    public function testBuildPlugins()
    {
        $result = $this->buildPlugin('system', 'weblinks', ['base' => JPATH_BASE . '/test-weblinks'])
            ->run();
        $this->assertTrue($result->wasSuccessful(), $result->getMessage());
        $result = $this->buildPlugin('finder', 'weblinks', ['base' => JPATH_BASE . '/test-weblinks'])
            ->run();
        $this->assertTrue($result->wasSuccessful(), $result->getMessage());
        $this->assertDirectoryExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0/plugins/system/weblinks');
        $this->assertFileExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0/plugins/system/weblinks/weblinks.xml');
        $this->assertDirectoryExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0/plugins/finder/weblinks');
        $this->assertFileExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0/plugins/finder/weblinks/weblinks.xml');
    }
}
