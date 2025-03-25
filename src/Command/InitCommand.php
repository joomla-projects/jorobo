<?php

/**
 * @package    JoRobo
 *
 * @copyright  Copyright (C) 2005 - 2025 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Jorobo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to initialise a repo for JoRobo
 *
 * @since  1.0
 */
class InitCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function configure(): void
    {
        $this->setName('init')
            ->setDescription('Initialise a folder as a Joomla extension repository.')
        ;
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   1.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('JoRobo Init');
        $io->info('Initialising folder for a Joomla extension');
        $this->io = $io;

        if (is_dir(JPATH_ROOT) && !is_file(JPATH_ROOT . '/composer.json')) {
            $io->error('The script is run from an unknown place and can\'t reliably find the root path of the repository. The discovered path was ' . JPATH_ROOT);

            return Command::FAILURE;
        }

        // Do we initialise with all features?
        $all = $io->ask('Do you want to initialise with all options? (JoRobo, gitignore, codestyle, phpstan, tests)', 'yes');

        if (!is_dir(JPATH_ROOT . '/src')) {
            $io->writeln('Creating /src folder');
            mkdir(JPATH_ROOT . '/src');
        }

        $io->writeln('Setting up JoRobo');
        $this->copy(JOROBO_ROOT . '/assets/init/RoboFile.php', JPATH_ROOT . '/RoboFile.php');
        $this->copy(JOROBO_ROOT . '/assets/init/jorobo.dist.ini', JPATH_ROOT . '/jorobo.dist.ini');

        if ($all == 'yes' || $io->ask('Want to add codestyle checks?', 'yes') === 'yes') {
            $io->writeln('Setting up codestyle checks');
            $this->copy(JOROBO_ROOT . '/assets/init/.editorconfig', JPATH_ROOT . '/.editorconfig');
            $this->copy(JOROBO_ROOT . '/assets/init/.php-cs-fixer.dist.php', JPATH_ROOT . '/.php-cs-dist-fixer.php');
            $this->copy(JOROBO_ROOT . '/assets/init/ruleset.xml', JPATH_ROOT . '/ruleset.xml');

            exec('cd ' . JPATH_ROOT . ' && composer require --dev squizlabs/php_codesniffer friendsofphp/php-cs-fixer');
        }

        if ($all == 'yes' || $io->ask('Want to add gitignore file?', 'yes') === 'yes') {
            $io->writeln('Setting up gitignore');
            $this->copy(JOROBO_ROOT . '/assets/init/.gitignore', JPATH_ROOT . '/.gitignore');
        }

        if ($all == 'yes' || $io->ask('Want to add phpstan static code analysis?', 'yes') === 'yes') {
            $io->writeln('Setting up phpstan');
            $this->copy(JOROBO_ROOT . '/assets/init/phpstan.neon', JPATH_ROOT . '/phpstan.neon');

            exec('cd ' . JPATH_ROOT . ' && composer require --dev phpstan/phpstan phpstan/phpstan-deprecation-rules');
        }

        if ($all == 'yes' || $io->ask('Want to add phpunit tests?', 'yes') === 'yes') {
            $io->writeln('Setting up phpunit');
            $this->copy(JOROBO_ROOT . '/assets/init/phpunit.xml.dist', JPATH_ROOT . '/phpunit.xml.dist');

            exec('cd ' . JPATH_ROOT . ' && composer require --dev phpunit/phpunit');
        }

        return Command::SUCCESS;
    }

    /**
     * Helper function to cleanup paths before copying files
     *
     * @param   string  $src Source file to copy from
     * @param   string  $dst Destination file to copy to
     *
     * @return bool
     */
    private function copy($src, $dst)
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $src = strtr($src, '/', '\\');
            $dst = strtr($dst, '/', '\\');
        }

        if (is_file($dst)) {
            $this->io->note('File already exists: ' . $dst);

            return false;
        }

        return copy($src, $dst);
    }
}
