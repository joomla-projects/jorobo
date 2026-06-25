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
 * Console command to add rector setup for your repo
 *
 * @since  1.0
 */
class RectorCommand extends Command
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
        $this->setName('rector')
            ->setDescription('Add rector with Joomla rules and an initial rector.php to your setup.')
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
        $io->title('JoRobo Rector');
        $io->info('Adding Rector to the setup including initial rector.php and Joomla-specific rules.');
        $this->io = $io;

        exec('cd ' . JPATH_ROOT . ' && composer require --dev rector/rector joomla-projects/jrector joomla-projects/typehints');

        if (!is_file(JPATH_ROOT . '/rector.php')) {
            $this->io->writeln('Copying default rector.php to project root.');
            $this->copy(JPATH_ROOT . '/vendor/joomla-projects/jrector/assets/rector.php', JPATH_ROOT . '/rector.php');
        } else {
            $this->io->writeln('rector.php already exists.');
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
