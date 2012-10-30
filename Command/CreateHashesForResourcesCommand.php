<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xi\Filelib\FileLibrary;

use Xi\Filelib\Migration\ResourceRefactorMigration;

/**
 * Calculates hashes for resources
 *
 * @author Pekkis
 */
class CreateHashesForResourcesCommand extends ContainerAwareCommand
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    protected function configure()
    {
        $this
            ->setName('filelib:create_hashes_for_resources')
            ->setDescription('Creates hashes for resources')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->filelib = $this->getContainer()->get('filelib');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migration = new ResourceRefactorMigration($this->filelib);

        $output->writeln("Starting migration...");

        $migration->execute();

        $output->writeln("Migration done!");

    }

}
