<?php
/**
 * Created by PhpStorm.
 * User: pp
 * Date: 9/14/17
 * Time: 21:55
 */

namespace Unirgy\SimpleUp\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Unirgy\SimpleUp\Helper\Exception;

class CheckUpdatesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('setup:unirgy:check-updates')
            ->setDescription('Check for module updates');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSimpleUpHelper()->checkUpdates();
        $output->writeln(__('Version updates have been fetched'));
    }
}