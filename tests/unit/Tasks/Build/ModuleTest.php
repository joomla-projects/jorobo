<?php

namespace JoRobo;

use Joomla\Jorobo\Tasks\Build\Tasks;
use PHPUnit\Framework\TestCase;
use Robo\Traits\TestTasksTrait;
use Symfony\Component\Filesystem\Filesystem;

class ModuleTest extends TestCase
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

    public function testBuildModule()
    {
        $result = $this->buildModule('weblinks', ['base' => JPATH_BASE . '/test-weblinks'])
            ->run();
        $this->assertTrue($result->wasSuccessful(), $result->getMessage());
        $this->assertDirectoryExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0-dev/modules/mod_weblinks');
        $this->assertFileExists(JPATH_BASE . '/test-weblinks/dist/weblinks-5.0.0-dev/modules/mod_weblinks/mod_weblinks.xml');
    }
}
