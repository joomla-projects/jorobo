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
class CICommand extends Command
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
        $this->setName('ci')
            ->setDescription('Add automatic CI setup to project.')
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
        $io->title('JoRobo CI');
        $io->info('Adding CI for project');
        $this->io = $io;

        if (is_dir(JPATH_ROOT) && !is_file(JPATH_ROOT . '/composer.json')) {
            $io->error('The script is run from an unknown place and can\'t reliably find the root path of the repository. The discovered path was ' . JPATH_ROOT);

            return Command::FAILURE;
        }

        // Do we initialise with all features?
        $type = $io->choice('For which platform do you want to add a CI setup?', ['github', 'gitlab'], 'github');

        $this->recurse_copy(JOROBO_ROOT . '/assets/ci/' . $type, JPATH_ROOT);

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

    private function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst);
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    $this->copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
