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

use Xi\Filelib\Event\FileEvent;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Xi\Filelib\File\Command\FileCommand;
use ReflectionObject;

/**
 * Recreates all versions provided by plugins
 *
 * @author Pekkis
 */
class QueueProcessorCommand extends ContainerAwareCommand
{

    /**
     *
     * @var Xi\Filelib\File\DefaultFileOperator
     */
    private $fileOperator;

    /**
     *
     * @var Xi\Filelib\Folder\FolderOperator
     */
    private $folderOperator;
    
    /**
     *
     * @var Xi\Filelib\FileLibrary
     */
    private $filelib;

    protected function configure()
    {
        $this
            ->setName('filelib:queue_processor')
            ->setDescription('Processes queue')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->filelib = $this->getContainer()->get('filelib');
        
        $this->fileOperator = $this->filelib->getFileOperator();
        $this->folderOperator = $this->filelib->getFolderOperator();
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $queue = $this->fileOperator->getQueue();
        
        do {
        
            $message = $queue->dequeue();

            if ($message) {
                var_dump($message);

                $obj = unserialize($message['body']);

                if ($obj instanceof FileCommand) {

                    $refl = new ReflectionObject($obj);
                    $prop = $refl->getProperty('fileOperator');
                    $prop->setAccessible(true);

                    $prop->setValue($obj, $this->fileOperator);

                    $prop->setAccessible(false);

                    $ret = $obj->execute();

                    if ($ret instanceof FileCommand) {
                        $queue->enqueue($ret);
                    }

                }
            
            
            
            
            
            } else {
                echo "sleeping...";
                usleep(200000);
            }
        
        } while(true);
        
        
        
    }

}
