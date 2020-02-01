<?php
/**
 * Created by PhpStorm.
 * User: pp
 * Date: 9/14/17
 * Time: 21:55
 */

namespace Unirgy\SimpleUp\Console\Command;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\AbstractModuleCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unirgy\SimpleUp\Model\Module;
use Unirgy\SimpleUp\Model\ModuleFactory;
use Unirgy\SimpleUp\Model\ResourceModel\Module\Collection;

class MassUpgradeCommand extends BaseCommand
{
    const INPUT_KEY_MODULES = 'modules';

    const MODULE_ENABLE_CMD = 'module:enable';

    const SETUP_UPGRADE_CMD = 'setup:upgrade';
    const INPUT_KEY_DI_COMPILE = 'with-di-compile';
    const SETUP_COMPILE_CMD = 'setup:di:compile';
    const INPUT_KEY_ENABLE = 'enable';

    protected function configure()
    {
        $this->setName('setup:unirgy:update-reinstall')
            ->setDescription('Update - reinstall unirgy modules');
        $this->addArgument(
            self::INPUT_KEY_MODULES,
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Name of the module'
        )->addOption(
            self::INPUT_KEY_DI_COMPILE,
            null,
            InputOption::VALUE_NONE,
            'Run ' . self::SETUP_COMPILE_CMD . ' after install'
        )->addOption(
            self::INPUT_KEY_ENABLE,
            'e',
            InputOption::VALUE_NONE,
            'Run ' . self::MODULE_ENABLE_CMD . ' and ' . self::SETUP_UPGRADE_CMD . ' after install'
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = $input->getArgument(self::INPUT_KEY_MODULES);
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln(implode(PHP_EOL, $messages));

            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        try {
            $this->installModules($output, $modules);

            $this->enableModules($input, $output, $modules);

            $this->runCompilation($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
        $output->writeln('Done');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Validate list of modules and return error messages
     *
     * @param string[] $modules
     * @return string[]
     */
    protected function validate(array $modules)
    {
        $messages = [];
        if (empty($modules)) {
            $messages[] = '<error>No modules specified. Specify a space-separated list of modules' .
                ' or use the --all option</error>';
        }

        return $messages;
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    protected function shouldRunCompile(InputInterface $input, OutputInterface $output)
    {
        $runCompile = $input->getOption(self::INPUT_KEY_DI_COMPILE);
        if ($runCompile && $input->getOption(self::INPUT_KEY_ENABLE) === false) {
            // if with-di-compile is passed, but not enable modules, ask if we should still compile
            $question = $this->getHelper('question');
            $questionText = 'Modules are not enabled, should ' . self::SETUP_COMPILE_CMD . ' be ran anyways?';
            $runCompile = $question->ask($input, $output, new ConfirmationQuestion($questionText, false));
        }

        return $runCompile;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $modules
     * @throws \Exception
     */
    protected function enableModules(InputInterface $input, OutputInterface $output, $modules)
    {
        if ($input->getOption(self::INPUT_KEY_ENABLE)) {
            $app = $this->getApplication();
            $moduleEnableCommand = $app->find(self::MODULE_ENABLE_CMD);
            $setupUpgradeCommand = $app->find(self::SETUP_UPGRADE_CMD);

            $output->writeln('<info>Running module:enable</info>');
            $modEnableArgs = [
                'command' => self::MODULE_ENABLE_CMD,
                AbstractModuleCommand::INPUT_KEY_MODULES => $modules,
            ];

            $modEnableInput = new ArrayInput($modEnableArgs);
            $moduleEnableCommand->run($modEnableInput, $output);

            $output->writeln('<info>Running setup:upgrade</info>');
            $setupUpgradeArgs = [
                'command' => self::SETUP_UPGRADE_CMD,
            ];

            $setupUpgradeInput = new ArrayInput($setupUpgradeArgs);
            $setupUpgradeCommand->run($setupUpgradeInput, $output);
        }
    }

    /**
     * @param OutputInterface $output
     * @param $modules
     * @throws \Unirgy\SimpleUp\Helper\Exception
     */
    protected function installModules(OutputInterface $output, $modules)
    {
        $factory = ObjectManager::getInstance()->get(\Unirgy\SimpleUp\Model\ModuleFactory::class);
        /** @var Collection $collection */
        $collection = $factory->create()->getCollection();
        $collection->addFieldToFilter('module_name', ['in' => $modules]);
        $helper = $this->getSimpleUpHelper();

        $moduleIds = [];
        /** @var Module $item */
        foreach ($collection as $item) {
            $output->writeln('<info>Installing: ' . $item->getModuleName() . ' ...</info>');
            $uri = $item->getDownloadUri();
            $output->writeln('<info>Downloading: ' . $uri . ' ...</info>');
            $filePath = $helper->download($uri);
            $helper->install($uri, $filePath);
            $output->writeln('<info>' . $item->getModuleName() . ' installed.</info>');
        }

        $output->writeln('<info>Modules have been upgraded</info>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function runCompilation(InputInterface $input, OutputInterface $output)
    {
        $runCompile = $this->shouldRunCompile($input, $output);
        if ($runCompile) {
            $app = $this->getApplication();
            $output->writeln("\n\n");
            $output->writeln('<comment> Running DI compile, it may take a while </comment>');
            $app->find(self::SETUP_COMPILE_CMD)->run(new ArrayInput([
                'command' => self::SETUP_COMPILE_CMD,
            ]), $output);
        }
    }
}