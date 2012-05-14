<?php

namespace Xi\Bundle\FilelibBundle\Command;

use Xi\Filelib\Event\FileEvent;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Recreates all versions provided by plugins
 *
 * @author Pekkis
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
            
            $po = $this->filelib->getFileOperator()->getProfile($file->getProfile());
            
            $event = new FileEvent($file);
                                    
            foreach ($po->getPlugins() as $plugin) {
                
                // If version plugin
                if($plugin instanceof \Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider) {

                    try {
                        $plugin->onDelete($event);
                        $output->writeln("Deleted version '{$plugin->getIdentifier()}' of file #{$file->getId()}");
                    } catch (\Exception $e) {
                        $output->writeln($e->getMessage());
                    }
                    
                    try {
                        $plugin->afterUpload($event);
                        $output->writeln("Recreated version '{$plugin->getIdentifier()}' of file #{$file->getId()}");
                    } catch (\Exception $e) {
                        $output->writeln($e->getMessage());
                    }
                }
                
            }

            
        }
        
        
        return 0;
        
        
    }

}
