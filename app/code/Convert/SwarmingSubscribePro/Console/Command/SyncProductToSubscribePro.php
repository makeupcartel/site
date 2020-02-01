<?php
namespace Convert\SwarmingSubscribePro\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncProductToSubscribePro extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    protected $action;
    protected $appState;
    public function __construct(
        \Convert\SwarmingSubscribePro\Model\Product $action
    )
    {
        $this->action = $action;
        $this->appState = $action->getAppState();
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        //if (!$this->appState->getAreaCode()) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        //}
        $output->writeln("Starting ...");
        $this->action->syncProductToSubscribePro();
        $output->writeln("Sync product to subcribe pro done!");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("sync_product:subscribepro");
        $this->setDescription("Sync product to Subscribe Pro");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}