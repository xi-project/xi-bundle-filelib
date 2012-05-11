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

use Xi\Filelib\Queue\Processor\DefaultQueueProcessor;
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
     * @var DefaultQueueProcessor
     */
    private $processor;

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

        $this->processor = new DefaultQueueProcessor($this->getContainer()->get('filelib'));

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        do {

            $ret = $this->processor->process();
            
            if (!$ret) {
                usleep(200000);
                echo "Sleeping...\n";
            }

        } while(true);

    }

}
