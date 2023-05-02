<?php

use PHPUnit\Framework\TestCase;

final class RoboFileTest extends TestCase
{
    public function testBuild(): void
    {
        $base = dirname(__DIR__);
        $this->assertDirectoryNotExists($base . '/test-weblinks/dist');
        exec($base . '/vendor/bin/robo build --base=' . $base . '/test-weblinks', $output, $result);
        $this->assertEquals(0, $result, 'build command should pass');
        $this->assertDirectoryExists($base . '/test-weblinks/dist');
    }
}
