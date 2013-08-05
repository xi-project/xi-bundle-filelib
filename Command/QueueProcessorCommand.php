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
use Xi\Filelib\Queue\QueueProcessor;
use Xi\Filelib\FilelibException;
use DateTime;

/**
 * Processes queue
 *
 * @author Pekkis
 */
class QueueProcessorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('xi_filelib:queue-processor')
            ->setDescription('Processes queue')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processor = new QueueProcessor(
            $this->getContainer()->get('xi_filelib'),
            $output
        );

        $hasMoreMessages = true;

        do {
            try {
                if ($hasMoreMessages = $processor->process()) {
                    $time = new DateTime();

                    $output->writeln(sprintf(
                        '%s OK: Processed something',
                        $time->format('Y-m-d H:i:s')
                    ));
                }
            } catch (FilelibException $e) {
                $time = new DateTime();

                $output->writeln(sprintf(
                    '%s FAIL: %s',
                    $time->format('Y-m-d H:i:s'),
                    $e->getMessage()
                ));
            }
        } while ($hasMoreMessages);
    }
}
