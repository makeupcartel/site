<?php
/**
 * Created by PhpStorm.
 * User: pp
 * Date: 9/14/17
 * Time: 21:55
 */

namespace Unirgy\SimpleUp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Unirgy\SimpleLicense\Helper\ProtectedCode;

class AddLicenseCommand extends MassUpgradeCommand
{
    const INPUT_KEY_INSTALL = 'install';

    const INPUT_KEY_LICENSE = 'license';
    /**
     * @var \Unirgy\SimpleLicense\Helper\Data
     */
    protected $licHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Unirgy\SimpleUp\Helper\Data $helper,
        \Unirgy\SimpleLicense\Helper\Data $licHelper
    )
    {
        parent::__construct($config, $helper);

        $this->licHelper = $licHelper;
    }

    protected function configure()
    {
        parent::configure();
        $this->setName('setup:unirgy:add-license')
            ->setDescription('Add Unirgy license');

        $definition = $this->getDefinition();
        $arguments = array_filter($definition->getArguments(), function (InputArgument $argument) {
            return $argument->getName() !== self::INPUT_KEY_MODULES; // we don't need to list modules
        });
        $definition->setArguments($arguments);

        $this->addArgument(
            self::INPUT_KEY_LICENSE,
            InputArgument::REQUIRED,
            'License code to add'
        )->addOption(
            self::INPUT_KEY_INSTALL,
            'i',
            InputOption::VALUE_NONE,
            'Install all license modules?');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @throws \Unirgy\SimpleLicense\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addLicense($input, $output);
        $modules = $this->fetchModules($input, $output);
        $this->enableModules($input, $output, $modules);
        $this->runCompilation($input, $output);
        $output->writeln('Done');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws \Unirgy\SimpleLicense\Exception
     */
    protected function addLicense(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument(self::INPUT_KEY_LICENSE);
        $install = $input->getOption(self::INPUT_KEY_INSTALL);
        ProtectedCode::retrieveLicense($key, $install);
        $output->writeln(sprintf('<info>The license has been added: %s</info>', $key));

        return true;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function fetchModules(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument(self::INPUT_KEY_LICENSE);
        $licenseModel = $this->licHelper->getLicenseModel($key);

        return $licenseModel->getModules();
    }
}