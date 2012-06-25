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
        $files = $this->filelib->getFileOperator()->findAll();

        foreach ($files as $file) {

            try {
                $profile = $this->filelib->getFileOperator()->getProfile($file->getProfile());
            } catch (\Exception $e) {
                continue;
            }

            $output->writeln("Processing file #{$file->getId()}");

            $resource = $file->getResource();
            $retrieved = $this->filelib->getStorage()->retrieve($resource);
            $resource->setHash(sha1_file($retrieved));
            $resource->setVersions($profile->getFileVersions($file));
            $this->filelib->getBackend()->updateResource($resource);

        }

        $output->writeln("All files processed");

    }
}
