<?php
/**
 * Created by PhpStorm.
 * User: pp
 * Date: 9/14/17
 * Time: 21:55
 */

namespace Unirgy\SimpleUp\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListModulesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('info:unirgy:list-modules')
            ->setDescription('List installed licenses');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateInstallation($output);

        $factory = ObjectManager::getInstance()->get(\Unirgy\SimpleUp\Model\ModuleFactory::class);
        $collection = $factory->create()->getCollection();
        $table = $this->getHelper('table');
        $table->setHeaders([
            'Module Name',
            'License Key',
            'Remote Version',
        ]);
        /** @var \Unirgy\SimpleUp\Model\Module $item */
        foreach ($collection as $item) {
            $table->addRow([
                $item->getData('module_name'),
                $item->getData('license_key'),
                $item->getData('remote_version'),
            ]);
        }
        $table->render($output);
        $output->writeln('Done');
    }
}