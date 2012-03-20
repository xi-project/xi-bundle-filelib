<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xi\Bundle\FilelibBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dumps assets to the filesystem.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class RecreateCommand extends ContainerAwareCommand
{

    /**
     *
     * @var Xi\Filelib\FileLibrary
     */
    private $filelib;

    protected function configure()
    {
        $this
            ->setName('filelib:recreate')
            ->setDescription('Recreates all filelib assets')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->filelib = $this->getContainer()->get('filelib');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $files = $this->filelib->file()->findAll();

        
        foreach ($files as $file) {

            $output->writeln($file->getId());
            
            
            $po = $this->filelib->getFileOperator()->getProfile($file->getProfile());
            
            foreach ($po->getPlugins() as $plugin) {
                
                // If version plugin
                if($plugin instanceof \Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider) {

                    // and plugin is valid for the specific file's type
                    if ($plugin->providesFor($file)) {

                                                                        
                        try {
                            $this->filelib->getPublisher()->unpublishVersion($file, $plugin);
                        } catch (\Exception $e) {
                            $output->writeln($e->getMessage());
                        }
                        
                        try {
                            $plugin->deleteVersion($file);
                        } catch (\Exception $e) {
                            $output->writeln($e->getMessage());
                        }
                        
                        
                        try {
                            $tmp = $plugin->createVersion($file);
                            $this->filelib->getStorage()->storeVersion($file, $plugin->getIdentifier(), $tmp);

                        } catch (\Exception $e) {
                            $output->writeln($e->getMessage());
                        }
                        
                        
                        try {
                            $this->filelib->getPublisher()->publishVersion($file, $plugin);
                        } catch (\Exception $e) {
                            $output->writeln($e->getMessage());
                        }
                                                
                    }
                }
                
            }

            
        }
        
        
        return true;
        
        
    }

}
