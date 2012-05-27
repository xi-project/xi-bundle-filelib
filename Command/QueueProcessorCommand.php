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
use Xi\Filelib\Queue\Processor\DefaultQueueProcessor;

/**
 * Recreates all versions provided by plugins
 *
 * @author Pekkis
 */
class QueueProcessorCommand extends ContainerAwareCommand
{
    /**
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
        } while (true);
    }
}
