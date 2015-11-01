<?php
class MetricsTest extends \Codeception\TestCase\Test
{
    use \JBuild\Tasks\Metrics\loadTasks;

    public function testCodestyleSubcommandUsesCodestyleClass()
    {
        $task = $this->taskMetrics('codestyle');

        $this->assertInstanceOf('\JBuild\Tasks\Metrics\CodeStyle', $task);
    }

    public function testCodestyleUsesJoomlaStandardAsDefault()
    {
        $options = $this->taskMetrics('codestyle')
            ->getOptions();

        $this->assertArrayHasKey('standard', $options);
        $this->assertEquals('joomla', $options['standard']);
    }

    public function testCodestyleCanUseOtherStandards()
    {
        $options = $this->taskMetrics('codestyle')
            ->standard('PSR-1')
            ->getOptions();

        $this->assertArrayHasKey('standard', $options);
        $this->assertEquals('psr-1', $options['standard']);
    }

    public function testIndividualOptionsCanBeAdded()
    {
        $command = $this->taskMetrics('codestyle')
            ->option('anyOption', 'anyValue')
            ->getCommand();

        $this->assertStringStartsWith('./vendor/bin/phpcs --anyOption=anyValue', $command);
    }
}
