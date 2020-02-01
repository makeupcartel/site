<?php

namespace Convert\Core\Console\Command;

use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManagerFactory;
use Symfony\Component\Console\Input\InputOption;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class SubscriberImportCommand
 * @package Convert\Core\Console\Command
 */
class SubscriberImportCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     *
     */
    const CSV_EMAIL_POSITION = 0;

    /**
     * SubscriberImportCommand constructor.
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param State $state
     * @param DirectoryList $_directoryList
     * @param StoreManagerInterface $_storeManager
     * @param SubscriberFactory $_subscriberFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        DirectoryList $_directoryList,
        StoreManagerInterface $_storeManager,
        SubscriberFactory $_subscriberFactory,
        CustomerFactory $customerFactory
    ){
        $this->_storeManager = $_storeManager;
        $this->_directoryList = $_directoryList;
        $this->_subscriberFactory = $_subscriberFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('newsletter:subscriber:import')
            ->setDescription('Imports newsletter subscribers');

        $this->setDefinition([new InputOption(
            'csv-path',
            null,
            InputOption::VALUE_REQUIRED,
            'Path to csv file within Magento.',
            null
        ),new InputOption(
            'store_id',
            null,
            InputOption::VALUE_REQUIRED,
            'Store ID.',
            null
        ),new InputOption(
            'website_id',
            null,
            InputOption::VALUE_REQUIRED,
            'Website ID.',
            null
        )]);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting</info>');

        $magentoRoot = $this->_directoryList->getPath(DirectoryList::ROOT);
        $csvPath = $magentoRoot . '/' . $input->getOption('csv-path');
        $store = $input->getOption('store_id');
        $website = $input->getOption('website_id');
        if(!file_exists($csvPath)) {
            $output->writeln("<error>CVS file not found in {$csvPath}</error>");
            return false;
        }

        $csv = array_map('str_getcsv', file($csvPath));
        $count = count($csv);

        foreach($csv as $key => $row) {
            $email = $row[self::CSV_EMAIL_POSITION];

            if(!$this->isValidEmail($email)) {
                continue 1;
            }
            $this->_storeManager->setCurrentStore($store);

            $subscriber = $this->_subscriberFactory->create();
            $subscriber->loadByEmail($email);
            if(!$subscriber->getId()){
                $customer = $this->_customerFactory->create()->setWebsiteId($website)->loadByEmail($email);
                $subscriber->setStatus(Subscriber::STATUS_SUBSCRIBED)
                    ->setStoreId($store)
                    ->setCustomerId($customer->getId())
                    ->setEmail($email)
                    ->save();

                $output->writeln("<info>{$key} of {$count} - {$email} has been added.</info>");
            }
        }
        return true;
    }

    /**
     * Remove all illegal characters from email and validates it.
     *
     * @param string $email
     * @return bool
     */
    protected function isValidEmail(string $email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }
}